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
    <title><?php echo htmlspecialchars($restaurant['restaurant_name']); ?> - Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include("header.php") ?>

    <div class="container mt-5">
        <div class="mb-4">
            <a href="javascript:history.back()" class="btn btn-secondary"> <- Back</a>
        </div>
        <h2>Menu for <?php echo htmlspecialchars($restaurant['restaurant_name']); ?></h2>
        <div class="row">
            <?php if ($menu_result->num_rows > 0): ?>
                <?php while ($menu_item = $menu_result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($menu_item['item_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($menu_item['description']); ?></p>
                                <p class="card-text"><strong>Price: $<?php echo number_format($menu_item['price'], 2); ?></strong></p>
                                <form action="add_to_cart.php" method="POST">
                                    <input type="hidden" name="menu_id" value="<?php echo $menu_item['menu_id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No menu items available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; 2023 Food Marketplaces. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>