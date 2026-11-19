<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch all customers from customers table joined with orders
$query = "SELECT c.*, 
                 COUNT(o.id) AS total_orders, 
                 COALESCE(SUM(o.total_amount_pkr), 0) AS lifetime_spend 
          FROM customers c 
          LEFT JOIN orders o ON c.id = o.customer_id 
          GROUP BY c.id 
          ORDER BY c.id DESC";

$result = mysqli_query($conn, $query);
$customers = [];
if ($result) {
    $customers = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$total_customers = count($customers);
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
            <input type="text" class="form-control" id="tableSearchInput" placeholder="Quick search customers by name, city, email...">
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

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="page-title">Patron Directory</h1>
            <p class="page-subtitle">Registered customers from your database across major cities.</p>
          </div>
          <div>
            <span class="badge bg-dark px-3 py-2 fs-6"><?= $total_customers ?> Registered Patrons</span>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Client ID</th>
                  <th>Customer Name</th>
                  <th>Contact Info</th>
                  <th>City</th>
                  <th>Orders</th>
                  <th>Lifetime Spend</th>
                  <th>Registered</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="filterable-table-body">
                <?php if (empty($customers)) : ?>
                  <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                      <i class="bi bi-people display-6 d-block mb-2 text-muted"></i>
                      No customers registered in the database yet.
                    </td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($customers as $c) : ?>
                    <tr>
                      <td><code>CUST-<?= str_pad($c['id'], 3, '0', STR_PAD_LEFT) ?></code></td>
                      <td>
                        <strong class="text-dark">
                          <a href="customer-details.php?id=<?= $c['id'] ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($c['name']) ?>
                          </a>
                        </strong>
                      </td>
                      <td>
                        <div><?= htmlspecialchars($c['email']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($c['phone'] ?? '') ?></small>
                      </td>
                      <td><?= htmlspecialchars($c['city'] ?? 'N/A') ?></td>
                      <td><span class="badge bg-light text-dark border"><?= intval($c['total_orders']) ?> Orders</span></td>
                      <td class="fw-bold">PKR <?= number_format($c['lifetime_spend']) ?></td>
                      <td class="small text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                      <td>
                        <?php if ($c['status'] == 'Active') : ?>
                          <span class="badge-status badge-status-success">Active</span>
                        <?php elseif ($c['status'] == 'Blocked') : ?>
                          <span class="badge-status badge-status-danger">Blocked</span>
                        <?php else : ?>
                          <span class="badge-status badge-status-neutral"><?= htmlspecialchars($c['status']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <a href="customer-details.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-dark py-1 px-2">
                          <i class="bi bi-eye"></i> View Profile
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted">Showing <?= $total_customers ?> customer records</span>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>
</html>
