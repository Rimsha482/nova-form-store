<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $product_id      = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $adjustment_type = trim($_POST['adjustment_type'] ?? 'add');
    $quantity        = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    $reason_note     = trim($_POST['reason_note'] ?? '');

    if ($product_id <= 0) {
        $errors[] = "Please select a valid footwear model";
    }

    if ($quantity <= 0) {
        $errors[] = "Adjustment quantity must be greater than 0";
    }

    // Check if product exists
    $find_query = mysqli_query($conn, "SELECT id, name, stock_quantity FROM products WHERE id = {$product_id} LIMIT 1");
    if (!$find_query || mysqli_num_rows($find_query) == 0) {
        $errors[] = "Selected product was not found";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../inventory.php");
        exit;
    }

    $product       = mysqli_fetch_assoc($find_query);
    $current_stock = intval($product['stock_quantity']);

    if ($adjustment_type == 'subtract') {
        $new_stock   = max(0, $current_stock - $quantity);
        $qty_change  = -$quantity;
        $change_type = 'Manual Adjustment';
    } else {
        $new_stock   = $current_stock + $quantity;
        $qty_change  = $quantity;
        $change_type = 'Restock';
    }

    // Update stock quantity in products
    $update_query = "UPDATE products SET stock_quantity = {$new_stock} WHERE id = {$product_id}";
    if (mysqli_query($conn, $update_query)) {

        // Record in inventory_logs
        $escaped_note = mysqli_real_escape_string($conn, $reason_note);
        $admin_id     = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : "NULL";
        $log_query    = "INSERT INTO inventory_logs (
            product_id, change_type, quantity_change, previous_stock, new_stock, reference_note, recorded_by_admin_id
        ) VALUES (
            {$product_id}, '{$change_type}', {$qty_change}, {$current_stock}, {$new_stock}, '{$escaped_note}', {$admin_id}
        )";
        mysqli_query($conn, $log_query);

        $_SESSION['success'] = "Stock for '{$product['name']}' adjusted successfully! New stock: {$new_stock} pairs.";
        header("Location: ../inventory.php");
        exit;
    } else {
        $_SESSION['errors'] = ["Failed to update stock: " . mysqli_error($conn)];
        header("Location: ../inventory.php");
        exit;
    }
} else {
    header("Location: ../inventory.php");
    exit;
}

