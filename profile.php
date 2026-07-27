<?php
session_start();
require_once 'db.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$profile_msg = $profile_err = "";
$password_msg = $password_err = "";

// 1. Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Handle profile details update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $first_name = trim($_POST["first_name"]);
    $last_name  = trim($_POST["last_name"]);
    $email      = trim($_POST["email"]);
    $phone      = !empty($_POST["phone"]) ? trim($_POST["phone"]) : NULL;
    $gender     = !empty($_POST["gender"]) ? $_POST["gender"] : NULL;

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $profile_err = "First Name, Last Name, and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_err = "Invalid email format.";
    } else {
        // Check if email is already used by another user
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $profile_err = "Email is already in use by another account.";
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, gender = ? WHERE id = ?");
            $update_stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $gender, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION["user_name"] = $first_name;
                $profile_msg = "Profile updated successfully!";
                
                // Update local array data
                $user['first_name'] = $first_name;
                $user['last_name']  = $last_name;
                $user['email']      = $email;
                $user['phone']      = $phone;
                $user['gender']     = $gender;
            } else {
                $profile_err = "Error updating profile.";
            }
            $update_stmt->close();
        }
        $check_stmt->close();
    }
}

// 3. Handle password change request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    $current_password = $_POST["current_password"];
    $new_password     = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_err = "All password fields are required.";
    } elseif (!password_verify($current_password, $user["password"])) {
        $password_err = "Current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $password_err = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $password_err = "New passwords do not match.";
    } else {
        $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
        $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $pass_stmt->bind_param("si", $hashed_new, $user_id);
        
        if ($pass_stmt->execute()) {
            $password_msg = "Password changed successfully!";
            $user["password"] = $hashed_new;
        } else {
            $password_err = "Error updating password.";
        }
        $pass_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - AuthSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">AuthSystem</a>
        <div>
            <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
            <a href="profile.php" class="btn btn-primary me-2">My Profile</a>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <!-- Edit Profile Form -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($profile_msg)): ?>
                        <div class="alert alert-success"><?php echo $profile_msg; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($profile_err)): ?>
                        <div class="alert alert-danger"><?php echo $profile_err; ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="female" <?php echo (($user['gender'] ?? '') == 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="male" <?php echo (($user['gender'] ?? '') == 'male') ? 'selected' : ''; ?>>Male</option>
                            </select>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary w-100 fw-bold">Save Profile Changes</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($password_msg)): ?>
                        <div class="alert alert-success"><?php echo $password_msg; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($password_err)): ?>
                        <div class="alert alert-danger"><?php echo $password_err; ?></div>
                    <?php endif; ?>

                    <form action="profile.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-danger w-100 fw-bold">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>