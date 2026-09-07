<?php

/**
 * Shared CSRF Protection Helper
 *
 * One CSRF token per session, reused across every form.
 * Include this file, then:
 *   - call csrfField() inside any <form> to output the hidden input
 *   - call verifyCSRFToken() at the top of any file that processes a POST
 */

function generateCSRFToken(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function csrfField(): void
{
    $token = generateCSRFToken();

    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function verifyCSRFToken(): bool
{
    if (empty($_POST["csrf_token"])) {
        return false;
    }

    if (empty($_SESSION["csrf_token"])) {
        return false;
    }

    return hash_equals(
        $_SESSION["csrf_token"],
        $_POST["csrf_token"]
    );
}

function requireCSRFToken(string $redirectTo, string $sessionErrorKey = "error"): void
{
    if (!verifyCSRFToken()) {

        $_SESSION[$sessionErrorKey] = "Your session expired or the request was invalid. Please try again.";

        header("Location: " . $redirectTo);
        exit;
    }
}