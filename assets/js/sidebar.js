document.addEventListener('DOMContentLoaded', function() {
    // Get current page URL
    const currentPath = window.location.pathname;
    console.log('Current Path:', currentPath);
    
    // Remove all active classes first
    document.querySelectorAll('.sidebar .nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Function to activate a menu item by URL pattern
    function activateMenuItem(urlPattern) {
        const link = document.querySelector(`.sidebar .nav-item a[href*="${urlPattern}"]`);
        if (link) {
            const navItem = link.closest('.nav-item');
            if (navItem) {
                navItem.classList.add('active');
                return true;
            }
        }
        return false;
    }
    
    // Check for logout page
    if (currentPath.includes('includes/logout.php')) {
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.closest('.nav-item').classList.add('active');
        }
        return;
    }
    
    // Check for module pages first (including index pages)
    const modules = ['sales', 'inventory', 'ledger'];
    let moduleFound = false;
    
    // First check if we're on a module's index page
    for (const module of modules) {
        if (currentPath.endsWith(`/${module}/index.php`) || 
            currentPath.endsWith(`/${module}/`)) {
            if (activateMenuItem(`/${module}/index.php`)) {
                return; // Exit if we found and activated a module index
            }
        }
    }
    
    // Then check for other module pages
    for (const module of modules) {
        if (currentPath.includes(`/${module}/`)) {
            if (activateMenuItem(`/${module}/index.php`)) {
                moduleFound = true;
                break;
            }
        }
    }
    
    // If we're not on a module page, check for dashboard
    if (!moduleFound && (currentPath.endsWith('index.php') || currentPath.endsWith('/') || currentPath.endsWith('/ABICO/'))) {
        activateMenuItem('index.php');
        return;
    }
    
    // If no module found, try to match by first path segment
    if (!moduleFound) {
        const firstSegment = currentPath.split('/').filter(Boolean)[1]; // Get second segment (after ABICO)
        if (firstSegment && modules.includes(firstSegment)) {
            activateMenuItem(`/${firstSegment}/index.php`);
        }
    }
});
