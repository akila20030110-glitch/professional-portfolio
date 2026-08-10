<?php
require_once __DIR__ . "/../config/app.php";
require_admin();

function total_from_query($conn, $sql)
{
    $result = mysqli_query($conn, $sql);
    return (int)mysqli_fetch_assoc($result)["total"];
}

$total_students = total_from_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'student'");
$total_lost = total_from_query($conn, "SELECT COUNT(*) AS total FROM items WHERE type = 'lost'");
$total_found = total_from_query($conn, "SELECT COUNT(*) AS total FROM items WHERE type = 'found'");
$total_pending = total_from_query($conn, "SELECT COUNT(*) AS total FROM claims WHERE status = 'pending'");
$total_returned = total_from_query($conn, "SELECT COUNT(*) AS total FROM items WHERE status = 'returned'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UniFind</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">UniFind Admin</a>
    <div class="nav-actions">
        <span class="user-name"><?= e($_SESSION["full_name"]) ?></span>
        <a class="btn btn-small" href="../logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <section class="page-heading">
        <div>
            <span class="eyebrow">Administration</span>
            <h1>Admin Dashboard</h1>
            <p>Review reports, users and ownership claims.</p>
        </div>
    </section>

    <section class="stats-grid">
        <div class="stat-card"><span>Students</span><strong><?= $total_students ?></strong></div>
        <div class="stat-card"><span>Lost Items</span><strong><?= $total_lost ?></strong></div>
        <div class="stat-card"><span>Found Items</span><strong><?= $total_found ?></strong></div>
        <div class="stat-card"><span>Pending Claims</span><strong><?= $total_pending ?></strong></div>
        <div class="stat-card"><span>Returned Items</span><strong><?= $total_returned ?></strong></div>
    </section>

    <section class="action-grid admin-actions-grid">
        <a class="action-card" href="claims.php">
            <span class="action-icon">◎</span>
            <h3>Manage Claims</h3>
            <p>Approve or reject student ownership requests.</p>
            <span class="card-link">Open Claims →</span>
        </a>

        <a class="action-card" href="items.php">
            <span class="action-icon">▤</span>
            <h3>Manage Items</h3>
            <p>Review all lost and found reports in the system.</p>
            <span class="card-link">Open Items →</span>
        </a>

        <a class="action-card" href="users.php">
            <span class="action-icon">●</span>
            <h3>Manage Users</h3>
            <p>View registered student accounts.</p>
            <span class="card-link">Open Users →</span>
        </a>
    </section>
</main>

<script src="../js/ui-effects.js"></script>
</body>
</html>
