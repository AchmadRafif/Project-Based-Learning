<?php
session_start();
include "config.php";

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

// Get order data - pastikan milik user yang login
$orderQuery = mysqli_query($conn, "
    SELECT * FROM orders 
    WHERE id = '$order_id' AND user_id = '$user_id'
");

if (mysqli_num_rows($orderQuery) === 0) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$order = mysqli_fetch_assoc($orderQuery);

// Get order items
$itemsQuery = mysqli_query($conn, "
    SELECT * FROM order_items 
    WHERE order_id = '$order_id'
");

$items = [];
while ($row = mysqli_fetch_assoc($itemsQuery)) {
    $items[] = $row;
}

echo json_encode([
    'success' => true,
    'order' => $order,
    'items' => $items
]);