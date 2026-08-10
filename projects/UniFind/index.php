<?php
require_once __DIR__ . "/config/app.php";

if (isset($_SESSION["user_id"])) {
    redirect_logged_in_user();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniFind - University Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="landing-nav">
    <a class="brand" href="index.php">UniFind</a>
    <div class="nav-actions">
        <a class="btn btn-secondary" href="login.php">Login</a>
        <a class="btn" href="register.php">Register</a>
    </div>
</header>

<main class="hero">
    <section class="hero-content">
        <span class="eyebrow">University Lost & Found</span>
        <h1>Find what you lost.<br>Return what you found.</h1>
        <p>
            UniFind helps university students report lost items, publish found items,
            search reports, discover possible matches and securely request ownership.
        </p>

        <div class="hero-actions">
            <a class="btn btn-large" href="register.php">Get Started</a>
            <a class="btn btn-secondary btn-large" href="login.php">I already have an account</a>
        </div>

        <div class="feature-strip">
            <div>
                <strong>Report</strong>
                <span>Lost and found items</span>
            </div>
            <div>
                <strong>Match</strong>
                <span>Smart possible matches</span>
            </div>
            <div>
                <strong>Claim</strong>
                <span>Admin verified ownership</span>
            </div>
        </div>
    </section>

    <section class="hero-card">
        <div class="mini-card">
            <span class="status-pill found-pill">FOUND</span>
            <h3>Blue Backpack</h3>
            <p>Found near the university library.</p>
            <div class="match-meter">
                <span>Possible match</span>
                <strong>90%</strong>
            </div>
        </div>

        <div class="mini-card offset-card">
            <span class="status-pill lost-pill">LOST</span>
            <h3>Student ID Card</h3>
            <p>Lost around the ICT laboratory.</p>
            <div class="match-meter">
                <span>Status</span>
                <strong>Active</strong>
            </div>
        </div>
    </section>
</main>

<script src="js/ui-effects.js"></script>
</body>
</html>
