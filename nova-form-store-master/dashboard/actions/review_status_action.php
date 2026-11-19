<?php
require_once __DIR__ . "/../settings/session_start.php";
require_once __DIR__ . "/../settings/auth_check.php";
require_once __DIR__ . "/../settings/db.php";

$id     = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = trim($_GET['action'] ?? '');
$status = trim($_GET['status'] ?? '');

if ($id > 0) {
    if ($action === 'delete') {
        if (mysqli_query($conn, "DELETE FROM reviews WHERE id = {$id}")) {
            $_SESSION['success'] = "Review deleted successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to delete review: " . mysqli_error($conn)];
        }
    } elseif (in_array($status, ['Approved', 'Pending', 'Hidden', 'Rejected'])) {
        $esc_status = mysqli_real_escape_string($conn, $status);
        if (mysqli_query($conn, "UPDATE reviews SET status = '{$esc_status}' WHERE id = {$id}")) {
            $_SESSION['success'] = "Review status changed to '{$status}'!";
        } else {
            $_SESSION['errors'] = ["Failed to update review status: " . mysqli_error($conn)];
        }
    }
}

header("Location: ../reviews.php");
exit;
