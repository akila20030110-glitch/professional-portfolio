<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];
$selected_lost_id = (int)($_GET["lost_id"] ?? 0);

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM items
     WHERE user_id = ? AND type = 'lost' AND status = 'active'
     ORDER BY created_at DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$lost_result = mysqli_stmt_get_result($stmt);

$lost_items = [];
while ($row = mysqli_fetch_assoc($lost_result)) {
    $lost_items[] = $row;
}

$selected_lost = null;
$matches = [];

if ($selected_lost_id > 0) {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT *
         FROM items
         WHERE id = ? AND user_id = ? AND type = 'lost' AND status = 'active'"
    );
    mysqli_stmt_bind_param($stmt, "ii", $selected_lost_id, $user_id);
    mysqli_stmt_execute($stmt);
    $selected_result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($selected_result) === 1) {
        $selected_lost = mysqli_fetch_assoc($selected_result);

        $stmt = mysqli_prepare(
            $conn,
            "SELECT items.*, users.full_name
             FROM items
             INNER JOIN users ON users.id = items.user_id
             WHERE items.type = 'found'
               AND items.status = 'active'
               AND items.category = ?
               AND items.user_id != ?
             ORDER BY items.created_at DESC"
        );
        mysqli_stmt_bind_param($stmt, "si", $selected_lost["category"], $user_id);
        mysqli_stmt_execute($stmt);
        $found_result = mysqli_stmt_get_result($stmt);

        while ($found = mysqli_fetch_assoc($found_result)) {
            $score = 40;

            $lost_location = strtolower(trim($selected_lost["location"]));
            $found_location = strtolower(trim($found["location"]));

            if ($lost_location === $found_location) {
                $score += 25;
            } elseif (
                str_contains($lost_location, $found_location) ||
                str_contains($found_location, $lost_location)
            ) {
                $score += 15;
            }

            $lost_date = strtotime($selected_lost["item_date"]);
            $found_date = strtotime($found["item_date"]);
            $days = abs(($lost_date - $found_date) / 86400);

            if ($days <= 3) {
                $score += 20;
            } elseif ($days <= 7) {
                $score += 10;
            }

            similar_text(
                strtolower($selected_lost["item_name"]),
                strtolower($found["item_name"]),
                $name_similarity
            );

            $score += (int)round(($name_similarity / 100) * 15);
            $found["match_score"] = min(100, $score);
            $matches[] = $found;
        }

        usort($matches, function ($a, $b) {
            return $b["match_score"] <=> $a["match_score"];
        });
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Possible Matches - UniFind</title>
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
    <section class="page-heading">
        <div>
            <span class="eyebrow">Smart Matching</span>
            <h1>Possible Matches</h1>
            <p>Select one of your active lost reports to compare it with found items.</p>
        </div>
    </section>

    <?php if (count($lost_items) > 0): ?>
        <form class="match-selector" method="GET">
            <select name="lost_id" required>
                <option value="">Select Your Lost Item</option>
                <?php foreach ($lost_items as $lost): ?>
                    <option value="<?= (int)$lost["id"] ?>" <?= $selected_lost_id === (int)$lost["id"] ? "selected" : "" ?>>
                        <?= e($lost["item_name"]) ?> - <?= e($lost["location"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Find Matches</button>
        </form>
    <?php else: ?>
        <div class="empty-state">
            <h3>No active lost reports</h3>
            <p>Report a lost item before using smart matching.</p>
            <a class="btn" href="report-lost.php">Report Lost Item</a>
        </div>
    <?php endif; ?>

    <?php if ($selected_lost): ?>
        <section class="selected-report">
            <span class="status-pill lost-pill">LOOKING FOR</span>
            <h2><?= e($selected_lost["item_name"]) ?></h2>
            <p><?= e($selected_lost["location"]) ?> · <?= e($selected_lost["item_date"]) ?> · <?= e($selected_lost["category"]) ?></p>
        </section>

        <div class="section-heading">
            <div>
                <h2>Suggested Found Items</h2>
                <p>Scores use category, location, date and item-name similarity.</p>
            </div>
        </div>

        <?php if (count($matches) > 0): ?>
            <section class="item-grid">
                <?php foreach ($matches as $match): ?>
                    <article class="item-card">
                        <?php if (!empty($match["image"]) && is_file(__DIR__ . "/uploads/" . $match["image"])): ?>
                            <img class="item-image" src="uploads/<?= e($match["image"]) ?>" alt="">
                        <?php else: ?>
                            <div class="item-image placeholder">No Image Available</div>
                        <?php endif; ?>

                        <div class="item-card-body">
                            <div class="card-topline">
                                <span class="match-score"><?= (int)$match["match_score"] ?>% MATCH</span>
                                <span class="status-pill found-pill">FOUND</span>
                            </div>

                            <h3><?= e($match["item_name"]) ?></h3>
                            <p class="item-description"><?= e($match["description"]) ?></p>

                            <div class="detail-list">
                                <span><strong>Location:</strong> <?= e($match["location"]) ?></span>
                                <span><strong>Date:</strong> <?= e($match["item_date"]) ?></span>
                                <span><strong>Reported by:</strong> <?= e($match["full_name"]) ?></span>
                            </div>

                            <a class="btn btn-block" href="claim-item.php?id=<?= (int)$match["id"] ?>">Claim Item</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <div class="empty-state">
                <h3>No possible matches yet</h3>
                <p>No active found items in the same category are currently available.</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
