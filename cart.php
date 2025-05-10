<?php
include 'db_connection.php';
session_start();

$user_id = $_SESSION['user_id']; // Assuming user is logged in

$query = "SELECT c.cart_id, m.item_name, m.price, c.quantity 
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
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include("header.php") ?>

    <div class="container mt-5">
        <div class="mb-4">
            <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
        </div>
        <h2>Your Shopping Cart</h2>
        <div id="cart-items">
            <?php if ($result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): 
                            $item_total = $row['price'] * $row['quantity'];
                            $total += $item_total; // Calculate total
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td>$<?php echo number_format($item_total, 2); ?></td>
                                <td>
                                    <form action="remove_from_cart.php" method="POST">
                                        <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                                        <button type="submit" class="btn btn-danger">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <h4>Total: $<?php echo number_format($total, 2); ?></h4>
                <div class="mt-4">
                    <a href="buy.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            <?php else: ?>
                <p class="text-muted">Your cart is currently empty.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; 2023 Food Marketplaces. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>