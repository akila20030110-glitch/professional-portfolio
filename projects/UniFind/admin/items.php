<?php
require_once __DIR__ . "/../config/app.php";
require_admin();

$type = trim($_GET["type"] ?? "");
$status = trim($_GET["status"] ?? "");

$sql = "
SELECT items.*, users.full_name, users.email
FROM items
INNER JOIN users ON users.id = items.user_id
WHERE 1=1
";

$params = [];
$types = "";

if (in_array($type, ["lost", "found"], true)) {
    $sql .= " AND items.type = ?";
    $params[] = &$type;
    $types .= "s";
}

if (in_array($status, ["active", "claimed", "returned"], true)) {
    $sql .= " AND items.status = ?";
    $params[] = &$status;
    $types .= "s";
}

$sql .= " ORDER BY items.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - UniFind</title>
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
            <span class="eyebrow">System Reports</span>
            <h1>Manage Items</h1>
            <p>Review all lost and found reports submitted by users.</p>
        </div>
    </section>

    <form class="filter-bar compact-filter" method="GET">
        <select name="type">
            <option value="">All Types</option>
            <option value="lost" <?= $type === "lost" ? "selected" : "" ?>>Lost</option>
            <option value="found" <?= $type === "found" ? "selected" : "" ?>>Found</option>
        </select>

        <select name="status">
            <option value="">All Statuses</option>
            <option value="active" <?= $status === "active" ? "selected" : "" ?>>Active</option>
            <option value="claimed" <?= $status === "claimed" ? "selected" : "" ?>>Claimed</option>
            <option value="returned" <?= $status === "returned" ? "selected" : "" ?>>Returned</option>
        </select>

        <button class="btn" type="submit">Filter</button>
        <a class="btn btn-secondary" href="items.php">Reset</a>
    </form>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <section class="item-grid">
            <?php while ($item = mysqli_fetch_assoc($result)): ?>
                <article class="item-card">
                    <?php if (!empty($item["image"]) && is_file(__DIR__ . "/../uploads/" . $item["image"])): ?>
                        <img class="item-image" src="../uploads/<?= e($item["image"]) ?>" alt="">
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
                            <span><strong>Reporter:</strong> <?= e($item["full_name"]) ?> (<?= e($item["email"]) ?>)</span>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    <?php else: ?>
        <div class="empty-state">
            <h3>No items found</h3>
            <p>No reports match the current filters.</p>
        </div>
    <?php endif; ?>
</main>

<script src="../js/ui-effects.js"></script>
</body>
</html>
