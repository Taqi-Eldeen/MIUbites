<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role']; 
?>
<nav class="navbar navbar-expand-lg bg-white sticky-top border">
    <div class="container">
        <a class="navbar-brand" href="#">
            <div style="position: relative; height: 50px; width: 200px;">
                <img src="images/MIU_bites.png" alt="Centered Image" height="250" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            </div>
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
                            <a class="nav-link" href="cart.php">Cart</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="buy.php">Checkout</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>