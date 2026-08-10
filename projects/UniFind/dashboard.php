<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        SUM(type = 'lost') AS lost_total,
        SUM(type = 'found') AS found_total
     FROM items
     WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$report_stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM claims WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$total_claims = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM notifications
     WHERE user_id = ? AND is_read = 0"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$notification_count = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM items
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$recent_reports = mysqli_stmt_get_result($stmt);

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">UniFind</a>
    <div class="nav-actions">
        <a class="nav-link" href="notifications.php">
            Notifications
            <?php if ($notification_count > 0): ?>
                <span class="notification-badge"><?= $notification_count ?></span>
            <?php endif; ?>
        </a>
        <span class="user-name"><?= e($_SESSION["full_name"]) ?></span>
        <a class="btn btn-small" href="logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash["type"]) ?>"><?= e($flash["message"]) ?></div>
    <?php endif; ?>

    <section class="page-heading">
        <div>
            <span class="eyebrow">Student Dashboard</span>
            <h1>Welcome, <?= e($_SESSION["full_name"]) ?></h1>
            <p>Report, search and recover items around your university.</p>
        </div>
    </section>

    <section class="stats-grid">
        <div class="stat-card">
            <span>My Lost Reports</span>
            <strong><?= (int)($report_stats["lost_total"] ?? 0) ?></strong>
        </div>
        <div class="stat-card">
            <span>My Found Reports</span>
            <strong><?= (int)($report_stats["found_total"] ?? 0) ?></strong>
        </div>
        <div class="stat-card">
            <span>My Claims</span>
            <strong><?= $total_claims ?></strong>
        </div>
        <div class="stat-card">
            <span>New Notifications</span>
            <strong><?= $notification_count ?></strong>
        </div>
    </section>

    <section class="action-grid">
        <a class="action-card" href="report-lost.php">
            <span class="action-icon">?</span>
            <h3>Report Lost Item</h3>
            <p>Tell UniFind about an item you have lost.</p>
            <span class="card-link">Report Lost →</span>
        </a>

        <a class="action-card" href="report-found.php">
            <span class="action-icon">✓</span>
            <h3>Report Found Item</h3>
            <p>Publish an item you found inside the university.</p>
            <span class="card-link">Report Found →</span>
        </a>

        <a class="action-card" href="search-items.php">
            <span class="action-icon">⌕</span>
            <h3>Search Items</h3>
            <p>Search all active lost and found reports.</p>
            <span class="card-link">Search Items →</span>
        </a>

        <a class="action-card" href="possible-matches.php">
            <span class="action-icon">≈</span>
            <h3>Possible Matches</h3>
            <p>Compare your lost reports with similar found items.</p>
            <span class="card-link">Find Matches →</span>
        </a>

        <a class="action-card" href="my-reports.php">
            <span class="action-icon">▤</span>
            <h3>My Reports</h3>
            <p>View, edit or remove your own reports.</p>
            <span class="card-link">Manage Reports →</span>
        </a>

        <a class="action-card" href="my-claims.php">
            <span class="action-icon">◎</span>
            <h3>My Claims</h3>
            <p>Track pending, approved and rejected ownership claims.</p>
            <span class="card-link">View Claims →</span>
        </a>
    </section>

    <section class="section-block">
        <div class="section-heading">
            <div>
                <h2>Recent Reports</h2>
                <p>Your latest lost and found activity.</p>
            </div>
            <a class="text-link" href="my-reports.php">View all</a>
        </div>

        <?php if (mysqli_num_rows($recent_reports) > 0): ?>
            <div class="recent-list">
                <?php while ($item = mysqli_fetch_assoc($recent_reports)): ?>
                    <div class="recent-row">
                        <div class="recent-main">
                            <?php if (!empty($item["image"]) && is_file(__DIR__ . "/uploads/" . $item["image"])): ?>
                                <img class="thumb" src="uploads/<?= e($item["image"]) ?>" alt="">
                            <?php else: ?>
                                <div class="thumb placeholder">No Image</div>
                            <?php endif; ?>

                            <div>
                                <h3><?= e($item["item_name"]) ?></h3>
                                <p><?= e($item["location"]) ?> · <?= e($item["item_date"]) ?></p>
                            </div>
                        </div>

                        <div class="recent-tags">
                            <span class="status-pill <?= $item["type"] === "lost" ? "lost-pill" : "found-pill" ?>">
                                <?= strtoupper(e($item["type"])) ?>
                            </span>
                            <span class="status-pill neutral-pill"><?= strtoupper(e($item["status"])) ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No reports yet</h3>
                <p>Create your first lost or found report from the options above.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
