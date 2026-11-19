<?php
/*
 * admin/food-items.php - Add, edit and delete food items
 *   food-items.php             -> list of all items
 *   food-items.php?action=add  -> add form
 *   food-items.php?edit=5      -> edit form for item 5
 */
require_once __DIR__ . '/includes/auth.php';

$restaurants = $pdo->query("SELECT id, name FROM restaurants ORDER BY name")->fetchAll();
$categories  = $pdo->query("SELECT id, name FROM food_categories ORDER BY name")->fetchAll();

// ---------- Delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM food_items WHERE id = ?");
    $stmt->execute([(int) $_POST['id']]);
    set_flash('success', 'Food item deleted.');
    redirect('food-items.php');
}

// ---------- Which form to show? ----------
$editId   = (int) ($_GET['edit'] ?? 0);
$showForm = ($_GET['action'] ?? '') === 'add' || $editId > 0;
$errors   = [];

// Default (empty) values for the add form
$item = ['name' => '', 'restaurant_id' => '', 'category_id' => '', 'price' => '',
         'description' => '', 'image' => '', 'is_veg' => 1, 'is_popular' => 0];

if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM food_items WHERE id = ?");
    $stmt->execute([$editId]);
    $item = $stmt->fetch();
    if (!$item) {
        set_flash('danger', 'Food item not found.');
        redirect('food-items.php');
    }
}

// ---------- Save (add or edit) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $item['name']          = trim($_POST['name'] ?? '');
    $item['restaurant_id'] = (int) ($_POST['restaurant_id'] ?? 0);
    $item['category_id']   = (int) ($_POST['category_id'] ?? 0);
    $item['price']         = $_POST['price'] ?? '';
    $item['description']   = trim($_POST['description'] ?? '');
    $item['is_veg']        = isset($_POST['is_veg']) ? 1 : 0;
    $item['is_popular']    = isset($_POST['is_popular']) ? 1 : 0;
    $imageUrl              = trim($_POST['image_url'] ?? '');

    // Validation
    if (strlen($item['name']) < 2) {
        $errors[] = 'Food name is required.';
    }
    if (!in_array($item['restaurant_id'], array_column($restaurants, 'id'))) {
        $errors[] = 'Please select a restaurant.';
    }
    if (!in_array($item['category_id'], array_column($categories, 'id'))) {
        $errors[] = 'Please select a category.';
    }
    if (!is_numeric($item['price']) || $item['price'] <= 0) {
        $errors[] = 'Price must be a number greater than 0.';
    }
    if (strlen($item['description']) < 5) {
        $errors[] = 'Please write a short description.';
    }

    // Image: 1) uploaded file  2) image URL  3) keep old image  4) category picture
    if (!empty($_FILES['image_file']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
        } elseif (!in_array($ext, $allowed) || getimagesize($_FILES['image_file']['tmp_name']) === false) {
            $errors[] = 'Only JPG, PNG, WEBP or GIF images are allowed.';
        } elseif ($_FILES['image_file']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be smaller than 2 MB.';
        } elseif (empty($errors)) {
            $fileName = 'food_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            move_uploaded_file($_FILES['image_file']['tmp_name'], __DIR__ . '/../assets/images/uploads/' . $fileName);
            $item['image'] = 'assets/images/uploads/' . $fileName;
        }
    } elseif ($imageUrl !== '') {
        if (filter_var($imageUrl, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $imageUrl)) {
            $item['image'] = $imageUrl;
        } else {
            $errors[] = 'Image URL is not valid.';
        }
    } elseif ($item['image'] === '' && empty($errors)) {
        $stmt = $pdo->prepare("SELECT image FROM food_categories WHERE id = ?");
        $stmt->execute([$item['category_id']]);
        $item['image'] = $stmt->fetchColumn();
    }

    if (empty($errors)) {
        if ($editId > 0) {
            $stmt = $pdo->prepare("UPDATE food_items SET name = ?, restaurant_id = ?, category_id = ?, price = ?,
                                   description = ?, image = ?, is_veg = ?, is_popular = ? WHERE id = ?");
            $stmt->execute([$item['name'], $item['restaurant_id'], $item['category_id'], $item['price'],
                            $item['description'], $item['image'], $item['is_veg'], $item['is_popular'], $editId]);
            set_flash('success', 'Food item updated.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO food_items (name, restaurant_id, category_id, price, description, image, is_veg, is_popular)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$item['name'], $item['restaurant_id'], $item['category_id'], $item['price'],
                            $item['description'], $item['image'], $item['is_veg'], $item['is_popular']]);
            set_flash('success', 'Food item added.');
        }
        redirect('food-items.php');
    }
    $showForm = true;
}

