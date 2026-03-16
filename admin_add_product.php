<?php
session_start();
include "db.php";

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = $_POST["name"];
    $desc = $_POST["description"];
    $price = $_POST["price"];

    $image = $_FILES["image"]["name"];
    $tmp = $_FILES["image"]["tmp_name"];

    $folder = "uploads/";
    move_uploaded_file($tmp, $folder.$image);

    $sql = "INSERT INTO products (name, description, price, image)
            VALUES ('$name','$desc','$price','$image')";

    if(mysqli_query($conn,$sql)){
        $msg = "Product added successfully!";
    } else {
        $msg = "Error adding product!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin – Add Product</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-container">
<h2>Add New Product</h2>

<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product Name" required>
    <textarea name="description" placeholder="Product Description" required></textarea>
    <input type="number" name="price" placeholder="Price" step="0.01" required>
    <input type="file" name="image" required>

    <button type="submit">Add Product</button>
    <p style="color:green;"><?php echo $msg; ?></p>
</form>
</div>

</body>
</html>
