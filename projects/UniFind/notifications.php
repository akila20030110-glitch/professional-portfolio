<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM notifications
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$update = mysqli_prepare(
    $conn,
    "UPDATE notifications
     SET is_read = 1
     WHERE user_id = ?"
);
mysqli_stmt_bind_param($update, "i", $user_id);
mysqli_stmt_execute($update);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">UniFind</a>
    <div class="nav-actions">
        <a class="btn btn-small btn-secondary" href="dashboard.php">Dashboard</a>
        <a class="btn btn-small" href="logout.php">Logout</a>
    </div>
</nav>

<main class="page-container narrow-page">
    <section class="page-heading">
        <div>
            <span class="eyebrow">Updates</span>
            <h1>Notifications</h1>
            <p>Claim decisions and important item updates appear here.</p>
        </div>
    </section>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="notification-list">
            <?php while ($notification = mysqli_fetch_assoc($result)): ?>
                <article class="notification-card <?= $notification["is_read"] ? "" : "unread" ?>">
                    <div>
                        <h3>UniFind Update</h3>
                        <p><?= e($notification["message"]) ?></p>
                    </div>
                    <span class="small-muted"><?= e($notification["created_at"]) ?></span>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No notifications</h3>
            <p>You do not have any updates yet.</p>
        </div>
    <?php endif; ?>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
