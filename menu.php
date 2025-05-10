<?php
include 'db_connection.php'; // Include the database connection file
session_start(); // Start the session

// Check if restaurant_id is set in the URL
if (isset($_GET['restaurant_id'])) {
    $restaurant_id = intval($_GET['restaurant_id']); // Get the restaurant ID from the URL

    // Fetch restaurant details
    $restaurant_query = "SELECT * FROM restaurants WHERE restaurant_id = ?";
    $stmt = $conn->prepare($restaurant_query);
    $stmt->bind_param("i", $restaurant_id);
    $stmt->execute();
    $restaurant_result = $stmt->get_result();

    // Check if the restaurant exists
    if ($restaurant_result->num_rows > 0) {
        $restaurant = $restaurant_result->fetch_assoc(); // Fetch the restaurant details

        $size_type = explode(" ", $restaurant['sizes']);

        $size_strings = [
            [
                "label" => "Small",
            ],
            [
                "label" => "Medium",
            ],
            [
                "label" => "Large",
            ],
            [
                "label" => "Extra Large",
            ],
        ];

        $size_is_numerical = false;
        $numerical_range_min = 0;
        $numerical_range_max = 0;
        if ($size_type[0] == 'n') {
            $size_is_numerical = true;
            $numerical_range_min = intval(explode("-", $size_type[1])[0]);
            $numerical_range_max = intval(explode("-", $size_type[1])[1]);
        } 


        // Fetch menu items for the restaurant
        $menu_query = "SELECT * FROM menus WHERE restaurant_id = ?";
        $menu_stmt = $conn->prepare($menu_query);
        $menu_stmt->bind_param("i", $restaurant_id);
        $menu_stmt->execute();
        $menu_result = $menu_stmt->get_result();
    } else {
        // Redirect to marketplaces if the restaurant does not exist
        header("Location: marketplaces.php");
        exit();
    }
} else {
    // Redirect to marketplaces if no restaurant_id is provided
    header("Location: marketplaces.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['restaurant_name']); ?> - Fashion Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <?php include("header.php") ?>

    <div class="container mt-5">
        <div class="mb-4">
            <a href="javascript:history.back()" class="btn btn-secondary"> <- Back</a>
        </div>
        <h2 class="mb-4"><?php echo htmlspecialchars($restaurant['restaurant_name']); ?> Collection</h2>
        <div class="row">
            <?php if ($menu_result->num_rows > 0): ?>
                <?php while ($item = $menu_result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo file_exists("images/". $restaurant['restaurant_name'] . "/" . $item['menu_id'] . '.png') ? "images/". $restaurant['restaurant_name'] . "/" . $item['menu_id'] . '.png': 'images/M.png' ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($item['item_name']); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo htmlspecialchars($item['description']); ?></p>
                                <p class="card-text">
                                    <strong>Price:</strong> $<?php echo number_format($item['price'], 2); ?>
                                </p>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <form action="add_to_cart.php" method="POST" class="mt-auto">
                                        <input type="hidden" name="menu_id" value="<?php echo $item['menu_id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                        <label for="size" class="form-label required">Size:</label>
                                        <?php if($size_is_numerical): ?>
                                            <input id="size" placeholder="28" class="form-control" type="number" name="number_size" min="<?php echo $numerical_range_min ?>" max="<?php echo $numerical_range_max ?>" required>
                                            <div class="invalid-feedback">
                                                Please enter a valid size between <?php echo $numerical_range_min ?> and <?php echo $numerical_range_max ?>
                                            </div>
                                        <?php else: ?>
                                            <select name="string_size" id="size" class="form-select" required>
                                                <?php foreach($size_strings as $size) : ?>
                                                    <option value="<?php echo $size['label'] ?>"><?php echo $size['label'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-primary mt-3">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <form action="login.php" method="GET" class="mt-auto">
                                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No items available in this collection.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include("footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>