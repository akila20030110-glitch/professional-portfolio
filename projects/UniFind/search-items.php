<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];
$search = trim($_GET["search"] ?? "");
$type = trim($_GET["type"] ?? "");
$category = trim($_GET["category"] ?? "");

$sql = "
SELECT items.*, users.full_name
FROM items
INNER JOIN users ON users.id = items.user_id
WHERE items.status = 'active'
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (items.item_name LIKE ? OR items.description LIKE ? OR items.location LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = &$term;
    $params[] = &$term;
    $params[] = &$term;
    $types .= "sss";
}

if (in_array($type, ["lost", "found"], true)) {
    $sql .= " AND items.type = ?";
    $params[] = &$type;
    $types .= "s";
}

if ($category !== "" && valid_category($category)) {
    $sql .= " AND items.category = ?";
    $params[] = &$category;
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
    <title>Search Items - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">UniFind</a>
    <div class="nav-actions">
        <a class="nav-link" href="my-claims.php">My Claims</a>
        <a class="btn btn-small btn-secondary" href="dashboard.php">Dashboard</a>
        <a class="btn btn-small" href="logout.php">Logout</a>
    </div>
</nav>

<main class="page-container">
    <section class="page-heading">
        <div>
            <span class="eyebrow">Explore Reports</span>
            <h1>Search Lost & Found Items</h1>
            <p>Use keywords, type and category to narrow the results.</p>
        </div>
    </section>

    <form class="filter-bar" method="GET">
        <input type="text" name="search" placeholder="Search item, description or location..." value="<?= e($search) ?>">

        <select name="type">
            <option value="">All Types</option>
            <option value="lost" <?= $type === "lost" ? "selected" : "" ?>>Lost</option>
            <option value="found" <?= $type === "found" ? "selected" : "" ?>>Found</option>
        </select>

        <select name="category">
            <option value="">All Categories</option>
            <?php foreach (get_categories() as $cat): ?>
                <option value="<?= e($cat) ?>" <?= $category === $cat ? "selected" : "" ?>><?= e($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <button class="btn" type="submit">Search</button>
        <a class="btn btn-secondary" href="search-items.php">Reset</a>
    </form>

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
                            <span class="small-muted"><?= e($item["item_date"]) ?></span>
                        </div>

                        <h3><?= e($item["item_name"]) ?></h3>
                        <p class="item-description"><?= e($item["description"]) ?></p>

                        <div class="detail-list">
                            <span><strong>Category:</strong> <?= e($item["category"]) ?></span>
                            <span><strong>Location:</strong> <?= e($item["location"]) ?></span>
                            <span><strong>Reported by:</strong> <?= e($item["full_name"]) ?></span>
                        </div>

                        <?php if ($item["type"] === "found"): ?>
                            <?php if ((int)$item["user_id"] === $user_id): ?>
                                <span class="status-pill neutral-pill">YOUR REPORT</span>
                            <?php else: ?>
                                <a class="btn btn-block" href="claim-item.php?id=<?= (int)$item["id"] ?>">Claim Item</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    <?php else: ?>
        <div class="empty-state">
            <h3>No items found</h3>
            <p>Try another keyword or reset the filters.</p>
        </div>
    <?php endif; ?>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
