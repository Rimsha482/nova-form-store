<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $brand_name              = trim($_POST['brand_name'] ?? '');
    $helpline_phone          = trim($_POST['helpline_phone'] ?? '');
    $support_email           = trim($_POST['support_email'] ?? '');
    $currency_timezone       = trim($_POST['currency_timezone'] ?? 'PKR (Pakistan Standard Time - PKT)');
    $delivery_fee            = floatval($_POST['delivery_fee'] ?? 0);
    $free_delivery_threshold = floatval($_POST['free_delivery_threshold'] ?? 0);
    $delivery_notice         = trim($_POST['delivery_notice'] ?? '');
    $exchange_policy         = trim($_POST['exchange_policy'] ?? '');

    // Validate required fields
    if (empty($brand_name)) {
        $errors[] = "Brand name is required.";
    }

    if (!empty($support_email) && !filter_var($support_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid support email address.";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../settings.php");
        exit;
    }

    // Auto-create table if not already created in MySQL
    $create_table = "CREATE TABLE IF NOT EXISTS `store_settings` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `brand_name` VARCHAR(150) NOT NULL DEFAULT 'NOVA FORM',
        `helpline_phone` VARCHAR(50) NOT NULL DEFAULT '+92 300 8472911',
        `support_email` VARCHAR(191) NOT NULL DEFAULT 'concierge@novaformshoes.pk',
        `currency_timezone` VARCHAR(100) NOT NULL DEFAULT 'PKR (Pakistan Standard Time - PKT)',
        `delivery_fee` DECIMAL(10,2) NOT NULL DEFAULT 250.00,
        `free_delivery_threshold` DECIMAL(10,2) NOT NULL DEFAULT 10000.00,
        `delivery_notice` VARCHAR(255) NULL,
        `exchange_policy` TEXT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    mysqli_query($conn, $create_table);

    // Escape values for safe query execution
    $esc_brand  = mysqli_real_escape_string($conn, $brand_name);
    $esc_phone  = mysqli_real_escape_string($conn, $helpline_phone);
    $esc_email  = mysqli_real_escape_string($conn, $support_email);
    $esc_tz     = mysqli_real_escape_string($conn, $currency_timezone);
    $esc_notice = mysqli_real_escape_string($conn, $delivery_notice);
    $esc_policy = mysqli_real_escape_string($conn, $exchange_policy);

    // Check if record with id = 1 exists
    $check = mysqli_query($conn, "SELECT id FROM store_settings WHERE id = 1 LIMIT 1");

    if ($check && mysqli_num_rows($check) > 0) {
        $sql = "UPDATE store_settings SET 
                    brand_name              = '{$esc_brand}',
                    helpline_phone          = '{$esc_phone}',
                    support_email           = '{$esc_email}',
                    currency_timezone       = '{$esc_tz}',
                    delivery_fee            = '{$delivery_fee}',
                    free_delivery_threshold = '{$free_delivery_threshold}',
                    delivery_notice         = '{$esc_notice}',
                    exchange_policy         = '{$esc_policy}'
                WHERE id = 1";
    } else {
        $sql = "INSERT INTO store_settings (
                    id, brand_name, helpline_phone, support_email, currency_timezone, 
                    delivery_fee, free_delivery_threshold, delivery_notice, exchange_policy
                ) VALUES (
                    1, '{$esc_brand}', '{$esc_phone}', '{$esc_email}', '{$esc_tz}', 
                    '{$delivery_fee}', '{$free_delivery_threshold}', '{$esc_notice}', '{$esc_policy}'
                )";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Store settings updated successfully!";
    } else {
        $_SESSION['errors'] = ["Failed to save settings: " . mysqli_error($conn)];
    }

    header("Location: ../settings.php");
    exit;
} else {
    header("Location: ../settings.php");
    exit;
}
