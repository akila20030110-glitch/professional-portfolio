<?php
require_once __DIR__ . "/config/app.php";

$admin_count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
$admin_count = (int)mysqli_fetch_assoc($admin_count_result)["total"];

$message = "";
$success = "";

if ($admin_count > 0) {
    $message = "An admin account already exists. This setup page is disabled.";
} elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($full_name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $message = "Enter a valid name, email and password with at least 6 characters.";
    } else {
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);

        if (mysqli_num_rows(mysqli_stmt_get_result($check)) > 0) {
            $message = "That email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (full_name, email, password, role)
                 VALUES (?, ?, ?, 'admin')"
            );
            mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $hash);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Admin account created. You can now login.";
            } else {
                $message = "Could not create the admin account.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a class="brand auth-brand" href="index.php">UniFind</a>
        <h1>Create First Admin</h1>
        <p class="muted">This page only works while no admin account exists.</p>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
            <a class="btn btn-block" href="login.php">Go to Login</a>
        <?php elseif ($admin_count === 0): ?>
            <form method="POST" class="form-stack">
                <div>
                    <label>Admin Name</label>
                    <input type="text" name="full_name" required>
                </div>
                <div>
                    <label>Admin Email</label>
                    <input type="email" name="email" required>
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password" minlength="6" required>
                </div>
                <button class="btn btn-block" type="submit">Create Admin</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="js/ui-effects.js"></script>
</body>
</html>
