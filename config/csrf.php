<?php
/**
 * CSRF Protection Helpers
 * Usage:
 *   In forms:   <?= csrf_field() ?>
 *   On POST:    verify_csrf();   (call before any processing)
 */

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}

function verify_csrf(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $submitted = $_POST['csrf_token'] ?? '';
    $stored    = $_SESSION['csrf_token'] ?? '';

    if (!$stored || !hash_equals($stored, $submitted)) {
        http_response_code(403);
        exit("Invalid or expired request token. Please go back and try again.");
    }

    // Rotate token after successful verification
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
