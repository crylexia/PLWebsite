<?php
function sendMail($to, $subject, $body)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION["last_otp_sent"])) {
        $secondsPassed = time() - $_SESSION["last_otp_sent"];
        if ($secondsPassed < 30) {
            $remaining = 30 - $secondsPassed;
            return "Please wait {$remaining} seconds before requesting another code.";
        }
    }
    $_SESSION["last_otp_sent"] = time();

    $apiKey = $_ENV['BREVO_API_KEY'];
    $payload = json_encode([
        "sender"     => ["name" => $_ENV['MAIL_FROM_NAME'], "email" => $_ENV['MAIL_FROM']],
        "to"         => [["email" => $to]],
        "subject"    => $subject,
        "htmlContent"=> $body
    ]);

    $ch = curl_init("https://api.brevo.com/v3/smtp/email");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: $apiKey",
        "content-type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 201) {
        return true;
    } else {
        error_log("Brevo Error: " . $response);
        return "Mailer Error: " . $response;
    }
}