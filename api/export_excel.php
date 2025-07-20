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

$type = $_GET['type'] ?? '';
$selected_month = $_GET['month'] ?? date('Y-m');

// Validate month format (YYYY-MM)
if (!preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = date('Y-m');
}

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $type . '_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

try {
    switch ($type) {
        case 'monthly_sales':
            // Get selected month's daily sales
            $current_month = $selected_month;
            $stmt = $db->prepare("
                SELECT 
                    DATE(created_at) as sale_date,
                    DAY(created_at) as day,
                    COUNT(*) as transaction_count,
                    SUM(total_amount) as daily_total
                FROM transactions 
                WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
                GROUP BY DATE(created_at), DAY(created_at)
                ORDER BY DATE(created_at)
            ");
            $stmt->execute([$current_month]);
            $daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Output Excel content
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th colspan='4' style='text-align: center; font-weight: bold; background-color: #4CAF50; color: white;'>Monthly Sales Report - " . date('F Y', strtotime($selected_month . '-01')) . "</th>";
            echo "</tr>";
            echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
            echo "<th>Date</th>";
            echo "<th>Day</th>";
            echo "<th>Transactions</th>";
            echo "<th>Total Sales (₱)</th>";
            echo "</tr>";
            
            $total_sales = 0;
            $total_transactions = 0;
            
            foreach ($daily_sales as $sale) {
                echo "<tr>";
                echo "<td>" . date('M d, Y', strtotime($sale['sale_date'])) . "</td>";
                echo "<td>" . $sale['day'] . "</td>";
                echo "<td>" . $sale['transaction_count'] . "</td>";
                echo "<td>" . number_format($sale['daily_total'], 2) . "</td>";
                echo "</tr>";
                
                $total_sales += $sale['daily_total'];
                $total_transactions += $sale['transaction_count'];
            }
            
            // Add totals row
            echo "<tr style='background-color: #e6f3ff; font-weight: bold;'>";
            echo "<td colspan='2'>TOTAL</td>";
            echo "<td>" . $total_transactions . "</td>";
            echo "<td>" . number_format($total_sales, 2) . "</td>";
            echo "</tr>";
            echo "</table>";
            break;
            
        case 'popular_items':
            // Get top 10 popular items for selected month
            $current_month = $selected_month;
            $stmt = $db->prepare("
                SELECT 
                    si.product_name,
                    SUM(si.quantity) as total_quantity,
                    SUM(si.subtotal) as total_sales,
                    AVG(si.price) as avg_price,
                    COUNT(DISTINCT si.transaction_id) as transaction_count
                FROM sale_items si
                JOIN transactions t ON si.transaction_id = t.id
                WHERE DATE_FORMAT(t.created_at, '%Y-%m') = ?
                GROUP BY si.product_id, si.product_name
                ORDER BY total_quantity DESC
                LIMIT 10
            ");
            $stmt->execute([$current_month]);
            $popular_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Output Excel content
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th colspan='6' style='text-align: center; font-weight: bold; background-color: #FF9800; color: white;'>Top 10 Popular Items - " . date('F Y', strtotime($selected_month . '-01')) . "</th>";
            echo "</tr>";
            echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
            echo "<th>Rank</th>";
            echo "<th>Product Name</th>";
            echo "<th>Quantity Sold</th>";
            echo "<th>Total Sales (₱)</th>";
            echo "<th>Average Price (₱)</th>";
            echo "<th>Transactions</th>";
            echo "</tr>";
            
            $rank = 1;
            $total_quantity = 0;
            $total_revenue = 0;
            
            foreach ($popular_items as $item) {
                echo "<tr>";
                echo "<td>" . $rank . "</td>";
                echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                echo "<td>" . $item['total_quantity'] . "</td>";
                echo "<td>" . number_format($item['total_sales'], 2) . "</td>";
                echo "<td>" . number_format($item['avg_price'], 2) . "</td>";
                echo "<td>" . $item['transaction_count'] . "</td>";
                echo "</tr>";
                
                $total_quantity += $item['total_quantity'];
                $total_revenue += $item['total_sales'];
                $rank++;
            }
            
            // Add totals row
            echo "<tr style='background-color: #e6f3ff; font-weight: bold;'>";
            echo "<td colspan='2'>TOTAL (Top 10)</td>";
            echo "<td>" . $total_quantity . "</td>";
            echo "<td>" . number_format($total_revenue, 2) . "</td>";
            echo "<td>-</td>";
            echo "<td>-</td>";
            echo "</tr>";
            echo "</table>";
            break;
            
        case 'all_sales':
            // Get all transactions for selected month with details
            $current_month = $selected_month;
            $stmt = $db->prepare("
                SELECT 
                    t.id,
                    t.customer_name,
                    t.total_amount,
                    t.payment_method,
                    t.payment_status,
                    t.created_at,
                    GROUP_CONCAT(
                        CONCAT(si.product_name, ' (', si.quantity, ' pcs @ ₱', si.price, ')')
                        SEPARATOR '; '
                    ) as items
                FROM transactions t
                LEFT JOIN sale_items si ON t.id = si.transaction_id
                WHERE DATE_FORMAT(t.created_at, '%Y-%m') = ?
                GROUP BY t.id
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$current_month]);
            $all_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Output Excel content
            echo "<table border='1'>";
            echo "<tr>";
            echo "<th colspan='7' style='text-align: center; font-weight: bold; background-color: #2196F3; color: white;'>All Sales Transactions - " . date('F Y', strtotime($selected_month . '-01')) . "</th>";
            echo "</tr>";
            echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>";
            echo "<th>Transaction ID</th>";
            echo "<th>Date & Time</th>";
            echo "<th>Customer</th>";
            echo "<th>Items Purchased</th>";
            echo "<th>Total Amount (₱)</th>";
            echo "<th>Payment Method</th>";
            echo "<th>Status</th>";
            echo "</tr>";
            
            $grand_total = 0;
            
            foreach ($all_sales as $sale) {
                echo "<tr>";
                echo "<td>" . $sale['id'] . "</td>";
                echo "<td>" . date('M d, Y h:i A', strtotime($sale['created_at'])) . "</td>";
                echo "<td>" . htmlspecialchars($sale['customer_name']) . "</td>";
                echo "<td>" . htmlspecialchars($sale['items']) . "</td>";
                echo "<td>" . number_format($sale['total_amount'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($sale['payment_method']) . "</td>";
                echo "<td>" . ucfirst($sale['payment_status']) . "</td>";
                echo "</tr>";
                
                $grand_total += $sale['total_amount'];
            }
            
            // Add totals row
            echo "<tr style='background-color: #e6f3ff; font-weight: bold;'>";
            echo "<td colspan='4'>GRAND TOTAL (" . count($all_sales) . " transactions)</td>";
            echo "<td>" . number_format($grand_total, 2) . "</td>";
            echo "<td colspan='2'>-</td>";
            echo "</tr>";
            echo "</table>";
            break;
            
        default:
            echo "<h3>Invalid export type</h3>";
            break;
    }
} catch (Exception $e) {
    echo "<h3>Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>
