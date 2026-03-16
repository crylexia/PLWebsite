<?php
session_start();
include "db.php";

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$id = $_GET["id"] ?? null;

if(!$id){
    header("Location: admin_products.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$product = mysqli_fetch_assoc($result);

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $desc = mysqli_real_escape_string($conn, $_POST["description"]);
    $price = $_POST["price"];

    if(!empty($_FILES["image"]["name"])){

        $imageName = time() . "_" . $_FILES["image"]["name"];
        $tmp = $_FILES["image"]["tmp_name"];
        move_uploaded_file($tmp, "uploads/" . $imageName);

        $sql = "UPDATE products 
                SET name='$name', description='$desc', price='$price', image='$imageName'
                WHERE id='$id'";
    } else {

        $sql = "UPDATE products 
                SET name='$name', description='$desc', price='$price'
                WHERE id='$id'";
    }

    mysqli_query($conn, $sql);

    header("Location: admin_products.php");
}
?>

<!DOCTYPE html>
<html>
<head>
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
    padding:40px;
    border-radius:20px;
    box-shadow:0 25px 60px rgba(0,0,0,0.3);
}

h2{
    text-align:center;
    margin-bottom:25px;
    color:#1e3a8a;
}

input, textarea{
    width:100%;
    padding:12px;
    margin-top:12px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:14px;
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

        <input type="text" name="name"
               value="<?= htmlspecialchars($product['name']) ?>"
               required>

        <textarea name="description" required>
<?= htmlspecialchars($product['description']) ?>
        </textarea>

        <input type="number" name="price" step="0.01"
               value="<?= $product['price'] ?>"
               required>

        <div class="image-preview">
            <p><strong>Current Image</strong></p>
            <img src="uploads/<?= htmlspecialchars($product['image']) ?>">
        </div>

        <input type="file" name="image">

        <button type="submit">Update Product</button>

    </form>

    <a href="admin_products.php" class="back">← Back to Admin</a>

</div>

</body>
</html>