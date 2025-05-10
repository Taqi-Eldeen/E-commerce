<?php
include 'db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $menu_id = $_POST['menu_id'];
    $string_size = $_POST['string_size'];
    $number_size = $_POST['number_size'];

    $size;
    if (isset($number_size))
        $size = strval($number_size);
    else 
        $size = $string_size;

    // Check if the item is already in the cart
    $check_query = "SELECT * FROM cart WHERE user_id = ? AND menu_id = ? AND size = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("iis", $user_id, $menu_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Item already in cart, update quantity
        $update_query = "UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND menu_id = ? AND size = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("iis", $user_id, $menu_id, $size);
        $update_stmt->execute();
    } else {
        // Item not in cart, insert new record
        $insert_query = "INSERT INTO cart (user_id, menu_id, size) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iis", $user_id, $menu_id, $size);
        $insert_stmt->execute();
    }

    header("Location: cart.php");
    exit();
}
