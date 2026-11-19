<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Fetch categories for filtering dropdown
$cat_query = "SELECT * FROM categories ORDER BY name ASC";
$cat_result = mysqli_query($conn, $cat_query);
$categories = $cat_result ? mysqli_fetch_all($cat_result, MYSQLI_ASSOC) : [];

// Fetch brands for filtering dropdown
$brand_query = "SELECT * FROM brands ORDER BY name ASC";
$brand_result = mysqli_query($conn, $brand_query);
$brands = $brand_result ? mysqli_fetch_all($brand_result, MYSQLI_ASSOC) : [];

// Fetch all products with category and brand details from database
$query = "SELECT products.*, 
                 categories.name AS category_name, 
                 brands.name AS brand_name 
          FROM products 
          LEFT JOIN categories ON products.category_id = categories.id 
          LEFT JOIN brands ON products.brand_id = brands.id 
          ORDER BY products.id DESC";

$result = mysqli_query($conn, $query);
$products = [];
if ($result) {
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$total_products = count($products);
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
            <h1 class="page-title">Footwear Inventory Catalog</h1>
            <p class="page-subtitle">Manage your real database models, pricing in PKR, sizes, and stock availability.</p>
          </div>
          <div class="d-flex gap-2">
            <a href="product-add.php" class="btn btn-sm btn-dark">
              <i class="bi bi-plus-lg me-1"></i> Add New Product
            </a>
          </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="admin-card mb-4">
          <div class="admin-card-body p-3">
            <div class="row g-3 align-items-center">
              <div class="col-md-4">
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                  <input type="text" id="productFilterInput" class="form-control" placeholder="Search by name, SKU or model...">
                </div>
              </div>
              <div class="col-md-3">
                <select class="form-select" id="categoryFilterSelect">
                  <option value="">All Categories (<?= count($categories) ?>)</option>
                  <?php foreach ($categories as $cat) : ?>
                    <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <select class="form-select" id="brandFilterSelect">
                  <option value="">All Brands (<?= count($brands) ?>)</option>
                  <?php foreach ($brands as $brand) : ?>
                    <option value="<?= htmlspecialchars($brand['name']) ?>"><?= htmlspecialchars($brand['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2 text-md-end">
                <span class="text-muted small"><strong><?= $total_products ?></strong> Active Items</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Table -->
        <div class="admin-card">
          <div class="admin-table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th width="40"><input type="checkbox" id="selectAllRows" class="form-check-input"></th>
                  <th>Product</th>
                  <th>SKU</th>
                  <th>Category</th>
                  <th>Brand</th>
                  <th>Selling Price</th>
                  <th>Stock</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody class="filterable-table-body">
                <?php if (empty($products)) : ?>
                  <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                      <i class="bi bi-box-seam display-6 d-block mb-2 text-muted"></i>
                      No products found in the database. 
                      <div class="mt-2">
                        <a href="product-add.php" class="btn btn-sm btn-dark">Add Your First Product</a>
                      </div>
                    </td>
                  </tr>
                <?php else : ?>
                  <?php foreach ($products as $p) : ?>
                    <?php
                      $img = !empty($p['image_url']) ? $p['image_url'] : 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=800&auto=format&fit=crop';
                      $stock = intval($p['stock_quantity']);
                      $brand = !empty($p['brand_name']) ? $p['brand_name'] : 'NOVA FORM';
                      $category = !empty($p['category_name']) ? $p['category_name'] : 'General';
                    ?>
                    <tr>
                      <td><input type="checkbox" class="form-check-input row-select-check"></td>
                      <td>
                        <div class="d-flex align-items-center gap-3">
                          <img src="<?= htmlspecialchars($img) ?>" class="rounded-1" style="width: 44px; height: 44px; object-fit: cover;" alt="<?= htmlspecialchars($p['name']) ?>">
                          <div>
                            <div class="fw-semibold">
                              <a href="product-edit.php?id=<?= $p['id'] ?>" class="text-dark text-decoration-none">
                                <?= htmlspecialchars($p['name']) ?>
                              </a>
                            </div>
                            <small class="text-muted"><?= htmlspecialchars($brand) ?></small>
                          </div>
                        </div>
                      </td>
                      <td><code><?= htmlspecialchars($p['sku']) ?></code></td>
                      <td><?= htmlspecialchars($category) ?></td>
                      <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($brand) ?></span></td>
                      <td class="fw-bold">PKR <?= number_format($p['price_pkr']) ?></td>
                      <td>
                        <span class="badge bg-light text-dark border">
                          <?= $stock ?> pairs
                        </span>
                      </td>
                      <td>
                        <?php if ($p['status'] == 'Active') : ?>
                          <span class="badge-status badge-status-success">Active</span>
                        <?php elseif ($p['status'] == 'Draft') : ?>
                          <span class="badge-status badge-status-warning">Draft</span>
                        <?php elseif ($p['status'] == 'Out of Stock') : ?>
                          <span class="badge-status badge-status-danger">Out of Stock</span>
                        <?php else : ?>
                          <span class="badge-status badge-status-neutral"><?= htmlspecialchars($p['status']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="d-flex gap-1">
                          <a href="../front-site/product-details.php?id=<?= $p['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View in store">
                            <i class="bi bi-eye"></i>
                          </a>
                          <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-dark py-1 px-2" title="Edit product">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 btn-trigger-delete" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" title="Delete product">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Footer Count -->
          <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <span class="small text-muted">Showing <?= $total_products ?> products from database</span>
          </div>
        </div>

      </main>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow">
        <form action="actions/delete_product_action.php" method="POST">
          <input type="hidden" name="id" id="deleteTargetId" value="">
          <div class="modal-body text-center p-4">
            <div class="text-danger fs-1 mb-2"><i class="bi bi-exclamation-circle"></i></div>
            <h5 class="fw-bold mb-2">Confirm Delete?</h5>
            <p class="text-muted small mb-4">Are you sure you want to remove <strong id="deleteTargetName">this product</strong> from the database?</p>
            <div class="d-flex justify-content-center gap-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-sm btn-danger px-3">Delete</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
  <script>
    // Set delete target in modal
    document.querySelectorAll('.btn-trigger-delete').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var id = this.getAttribute('data-id');
        var name = this.getAttribute('data-name');
        document.getElementById('deleteTargetId').value = id;
        document.getElementById('deleteTargetName').textContent = name;
      });
    });

    // Instant live search & category/brand filter
    var filterInput = document.getElementById('productFilterInput');
    var categoryFilter = document.getElementById('categoryFilterSelect');
    var brandFilter = document.getElementById('brandFilterSelect');

    function applyProductFilters() {
      var query = filterInput ? filterInput.value.toLowerCase().trim() : '';
      var selectedCat = categoryFilter ? categoryFilter.value.toLowerCase() : '';
      var selectedBrand = brandFilter ? brandFilter.value.toLowerCase() : '';
      var rows = document.querySelectorAll('.filterable-table-body tr');

      rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        var matchesQuery = !query || text.includes(query);
        var matchesCat = !selectedCat || text.includes(selectedCat);
        var matchesBrand = !selectedBrand || text.includes(selectedBrand);

        row.style.display = (matchesQuery && matchesCat && matchesBrand) ? '' : 'none';
      });
    }

    if (filterInput) filterInput.addEventListener('input', applyProductFilters);
    if (categoryFilter) categoryFilter.addEventListener('change', applyProductFilters);
    if (brandFilter) brandFilter.addEventListener('change', applyProductFilters);
  </script>
</body>

</html>
