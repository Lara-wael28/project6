<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - AuthSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">AuthSystem</a>
        <div>
            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
                <a href="profile.php" class="btn btn-outline-light me-2">My Profile</a>
                <a href="logout.php" class="btn btn-outline-danger">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container text-center py-5">
    <div class="p-5 mb-4 bg-white rounded-3 shadow-sm border mt-4">
        <h1 class="display-5 fw-bold text-dark">User Authentication System</h1>
        <p class="lead mt-3 text-secondary">A web application for secure user registration, login, and profile management.</p>
        <hr class="my-4">
        
        <?php if (!isset($_SESSION["user_id"])): ?>
            <p class="mb-4 text-muted">Please register or log in to access your account.</p>
            <a href="register.php" class="btn btn-primary btn-lg me-2 px-4">Register</a>
            <a href="login.php" class="btn btn-outline-secondary btn-lg px-4">Login</a>
        <?php else: ?>
            <p class="text-success fw-semibold fs-5 mb-4">Logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            <a href="dashboard.php" class="btn btn-primary btn-lg px-4">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>