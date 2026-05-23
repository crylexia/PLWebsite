<?php
session_start();
include "../config/db.php";
include "../config/csrf.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["product_id"])) {
    header("Location: products.php");
    exit();
}

verify_csrf();

$user_id    = (int) $_SESSION["user_id"];
$product_id = (int) $_POST["product_id"];

// Check if already favorited
$stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
}

// Safe redirect: only allow relative paths on our own site
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$allowed_paths = ['products.php', 'catalog.php'];
$redirect = 'products.php';
foreach ($allowed_paths as $path) {
    if (str_ends_with(parse_url($referer, PHP_URL_PATH) ?? '', $path)) {
        $redirect = $path;
        break;
    }
}

header("Location: " . $redirect);
exit();
