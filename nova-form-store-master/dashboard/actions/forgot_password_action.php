<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../forgot-password.php");
        exit;
    }

    $esc_email = mysqli_real_escape_string($conn, $email);
    $query = "SELECT id, name FROM admins WHERE email = '{$esc_email}' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        $temp_password = "Nova@" . rand(100000, 999999);
        $hashed = password_hash($temp_password, PASSWORD_DEFAULT);

        $update = "UPDATE admins SET password_hash = '{$hashed}' WHERE id = {$admin['id']}";
        if (mysqli_query($conn, $update)) {
            $_SESSION['success'] = "Password reset! Temporary password for <strong>" . htmlspecialchars($admin['name']) . "</strong> is: <code>{$temp_password}</code>. Please login and change it.";
            header("Location: ../login.php");
            exit;
        } else {
            $_SESSION['errors'] = ["Failed to reset password: " . mysqli_error($conn)];
            header("Location: ../forgot-password.php");
            exit;
        }
    } else {
        $_SESSION['errors'] = ["No administrator account found with this email address."];
        header("Location: ../forgot-password.php");
        exit;
    }
} else {
    header("Location: ../forgot-password.php");
    exit;
}
