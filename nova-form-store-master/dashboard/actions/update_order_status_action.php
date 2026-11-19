<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $order_id       = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $order_status   = trim($_POST['order_status'] ?? '');
    $payment_status = trim($_POST['payment_status'] ?? '');

    $valid_order_statuses = ['Pending Confirmation', 'Processing', 'Confirmed', 'Dispatched', 'Delivered', 'Cancelled', 'Returned'];
    $valid_pay_statuses   = ['Unpaid', 'Paid', 'Refunded', 'Failed'];

    if ($order_id > 0 && in_array($order_status, $valid_order_statuses) && in_array($payment_status, $valid_pay_statuses)) {
        $esc_order_status = mysqli_real_escape_string($conn, $order_status);
        $esc_pay_status   = mysqli_real_escape_string($conn, $payment_status);

        $sql = "UPDATE orders SET order_status = '{$esc_order_status}', payment_status = '{$esc_pay_status}' WHERE id = {$order_id}";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['success'] = "Order status updated successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to update order: " . mysqli_error($conn)];
        }
    } else {
        $_SESSION['errors'] = ["Invalid order data or status parameters."];
    }

    header("Location: ../order-details.php?id=" . $order_id);
    exit;
} else {
    header("Location: ../orders.php");
    exit;
}
