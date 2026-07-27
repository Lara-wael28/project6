<?php
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

$first_name_err = $last_name_err = $email_err = $password_err = $confirm_password_err = "";
$register_err = "";
$first_name = $last_name = $email = $phone = $gender = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validate First Name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "First name is required.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // 2. Validate Last Name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Last name is required.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // 3. Validate Email & Check Duplicate
    if (empty(trim($_POST["email"]))) {
        $email_err = "Email is required.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $email = trim($_POST["email"]);
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $email_err = "Email is already registered.";
        }
        $stmt->close();
    }

    // 4. Validate Password
    if (empty($_POST["password"])) {
        $password_err = "Password is required.";
    } elseif (strlen($_POST["password"]) < 6) {
        $password_err = "Password must be at least 6 characters.";
    } else {
        $password = $_POST["password"];
    }

    // 5. Validate Confirm Password
    if (empty($_POST["confirm_password"])) {
        $confirm_password_err = "Please confirm password.";
    } else {
        if ($_POST["password"] !== $_POST["confirm_password"]) {
            $confirm_password_err = "Passwords do not match.";
        }
    }

    $phone  = !empty($_POST["phone"]) ? trim($_POST["phone"]) : NULL;
    $gender = !empty($_POST["gender"]) ? $_POST["gender"] : NULL;

    // Save user data if no validation errors exist
    if (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, gender) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $first_name, $last_name, $email, $hashed_password, $phone, $gender);

        if ($stmt->execute()) {
            $_SESSION['success_msg'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit();
        } else {
            $register_err = "Error during registration. Please try again.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AuthSystem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">AuthSystem</a>
        <div>
            <a href="login.php" class="btn btn-outline-light me-2">Login</a>
            <a href="register.php" class="btn btn-primary">Register</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0 fs-4">Create Account</h4>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($register_err)): ?>
                        <div class="alert alert-danger py-2"><?php echo $register_err; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($first_name); ?>">
                                <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($last_name); ?>">
                                <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">Select Gender</option>
                                    <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                            <div class="invalid-feedback"><?php echo $password_err; ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                            <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2">Register</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>