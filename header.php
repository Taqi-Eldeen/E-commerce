<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="marketplaces.php">
            <h2>MIU Fashion</h2>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <?php if ($userRole == "customer"): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="marketplaces.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">Shopping Cart</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="buy.php">Checkout</a>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="marketplaces.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="restaurant_pending_orders.php">Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="list_items.php">List Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_fashion_item.php">Add Items</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>