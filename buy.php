<?php
include 'db_connection.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if session is not set
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Assuming user is logged in

// Retrieve the cart items and calculate the total price
$query = "SELECT m.price, c.quantity, m.restaurant_id
          FROM cart c 
          JOIN menus m ON c.menu_id = m.menu_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total = 0;
$order_items = [];

if ($result->num_rows > 0) {
    // $restaurant_id = $result["restaurant_id"];
    while ($row = $result->fetch_assoc()) {
        if (isset($order_items[$row["restaurant_id"]])) {
            $order_items[$row["restaurant_id"]] += $row['price'] * $row['quantity'];
        } else {
            $order_items[$row["restaurant_id"]] = $row['price'] * $row['quantity'];
        }
        $total += $row['price'] * $row['quantity'];
    }
} 

// Process the order
function processOrder($conn, $user_id, $order_items) {
    // Ensure payment method and delivery type are set
    if (isset($_POST['payment_method']) && isset($_POST['delivery_type']) && count($order_items) != 0 ) {
        $payment_method = $_POST['payment_method'];
        $delivery_type = $_POST['delivery_type'];
        $eta = date('Y-m-d H:i:s', strtotime('+30 minutes')); // Example ETA of 30 minutes

        // Handle cash payment option
        if ($payment_method === 'cash') {
            // Cash doesn't need additional validation
            $card_name = null;  // Null values for cash
            $card_number = null;
            $expiry_date = null;
            $cvv = null;
        } 
        // Handle credit card payment option
        elseif ($payment_method === 'credit_card') {
            $card_name = $_POST['card_name'];
            $card_number = $_POST['card_number'];
            $expiry_date = $_POST['expiry_date'];
            $cvv = $_POST['cvv'];

            // Validate card information
            if (!validateCard($card_number, $expiry_date, $cvv)) {
                echo "Invalid card information."; // Display error if card validation fails
                return;
            }
        }

        // Insert order into the database
        $hasSucceeded = true;
        foreach($order_items as $restaurant_id => $total) {
            $insert_order_query = "INSERT INTO orders (customer_id, restaurant_id, total_price, payment_method, delivery_type, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $order_stmt = $conn->prepare($insert_order_query);
            $order_stmt->bind_param("iidsss", $user_id, $restaurant_id, $total, $payment_method, $delivery_type, $eta);

            if (!$order_stmt->execute()) {
                $hasSucceeded = false;
                echo "Error: " . $order_stmt->error; // Display error if order insertion fails
            }
        }

        if ($hasSucceeded) {
            clearCart($conn, $user_id);
            header("Location: marketplaces.php");
            exit();
        }

    } else {
        echo "Payment method or delivery type not set."; // Debugging message
    }
}

// Validate card information (this is just a placeholder for actual validation)
function validateCard($card_number, $expiry_date, $cvv) {
    // Add your card validation logic here
    // For example, check if card number is valid, expiry date is not in the past, etc.
    return true; // Placeholder for actual validation
}

// Clear the cart after an order is processed
function clearCart($conn, $user_id) {
    $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $clear_stmt = $conn->prepare($clear_cart_query);
    $clear_stmt->bind_param("i", $user_id);
    $clear_stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process the order after form submission
    processOrder($conn, $user_id, $order_items);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MIU Fashion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <?php include("header.php") ?>

    <div class="container mt-5">
        <div class="mb-4">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <?php if (count($order_items) > 0) : ?>
                <form id="payment-form" method="POST" class="needs-validation" novalidate>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Payment Information</h4>
                            <div class="mb-4">
                                <label for="payment-method" class="form-label required">Payment Method</label>
                                <select class="form-select" id="payment-method" name="payment_method" required>
                                    <option value="">Select payment method</option>
                                    <option value="cash">Cash</option>    
                                    <option value="credit_card">Credit Card</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a payment method
                                </div>
                            </div>
                            <div id="card-fields" style="display: none;">
                                <div class="mb-4">
                                    <label for="card-name" class="form-label required">Cardholder Name</label>
                                    <input type="text" class="form-control" id="card-name" name="card_name" disabled required>
                                    <div class="invalid-feedback">
                                        Please enter the cardholder name
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="card-number" class="form-label required">Card Number</label>
                                    <input type="text" class="form-control" id="card-number" name="card_number" disabled required pattern="[0-9]{16}">
                                    <div class="invalid-feedback">
                                        Please enter a valid 16-digit card number
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label for="expiry-date" class="form-label required">Expiry Date (MM/YY)</label>
                                        <input type="text" class="form-control" id="expiry-date" name="expiry_date" disabled required pattern="(0[1-9]|1[0-2])\/([0-9]{2})">
                                        <div class="invalid-feedback">
                                            Please enter a valid expiry date (MM/YY)
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label for="cvv" class="form-label required">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" disabled required pattern="[0-9]{3,4}">
                                        <div class="invalid-feedback">
                                            Please enter a valid CVV
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Delivery Information</h4>
                            <div class="mb-4">
                                <label for="delivery-type" class="form-label required">Delivery Type</label>
                                <select class="form-select" id="delivery-type" name="delivery_type" required>
                                    <option value="">Select delivery type</option>
                                    <option value="pickup">Pick-up</option>
                                    <option value="delivery">Delivery</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a delivery type
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-cart-x display-1 text-muted"></i>
                            <p class="mt-3 text-muted">Your cart is currently empty.</p>
                            <a href="marketplaces.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Order Summary</h4>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <strong>Total</strong>
                            <strong>$<?php echo number_format($total, 2); ?></strong>
                        </div>
                        <?php if (count($order_items) > 0) : ?>
                            <button type="submit" form="payment-form" class="btn btn-primary w-100">Complete Purchase</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include("footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script>
        // Show card fields and enable the inputs only if credit card is selected
        document.getElementById('payment-method').addEventListener('change', function() {
            var cardFields = document.getElementById('card-fields');
            if (this.value === 'credit_card') {
                var cardInputs = document.querySelectorAll('#card-fields input')
                cardInputs.forEach(i => i.disabled = false)
                cardFields.style.display = 'block';
            } else {
                var cardInputs = document.querySelectorAll('#card-fields input')
                cardInputs.forEach(i => i.disabled = true)
                cardFields.style.display = 'none';
            }
        });

        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
