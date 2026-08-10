<?php
require_once __DIR__ . "/../config/app.php";
require_admin();

$query = "
SELECT
    claims.id AS claim_id,
    claims.message,
    claims.status AS claim_status,
    claims.created_at,
    items.id AS item_id,
    items.item_name,
    items.category,
    items.location,
    items.item_date,
    items.image,
    items.status AS item_status,
    claimant.full_name AS claimant_name,
    claimant.email AS claimant_email,
    reporter.full_name AS reporter_name
FROM claims
INNER JOIN items ON items.id = claims.item_id
INNER JOIN users AS claimant ON claimant.id = claims.user_id
INNER JOIN users AS reporter ON reporter.id = items.user_id
ORDER BY
    CASE WHEN claims.status = 'pending' THEN 0 ELSE 1 END,
    claims.created_at DESC
";

$result = mysqli_query($conn, $query);
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Claims - UniFind</title>
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
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash["type"]) ?>"><?= e($flash["message"]) ?></div>
    <?php endif; ?>

    <section class="page-heading">
        <div>
            <span class="eyebrow">Verification</span>
            <h1>Claim Requests</h1>
            <p>Review ownership details before approving a returned item.</p>
        </div>
    </section>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="claim-list">
            <?php while ($claim = mysqli_fetch_assoc($result)): ?>
                <article class="admin-claim-card">
                    <?php if (!empty($claim["image"]) && is_file(__DIR__ . "/../uploads/" . $claim["image"])): ?>
                        <img class="admin-claim-image" src="../uploads/<?= e($claim["image"]) ?>" alt="">
                    <?php else: ?>
                        <div class="admin-claim-image placeholder">No Image</div>
                    <?php endif; ?>

                    <div class="claim-row-body">
                        <div class="card-topline">
                            <h3><?= e($claim["item_name"]) ?></h3>
                            <span class="status-pill <?= e($claim["claim_status"]) ?>-pill">
                                <?= strtoupper(e($claim["claim_status"])) ?>
                            </span>
                        </div>

                        <div class="detail-list compact">
                            <span><strong>Category:</strong> <?= e($claim["category"]) ?></span>
                            <span><strong>Found at:</strong> <?= e($claim["location"]) ?></span>
                            <span><strong>Found date:</strong> <?= e($claim["item_date"]) ?></span>
                            <span><strong>Reported by:</strong> <?= e($claim["reporter_name"]) ?></span>
                            <span><strong>Claimant:</strong> <?= e($claim["claimant_name"]) ?> (<?= e($claim["claimant_email"]) ?>)</span>
                            <span><strong>Submitted:</strong> <?= e($claim["created_at"]) ?></span>
                        </div>

                        <div class="claim-message">
                            <strong>Ownership details</strong>
                            <p><?= e($claim["message"]) ?></p>
                        </div>

                        <?php if ($claim["claim_status"] === "pending"): ?>
                            <div class="button-row">
                                <form method="POST" action="update-claim.php">
                                    <input type="hidden" name="id" value="<?= (int)$claim["claim_id"] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button class="btn btn-success" type="submit" onclick="return confirm('Approve this claim and mark the item returned?');">
                                        Approve
                                    </button>
                                </form>

                                <form method="POST" action="update-claim.php">
                                    <input type="hidden" name="id" value="<?= (int)$claim["claim_id"] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-danger" type="submit" onclick="return confirm('Reject this claim?');">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No claim requests</h3>
            <p>No students have submitted ownership claims yet.</p>
        </div>
    <?php endif; ?>
</main>

<script src="../js/ui-effects.js"></script>
</body>
</html>
