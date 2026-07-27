<?php
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

$email_err = $password_err = $login_err = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty($_POST["password"])) {
        $password_err = "Please enter password.";
    } else {
        $password = $_POST["password"];
    }

    // Authenticate user
    if (empty($email_err) && empty($password_err)) {
        $stmt = $conn->prepare("SELECT id, first_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION["user_id"] = $user['id'];
                $_SESSION["user_name"] = $user['first_name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $login_err = "Invalid email or password.";
            }
        } else {
            $login_err = "Invalid email or password.";
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
    <title>Login - AuthSystem</title>
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
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h4 class="mb-0 fs-5">User Login</h4>
                </div>
                <div class="card-body p-4">
                    
                    <?php if (isset($_SESSION['success_msg'])): ?>
                        <div class="alert alert-success py-2"><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($login_err)): ?>
                        <div class="alert alert-danger py-2"><?php echo $login_err; ?></div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                            <div class="invalid-feedback"><?php echo $password_err; ?></div>
                        </div>

                        <button type="submit" class="btn btn-dark w-100 fw-bold py-2 mt-2">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>