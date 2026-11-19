<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch all orders from orders table
$query = "SELECT * FROM orders ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$orders = [];
if ($result) {
    $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$total_orders = count($orders);
$pending_count = 0;
$processing_count = 0;
$delivered_count = 0;

foreach ($orders as $o) {
    if ($o['order_status'] == 'Pending Confirmation') $pending_count++;
    if ($o['order_status'] == 'Processing') $processing_count++;
    if ($o['order_status'] == 'Delivered') $delivered_count++;
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
            <input type="text" class="form-control" id="tableSearchInput" placeholder="Quick search orders by #, customer, city...">
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
              <li><a class="dropdown-item small py-2" href="orders.php"><i class="bi bi-bag-check text-success me-2"></i> Manage recent orders</a></li>
              <li><a class="dropdown-item small py-2" href="inventory.php"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Review inventory alerts</a></li>
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

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
          <div>
            <h1 class="page-title">Orders Management</h1>
            <p class="page-subtitle">Track, confirm, and dispatch customer orders across Pakistan.</p>
          </div>
          <div>
            <span class="badge bg-dark px-3 py-2 fs-6"><?= $total_orders ?> Total Orders</span>
          </div>
        </div>

        <!-- Orders Table -->
        <div class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Total Amount</th>
                  <th>Payment Method</th>
                  <th>Payment Status</th>
                  <th>Order Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody class="filterable-table-body">
                <?php if (empty($orders)) : ?>
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="bi bi-cart-x display-6 d-block mb-2 text-muted"></i>
                      No orders recorded in the database yet.
                    </td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($orders as $o) : ?>
                    <tr>
                      <td>
                        <strong>
                          <a href="order-details.php?id=<?= $o['id'] ?>" class="text-dark text-decoration-none">
                            #<?= htmlspecialchars($o['order_number']) ?>
                          </a>
                        </strong>
                      </td>
                      <td>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($o['customer_name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($o['shipping_city']) ?></small>
                      </td>
                      <td class="small text-muted"><?= date('d M Y', strtotime($o['placed_at'] ?? $o['created_at'])) ?></td>
                      <td class="fw-bold">PKR <?= number_format($o['total_amount_pkr']) ?></td>
                      <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($o['payment_method']) ?></span></td>
                      <td>
                        <?php if ($o['payment_status'] == 'Paid') : ?>
                          <span class="badge bg-success bg-opacity-10 text-success">Paid</span>
                        <?php elseif ($o['payment_status'] == 'Unpaid') : ?>
                          <span class="badge bg-warning bg-opacity-10 text-warning">Unpaid</span>
                        <?php else : ?>
                          <span class="badge bg-danger bg-opacity-10 text-danger"><?= htmlspecialchars($o['payment_status']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($o['order_status'] == 'Delivered') : ?>
                          <span class="badge-status badge-status-success">Delivered</span>
                        <?php elseif ($o['order_status'] == 'Dispatched') : ?>
                          <span class="badge-status badge-status-info">Dispatched</span>
                        <?php elseif ($o['order_status'] == 'Processing') : ?>
                          <span class="badge-status badge-status-warning">Processing</span>
                        <?php elseif ($o['order_status'] == 'Cancelled') : ?>
                          <span class="badge-status badge-status-danger">Cancelled</span>
                        <?php else : ?>
                          <span class="badge-status badge-status-neutral"><?= htmlspecialchars($o['order_status']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a href="order-details.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-dark py-1 px-2" title="Manage order">
                          <i class="bi bi-eye"></i> Details
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted">Showing <?= $total_orders ?> orders from database</span>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>
</html>
