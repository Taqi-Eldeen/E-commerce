<?php
include 'db_connection.php';
session_start();

// Check if the user is logged in and is a restaurant owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];

    // Update the order status to completed
    $update_query = "UPDATE orders SET status = 'completed' WHERE order_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        header("Location: restaurant_pending_orders.php"); // Redirect back to the orders page
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?> 