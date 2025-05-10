<?php
include 'db_connection.php'; // Include the database connection file
session_start(); // Start the session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if email and password are set in the POST request
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Get form data
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check if the user exists
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role']; // Store the role for later use
                $_SESSION['restaurant_id'] = $user['restaurant_id'];
                
                // Redirect to the restaurant pending orders page after successful login
                if ($_SESSION['role'] === 'restaurant') {
                    header("Location: restaurant_pending_orders.php");
                } else {
                    header("Location: marketplaces.php");
                }
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user found with that email.";
        }
    } else {
        echo "Email and password are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Login</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Don't have an account? <a href="register.php">Sign up here</a>.</p>
    </div>
</body>
</html>