<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no order ID, pick latest order
if ($order_id <= 0) {
    $first_order = mysqli_query($conn, "SELECT id FROM orders ORDER BY id DESC LIMIT 1");
    if ($first_order && mysqli_num_rows($first_order) > 0) {
        $row = mysqli_fetch_assoc($first_order);
        $order_id = intval($row['id']);
    }
}

// Fetch order
$order = null;
if ($order_id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM orders WHERE id = {$order_id} LIMIT 1");
    if ($res && mysqli_num_rows($res) > 0) {
        $order = mysqli_fetch_assoc($res);
    }
}

// Fetch order items
$items = [];
if ($order_id > 0) {
    $items_res = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id = {$order_id}");
    if ($items_res) {
        $items = mysqli_fetch_all($items_res, MYSQLI_ASSOC);
    }
}
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>

<body>

  <div class="admin-wrapper">

    <!-- Admin Sidebar -->
    <?php require_once __DIR__ . "/partials/sidebar.php"; ?>

    <div class="admin-main">

      <!-- Topbar -->
      <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
          <button class="btn btn-link text-dark p-0 d-lg-none" id="sidebarToggleBtn" aria-label="Toggle Navigation">
            <i class="bi bi-list fs-3"></i>
          </button>
          <div class="topbar-search">
            <i class="bi bi-search search-icon"></i>
            <input type="text" class="form-control" id="tableSearchInput" placeholder="Quick search inventory, orders...">
          </div>
        </div>

        <div class="topbar-actions">
          <a href="../front-site/index.php" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1">
            <i class="bi bi-eye"></i> View Site
          </a>

          <div class="dropdown">
            <a href="#" class="topbar-icon-btn" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
              <i class="bi bi-bell"></i>
              <span class="topbar-badge-dot"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 280px;">
              <li><h6 class="dropdown-header text-uppercase small fw-bold">Recent Alerts</h6></li>
              <li><a class="dropdown-item small py-2" href="orders.php"><i class="bi bi-bag-check text-success me-2"></i> Manage orders</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-center small text-primary fw-semibold" href="orders.php">View All Notifications</a></li>
            </ul>
          </div>

          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop" class="admin-avatar me-2" alt="Admin">
              <div class="d-none d-sm-block text-start">
                <div class="fw-semibold small leading-none">Admin</div>
                <div class="text-muted" style="font-size: 0.7rem;">Store Manager</div>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2 text-muted"></i> My Profile</a></li>
              <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2 text-muted"></i> Store Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="actions/logout_action.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </header>

      <main class="admin-content">

        <!-- Error Notifications -->
        <?php if (!empty($errors)) : ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
              <?php foreach ($errors as $error) : ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <!-- Success Notification -->
        <?php if (!empty($success)) : ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <?php if (!$order) : ?>
          <div class="alert alert-warning">
            <h5>Order Not Found</h5>
            <p>No order found in the database. Please check the orders list.</p>
            <a href="orders.php" class="btn btn-sm btn-dark">Back to Orders</a>
          </div>
        <?php else : ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="page-title">Order #<?= htmlspecialchars($order['order_number']) ?> Details</h1>
            <p class="page-subtitle">Placed by <?= htmlspecialchars($order['customer_name']) ?> on <?= date('d M Y, h:i A', strtotime($order['placed_at'] ?? $order['created_at'])) ?></p>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();"><i class="bi bi-printer me-1"></i> Print Invoice</button>
            <a href="orders.php" class="btn btn-sm btn-dark">Back to Orders</a>
          </div>
        </div>

        <div class="row g-4">

          <div class="col-lg-8">

            <!-- Status & Dispatch Control -->
            <div class="admin-card mb-4 border-start border-4 border-warning">
              <div class="admin-card-header"><h5 class="admin-card-title">Manage Order & Payment Status</h5></div>
              <div class="admin-card-body">
                <form action="actions/update_order_status_action.php" method="POST">
                  <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                  <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                      <label class="form-label small fw-bold">Order Status</label>
                      <select name="order_status" class="form-select form-select-sm" required>
                        <?php 
                          $statuses = ['Pending Confirmation', 'Processing', 'Confirmed', 'Dispatched', 'Delivered', 'Cancelled', 'Returned'];
                          foreach ($statuses as $st) :
                        ?>
                          <option value="<?= $st ?>" <?= ($order['order_status'] == $st) ? 'selected' : '' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">Payment Status</label>
                      <select name="payment_status" class="form-select form-select-sm" required>
                        <option value="Unpaid" <?= ($order['payment_status'] == 'Unpaid') ? 'selected' : '' ?>>Unpaid</option>
                        <option value="Paid" <?= ($order['payment_status'] == 'Paid') ? 'selected' : '' ?>>Paid</option>
                        <option value="Refunded" <?= ($order['payment_status'] == 'Refunded') ? 'selected' : '' ?>>Refunded</option>
                        <option value="Failed" <?= ($order['payment_status'] == 'Failed') ? 'selected' : '' ?>>Failed</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <button type="submit" class="btn btn-sm btn-dark w-100">Update Status</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Ordered Items Table -->
            <div class="admin-card mb-4">
              <div class="admin-card-header"><h5 class="admin-card-title">Ordered Footwear Items</h5></div>
              <div class="admin-table-wrap">
                <table class="admin-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Variant Details</th>
                      <th>Unit Price</th>
                      <th>Qty</th>
                      <th>Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($items)) : ?>
                      <tr>
                        <td colspan="5" class="text-center py-3 text-muted">No individual items recorded for this order.</td>
                      </tr>
                    <?php else : ?>
                      <?php foreach ($items as $item) : ?>
                        <tr>
                          <td>
                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                            <div class="text-muted small">SKU: <?= htmlspecialchars($item['product_sku'] ?? 'N/A') ?></div>
                          </td>
                          <td>
                            Size: EU <?= htmlspecialchars($item['size_eu'] ?? '') ?><br>
                            <small class="text-muted"><?= htmlspecialchars($item['color_name'] ?? '') ?></small>
                          </td>
                          <td>PKR <?= number_format($item['unit_price_pkr']) ?></td>
                          <td><?= intval($item['quantity']) ?></td>
                          <td class="fw-bold">PKR <?= number_format($item['subtotal_pkr']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>

          <!-- Right Column Summary -->
          <div class="col-lg-4">
            
            <!-- Customer Card -->
            <div class="admin-card mb-4">
              <div class="admin-card-header"><h5 class="admin-card-title">Customer Details</h5></div>
              <div class="admin-card-body small">
                <div class="mb-2"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></div>
                <div class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></div>
                <div class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?></div>
                <div class="mb-2"><strong>City:</strong> <?= htmlspecialchars($order['shipping_city']) ?></div>
              </div>
            </div>

            <!-- Financial Summary -->
            <div class="admin-card mb-4">
              <div class="admin-card-header"><h5 class="admin-card-title">Payment & Total</h5></div>
              <div class="admin-card-body small">
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Subtotal:</span>
                  <strong>PKR <?= number_format($order['subtotal_pkr']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Delivery Fee:</span>
                  <strong>PKR <?= number_format($order['shipping_fee_pkr']) ?></strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2 fs-6">
                  <span>Grand Total:</span>
                  <strong class="text-dark">PKR <?= number_format($order['total_amount_pkr']) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-muted">Payment Method:</span>
                  <span><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>
              </div>
            </div>

          </div>

        </div>

        <?php endif; ?>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>
</html>



