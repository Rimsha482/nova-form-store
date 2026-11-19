<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch logged in administrator data
$admin_id = intval($_SESSION['user_id'] ?? 0);
$query    = "SELECT * FROM admins WHERE id = {$admin_id} LIMIT 1";
$result   = mysqli_query($conn, $query);
$admin    = ($result && mysqli_num_rows($result) > 0) ? mysqli_fetch_assoc($result) : null;

$avatar = (!empty($admin['avatar_url'])) 
  ? $admin['avatar_url'] 
  : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop';
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
              <li><a class="dropdown-item small py-2" href="orders.php"><i class="bi bi-bag-check text-success me-2"></i> Manage recent orders</a></li>
              <li><a class="dropdown-item small py-2" href="inventory.php"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Review inventory alerts</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-center small text-primary fw-semibold" href="orders.php">View All Notifications</a></li>
            </ul>
          </div>

          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?= htmlspecialchars($avatar) ?>" class="admin-avatar me-2" alt="Admin" style="width: 32px; height: 32px; object-fit: cover; border-radius: 50%;">
              <div class="d-none d-sm-block text-start">
                <div class="fw-semibold small leading-none"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></div>
                <div class="text-muted" style="font-size: 0.7rem;"><?= htmlspecialchars($admin['role'] ?? 'Store Director') ?></div>
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
            <h1 class="page-title">Administrator Profile</h1>
            <p class="page-subtitle">Manage personal credentials, contact info, and login security.</p>
          </div>
        </div>

        <form action="actions/update_profile_action.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="existing_avatar" value="<?= htmlspecialchars($admin['avatar_url'] ?? '') ?>">

          <div class="row g-4">

            <div class="col-lg-8">

              <!-- Account Details -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">1. Account Details</h5></div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Full Name *</label>
                      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($admin['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Administrative Role</label>
                      <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($admin['role'] ?? 'Store Director') ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Email Address *</label>
                      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Phone Number</label>
                      <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>" placeholder="+92 300 1234567">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Security & Password -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">2. Security & Password Update</h5></div>
                <div class="admin-card-body">
                  <p class="text-muted small mb-3">Leave blank if you do not wish to change your password.</p>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">New Password</label>
                      <input type="password" name="new_password" class="form-control" placeholder="Minimum 6 characters">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Confirm New Password</label>
                      <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
                    </div>
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-dark px-4 py-2"><i class="bi bi-check-lg me-1"></i> Update Profile</button>

            </div>

            <!-- Right Column: Avatar Details -->
            <div class="col-lg-4">
              <div class="admin-card p-4 text-center">
                <img src="<?= htmlspecialchars($avatar) ?>" class="admin-avatar mb-3" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;" alt="Admin Avatar">
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></h5>
                <div class="text-muted small mb-3"><?= htmlspecialchars($admin['role'] ?? 'Store Director') ?></div>
                
                <div class="text-start mt-3 border-top pt-3">
                  <label class="form-label small fw-bold">Update Profile Photo</label>
                  <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*">
                  <div class="text-muted mt-1" style="font-size: 0.72rem;">JPG, PNG, WEBP (Max 3MB). Leave empty to keep existing.</div>
                </div>
              </div>

              <div class="admin-card mt-4">
                <div class="admin-card-header"><h5 class="admin-card-title">Account Security</h5></div>
                <div class="admin-card-body small text-muted">
                  <div class="d-flex justify-content-between mb-2">
                    <span>Status:</span>
                    <strong class="text-success"><?= htmlspecialchars($admin['status'] ?? 'Active') ?></strong>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span>Account ID:</span>
                    <strong>#<?= $admin_id ?></strong>
                  </div>
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
