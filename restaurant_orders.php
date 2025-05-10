<?php
include 'db_connection.php';
session_start();

// Debugging session data
echo 'Session User ID: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . '<br>';
echo 'Session Role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set') . '<br>';

// Check if the user is logged in and is a restaurant owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header("Location: index.php");
    exit();
}

$restaurant_id = $_SESSION['user_id']; // Assuming user_id is the restaurant_id for simplicity
echo 'Restaurant ID: ' . $restaurant_id . '<br>'; // Debugging restaurant_id

// Fetch pending orders for the restaurant
$query = "SELECT o.order_id, o.customer_id, o.total_price, o.payment_method, o.delivery_type, o.created_at 
          FROM orders o 
          WHERE o.restaurant_id = ? AND o.status = 'processing'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $restaurant_id);

// Debugging the query execution
echo "Executing query: $query<br>";

if (!$stmt->execute()) {
    echo "Error executing query: " . $stmt->error;
    exit();
}

$result = $stmt->get_result();
echo 'Number of orders: ' . $result->num_rows . '<br>'; // Debugging the result count
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Pending Orders</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Delivery Type</th>
                    <th>Order Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result->num_rows === 0) {
                    echo '<tr><td colspan="7" class="text-muted">No pending orders at the moment.</td></tr>';
                } else {
                    // Loop through each order and display it in the table
                    while ($order = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $order['order_id'] . '</td>';
                        echo '<td>' . $order['customer_id'] . '</td>';
                        echo '<td>$' . number_format($order['total_price'], 2) . '</td>';
                        echo '<td>' . htmlspecialchars($order['payment_method']) . '</td>';
                        echo '<td>' . htmlspecialchars($order['delivery_type']) . '</td>';
                        echo '<td>' . $order['created_at'] . '</td>';
                        echo '<td>
                                <form action="complete_order.php" method="POST">
                                    <input type="hidden" name="order_id" value="' . $order['order_id'] . '">
                                    <button type="submit" class="btn btn-success">Done</button>
                                </form>
                              </td>';
                        echo '</tr>';
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; 2023 Food Marketplaces. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
