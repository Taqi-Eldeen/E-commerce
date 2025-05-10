<?php
include 'db_connection.php'; // Include the database connection file
session_start(); // Start the session

// Fetch collections from the database
$collections = [];
$result = $conn->query("SELECT restaurant_id as id, restaurant_name as name FROM restaurants");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $collections[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $collection_id = $_POST['collection_id'];
    $image = "";

    $stmt = $conn->prepare("INSERT INTO menus (item_name, description, price, restaurant_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $name, $description, $price, $collection_id);
    
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Item added successfully!</div>";
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $collection_name = "unknown";
            foreach ($collections as $collection) {
                if ($collection['id'] == $collection_id) {
                    $collection_name = $collection['name'];
                    break;
                }
            }
            $target_dir = "images/" . $collection_name . "/";
            $target_file = $target_dir . $stmt->insert_id . ".png";
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ["jpg", "jpeg", "png"];

            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image = $target_file;
                }
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Fashion Item - MIU Fashion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-4">Add New Fashion Item</h2>
                <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="name" class="form-label required">Item Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="invalid-feedback">
                            Please enter an item name
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="form-label required">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        <div class="invalid-feedback">
                            Please enter a description
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="price" class="form-label required">Price ($)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                        <div class="invalid-feedback">
                            Please enter a valid price
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="collection" class="form-label required">Collection</label>
                        <select class="form-select" id="collection" name="collection_id" required>
                            <option value="">Select a collection</option>
                            <?php foreach ($collections as $collection) { ?>
                                <option value="<?php echo $collection['id']; ?>">
                                    <?php echo htmlspecialchars($collection['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div class="invalid-feedback">
                            Please select a collection
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="image" class="form-label">Upload Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Item
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include("footer.php") ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <script>
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