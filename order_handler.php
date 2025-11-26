<?php
session_start();
include "config.php";

header('Content-Type: application/json');

// Cek admin login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized - Please login as admin']);
    exit;
}

// Get action from either POST or GET
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// Log untuk debugging
error_log("Order Handler - Action: " . $action);

try {
    switch ($action) {
        case 'get_order_detail':
            // Ambil detail pesanan
            if (!isset($_GET['order_id'])) {
                echo json_encode(['success' => false, 'message' => 'Order ID tidak ditemukan']);
                exit;
            }

            $order_id = intval($_GET['order_id']);
            error_log("Getting order detail for ID: " . $order_id);

            // Get order info dengan prepared statement
            $orderQuery = mysqli_prepare($conn, "
                SELECT o.*, u.name as customer_name, u.email as customer_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
            ");
            
            if (!$orderQuery) {
                error_log("Prepare failed: " . mysqli_error($conn));
                echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }

            mysqli_stmt_bind_param($orderQuery, "i", $order_id);
            mysqli_stmt_execute($orderQuery);
            $orderResult = mysqli_stmt_get_result($orderQuery);
            $order = mysqli_fetch_assoc($orderResult);

            if (!$order) {
                error_log("Order not found: " . $order_id);
                echo json_encode(['success' => false, 'message' => 'Order tidak ditemukan']);
                exit;
            }

            // Get order items dengan prepared statement
            $itemsQuery = mysqli_prepare($conn, "
                SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC
            ");
            
            if (!$itemsQuery) {
                error_log("Items query prepare failed: " . mysqli_error($conn));
                echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }

            mysqli_stmt_bind_param($itemsQuery, "i", $order_id);
            mysqli_stmt_execute($itemsQuery);
            $itemsResult = mysqli_stmt_get_result($itemsQuery);

            $items = [];
            while ($row = mysqli_fetch_assoc($itemsResult)) {
                $items[] = $row;
            }

            error_log("Order found with " . count($items) . " items");

            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $items
            ]);
            break;

        case 'update_status':
            // Update status pesanan
            if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
                echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
                exit;
            }

            $order_id = intval($_POST['order_id']);
            $new_status = mysqli_real_escape_string($conn, $_POST['status']);
            
            error_log("Updating order $order_id to status: $new_status");

            // Validasi status
            $valid_statuses = ['pending', 'processing', 'completed', 'cancelled'];
            if (!in_array($new_status, $valid_statuses)) {
                echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                exit;
            }

            $updateQuery = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
            
            if (!$updateQuery) {
                error_log("Update prepare failed: " . mysqli_error($conn));
                echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
                exit;
            }

            mysqli_stmt_bind_param($updateQuery, "si", $new_status, $order_id);
            mysqli_stmt_execute($updateQuery);

            if (mysqli_stmt_affected_rows($updateQuery) > 0) {
                error_log("Status updated successfully");
                echo json_encode([
                    'success' => true,
                    'message' => 'Status berhasil diupdate',
                    'new_status' => $new_status
                ]);
            } else {
                error_log("No rows affected");
                echo json_encode(['success' => false, 'message' => 'Gagal update status atau tidak ada perubahan']);
            }
            break;

        case 'get_stats':
            // Ambil statistik
            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

            // Pesanan hari ini
            $todayQuery = mysqli_prepare($conn, "
                SELECT COUNT(*) as count, COALESCE(SUM(total_price), 0) as total
                FROM orders 
                WHERE DATE(created_at) = ?
            ");
            mysqli_stmt_bind_param($todayQuery, "s", $date);
            mysqli_stmt_execute($todayQuery);
            $todayResult = mysqli_stmt_get_result($todayQuery);
            $todayStats = mysqli_fetch_assoc($todayResult);

            // Pending orders
            $pendingQuery = mysqli_query($conn, "
                SELECT COUNT(*) as count 
                FROM orders 
                WHERE status IN ('pending', 'processing')
            ");
            $pendingStats = mysqli_fetch_assoc($pendingQuery);

            echo json_encode([
                'success' => true,
                'today_orders' => $todayStats['count'],
                'today_revenue' => $todayStats['total'],
                'pending_orders' => $pendingStats['count']
            ]);
            break;

        default:
            error_log("Invalid action: " . $action);
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
} catch (Exception $e) {
    error_log("Exception in order_handler.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>