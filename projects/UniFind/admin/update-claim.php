<?php
require_once __DIR__ . "/../config/app.php";
require_admin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: claims.php");
    exit();
}

$claim_id = (int)($_POST["id"] ?? 0);
$action = $_POST["action"] ?? "";

if (!in_array($action, ["approve", "reject"], true)) {
    set_flash("error", "Invalid claim action.");
    header("Location: claims.php");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT claims.*, items.user_id AS reporter_id
     FROM claims
     INNER JOIN items ON items.id = claims.item_id
     WHERE claims.id = ? AND claims.status = 'pending'"
);
mysqli_stmt_bind_param($stmt, "i", $claim_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) !== 1) {
    set_flash("error", "Pending claim not found.");
    header("Location: claims.php");
    exit();
}

$claim = mysqli_fetch_assoc($result);
$item_id = (int)$claim["item_id"];
$claimant_id = (int)$claim["user_id"];
$reporter_id = (int)$claim["reporter_id"];

if ($action === "approve") {
    mysqli_begin_transaction($conn);

    try {
        $approve = mysqli_prepare($conn, "UPDATE claims SET status = 'approved' WHERE id = ?");
        mysqli_stmt_bind_param($approve, "i", $claim_id);
        mysqli_stmt_execute($approve);

        $reject_others = mysqli_prepare(
            $conn,
            "UPDATE claims
             SET status = 'rejected'
             WHERE item_id = ? AND id != ? AND status = 'pending'"
        );
        mysqli_stmt_bind_param($reject_others, "ii", $item_id, $claim_id);
        mysqli_stmt_execute($reject_others);

        $item_update = mysqli_prepare($conn, "UPDATE items SET status = 'returned' WHERE id = ?");
        mysqli_stmt_bind_param($item_update, "i", $item_id);
        mysqli_stmt_execute($item_update);

        $message = "Your claim was approved. The item has been marked as returned.";
        $notify = mysqli_prepare($conn, "INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        mysqli_stmt_bind_param($notify, "is", $claimant_id, $message);
        mysqli_stmt_execute($notify);

        $reporter_message = "A claim for one of your found items was approved. The item is now marked as returned.";
        $notify_reporter = mysqli_prepare($conn, "INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        mysqli_stmt_bind_param($notify_reporter, "is", $reporter_id, $reporter_message);
        mysqli_stmt_execute($notify_reporter);

        mysqli_commit($conn);
        set_flash("success", "Claim approved and item marked as returned.");
    } catch (Throwable $e) {
        mysqli_rollback($conn);
        set_flash("error", "Could not approve the claim.");
    }
} else {
    $reject = mysqli_prepare($conn, "UPDATE claims SET status = 'rejected' WHERE id = ?");
    mysqli_stmt_bind_param($reject, "i", $claim_id);
    mysqli_stmt_execute($reject);

    $message = "Your claim request was rejected.";
    $notify = mysqli_prepare($conn, "INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    mysqli_stmt_bind_param($notify, "is", $claimant_id, $message);
    mysqli_stmt_execute($notify);

    set_flash("success", "Claim rejected.");
}

header("Location: claims.php");
exit();
?>
