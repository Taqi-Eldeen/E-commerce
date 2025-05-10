<?php
include 'db_connection.php';
session_start();

$user_id = $_SESSION['user_id']; // Assuming user is logged in

$query = "SELECT c.cart_id, m.item_name, m.price, c.quantity, c.size
          FROM cart c 
          JOIN menus m ON c.menu_id = m.menu_id 
          WHERE c.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$total = 0; // Initialize total variable
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MIU Fashion</title>
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
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Your Shopping Cart</h2>
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Size</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): 
                                            $item_total = $row['price'] * $row['quantity'];
                                            $total += $item_total;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                                <td><?php echo $row['quantity']; ?></td>
                                                <td><?php echo $row['size']; ?></td>
                                                <td>$<?php echo number_format($item_total, 2); ?></td>
                                                <td>
                                                    <form action="remove_from_cart.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to remove this item?');">
                                                            <i class="bi bi-trash"></i> Remove
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-cart-x display-1 text-muted"></i>
                                <p class="mt-3 text-muted">Your cart is currently empty.</p>
                                <a href="marketplaces.php" class="btn btn-primary">Continue Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <?php if ($result->num_rows > 0): ?>
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
                        <a href="buy.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include("footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</body>
</html>