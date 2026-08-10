<?php
require_once __DIR__ . "/config/app.php";
require_login();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $item_name = trim($_POST["item_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $item_date = $_POST["item_date"] ?? "";
    $description = trim($_POST["description"] ?? "");

    if ($item_name === "" || !valid_category($category) || $location === "" || $item_date === "" || $description === "") {
        $message = "Please complete all required fields.";
    } else {
        $upload = upload_item_image($_FILES["image"] ?? null, __DIR__ . "/uploads");

        if (!$upload["success"]) {
            $message = $upload["message"];
        } else {
            $user_id = (int)$_SESSION["user_id"];
            $image = $upload["filename"];
            $type = "lost";
            $status = "active";

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO items
                (user_id, item_name, category, description, location, item_date, image, type, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "issssssss",
                $user_id,
                $item_name,
                $category,
                $description,
                $location,
                $item_date,
                $image,
                $type,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {
                set_flash("success", "Lost item reported successfully.");
                header("Location: my-reports.php");
                exit();
            }

            delete_item_image($image, __DIR__ . "/uploads");
            $message = "Could not save the report.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lost Item - UniFind</title>
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

<main class="form-page">
    <div class="form-card">
        <span class="status-pill lost-pill">LOST REPORT</span>
        <h1>Report Lost Item</h1>
        <p class="muted">Add clear details to improve the chance of finding a match.</p>

        <?php if ($message !== ""): ?>
            <div class="alert alert-error"><?= e($message) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-stack">
            <div class="form-grid">
                <div>
                    <label>Item Name</label>
                    <input type="text" name="item_name" value="<?= e($_POST["item_name"] ?? "") ?>" placeholder="Black wallet" required>
                </div>

                <div>
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php foreach (get_categories() as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= (($_POST["category"] ?? "") === $cat) ? "selected" : "" ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Lost Location</label>
                    <input type="text" name="location" value="<?= e($_POST["location"] ?? "") ?>" placeholder="University Library" required>
                </div>

                <div>
                    <label>Date Lost</label>
                    <input type="date" name="item_date" value="<?= e($_POST["item_date"] ?? "") ?>" required>
                </div>
            </div>

            <div>
                <label>Description</label>
                <textarea name="description" placeholder="Colour, brand, identifying details..." required><?= e($_POST["description"] ?? "") ?></textarea>
            </div>

            <div>
                <label>Item Photo <span class="muted">(optional, max 5MB)</span></label>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </div>

            <button class="btn btn-block" type="submit">Submit Lost Report</button>
        </form>
    </div>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
