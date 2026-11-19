<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch current store settings from store_settings table
$query = "SELECT * FROM store_settings WHERE id = 1 LIMIT 1";
$result = mysqli_query($conn, $query);
$settings = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : [];
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
          <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center gap-1">
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
            <h1 class="page-title">Storefront Configuration & Logistics Settings</h1>
            <p class="page-subtitle">Configure Pakistani delivery thresholds, store contact details, and policies.</p>
          </div>
        </div>

        <form action="actions/save_settings_action.php" method="POST">
          <div class="row g-4">

            <div class="col-lg-8">

              <!-- Store Identity -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">1. Store Identity</h5></div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Brand Name *</label>
                      <input type="text" name="brand_name" class="form-control" value="<?= htmlspecialchars($settings['brand_name'] ?? 'NOVA FORM') ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Helpline Phone</label>
                      <input type="text" name="helpline_phone" class="form-control" value="<?= htmlspecialchars($settings['helpline_phone'] ?? '+92 300 8472911') ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Support Email</label>
                      <input type="email" name="support_email" class="form-control" value="<?= htmlspecialchars($settings['support_email'] ?? 'concierge@novaformshoes.pk') ?>">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Currency & Timezone</label>
                      <input type="text" name="currency_timezone" class="form-control bg-light" value="<?= htmlspecialchars($settings['currency_timezone'] ?? 'PKR (Pakistan Standard Time - PKT)') ?>" readonly>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Logistics Settings -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">2. Delivery Fees & Logistics</h5></div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Standard Delivery Fee (PKR)</label>
                      <input type="number" step="0.01" name="delivery_fee" class="form-control" value="<?= htmlspecialchars($settings['delivery_fee'] ?? '250') ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Free Delivery Threshold (PKR)</label>
                      <input type="number" step="0.01" name="free_delivery_threshold" class="form-control" value="<?= htmlspecialchars($settings['free_delivery_threshold'] ?? '10000') ?>" required>
                    </div>
                    <div class="col-12">
                      <label class="form-label small fw-bold">Delivery Estimate Notice</label>
                      <input type="text" name="delivery_notice" class="form-control" value="<?= htmlspecialchars($settings['delivery_notice'] ?? '2-3 business days for major cities (Lahore, Karachi, Islamabad); 3-5 days nationwide.') ?>">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Policies -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">3. Exchange & Warranty Policies</h5></div>
                <div class="admin-card-body">
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Exchange Period & Terms</label>
                    <textarea name="exchange_policy" class="form-control" rows="3"><?= htmlspecialchars($settings['exchange_policy'] ?? '15-Day Complimentary Doorstep Exchange for sizing adjustment.') ?></textarea>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-dark px-4 py-2"><i class="bi bi-check-lg me-1"></i> Save Settings</button>

            </div>

            <!-- Right Column Quick Status -->
            <div class="col-lg-4">
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">Payment & Security Status</h5></div>
                <div class="admin-card-body small">
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Primary Gateway:</span>
                    <strong>Cash on Delivery (Active)</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Direct Bank Transfer:</span>
                    <strong>Meezan / HBL / Alfalah</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">SSL Encryption:</span>
                    <strong class="text-success"><i class="bi bi-shield-check"></i> Enabled</strong>
                  </div>
                </div>
              </div>

              <div class="admin-card">
                <div class="admin-card-header"><h5 class="admin-card-title">Database Storage</h5></div>
                <div class="admin-card-body small text-muted">
                  These configuration settings are stored in the <code>store_settings</code> table and apply across your checkout and storefront pages.
                </div>
              </div>
            </div>

          </div>
        </form>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>
</html>
