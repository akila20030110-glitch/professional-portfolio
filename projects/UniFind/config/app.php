<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/database.php";

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function require_login()
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit();
    }
}

function require_admin()
{
    if (
        !isset($_SESSION["user_id"]) ||
        !isset($_SESSION["role"]) ||
        $_SESSION["role"] !== "admin"
    ) {
        header("Location: ../login.php");
        exit();
    }
}

function redirect_logged_in_user()
{
    if (!isset($_SESSION["user_id"])) {
        return;
    }

    if (isset($_SESSION["role"]) && $_SESSION["role"] === "admin") {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

function set_flash($type, $message)
{
    $_SESSION["flash"] = [
        "type" => $type,
        "message" => $message
    ];
}

function get_flash()
{
    if (!isset($_SESSION["flash"])) {
        return null;
    }

    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    return $flash;
}

function upload_item_image($file, $uploadDirectory)
{
    if (!isset($file) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return ["success" => true, "filename" => ""];
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["success" => false, "message" => "Image upload failed."];
    }

    if ($file["size"] > 5 * 1024 * 1024) {
        return ["success" => false, "message" => "Image must be smaller than 5MB."];
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        return ["success" => false, "message" => "Only JPG, PNG and WEBP images are allowed."];
    }

    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0775, true);
    }

    $filename = time() . "_" . bin2hex(random_bytes(6)) . "." . $allowed[$mime];
    $destination = rtrim($uploadDirectory, "/\\") . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        return ["success" => false, "message" => "Could not save the uploaded image."];
    }

    return ["success" => true, "filename" => $filename];
}

function delete_item_image($filename, $uploadDirectory)
{
    if (!$filename) {
        return;
    }

    $path = rtrim($uploadDirectory, "/\\") . DIRECTORY_SEPARATOR . $filename;

    if (is_file($path)) {
        @unlink($path);
    }
}

function valid_category($category)
{
    $categories = [
        "Electronics",
        "Wallet",
        "ID Card",
        "Books",
        "Keys",
        "Bag",
        "Clothing",
        "Other"
    ];

    return in_array($category, $categories, true);
}

function get_categories()
{
    return [
        "Electronics",
        "Wallet",
        "ID Card",
        "Books",
        "Keys",
        "Bag",
        "Clothing",
        "Other"
    ];
}
?>
