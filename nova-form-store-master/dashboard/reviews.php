<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch all customer reviews joined with products table
$query = "SELECT r.*, p.name AS product_name 
          FROM reviews r 
          LEFT JOIN products p ON r.product_id = p.id 
          ORDER BY r.id DESC";

$result = mysqli_query($conn, $query);
$reviews = [];
if ($result) {
    $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$total_reviews = count($reviews);
$approved_count = 0;
$pending_count  = 0;

foreach ($reviews as $rev) {
    if ($rev['status'] == 'Approved') $approved_count++;
    if ($rev['status'] == 'Pending') $pending_count++;
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
            <input type="text" class="form-control" id="tableSearchInput" placeholder="Quick search reviews by product, customer, text...">
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
              <li><a class="dropdown-item small py-2" href="reviews.php"><i class="bi bi-star text-primary me-2"></i> Pending reviews: <?= $pending_count ?></a></li>
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
            <h1 class="page-title">Patron Testimonials & Reviews</h1>
            <p class="page-subtitle">Moderate customer reviews and approve social proof for your store catalog.</p>
          </div>
          <div>
            <span class="badge bg-dark px-3 py-2 fs-6"><?= $total_reviews ?> Total Reviews</span>
          </div>
        </div>

        <div class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Product</th>
                  <th>Customer</th>
                  <th>Rating</th>
                  <th>Comment</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody class="filterable-table-body">
                <?php if (empty($reviews)) : ?>
                  <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="bi bi-chat-square-text display-6 d-block mb-2 text-muted"></i>
                      No reviews found in the database.
                    </td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($reviews as $r) : ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($r['product_name'] ?? 'Footwear Model') ?></strong></td>
                      <td>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($r['customer_name']) ?></div>
                      </td>
                      <td>
                        <div class="text-warning small text-nowrap">
                          <?php
                            $rating = intval($r['rating']);
                            for ($i = 1; $i <= 5; $i++) {
                                echo ($i <= $rating) ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                            }
                          ?>
                        </div>
                      </td>
                      <td class="small text-muted" style="max-width: 320px;">
                        "<?= htmlspecialchars($r['review_text']) ?>"
                      </td>
                      <td class="small text-muted text-nowrap"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                      <td>
                        <?php if ($r['status'] == 'Approved') : ?>
                          <span class="badge bg-success bg-opacity-10 text-success">Approved</span>
                        <?php elseif ($r['status'] == 'Pending') : ?>
                          <span class="badge bg-warning bg-opacity-10 text-warning">Pending</span>
                        <?php elseif ($r['status'] == 'Hidden') : ?>
                          <span class="badge bg-secondary bg-opacity-10 text-secondary">Hidden</span>
                        <?php else : ?>
                          <span class="badge bg-danger bg-opacity-10 text-danger"><?= htmlspecialchars($r['status']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="d-flex gap-1">
                          <?php if ($r['status'] !== 'Approved') : ?>
                            <a href="actions/review_status_action.php?id=<?= $r['id'] ?>&status=Approved" class="btn btn-sm btn-outline-success py-1 px-2" title="Approve Review">
                              <i class="bi bi-check-lg"></i>
                            </a>
                          <?php endif; ?>
                          <?php if ($r['status'] !== 'Hidden') : ?>
                            <a href="actions/review_status_action.php?id=<?= $r['id'] ?>&status=Hidden" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Hide Review">
                              <i class="bi bi-eye-slash"></i>
                            </a>
                          <?php endif; ?>
                          <a href="actions/review_status_action.php?id=<?= $r['id'] ?>&action=delete" class="btn btn-sm btn-outline-danger py-1 px-2" title="Delete Review" onclick="return confirm('Are you sure you want to delete this review?');">
                            <i class="bi bi-trash"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted">Showing <?= $total_reviews ?> reviews</span>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>
</html>
