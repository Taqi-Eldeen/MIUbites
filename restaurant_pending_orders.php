<?php
include 'db_connection.php';
session_start();

// echo 'Session User ID: ' . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . '<br>';
// echo 'Session Role: ' . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set') . '<br>';
// echo 'Session Restaurant ID: ' . (isset($_SESSION['restaurant_id']) ? $_SESSION['restaurant_id'] : 'Not set') . '<br>';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'restaurant') {
    header("Location: index.php");
    exit();
}

$query = "SELECT o.order_id, o.customer_id, o.restaurant_id, o.total_price, o.payment_method, o.delivery_type, o.created_at, r.restaurant_name 
          FROM orders o
          JOIN restaurants r ON o.restaurant_id = r.restaurant_id
          WHERE o.status = 'processing' ";

if (isset($_SESSION['restaurant_id'])) {
    $restaurant_id = $_SESSION['restaurant_id'];
    $query .= "AND r.restaurant_id = $restaurant_id";
    $name_query = "SELECT restaurant_name FROM restaurants WHERE restaurant_id = $restaurant_id";
    $stmt = $conn->prepare($name_query);
    $stmt->execute();
    $result = $stmt->get_result();
    $restaurant_name = $result->fetch_assoc()["restaurant_name"];
}


$stmt = $conn->prepare($query);


// echo "Executing query: $query<br>";

if (!$stmt->execute()) {
    echo "Error executing query: " . $stmt->error;
    exit();
}

$result = $stmt->get_result();
// echo 'Number of orders: ' . $result->num_rows . '<br>'; 
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
    <?php include("header.php") ?>
    <div class="container mt-5">
        <h2>Pending Orders <?php echo isset($restaurant_name) ? "For $restaurant_name" : "Across All Restaurants" ?></h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer ID</th>
                    <?php if (!isset($restaurant_name)) echo '<th>Restaurant</th>' ?>
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
                    echo '<tr><td colspan="8" class="text-muted">No pending orders at the moment.</td></tr>';
                } else {
                    // Loop through each order and display it in the table
                    while ($order = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $order['order_id'] . '</td>';
                        echo '<td>' . $order['customer_id'] . '</td>';
                        if (!isset($restaurant_name)) echo '<td>' . htmlspecialchars($order['restaurant_name']) . '</td>';
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
