<?php
require_once __DIR__ . "/settings/session_start.php";
require_once __DIR__ . "/settings/auth_check.php";
require_once __DIR__ . "/settings/db.php";

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Get product ID from query parameter
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no product ID is provided in URL, pick the latest product from database
if ($product_id <= 0) {
    $first_prod = mysqli_query($conn, "SELECT id FROM products ORDER BY id DESC LIMIT 1");
    if ($first_prod && mysqli_num_rows($first_prod) > 0) {
        $first_row = mysqli_fetch_assoc($first_prod);
        $product_id = intval($first_row['id']);
    }
}

// Fetch product data
$product = null;
if ($product_id > 0) {
    $query = "SELECT * FROM products WHERE id = {$product_id} LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    }
}

// Fetch all categories
$cat_query = "SELECT * FROM categories ORDER BY name ASC";
$cat_result = mysqli_query($conn, $cat_query);
$categories = $cat_result ? mysqli_fetch_all($cat_result, MYSQLI_ASSOC) : [];

// Fetch all brands
$brand_query = "SELECT * FROM brands ORDER BY name ASC";
$brand_result = mysqli_query($conn, $brand_query);
$brands = $brand_result ? mysqli_fetch_all($brand_result, MYSQLI_ASSOC) : [];

// Fetch product variants if available
$variants = [];
if ($product_id > 0) {
    $var_query = "SELECT * FROM product_variants WHERE product_id = {$product_id} ORDER BY size_eu ASC";
    $var_result = mysqli_query($conn, $var_query);
    if ($var_result && mysqli_num_rows($var_result) > 0) {
        $variants = mysqli_fetch_all($var_result, MYSQLI_ASSOC);
    }
}

