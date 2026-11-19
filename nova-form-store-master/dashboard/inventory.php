<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch all products with their category names using simple core PHP
$query = "SELECT products.*, categories.name AS category_name 
          FROM products 
          LEFT JOIN categories ON products.category_id = categories.id 
          ORDER BY products.id DESC";
$result = mysqli_query($conn, $query);

$products = [];
if ($result) {
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Calculate summary numbers using basic variables and loops
$total_products    = count($products);
$total_stock_pairs = 0;
$low_stock_count   = 0;
$out_of_stock_count = 0;

foreach ($products as $p) {
    $qty       = intval($p['stock_quantity']);
    $threshold = intval($p['low_stock_threshold'] ?? 5);

    $total_stock_pairs += $qty;

    if ($qty <= 0) {
        $out_of_stock_count++;
    } elseif ($qty <= $threshold) {
        $low_stock_count++;
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
            <input type="text" class="form-control" id="tableSearchInput" placeholder="Quick search inventory, SKU, category...">
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
              <li><a class="dropdown-item small py-2" href="inventory.php"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Low stock count: <?= $low_stock_count ?></a></li>
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

        <!-- Page Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
          <div>
            <h1 class="page-title">Warehouse Inventory Tracking</h1>
            <p class="page-subtitle">Real-time stock ledger across fulfillment centers.</p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#stockAdjustModal">
              <i class="bi bi-arrow-down-up me-1"></i> Quick Stock Adjustment
            </button>
            <a href="product-add.php" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
          </div>
        </div>

        <!-- Inventory KPI Cards -->
        <div class="row g-3 mb-4">
          <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <span class="text-muted small">Total Models</span>
                  <h4 class="mb-0 fw-bold"><?= $total_products ?></h4>
                </div>
                <div class="fs-2 text-primary"><i class="bi bi-box-seam"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <span class="text-muted small">Total Stock</span>
                  <h4 class="mb-0 fw-bold"><?= $total_stock_pairs ?> pairs</h4>
                </div>
                <div class="fs-2 text-success"><i class="bi bi-boxes"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <span class="text-muted small">Low Stock Items</span>
                  <h4 class="mb-0 fw-bold text-warning"><?= $low_stock_count ?></h4>
                </div>
                <div class="fs-2 text-warning"><i class="bi bi-exclamation-triangle"></i></div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <span class="text-muted small">Out of Stock</span>
                  <h4 class="mb-0 fw-bold text-danger"><?= $out_of_stock_count ?></h4>
                </div>
                <div class="fs-2 text-danger"><i class="bi bi-x-circle"></i></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Inventory Table -->
        <div class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Footwear Model</th>
                  <th>SKU</th>
                  <th>Available Stock</th>
                  <th>Reorder Level</th>
                  <th>Stock Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody class="filterable-table-body">
                <?php if (empty($products)) : ?>
                  <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                      No products found in inventory. <a href="product-add.php">Add a product now</a>.
                    </td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($products as $p) : ?>
                    <?php
                      $stock = intval($p['stock_quantity']);
                      $threshold = intval($p['low_stock_threshold'] ?? 5);
                      $image = !empty($p['image_url']) ? $p['image_url'] : 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=800&auto=format&fit=crop';
                    ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?= htmlspecialchars($image) ?>" class="rounded-1" style="width: 40px; height: 40px; object-fit: cover;" alt="<?= htmlspecialchars($p['name']) ?>">
                          <div>
                            <strong class="text-dark"><?= htmlspecialchars($p['name']) ?></strong>
                            <div class="text-muted small"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></div>
                          </div>
                        </div>
                      </td>
                      <td><code><?= htmlspecialchars($p['sku']) ?></code></td>
                      <td class="fw-bold"><?= $stock ?> pairs</td>
                      <td><?= $threshold ?> pairs</td>
                      <td>
                        <?php if ($stock <= 0) : ?>
                          <span class="badge bg-danger">Out of Stock</span>
                        <?php elseif ($stock <= $threshold) : ?>
                          <span class="badge bg-warning text-dark">Low Stock</span>
                        <?php else : ?>
                          <span class="badge bg-success">In Stock</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="d-flex gap-1">
                          <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2" data-bs-toggle="modal" data-bs-target="#stockAdjustModal" onclick="setAdjustProduct('<?= $p['id'] ?>')" title="Quick stock adjust">
                            <i class="bi bi-arrow-down-up"></i> Adjust
                          </button>
                          <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark py-1 px-2" title="Edit product details">
                            <i class="bi bi-pencil"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Stock Adjust Modal -->
        <div class="modal fade" id="stockAdjustModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title fs-6 fw-bold">Stock Adjustment Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form action="actions/adjust_stock_action.php" method="POST">
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Select Footwear Model *</label>
                    <select class="form-select" name="product_id" id="modalProductSelect" required>
                      <option value="" disabled selected>-- Select Footwear Model --</option>
                      <?php foreach ($products as $p) : ?>
                        <option value="<?= $p['id'] ?>">
                          <?= htmlspecialchars($p['name']) ?> (SKU: <?= htmlspecialchars($p['sku']) ?> &bull; Current: <?= $p['stock_quantity'] ?>)
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="row g-2 mb-3">
                    <div class="col-6">
                      <label class="form-label small fw-bold">Adjustment Type *</label>
                      <select class="form-select" name="adjustment_type" required>
                        <option value="add">Add (+ Restock / Received)</option>
                        <option value="subtract">Subtract (- Damaged / Returned)</option>
                      </select>
                    </div>
                    <div class="col-6">
                      <label class="form-label small fw-bold">Quantity (Pairs) *</label>
                      <input type="number" name="quantity" class="form-control" value="5" min="1" required>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Reason Note (Optional)</label>
                    <input type="text" name="reason_note" class="form-control" placeholder="e.g. Workshop shipment batch #4">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-sm btn-dark">Record Adjustment</button>
                </div>
              </form>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
  <script>
    function setAdjustProduct(productId) {
      var select = document.getElementById('modalProductSelect');
      if (select) {
        select.value = productId;
      }
    }
  </script>
</body>
</html>
