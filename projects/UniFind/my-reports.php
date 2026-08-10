<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM items
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports - UniFind</title>
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

<main class="page-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash["type"]) ?>"><?= e($flash["message"]) ?></div>
    <?php endif; ?>

    <section class="page-heading">
        <div>
            <span class="eyebrow">Your Activity</span>
            <h1>My Reports</h1>
            <p>Manage the lost and found items you have submitted.</p>
        </div>
    </section>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <section class="item-grid">
            <?php while ($item = mysqli_fetch_assoc($result)): ?>
                <article class="item-card">
                    <?php if (!empty($item["image"]) && is_file(__DIR__ . "/uploads/" . $item["image"])): ?>
                        <img class="item-image" src="uploads/<?= e($item["image"]) ?>" alt="">
                    <?php else: ?>
                        <div class="item-image placeholder">No Image Available</div>
                    <?php endif; ?>

                    <div class="item-card-body">
                        <div class="card-topline">
                            <span class="status-pill <?= $item["type"] === "lost" ? "lost-pill" : "found-pill" ?>">
                                <?= strtoupper(e($item["type"])) ?>
                            </span>
                            <span class="status-pill neutral-pill"><?= strtoupper(e($item["status"])) ?></span>
                        </div>

                        <h3><?= e($item["item_name"]) ?></h3>
                        <p class="item-description"><?= e($item["description"]) ?></p>

                        <div class="detail-list">
                            <span><strong>Category:</strong> <?= e($item["category"]) ?></span>
                            <span><strong>Location:</strong> <?= e($item["location"]) ?></span>
                            <span><strong>Date:</strong> <?= e($item["item_date"]) ?></span>
                        </div>

                        <?php if ($item["status"] === "active"): ?>
                            <div class="button-row">
                                <a class="btn btn-secondary" href="edit-report.php?id=<?= (int)$item["id"] ?>">Edit</a>

                                <form method="POST" action="delete-report.php" onsubmit="return confirm('Delete this report?');">
                                    <input type="hidden" name="id" value="<?= (int)$item["id"] ?>">
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <p class="small-muted">Returned items cannot be edited or deleted.</p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    <?php else: ?>
        <div class="empty-state">
            <h3>No reports yet</h3>
            <p>You have not submitted any lost or found items.</p>
        </div>
    <?php endif; ?>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