// If no variants exist in database, provide standard size run (EU 39-46)
if (empty($variants)) {
    $default_sizes = [39, 40, 41, 42, 43, 44, 45, 46];
    foreach ($default_sizes as $sz) {
        $variants[] = [
            'id' => '',
            'size_eu' => $sz,
            'color_name' => 'Nero Black',
            'sku_variant' => ($product ? $product['sku'] . '-' . $sz : ''),
            'stock_quantity' => 0,
            'status' => 'Out of Stock'
        ];
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
              <li><a class="dropdown-item small py-2" href="orders.php"><i class="bi bi-bag-check text-success me-2"></i> New orders received</a></li>
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

        <?php if (!$product) : ?>
          <div class="alert alert-warning">
            <h5>No Product Found</h5>
            <p>The product you are trying to edit does not exist or has been removed.</p>
            <a href="products.php" class="btn btn-sm btn-dark">Go to Products List</a>
          </div>
        <?php else : ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="page-title">Edit Footwear: <?= htmlspecialchars($product['name']) ?></h1>
            <p class="page-subtitle">Product ID: #<?= $product['id'] ?> &bull; SKU: <?= htmlspecialchars($product['sku']) ?> &bull; Status: <?= htmlspecialchars($product['status']) ?></p>
          </div>
          <div class="d-flex gap-2">
            <a href="../front-site/product-details.php" target="_blank" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-eye me-1"></i> View Live
            </a>
            <a href="products.php" class="btn btn-sm btn-dark">
              Back to List
            </a>
          </div>
        </div>

        <form action="actions/edit_product_action.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $product['id'] ?>">
          <input type="hidden" name="existing_image" value="<?= htmlspecialchars($product['image_url'] ?? '') ?>">

          <div class="row g-4">

            <!-- Left Column: Primary Details -->
            <div class="col-lg-8">

              <!-- Basic Information -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">1. Footwear Details</h5></div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-8">
                      <label class="form-label small fw-bold">Product Name *</label>
                      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">SKU Code *</label>
                      <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Category *</label>
                      <select class="form-select" name="category_id" required>
                        <option value="" disabled>-- Select Category --</option>
                        <?php foreach ($categories as $cat) : ?>
                          <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label small fw-bold">Atelier Brand</label>
                      <select class="form-select" name="brand_id">
                        <option value="">-- No Brand / Default --</option>
                        <?php foreach ($brands as $brand) : ?>
                          <option value="<?= $brand['id'] ?>" <?= ($product['brand_id'] == $brand['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($brand['name']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="form-label small fw-bold">Short Description</label>
                      <textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                      <label class="form-label small fw-bold">Full Craftsmanship Description</label>
                      <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Pricing (PKR) -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">2. Pricing (PKR)</h5></div>
                <div class="admin-card-body">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">Selling Price (PKR) *</label>
                      <input type="number" step="0.01" name="price_pkr" class="form-control" value="<?= htmlspecialchars($product['price_pkr']) ?>" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">Original / Retail Price (PKR)</label>
                      <input type="number" step="0.01" name="original_price_pkr" class="form-control" value="<?= htmlspecialchars($product['original_price_pkr'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label small fw-bold">Cost of Goods (Internal PKR)</label>
                      <input type="number" step="0.01" name="cost_price_pkr" class="form-control" value="<?= htmlspecialchars($product['cost_price_pkr'] ?? '') ?>">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Size Variants & Stock -->
              <div class="admin-card mb-4">
                <div class="admin-card-header d-flex justify-content-between align-items-center">
                  <h5 class="admin-card-title">3. Size Variants & Stock</h5>
                  <span class="badge bg-light text-dark border">Standard EU Footwear Run</span>
                </div>
                <div class="admin-card-body p-0">
                  <div class="table-responsive">
                    <table class="table variant-table table-bordered mb-0 small">
                      <thead class="table-light">
                        <tr>
                          <th>Size</th>
                          <th>Colorway</th>
                          <th>Variant SKU</th>
                          <th>Stock Qty</th>
                          <th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($variants as $idx => $v) : ?>
                          <tr>
                            <td>
                              <strong>EU <?= htmlspecialchars($v['size_eu']) ?></strong>
                              <input type="hidden" name="variants[<?= $idx ?>][id]" value="<?= htmlspecialchars($v['id'] ?? '') ?>">
                              <input type="hidden" name="variants[<?= $idx ?>][size]" value="<?= htmlspecialchars($v['size_eu']) ?>">
                            </td>
                            <td>
                              <input type="text" name="variants[<?= $idx ?>][color]" class="form-control form-control-sm" value="<?= htmlspecialchars($v['color_name'] ?? 'Nero Black') ?>">
                            </td>
                            <td>
                              <input type="text" name="variants[<?= $idx ?>][sku_variant]" class="form-control form-control-sm" value="<?= htmlspecialchars($v['sku_variant'] ?? '') ?>">
                            </td>
                            <td>
                              <input type="number" name="variants[<?= $idx ?>][stock]" class="form-control form-control-sm" value="<?= intval($v['stock_quantity'] ?? 0) ?>" min="0" style="width: 80px;">
                            </td>
                            <td>
                              <?php
                                $v_stock = intval($v['stock_quantity'] ?? 0);
                                if ($v_stock <= 0) {
                                    echo '<span class="badge bg-danger">Out of Stock</span>';
                                } elseif ($v_stock <= 5) {
                                    echo '<span class="badge bg-warning text-dark">Low Stock</span>';
                                } else {
                                    echo '<span class="badge bg-success">In Stock</span>';
                                }
                              ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

            </div>

            <!-- Right Column: Images & Meta -->
            <div class="col-lg-4">

              <!-- Images Box -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">Product Image</h5></div>
                <div class="admin-card-body text-center">
                  <?php
                    $img_src = !empty($product['image_url']) ? $product['image_url'] : 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=800&auto=format&fit=crop';
                  ?>
                  <div class="mb-3">
                    <img src="<?= htmlspecialchars($img_src) ?>" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;" alt="<?= htmlspecialchars($product['name']) ?>">
                  </div>
                  <div class="text-start">
                    <label class="form-label small fw-bold">Upload New Image (Optional)</label>
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                    <div class="text-muted mt-1" style="font-size: 0.72rem;">Leave empty to keep existing image. Allowed: JPG, PNG, WEBP.</div>
                  </div>
                </div>
              </div>

              <!-- Product Specifications -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">Footwear Attributes</h5></div>
                <div class="admin-card-body">
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Upper Material</label>
                    <input type="text" name="material" class="form-control form-control-sm" value="<?= htmlspecialchars($product['material'] ?? '') ?>" placeholder="e.g. Full Grain Italian Calfskin">
                  </div>
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Sole Construction</label>
                    <input type="text" name="sole_type" class="form-control form-control-sm" value="<?= htmlspecialchars($product['sole_type'] ?? '') ?>" placeholder="e.g. Goodyear Welted">
                  </div>
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Occasion</label>
                    <input type="text" name="occasion" class="form-control form-control-sm" value="<?= htmlspecialchars($product['occasion'] ?? '') ?>" placeholder="e.g. Formal, Wedding">
                  </div>
                </div>
              </div>

              <!-- Status & Publishing Flags -->
              <div class="admin-card mb-4">
                <div class="admin-card-header"><h5 class="admin-card-title">Publishing & Status</h5></div>
                <div class="admin-card-body">
                  <div class="mb-3">
                    <label class="form-label small fw-bold">Status</label>
                    <select class="form-select form-select-sm" name="status">
                      <option value="Active" <?= ($product['status'] == 'Active') ? 'selected' : '' ?>>Active (Visible)</option>
                      <option value="Draft" <?= ($product['status'] == 'Draft') ? 'selected' : '' ?>>Draft</option>
                      <option value="Archived" <?= ($product['status'] == 'Archived') ? 'selected' : '' ?>>Archived</option>
                      <option value="Out of Stock" <?= ($product['status'] == 'Out of Stock') ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                  </div>
                  <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_new" value="1" id="flagNewArrival" <?= (!empty($product['is_new'])) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="flagNewArrival">New Arrival Tag</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_sale" value="1" id="flagOnSale" <?= (!empty($product['is_sale'])) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="flagOnSale">Show On Sale</label>
                  </div>
                </div>
              </div>

              <div class="d-flex flex-column gap-2">
                <button type="submit" class="btn btn-dark w-100 py-2"><i class="bi bi-check-lg me-1"></i> Update Product</button>
                <a href="products.php" class="btn btn-outline-secondary w-100">Cancel</a>
              </div>

            </div>

          </div>
        </form>

        <?php endif; ?>

      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/dashboard.js"></script>
</body>
</html>
