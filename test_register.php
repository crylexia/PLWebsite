<?php
require_once 'vendor/autoload.php';
include 'config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ─── TEST DATA (change these) ──────────────────────────
$fullname = 'Test User';
$username = 'testuser123';
$email    = 'testuser@example.com';
$password = password_hash('password123', PASSWORD_DEFAULT);

// ─── CHECK USERNAME ────────────────────────────────────
$check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($check, "s", $username);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    die("❌ Username already exists!");
}

// ─── CHECK EMAIL ───────────────────────────────────────
$checkEmail = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($checkEmail, "s", $email);
mysqli_stmt_execute($checkEmail);
mysqli_stmt_store_result($checkEmail);

if (mysqli_stmt_num_rows($checkEmail) > 0) {
    die("❌ Email already registered!");
}

echo "✅ Username and email are available\n";

// ─── GENERATE OTP ──────────────────────────────────────
$otp = rand(100000, 999999);
echo "✅ OTP generated: {$otp}\n";

// ─── SEND VIA MAILTRAP ─────────────────────────────────
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'dc97b5fda5758f';
    $mail->Password   = 'd2af7c2bab9239';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 2525;

    $mail->setFrom('lakbaylokal01@gmail.com', 'LakbayLokal');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your LakbayLokal Verification Code';
    $mail->Body    = "Hello <b>{$fullname}</b>, your OTP is: <b>{$otp}</b>. Expires in 10 minutes.";

    $mail->send();
    echo "✅ Email sent — check your Mailtrap inbox!\n";
} catch (Exception $e) {
    echo "❌ Mailer Error: {$mail->ErrorInfo}\n";
}