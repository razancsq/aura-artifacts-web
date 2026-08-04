<?php
// ============================================================
// admin-products.php - Manage Products (Task 9, 10)
// Author: Team Member
// ============================================================
require_once '../config.php';

// Admin auth guard
require_once 'admin-auth.php';

$admin_id = (int)$_SESSION['admin_id'];
$msg      = '';
$error    = '';

// ------- ADD PRODUCT (Task 9) -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $name        = trim($conn->real_escape_string($_POST['name']        ?? ''));
    $description = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $base_price  = (float)($_POST['base_price'] ?? 0);
    $stock       = (int)  ($_POST['stock']       ?? 0);
    $category_id = (int)  ($_POST['category_id'] ?? 0);
    $image_url   = trim($conn->real_escape_string($_POST['image_url']   ?? ''));
    $image_url_2 = trim($conn->real_escape_string($_POST['image_url_2'] ?? ''));
    $image_url_3 = trim($conn->real_escape_string($_POST['image_url_3'] ?? ''));

    // Handle image uploads (3 images)
    $image_fields = ['image' => &$image_url, 'image_2' => &$image_url_2, 'image_3' => &$image_url_3];
    foreach ($image_fields as $field => &$url_var) {
        if (!empty($_FILES[$field]['name'])) {
            $ext       = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $filename  = 'product_' . time() . '_' . $field . '.' . $ext;
            $target    = '../assets/images/' . $filename;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                $url_var = 'assets/images/' . $filename;
            }
        }
    }
    unset($url_var);

    if (!$name || $base_price <= 0) {
        $error = "Name and price are required.";
    } else {
        // Prepared statement: add product
        $stmt = $conn->prepare("INSERT INTO product (name, description, base_price, stock, image_url, image_url_2, image_url_3, category_id, managed_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisssii", $name, $description, $base_price, $stock, $image_url, $image_url_2, $image_url_3, $category_id, $admin_id);
        $stmt->execute();
        $stmt->close();
        $msg = "product '$name' added successfully!";
    }
}

// ------- MODIFY PRODUCT (Task 10) -------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'modify') {
    $product_id  = (int)  $_POST['product_id'];
    $name        = trim($conn->real_escape_string($_POST['name']        ?? ''));
    $description = trim($conn->real_escape_string($_POST['description'] ?? ''));
    $base_price  = (float)$_POST['base_price'];
    $stock       = (int)  $_POST['stock'];
    $category_id = (int)  $_POST['category_id'];
    $image_url   = trim($conn->real_escape_string($_POST['image_url']   ?? ''));
    $image_url_2 = trim($conn->real_escape_string($_POST['image_url_2'] ?? ''));
    $image_url_3 = trim($conn->real_escape_string($_POST['image_url_3'] ?? ''));

    // Handle image uploads (3 images)
    $image_fields = ['image' => &$image_url, 'image_2' => &$image_url_2, 'image_3' => &$image_url_3];
    foreach ($image_fields as $field => &$url_var) {
        if (!empty($_FILES[$field]['name'])) {
            $ext      = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . $field . '.' . $ext;
            $target   = '../assets/images/' . $filename;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                $url_var = 'assets/images/' . $filename;
            }
        }
    }
    unset($url_var);

    // Prepared statement: update product
    $stmt = $conn->prepare("UPDATE product SET name=?, description=?, base_price=?, stock=?, image_url=?, image_url_2=?, image_url_3=?, category_id=?, managed_by=? WHERE product_id=?");
    $stmt->bind_param("ssdisssiii", $name, $description, $base_price, $stock, $image_url, $image_url_2, $image_url_3, $category_id, $admin_id, $product_id);
    $stmt->execute();
    $stmt->close();
    $msg = "product updated successfully!";
}

// ------- DELETE PRODUCT (Task 10) -------
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    // Prepared statement: delete product
    $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    $msg = "product deleted.";
}

// ------- SEARCH (Task 10) -------
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = $search ? "WHERE p.name LIKE ?" : '';

if ($search) {
    // Prepared statement: search products
    $searchParam = "%$search%";
    $stmt = $conn->prepare("SELECT p.*, c.name AS cat FROM product p LEFT JOIN category c ON p.category_id = c.category_id WHERE p.name LIKE ? ORDER BY p.product_id DESC");
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query("SELECT p.*, c.name AS cat FROM product p LEFT JOIN category c ON p.category_id = c.category_id ORDER BY p.product_id DESC");
}
$categories = $conn->query("SELECT * FROM category ORDER BY name");
$cats_arr   = [];
while ($c = $categories->fetch_assoc()) $cats_arr[] = $c;

