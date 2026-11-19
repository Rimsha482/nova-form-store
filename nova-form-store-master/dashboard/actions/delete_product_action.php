<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0) {
        // Delete related variants
        mysqli_query($conn, "DELETE FROM product_variants WHERE product_id = {$id}");

        // Delete product
        $delete = mysqli_query($conn, "DELETE FROM products WHERE id = {$id}");

        if ($delete) {
            $_SESSION['success'] = "Product deleted successfully from database!";
        } else {
            $_SESSION['errors'] = ["Failed to delete product: " . mysqli_error($conn)];
        }
    } else {
        $_SESSION['errors'] = ["Invalid product ID."];
    }
}

header("Location: ../products.php");
exit;