// ---------- List of items ----------
$foods = $pdo->query("SELECT f.*, r.name AS restaurant_name, c.name AS category_name, c.image AS category_image
                      FROM food_items f
                      JOIN restaurants r ON r.id = f.restaurant_id
                      JOIN food_categories c ON c.id = f.category_id
                      ORDER BY f.id DESC")->fetchAll();

$pageTitle = 'Food Items';
require_once __DIR__ . '/includes/header.php';
?>

<?php if ($showForm): ?>
    <!-- ===== Add / Edit form ===== -->
    <div class="card stat-card mb-4">
        <div class="card-body p-4">
            <h5 class="mb-3"><?= $editId ? 'Edit Food Item' : 'Add New Food Item' ?></h5>

            <?php if ($errors): ?>
                <div class="alert alert-danger"><ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                </ul></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Food Name</label>
                        <input type="text" name="name" class="form-control" value="<?= e($item['name']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price (₹)</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="1" value="<?= e($item['price']) ?>" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_veg" id="is_veg" <?= $item['is_veg'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_veg">Veg</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_popular" id="is_popular" <?= $item['is_popular'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_popular">Popular</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Restaurant</label>
                        <select name="restaurant_id" class="form-select" required>
                            <option value="">-- Select restaurant --</option>
                            <?php foreach ($restaurants as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= $item['restaurant_id'] == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select category --</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $item['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" required><?= e($item['description']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Upload Image (JPG/PNG, max 2 MB)</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">or Image URL</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://...">
                    </div>
                    <?php if ($item['image']): ?>
                        <div class="col-12">
                            <small class="text-muted d-block mb-1">Current image (kept if you don't choose a new one):</small>
                            <img src="<?= e(img_src($item['image'], '../')) ?>" alt="" style="width:100px;height:80px;object-fit:cover;border-radius:10px;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-4">
                    <button class="btn btn-orange"><i class="bi bi-save"></i> Save</button>
                    <a href="food-items.php" class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <a href="food-items.php?action=add" class="btn btn-orange mb-3"><i class="bi bi-plus-lg"></i> Add Food Item</a>
<?php endif; ?>

<!-- ===== List ===== -->
<div class="card stat-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr><th>Image</th><th>Name</th><th>Restaurant</th><th>Category</th><th>Price</th><th>Type</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($foods as $f): ?>
                    <tr>
                        <td><img src="<?= e(img_src($f['image'], '../')) ?>" alt=""
                                 onerror="this.onerror=null;this.src='../<?= e($f['category_image']) ?>';"></td>
                        <td>
                            <strong><?= e($f['name']) ?></strong>
                            <?php if ($f['is_popular']): ?><span class="badge bg-warning text-dark">Popular</span><?php endif; ?>
                        </td>
                        <td><?= e($f['restaurant_name']) ?></td>
                        <td><?= e($f['category_name']) ?></td>
                        <td><?= rupees($f['price']) ?></td>
                        <td><span class="veg-icon <?= $f['is_veg'] ? 'veg' : 'nonveg' ?>"></span></td>
                        <td class="text-end text-nowrap">
                            <a href="food-items.php?edit=<?= (int) $f['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="post" class="d-inline confirm-delete">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
