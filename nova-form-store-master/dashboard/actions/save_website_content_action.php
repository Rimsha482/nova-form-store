<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $hero_headline  = trim($_POST['hero_headline'] ?? '');
    $hero_subtitle  = trim($_POST['hero_subtitle'] ?? '');
    $hero_lead      = trim($_POST['hero_lead'] ?? '');
    $promo_headline = trim($_POST['promo_headline'] ?? '');
    $promo_btn_text = trim($_POST['promo_btn_text'] ?? '');

    // Validate required fields
    if (empty($hero_headline)) {
        $errors[] = "Homepage hero headline is required.";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../website-content.php");
        exit;
    }

    // Auto-create table if not already created in MySQL
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `website_content` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `hero_headline` VARCHAR(255) NOT NULL,
        `hero_subtitle` VARCHAR(255) NULL,
        `hero_lead` TEXT NULL,
        `promo_headline` VARCHAR(255) NULL,
        `promo_btn_text` VARCHAR(100) NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    mysqli_query($conn, $create_table_sql);

    // Escape strings for safe SQL execution
    $esc_headline   = mysqli_real_escape_string($conn, $hero_headline);
    $esc_subtitle   = mysqli_real_escape_string($conn, $hero_subtitle);
    $esc_lead       = mysqli_real_escape_string($conn, $hero_lead);
    $esc_promo_head = mysqli_real_escape_string($conn, $promo_headline);
    $esc_promo_btn  = mysqli_real_escape_string($conn, $promo_btn_text);

    // Check if record with id = 1 exists
    $check_query = mysqli_query($conn, "SELECT id FROM website_content WHERE id = 1 LIMIT 1");

    if ($check_query && mysqli_num_rows($check_query) > 0) {
        // Update existing record
        $sql = "UPDATE website_content SET 
                    hero_headline  = '{$esc_headline}',
                    hero_subtitle  = '{$esc_subtitle}',
                    hero_lead      = '{$esc_lead}',
                    promo_headline = '{$esc_promo_head}',
                    promo_btn_text = '{$esc_promo_btn}'
                WHERE id = 1";
    } else {
        // Insert new record
        $sql = "INSERT INTO website_content (id, hero_headline, hero_subtitle, hero_lead, promo_headline, promo_btn_text) 
                VALUES (1, '{$esc_headline}', '{$esc_subtitle}', '{$esc_lead}', '{$esc_promo_head}', '{$esc_promo_btn}')";
    }

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Website content updated successfully!";
    } else {
        $_SESSION['errors'] = ["Failed to save website content: " . mysqli_error($conn)];
    }

    header("Location: ../website-content.php");
    exit;
} else {
    header("Location: ../website-content.php");
    exit;
}
