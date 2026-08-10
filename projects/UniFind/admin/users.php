<?php
require_once __DIR__ . "/../config/app.php";
require_admin();

$result = mysqli_query(
    $conn,
    "SELECT
        users.id,
        users.full_name,
        users.email,
        users.role,
        users.created_at,
        COUNT(items.id) AS report_count
     FROM users
     LEFT JOIN items ON items.user_id = users.id
     GROUP BY users.id
     ORDER BY users.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - UniFind</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">UniFind Admin</a>
    <div class="nav-actions">
        <a class="btn btn-small btn-secondary" href="dashboard.php">Dashboard</a>
        <a class="btn btn-small" href="../logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <section class="page-heading">
        <div>
            <span class="eyebrow">Accounts</span>
            <h1>Manage Users</h1>
            <p>View registered students and administrators.</p>
        </div>
    </section>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Reports</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= e($user["full_name"]) ?></td>
                        <td><?= e($user["email"]) ?></td>
                        <td><span class="status-pill neutral-pill"><?= strtoupper(e($user["role"])) ?></span></td>
                        <td><?= (int)$user["report_count"] ?></td>
                        <td><?= e($user["created_at"]) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<script src="../js/ui-effects.js"></script>
</body>
</html>
