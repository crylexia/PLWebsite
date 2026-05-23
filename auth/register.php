<?php
session_start();
include "../config/db.php";
include "../config/csrf.php";
include "../config/mailer.php"; // ← add this

$message = "";

 // Show error messages from redirects
            if (isset($_GET["error"])) {
                if ($_GET["error"] == "expired") {
                    $message = "Your verification code expired. Please register again.";
                } elseif ($_GET["error"] == "toomany") {
                    $message = "Too many failed attempts. Please register again.";
                }
            }

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    verify_csrf();

    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check username — prepared statement (fixes SQL injection)
    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($check, "s", $username);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        $message = "Username already exists!";

    } else {

        // Check email too
        $checkEmail = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($checkEmail, "s", $email);
        mysqli_stmt_execute($checkEmail);
        mysqli_stmt_store_result($checkEmail);

        if (mysqli_stmt_num_rows($checkEmail) > 0) {
            $message = "Email is already registered!";

        } else {

            // Generate OTP
            $otp     = rand(100000, 999999);
            $expires = time() + (10 * 60); // expires in 10 minutes

            // Save to session — NO show_otp anymore
            $_SESSION["reg_data"] = [
                "fullname" => $fullname,
                "username" => $username,
                "email"    => $email,
                "password" => $password
            ];
            $_SESSION["reg_otp"]     = $otp;
            $_SESSION["otp_expires"] = $expires;  // ← NEW: expiry
            $_SESSION["otp_attempts"] = 0;

            // Send OTP via email
            $subject = "Your LakbayLokal Verification Code";
            $body = "
                <div style='font-family:sans-serif; max-width:480px; margin:auto;'>
                    <h2 style='color:#102a43;'>Verify Your Account</h2>
                    <p>Hello <strong>{$fullname}</strong>,</p>
                    <p>Use the code below to complete your registration.
                       It expires in <strong>10 minutes</strong>.</p>
                    <div style='background:#fef3c7; padding:20px; border-radius:10px;
                                text-align:center; font-size:32px; font-weight:bold;
                                letter-spacing:8px; color:#92400e;'>
                        {$otp}
                    </div>
                    <p style='color:#999; font-size:13px; margin-top:20px;'>
                        If you didn't request this, ignore this email.
                    </p>
                </div>
            ";

            if (sendMail($email, $subject, $body)) {
                header("Location: verify_register.php");
                exit();
            } else {
                $message = "Failed to send verification email. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - LakbayLokal</title>
<link rel="stylesheet" href="../assets/css/style.css">

<style>
.register-wrapper {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(to right, #102a43, #1e3a8a);
    padding: 20px;
    box-sizing: border-box;
}

.register-card {
    background: white;
    width: 420px;
    max-width: 95vw;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    text-align: center;
}

.register-card .logo {
    font-size: 26px;
    font-weight: bold;
    color: #f59e0b;
    margin-bottom: 6px;
}

.register-card h2 {
    color: #102a43;
    margin: 6px 0 4px;
}

.register-card p.subtitle {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 10px;
}

.register-card input {
    width: 100%;
    padding: 14px;
    margin-top: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    font-size: 14px;
    box-sizing: border-box;
    background: #f8fafc;
}

.register-card input:focus {
    outline: none;
    border-color: #1e3a8a;
    background: #fff;
}

.register-card button {
    width: 100%;
    padding: 14px;
    margin-top: 20px;
    background: #f59e0b;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    color: #1f2937;
    transition: 0.2s;
}

.register-card button:hover {
    background: #d97706;
}

.register-card .error-msg {
    color: #dc2626;
    font-size: 13px;
    margin-top: 10px;
}

.register-card .login-link {
    margin-top: 16px;
    font-size: 14px;
    color: #475569;
}

.register-card .login-link a {
    color: #1e3a8a;
    font-weight: bold;
    text-decoration: none;
}

.register-card .login-link a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<div class="register-wrapper">
<div class="register-card">

    <div class="logo">LakbayLokal</div>
    <h2>Create Account</h2>
    <p class="subtitle">Join and explore authentic local products</p>

    <form method="POST">
        <?= csrf_field() ?>
        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Register</button>

        <?php if($message): ?>
            <p class="error-msg"><?php echo $message; ?></p>
        <?php endif; ?>

        <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
    </form>

</div>
</div>

</body>
</html>
