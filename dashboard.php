<?php
session_start();
require_once 'db.php';

// Auth guard
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch current user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, gender FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AuthSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">AuthSystem</a>
        <div>
            <a href="dashboard.php" class="btn btn-primary me-2">Dashboard</a>
            <a href="profile.php" class="btn btn-outline-light me-2">My Profile</a>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="p-5 mb-4 bg-white rounded-3 shadow-sm border">
        <h1 class="display-6 fw-bold text-dark">Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
        <p class="fs-6 text-muted">User account summary and settings.</p>
        <hr class="my-4">
        
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block mb-1">Email</small>
                    <span class="fw-bold"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block mb-1">Phone</small>
                    <span class="fw-bold"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <small class="text-muted d-block mb-1">Gender</small>
                    <span class="fw-bold"><?php echo htmlspecialchars(ucfirst($user['gender'] ?? 'N/A')); ?></span>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="profile.php" class="btn btn-primary">Edit Profile & Password</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>