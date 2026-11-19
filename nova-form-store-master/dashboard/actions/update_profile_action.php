<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $admin_id         = intval($_SESSION['user_id'] ?? 0);
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $existing_avatar  = trim($_POST['existing_avatar'] ?? '');
    $avatar_url       = $existing_avatar;

    // Validate required fields
    if (empty($name)) {
        $errors[] = "Full name is required.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }

    // Check if email is already taken by another admin
    $esc_email = mysqli_real_escape_string($conn, $email);
    $check_email = mysqli_query($conn, "SELECT id FROM admins WHERE email = '{$esc_email}' AND id != {$admin_id} LIMIT 1");
    if ($check_email && mysqli_num_rows($check_email) > 0) {
        $errors[] = "This email address is already in use by another administrator.";
    }

    // Validate password if provided
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Password confirmation does not match.";
        }
    }

    // Handle avatar upload if provided
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['avatar']['tmp_name'];
        $file_name = $_FILES['avatar']['name'];
        $file_size = $_FILES['avatar']['size'];
        $ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid avatar image format. Allowed: JPG, PNG, WEBP.";
        } elseif ($file_size > 3 * 1024 * 1024) {
            $errors[] = "Avatar size must not exceed 3MB.";
        } else {
            $upload_dir = __DIR__ . "/../uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $new_filename = 'avatar_' . time() . '_' . uniqid() . '.' . $ext;
            $destination  = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $destination)) {
                $avatar_url = "uploads/" . $new_filename;
            } else {
                $errors[] = "Failed to upload avatar.";
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../profile.php");
        exit;
    }

    // Escape inputs for database query
    $esc_name   = mysqli_real_escape_string($conn, $name);
    $esc_phone  = mysqli_real_escape_string($conn, $phone);
    $esc_avatar = mysqli_real_escape_string($conn, $avatar_url);

    // Update with or without new password
    if (!empty($new_password)) {
        $hashed_pwd = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE admins SET 
                    name = '{$esc_name}',
                    email = '{$esc_email}',
                    phone = '{$esc_phone}',
                    avatar_url = '{$esc_avatar}',
                    password_hash = '{$hashed_pwd}'
                WHERE id = {$admin_id}";
    } else {
        $sql = "UPDATE admins SET 
                    name = '{$esc_name}',
                    email = '{$esc_email}',
                    phone = '{$esc_phone}',
                    avatar_url = '{$esc_avatar}'
                WHERE id = {$admin_id}";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Profile credentials updated successfully!";
    } else {
        $_SESSION['errors'] = ["Failed to update profile: " . mysqli_error($conn)];
    }

    header("Location: ../profile.php");
    exit;
} else {
    header("Location: ../profile.php");
    exit;
}
