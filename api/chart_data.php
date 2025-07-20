<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/session.php';

$session = new session();
$session->init();

// Check if user is logged in
if (!$session->get('login')) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$database = new dbconn();
$db = $database->getConnection();

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$selected_month = $_GET['month'] ?? date('Y-m');

// Validate month format (YYYY-MM)
if (!preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = date('Y-m');
}

try {
    switch ($type) {
        case 'monthly_sales':
            // Get selected month's daily sales
            $current_month = $selected_month;
            $stmt = $db->prepare("
                SELECT 
                    DAY(created_at) as day,
                    SUM(total_amount) as daily_total
                FROM transactions 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
                GROUP BY DAY(created_at)
                ORDER BY DAY(created_at)
            ");
            $stmt->execute([$current_month]);
            $daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug logging
            error_log('Chart API - Selected month: ' . $selected_month);
            error_log('Chart API - Query month: ' . $current_month);
            error_log('Chart API - Daily sales count: ' . count($daily_sales));
            
            // Create array for all days of the month (1-31)
            $days_in_month = date('t', strtotime($selected_month . '-01')); // Get number of days in selected month
            $sales_data = array_fill(1, $days_in_month, 0);
            
            // Fill in actual sales data
            foreach ($daily_sales as $sale) {
                $sales_data[$sale['day']] = (float)$sale['daily_total'];
            }
            
            echo json_encode([
                'labels' => range(1, $days_in_month),
                'data' => array_values($sales_data),
                'month' => date('F Y', strtotime($selected_month . '-01'))
            ]);
            break;
            
        case 'popular_items':
            // Get top 10 popular items for selected month
            $current_month = $selected_month;
            $stmt = $db->prepare("
                SELECT 
                    si.product_name,
                    SUM(si.quantity) as total_quantity,
                    SUM(si.subtotal) as total_sales
                FROM sale_items si
                JOIN transactions t ON si.transaction_id = t.id
                WHERE DATE_FORMAT(t.created_at, '%Y-%m') = ?
                GROUP BY si.product_id, si.product_name
                ORDER BY total_quantity DESC
                LIMIT 10
            ");
            $stmt->execute([$current_month]);
            $popular_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $labels = [];
            $quantities = [];
            $sales = [];
            
            foreach ($popular_items as $item) {
                $labels[] = $item['product_name'];
                $quantities[] = (int)$item['total_quantity'];
                $sales[] = (float)$item['total_sales'];
            }
            
            echo json_encode([
                'labels' => $labels,
                'quantities' => $quantities,
                'sales' => $sales,
                'month' => date('F Y', strtotime($selected_month . '-01'))
            ]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid chart type']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
