<?php
require_once __DIR__ . "/config/app.php";
redirect_logged_in_user();

$message = "";
$flash = get_flash();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        }
    }

    $message = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <a class="brand auth-brand" href="index.php">UniFind</a>
        <h1>Welcome Back</h1>
        <p class="muted">Login to continue to UniFind.</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash["type"]) ?>"><?= e($flash["message"]) ?></div>
        <?php endif; ?>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-stack">
            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?= e($_POST["email"] ?? "") ?>" required>
            </div>

            <div>
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button class="btn btn-block" type="submit">Login</button>
        </form>

        <p class="auth-footer">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </div>
</div>

<script src="js/ui-effects.js"></script>
</body>
</html>
