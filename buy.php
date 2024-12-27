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
// $restaurant_id = $_SESSION['user_id']; // Assuming user_id is the restaurant_id for simplicity

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
        // $order_item = [
        //     "total" => $row['price'] * $row['quantity'],
        //     "" => $row["restaurant_id"]
        // ];
        // array_push($order_items, $order_item);
        if (isset($order_items[$row["restaurant_id"]])) {
            $order_items[$row["restaurant_id"]] += $row['price'] * $row['quantity'];
        } else {
            $order_items[$row["restaurant_id"]] = $row['price'] * $row['quantity'];
        }
        $total += $row['price'] * $row['quantity'];
    }
} else {
    echo "No items found in the cart.";
}

// Process the order
function processOrder($conn, $user_id, $order_items) {
    // Ensure payment method and delivery type are set
    if (isset($_POST['payment_method']) && isset($_POST['delivery_type']) && count($order_items) != 0 ) {
        $payment_method = $_POST['payment_method'];
        $delivery_type = $_POST['delivery_type'];
        var_dump($delivery_type);
        // $eta = date('Y-m-d H:i:s', strtotime('+30 minutes')); // Example ETA of 30 minutes

        // // Handle cash payment option
        // if ($payment_method === 'cash') {
        //     // Cash doesn't need additional validation
        //     $card_name = null;  // Null values for cash
        //     $card_number = null;
        //     $expiry_date = null;
        //     $cvv = null;
        // } 
        // // Handle credit card payment option
        // elseif ($payment_method === 'credit_card') {
        //     $card_name = $_POST['card_name'];
        //     $card_number = $_POST['card_number'];
        //     $expiry_date = $_POST['expiry_date'];
        //     $cvv = $_POST['cvv'];

        //     // Validate card information
        //     if (!validateCard($card_number, $expiry_date, $cvv)) {
        //         echo "Invalid card information."; // Display error if card validation fails
        //         return;
        //     }
        // }

        // // Insert order into the database
        // $hasSucceeded = true;
        // foreach($order_items as $restaurant_id => $total) {
        //     $insert_order_query = "INSERT INTO orders (customer_id, restaurant_id, total_price, payment_method, delivery_type, created_at) VALUES (?, ?, ?, ?, ?, ?)";
        //     $order_stmt = $conn->prepare($insert_order_query);
        //     $order_stmt->bind_param("iidsis", $user_id, $restaurant_id, $total, $payment_method, $delivery_type, $eta);

        //     if (!$order_stmt->execute()) {
        //         $hasSucceeded = false;
        //         echo "Error: " . $order_stmt->error; // Display error if order insertion fails
        //     }
        // }

        // if ($hasSucceeded) {
        //     clearCart($conn, $user_id);
        //     header("Location: marketplaces.php");
        //     exit();
        // }

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
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">Food Marketplaces</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">Cart</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="mb-4">
            <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
        </div>
        <h2 class="mb-4">Checkout</h2>
        <div id="cart-summary" class="mb-4">
            <h5>Order Summary</h5>
            <p>Total: $<?php echo number_format($total, 2); ?></p>
        </div>
        <form id="payment-form" method="POST">
            <div class="mb-3">
                <label for="payment-method" class="form-label">Payment Method</label>
                <select class="form-select" id="payment-method" name="payment_method" required>
                    <option value="cash">Cash</option>    
                    <option value="credit_card">Credit Card</option>
                </select>
            </div>
            <div id="card-fields" style="display: none;">
                <h5>Card Information</h5>
                <div class="mb-3">
                    <label for="card-name" class="form-label">Cardholder Name</label>
                    <input type="text" class="form-control" id="card-name" name="card_name" disabled required>
                </div>
                <div class="mb-3">
                    <label for="card-number" class="form-label">Card Number</label>
                    <input type="text" class="form-control" id="card-number" name="card_number" disabled required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="expiry-date" class="form-label">Expiry Date (MM/YY)</label>
                        <input type="text" class="form-control" id="expiry-date" name="expiry_date" disabled required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="cvv" class="form-label">CVV</label>
                        <input type="text" class="form-control" id="cvv" name="cvv" disabled required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="delivery-type" class="form-label">Delivery Type</label>
                <select class="form-select" id="delivery-type" name="delivery_type" required>
                    <option value="pickup">Pick-up</option>
                    <option value="delivery">Delivery</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Complete Purchase</button>
        </form>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; 2023 Food Marketplaces. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>
