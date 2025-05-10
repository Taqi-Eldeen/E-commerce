<?php
include 'db_connection.php'; // Include the database connection file
session_start(); // Start the session

// Fetch all active restaurants from the database
$query = "SELECT * FROM restaurants WHERE status = 'active'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIU Fashion - Clothing Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>

<body>
    <?php include("header.php") ?>

    <div class="container mt-5">
        <h2 class="mb-4">Featured Collections</h2>
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($restaurant = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo file_exists("images/" . $restaurant['restaurant_name'] . '.png') ? "images/" . $restaurant['restaurant_name'] . ".png" : 'images/MIU_Bites.png' ?>" class="card-img-top" alt="<?php echo htmlspecialchars($restaurant['restaurant_name']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($restaurant['restaurant_name']); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars($restaurant['description']); ?></p>
                                <a href="menu.php?restaurant_id=<?php echo $restaurant['restaurant_id']; ?>" class="btn btn-primary mt-auto">View Collection</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No collections available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include("footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>