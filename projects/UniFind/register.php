<?php
require_once __DIR__ . "/config/app.php";
redirect_logged_in_user();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($full_name === "" || $email === "" || $password === "") {
        $message = "Please complete all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (full_name, email, password, role)
                 VALUES (?, ?, ?, 'student')"
            );

            mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                set_flash("success", "Registration successful. You can now login.");
                header("Location: login.php");
                exit();
            }

            $message = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a class="brand auth-brand" href="index.php">UniFind</a>
        <h1>Create Account</h1>
        <p class="muted">Join the university lost and found community.</p>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-stack">
            <div>
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= e($_POST["full_name"] ?? "") ?>" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?= e($_POST["email"] ?? "") ?>" required>
            </div>

            <div>
                <label>Password</label>
                <input type="password" name="password" minlength="6" required>
            </div>

            <div>
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" minlength="6" required>
            </div>

            <button class="btn btn-block" type="submit">Create Account</button>
        </form>

        <p class="auth-footer">
            Already registered? <a href="login.php">Login</a>
        </p>
    </div>
</div>

<script src="js/ui-effects.js"></script>
</body>
</html>
