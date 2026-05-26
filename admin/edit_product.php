<?php
session_start();
include "../config/db.php";
include "../config/csrf.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$id = (int)($_GET["id"] ?? 0);

if (!$id) {
    header("Location: admin_products.php");
    exit();
}

/* Fetch product using prepared statement */
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: admin_products.php");
    exit();
}

/* Allowed upload types */
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
$allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    verify_csrf();

    $name     = trim($_POST["name"]);
    $desc     = trim($_POST["description"]);
    $price    = (float) $_POST["price"];
    $category = trim($_POST["category"]);

    if (!empty($_FILES["image"]["name"])) {

        $tmp      = $_FILES["image"]["tmp_name"];
        $origName = $_FILES["image"]["name"];
        $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes) || !in_array($ext, $allowedExts)) {
            $uploadError = "Invalid image type. Only JPG, PNG, and WebP are allowed.";
        } else {
            $safeBase  = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
            $publicId  = time() . '_' . $safeBase;

            include_once "../config/cloudinary.php";
            $imageUrl = uploadToCloudinary($tmp, $publicId);

            if (!$imageUrl) {
                $uploadError = "Image upload failed. Check Cloudinary credentials.";
            } else {
                $stmt = $conn->prepare(
                    "UPDATE products SET name=?, description=?, price=?, category=?, image=? WHERE id=?"
                );
                $stmt->bind_param("ssdssl", $name, $desc, $price, $category, $imageUrl, $id);
                $stmt->execute();
                $stmt->close();

                header("Location: admin_products.php");
                exit();
            }
        }

    } else {

        $stmt = $conn->prepare(
            "UPDATE products SET name=?, description=?, price=?, category=? WHERE id=?"
        );
        $stmt->bind_param("ssdsi", $name, $desc, $price, $category, $id);
        $stmt->execute();
        $stmt->close();

        header("Location: admin_products.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product</title>

<style>
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:linear-gradient(135deg,#1e3a8a,#0f172a);
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}

.card{
    background:rgba(255,255,255,0.95);
    backdrop-filter:blur(10px);
    width:480px;
    max-width:95vw;
    padding:40px;
    border-radius:20px;
    box-shadow:0 25px 60px rgba(0,0,0,0.3);
}

h2{
    text-align:center;
    margin-bottom:25px;
    color:#1e3a8a;
}

input, textarea, select{
    width:100%;
    padding:12px;
    margin-top:12px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:14px;
    box-sizing:border-box;
}

textarea{
    height:110px;
    resize:none;
}

.image-preview{
    text-align:center;
    margin-top:15px;
}

.image-preview img{
    width:150px;
    height:150px;
    object-fit:cover;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

button{
    width:100%;
    margin-top:20px;
    padding:14px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    color:white;
    font-size:15px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s ease;
}

button:hover{
    transform:translateY(-4px);
    box-shadow:0 15px 35px rgba(0,0,0,0.3);
}

.back{
    display:block;
    text-align:center;
    margin-top:18px;
    text-decoration:none;
    color:#1e3a8a;
    font-weight:600;
}

.back:hover{
    text-decoration:underline;
}
</style>

</head>
<body>

<div class="card">

    <h2>✏ Edit Product</h2>

    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if (!empty($uploadError)): ?>
        <p style="color:#dc2626;font-weight:600;margin-top:8px;"><?= htmlspecialchars($uploadError) ?></p>
        <?php endif; ?>

        <input type="text" name="name"
               value="<?= htmlspecialchars($product['name']) ?>"
               required>

        <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>

        <input type="number" name="price" step="0.01"
               value="<?= $product['price'] ?>"
               required>

        <!-- CATEGORY FIELD -->
        <select name="category" required>
            <option value="Attire" <?= ($product['category'] == 'Attire') ? 'selected' : '' ?>>Attire</option>
            <option value="Food Delicacy" <?= ($product['category'] == 'Food Delicacy') ? 'selected' : '' ?>>Food Delicacy</option>
            <option value="Handicrafts" <?= ($product['category'] == 'Handicrafts') ? 'selected' : '' ?>>Handicrafts</option>
            <option value="Souvenirs" <?= ($product['category'] == 'Souvenirs') ? 'selected' : '' ?>>Souvenirs</option>
            <option value="Accessories" <?= ($product['category'] == 'Accessories') ? 'selected' : '' ?>>Accessories</option>
            <option value="Home Decor" <?= ($product['category'] == 'Home Decor') ? 'selected' : '' ?>>Home Decor</option>
            <option value="Local Art" <?= ($product['category'] == 'Local Art') ? 'selected' : '' ?>>Local Art</option>
            <option value="General" <?= ($product['category'] == 'General') ? 'selected' : '' ?>>General</option>
        </select>

        <div class="image-preview">
            <p><strong>Current Image</strong></p>
            <img src="<?= htmlspecialchars($product['image']) ?>">
        </div>

        <input type="file" name="image">

        <button type="submit">Update Product</button>

    </form>

    <a href="admin_products.php" class="back">← Back to Admin</a>

</div>

</body>
</html>