// Load product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id     = (int)$_GET['edit'];
    // Prepared statement: get product for editing
    $stmt = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_res    = $stmt->get_result();
    $edit_product = $edit_res->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Aura Artifacts Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        body { background:#f0f2f5; font-family:'Poppins',sans-serif; display:flex; min-height:100vh; }
        /* Sidebar */
        .sidebar { width:240px; background:#2d3e50; min-height:100vh; padding:30px 20px; flex-shrink:0; }
        .sidebar-logo { font-family:'Cormorant Garamond',serif; font-size:1.4rem; color:#fff; margin-bottom:32px; display:block; text-decoration:none; }
        .sidebar a { display:block; color:rgba(255,255,255,0.7); text-decoration:none; padding:10px 14px; border-radius:8px; margin-bottom:4px; font-size:0.88rem; }
        .sidebar a:hover, .sidebar a.active { background:rgba(255,255,255,0.1); color:#fff; }
        .sidebar .logout { color:#ff9ebb; margin-top:24px; }
        /* Main */
        .main { flex:1; padding:32px; }
        .page-title { font-family:'Cormorant Garamond',serif; font-size:2rem; color:#2d3e50; margin-bottom:24px; }
        .card { background:#fff; border-radius:16px; padding:28px; box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-bottom:24px; }
        .card h2 { font-family:'Cormorant Garamond',serif; font-size:1.4rem; color:#2d3e50; margin-bottom:18px; }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        @media(max-width:600px){ .form-grid{grid-template-columns:1fr;} }
        .input-label { display:block; font-size:0.72rem; letter-spacing:1px; text-transform:uppercase; color:#888; margin-bottom:4px; font-weight:600; }
        .form-input, textarea, select { width:100%; padding:10px 14px; border:1.5px solid #e0e0e0; border-radius:8px; font-size:0.9rem; font-family:'Poppins',sans-serif; background:#f8f9fa; }
        .form-input:focus, textarea:focus, select:focus { outline:none; border-color:#D4AF37; }
        textarea { resize:vertical; min-height:80px; }
        .btn { padding:10px 24px; border:none; border-radius:8px; cursor:pointer; font-size:0.88rem; font-weight:600; }
        .btn-gold  { background:#D4AF37; color:#fff; }
        .btn-gold:hover { background:#b8962e; }
        .btn-red   { background:#e74c3c; color:#fff; }
        .btn-blue  { background:#3498db; color:#fff; }
        .btn-sm    { padding:6px 14px; font-size:0.8rem; }
        .alert-success { background:#e8f8f5; color:#27ae60; border-radius:8px; padding:12px; margin-bottom:16px; }
        .alert-error   { background:#fce8e8; color:#c0392b; border-radius:8px; padding:12px; margin-bottom:16px; }
        table { width:100%; border-collapse:collapse; }
        th { background:#f0f2f5; padding:10px 14px; text-align:left; font-size:0.78rem; text-transform:uppercase; letter-spacing:1px; color:#666; }
        td { padding:12px 14px; border-bottom:1px solid #f0f0f0; font-size:0.88rem; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        .product-thumb { width:46px; height:46px; object-fit:cover; border-radius:8px; }
        .search-row { display:flex; gap:12px; margin-bottom:18px; }
        .search-row input { flex:1; padding:10px 14px; border:1.5px solid #e0e0e0; border-radius:8px; font-size:0.9rem; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <a href="admin-dashboard.php" class="sidebar-logo">AURA ARTIFACTS Admin</a>
    <a href="admin-dashboard.php">📊 Dashboard</a>
    <a href="admin-products.php" class="active">📦 Products</a>
    <a href="admin-orders.php">📋 Orders</a>
    <a href="admin-customers.php">👥 Customers</a>
    <a href="admin-logout.php" class="logout">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main">
    <h1 class="page-title">Manage Products</h1>

    <?php if ($msg):   ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <div class="card">
        <h2><?= $edit_product ? '✏️ Edit product' : '➕ Add New product' ?></h2>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateProductForm()">
            <input type="hidden" name="action" value="<?= $edit_product ? 'modify' : 'add' ?>">
            <?php if ($edit_product): ?>
                <input type="hidden" name="product_id" value="<?= $edit_product['product_id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div>
                    <label class="input-label">product Name *</label>
                    <input class="form-input" type="text" name="name" id="pName"
                           value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>"
                           placeholder="e.g. Pink Tourmaline Earrings">
                </div>
                <div>
                    <label class="input-label">Base Price ($) *</label>
                    <input class="form-input" type="number" name="base_price" id="pPrice"
                           step="0.01" min="0"
                           value="<?= $edit_product['base_price'] ?? '' ?>"
                           placeholder="e.g. 150.00">
                </div>
                <div>
                    <label class="input-label">Stock *</label>
                    <input class="form-input" type="number" name="stock" id="pStock"
                           min="0"
                           value="<?= $edit_product['stock'] ?? '0' ?>">
                </div>
                <div>
                    <label class="input-label">category</label>
                    <select name="category_id" class="form-input">
                        <option value="">-- Select category --</option>
                        <?php foreach ($cats_arr as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"
                                <?= ($edit_product['category_id'] ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:1/-1">
                    <label class="input-label">Description</label>
                    <textarea name="description" placeholder="product description..."><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="input-label">Image 1 URL (or upload below)</label>
                    <input class="form-input" type="text" name="image_url"
                           value="<?= htmlspecialchars($edit_product['image_url'] ?? '') ?>"
                           placeholder="assets/images/product.png">
                </div>
                <div>
                    <label class="input-label">Upload Image 1</label>
                    <input type="file" name="image" accept="image/*" style="padding:8px 0;">
                </div>
                <div>
                    <label class="input-label">Image 2 URL (or upload below)</label>
                    <input class="form-input" type="text" name="image_url_2"
                           value="<?= htmlspecialchars($edit_product['image_url_2'] ?? '') ?>"
                           placeholder="assets/images/product-2.png">
                </div>
                <div>
                    <label class="input-label">Upload Image 2</label>
                    <input type="file" name="image_2" accept="image/*" style="padding:8px 0;">
                </div>
                <div>
                    <label class="input-label">Image 3 URL (or upload below)</label>
                    <input class="form-input" type="text" name="image_url_3"
                           value="<?= htmlspecialchars($edit_product['image_url_3'] ?? '') ?>"
                           placeholder="assets/images/product-3.png">
                </div>
                <div>
                    <label class="input-label">Upload Image 3</label>
                    <input type="file" name="image_3" accept="image/*" style="padding:8px 0;">
                </div>
            </div>

            <div id="form-error" style="color:#c0392b; font-size:0.85rem; margin:10px 0; display:none;"></div>

            <div style="margin-top:16px; display:flex; gap:12px;">
                <button type="submit" class="btn btn-gold">
                    <?= $edit_product ? '💾 Save Changes' : '➕ Add product' ?>
                </button>
                <?php if ($edit_product): ?>
                    <a href="admin-products.php" class="btn btn-blue" style="text-decoration:none; display:inline-flex; align-items:center;">✕ Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- PRODUCT LIST -->
    <div class="card">
        <h2>📦 All Products</h2>

        <!-- Search (Task 10) -->
        <form method="GET" class="search-row">
            <input type="text" name="search" placeholder="Search products by name..."
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-gold">Search</button>
            <?php if ($search): ?><a href="admin-products.php" class="btn btn-blue" style="text-decoration:none; display:flex; align-items:center;">Clear</a><?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $p['product_id'] ?></td>
                    <td>
                        <img src="../<?= htmlspecialchars($p['image_url'] ?? 'assets/images/placeholder.png') ?>"
                             alt="" class="product-thumb"
                             onerror="this.src='../assets/images/placeholder.png'">
                    </td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['cat'] ?? '-') ?></td>
                    <td>$<?= number_format($p['base_price'], 2) ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td>
                        <a href="admin-products.php?edit=<?= $p['product_id'] ?>" class="btn btn-blue btn-sm" style="text-decoration:none; display:inline-block;">✏️ Edit</a>
                        <a href="admin-products.php?delete=<?= $p['product_id'] ?>"
                           class="btn btn-red btn-sm"
                           onclick="return confirm('Delete this product?')"
                           style="text-decoration:none; display:inline-block; margin-left:6px;">🗑 Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// JavaScript Validation (Task 13)
function validateProductForm() {
    const name  = document.getElementById('pName').value.trim();
    const price = parseFloat(document.getElementById('pPrice').value);
    const stock = parseInt(document.getElementById('pStock').value);
    const err   = document.getElementById('form-error');

    if (!name) {
        err.textContent = 'product name is required.';
        err.style.display = 'block';
        return false;
    }
    if (isNaN(price) || price <= 0) {
        err.textContent = 'Please enter a valid price.';
        err.style.display = 'block';
        return false;
    }
    if (isNaN(stock) || stock < 0) {
        err.textContent = 'Stock must be 0 or more.';
        err.style.display = 'block';
        return false;
    }
    err.style.display = 'none';
    return true;
}
</script>
</body>
</html>
