<?php
require_once __DIR__ . "/config/app.php";
require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my-reports.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
$item_id = (int)($_POST["id"] ?? 0);

$stmt = mysqli_prepare(
    $conn,
    "SELECT image, status
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
    set_flash("error", "Returned items cannot be deleted.");
    header("Location: my-reports.php");
    exit();
}

$claim_check = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM claims
     WHERE item_id = ? AND status = 'pending'"
);
mysqli_stmt_bind_param($claim_check, "i", $item_id);
mysqli_stmt_execute($claim_check);
$pending_count = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($claim_check))["total"];

if ($pending_count > 0) {
    set_flash("error", "This report has a pending claim and cannot be deleted.");
    header("Location: my-reports.php");
    exit();
}

$delete = mysqli_prepare(
    $conn,
    "DELETE FROM items
     WHERE id = ? AND user_id = ?"
);
mysqli_stmt_bind_param($delete, "ii", $item_id, $user_id);

if (mysqli_stmt_execute($delete)) {
    delete_item_image($item["image"], __DIR__ . "/uploads");
    set_flash("success", "Report deleted.");
} else {
    set_flash("error", "Could not delete the report.");
}

header("Location: my-reports.php");
exit();
?>
