<?php
require_once __DIR__ . "/config/app.php";
require_login();

$user_id = (int)$_SESSION["user_id"];
$item_id = (int)($_GET["id"] ?? 0);
$message = "";

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM items
     WHERE id = ? AND user_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $item_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    set_flash("error", "Report not found.");
    header("Location: my-reports.php");
    exit();
}

$item = mysqli_fetch_assoc($result);

if ($item["status"] !== "active") {
    set_flash("error", "Returned items cannot be edited.");
    header("Location: my-reports.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $item_date = $_POST["item_date"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $image = $item["image"];

    if ($item_name === "" || !valid_category($category) || $location === "" || $item_date === "" || $description === "") {
        $message = "Please complete all required fields.";
    } else {
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $upload = upload_item_image($_FILES["image"], __DIR__ . "/uploads");

            if (!$upload["success"]) {
                $message = $upload["message"];
            } else {
                $new_image = $upload["filename"];
                delete_item_image($image, __DIR__ . "/uploads");
                $image = $new_image;
            }
        }

        if ($message === "") {
            $update = mysqli_prepare(
                $conn,
                "UPDATE items
                 SET item_name = ?, category = ?, description = ?, location = ?, item_date = ?, image = ?
                 WHERE id = ? AND user_id = ?"
            );

            mysqli_stmt_bind_param(
                $update,
                "ssssssii",
                $item_name,
                $category,
                $description,
                $location,
                $item_date,
                $image,
                $item_id,
                $user_id
            );

            if (mysqli_stmt_execute($update)) {
                set_flash("success", "Report updated successfully.");
                header("Location: my-reports.php");
                exit();
            }

            $message = "Could not update the report.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report - UniFind</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="dashboard.php">UniFind</a>
    <div class="nav-actions">
        <a class="btn btn-small btn-secondary" href="my-reports.php">My Reports</a>
        <a class="btn btn-small" href="logout.php">Logout</a>
    </div>
</nav>

<main class="form-page">
    <div class="form-card">
        <span class="status-pill <?= $item["type"] === "lost" ? "lost-pill" : "found-pill" ?>">
            <?= strtoupper(e($item["type"])) ?>
        </span>
        <h1>Edit Report</h1>
        <p class="muted">Update the details of your active report.</p>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-stack">
            <div class="form-grid">
                <div>
                    <label>Item Name</label>
                    <input type="text" name="item_name" value="<?= e($_POST["item_name"] ?? $item["item_name"]) ?>" required>
                </div>

                <div>
                    <label>Category</label>
                    <?php $current_category = $_POST["category"] ?? $item["category"]; ?>
                    <select name="category" required>
                        <?php foreach (get_categories() as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= $current_category === $cat ? "selected" : "" ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Location</label>
                    <input type="text" name="location" value="<?= e($_POST["location"] ?? $item["location"]) ?>" required>
                </div>

                <div>
                    <label>Date</label>
                    <input type="date" name="item_date" value="<?= e($_POST["item_date"] ?? $item["item_date"]) ?>" required>
                </div>
            </div>

            <div>
                <label>Description</label>
                <textarea name="description" required><?= e($_POST["description"] ?? $item["description"]) ?></textarea>
            </div>

            <?php if (!empty($item["image"]) && is_file(__DIR__ . "/uploads/" . $item["image"])): ?>
                <div>
                    <label>Current Image</label>
                    <img class="edit-preview" src="uploads/<?= e($item["image"]) ?>" alt="">
                </div>
            <?php endif; ?>

            <div>
                <label>Replace Image <span class="muted">(optional)</span></label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </div>

            <button class="btn btn-block" type="submit">Save Changes</button>
        </form>
    </div>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
