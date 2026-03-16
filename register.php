<?php
include "db.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $fullname = $_POST["fullname"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    
    if(mysqli_num_rows($check) > 0){
        $message = "Username already exists!";
    } else {
        $sql = "INSERT INTO users (fullname, username, email, password) 
                VALUES ('$fullname','$username','$email','$password')";
        if(mysqli_query($conn, $sql)){
            header("Location: login.php");
        } else {
            $message = "Registration failed!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register - LakbayLokal</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="form-container">
<h2>Create Your LakbayLokal Account</h2>

<form method="POST">
    <input type="text" name="fullname" placeholder="Full Name" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>

    <button type="submit">Register</button>

    <p style="color:red;"><?php echo $message; ?></p>
    <p>Already have an account? <a href="login.php">Login</a></p>
</form>
</div>

</body>
</html>
