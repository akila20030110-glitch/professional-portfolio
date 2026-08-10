<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT claims.*, items.item_name, items.category, items.location, items.item_date, items.image
     FROM claims
     INNER JOIN items ON items.id = claims.item_id
     WHERE claims.user_id = ?
     ORDER BY claims.created_at DESC"
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
    <title>My Claims - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">UniFind</a>
    <div class="nav-actions">
        <a class="btn btn-small btn-secondary" href="search-items.php">Search Items</a>
        <a class="btn btn-small" href="logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash["type"]) ?>"><?= e($flash["message"]) ?></div>
    <?php endif; ?>

    <section class="page-heading">
        <div>
            <span class="eyebrow">Ownership Requests</span>
            <h1>My Claims</h1>
            <p>Track the current status of your submitted claims.</p>
        </div>
    </section>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="claim-list">
            <?php while ($claim = mysqli_fetch_assoc($result)): ?>
                <article class="claim-row">
                    <?php if (!empty($claim["image"]) && is_file(__DIR__ . "/uploads/" . $claim["image"])): ?>
                        <img class="claim-thumb" src="uploads/<?= e($claim["image"]) ?>" alt="">
                    <?php else: ?>
                        <div class="claim-thumb placeholder">No Image</div>
                    <?php endif; ?>

                    <div class="claim-row-body">
                        <div class="card-topline">
                            <h3><?= e($claim["item_name"]) ?></h3>
                            <span class="status-pill <?= e($claim["status"]) ?>-pill">
                                <?= strtoupper(e($claim["status"])) ?>
                            </span>
                        </div>

                        <div class="detail-list compact">
                            <span><strong>Category:</strong> <?= e($claim["category"]) ?></span>
                            <span><strong>Location:</strong> <?= e($claim["location"]) ?></span>
                            <span><strong>Submitted:</strong> <?= e($claim["created_at"]) ?></span>
                        </div>

                        <p class="item-description"><strong>Your message:</strong> <?= e($claim["message"]) ?></p>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No claims yet</h3>
            <p>Search found items and send a claim when you recognize your property.</p>
        </div>
    <?php endif; ?>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
