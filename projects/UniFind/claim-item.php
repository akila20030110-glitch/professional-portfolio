<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];
$item_id = (int)($_GET["id"] ?? 0);
$message = "";

$stmt = mysqli_prepare(
    $conn,
    "SELECT items.*, users.full_name
     FROM items
     INNER JOIN users ON users.id = items.user_id
     WHERE items.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $item_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    header("Location: search-items.php");
    exit();
}

$item = mysqli_fetch_assoc($result);

if ($item["type"] !== "found" || $item["status"] !== "active" || (int)$item["user_id"] === $user_id) {
    set_flash("error", "This item cannot be claimed.");
    header("Location: search-items.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $claim_message = trim($_POST["message"] ?? "");

    if ($claim_message === "") {
        $message = "Please explain why you believe this item belongs to you.";
    } else {
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM claims
             WHERE item_id = ? AND user_id = ? AND status = 'pending'"
        );
        mysqli_stmt_bind_param($check, "ii", $item_id, $user_id);
        mysqli_stmt_execute($check);

        if (mysqli_num_rows(mysqli_stmt_get_result($check)) > 0) {
            $message = "You already have a pending claim for this item.";
        } else {
            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO claims (item_id, user_id, message, status)
                 VALUES (?, ?, ?, 'pending')"
            );
            mysqli_stmt_bind_param($insert, "iis", $item_id, $user_id, $claim_message);

            if (mysqli_stmt_execute($insert)) {
                set_flash("success", "Claim request sent. Waiting for admin verification.");
                header("Location: my-claims.php");
                exit();
            }

            $message = "Could not send the claim request.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item - UniFind</title>
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

<main class="form-page">
    <div class="form-card wide-card">
        <div class="claim-layout">
            <div>
                <?php if (!empty($item["image"]) && is_file(__DIR__ . "/uploads/" . $item["image"])): ?>
                    <img class="claim-image" src="uploads/<?= e($item["image"]) ?>" alt="">
                <?php else: ?>
                    <div class="claim-image placeholder">No Image Available</div>
                <?php endif; ?>
            </div>

            <div>
                <span class="status-pill found-pill">FOUND ITEM</span>
                <h1><?= e($item["item_name"]) ?></h1>

                <div class="detail-list">
                    <span><strong>Category:</strong> <?= e($item["category"]) ?></span>
                    <span><strong>Location:</strong> <?= e($item["location"]) ?></span>
                    <span><strong>Date:</strong> <?= e($item["item_date"]) ?></span>
                    <span><strong>Reported by:</strong> <?= e($item["full_name"]) ?></span>
                </div>

                <p class="item-description"><?= e($item["description"]) ?></p>
            </div>
        </div>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="form-stack claim-form">
            <div>
                <label>Ownership Details</label>
                <textarea
                    name="message"
                    placeholder="Explain details that help the admin verify ownership..."
                    required
                ><?= e($_POST["message"] ?? "") ?></textarea>
                <p class="small-muted">Do not include passwords or highly sensitive personal information.</p>
            </div>

            <button class="btn btn-block" type="submit">Send Claim Request</button>
        </form>
    </div>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
