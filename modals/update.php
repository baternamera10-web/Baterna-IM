<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once "../connection.php";
require_once "../classes/product.php";

$productObj = new Product($conn);
$id = $_GET['id'] ?? ($_POST['id'] ?? null);
if (!$id) { header("Location: ../index.php"); exit; }
$product = $productObj->getById($id);
if (!$product) { header("Location: ../index.php"); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $quantity = $_POST['quantity'] ?? 0;
    $image = null;
    if ($name && $price !== '' && $category) {
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = "../uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $filename = time() . "_" . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image = "uploads/" . $filename;
            }
        }
        if ($productObj->update($id, $name, $price, $category, $quantity, $image)) {
            header("Location: ../index.php"); exit;
        }
        $error = "Failed to update product.";
    } else { $error = "Please fill in all required fields."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product · Fresh Catch</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    body {
        min-height: 100vh; padding: 40px 20px;
        background: linear-gradient(135deg, #f6f9f8 0%, #eaf3f0 100%);
        display: flex; align-items: flex-start; justify-content: center;
    }
    .modal-card {
        background: white; width: 100%; max-width: 540px;
        border-radius: 20px; overflow: hidden;
        box-shadow: 0 20px 50px rgba(13,59,46,0.1);
        border: 1px solid #e6ede9;
    }
    .modal-header {
        padding: 28px 30px;
        background: linear-gradient(135deg, #0d3b2e, #14b8a6);
        color: white; position: relative; overflow: hidden;
    }
    .modal-header::after {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 160px; height: 160px; border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.15), transparent 70%);
    }
    .modal-header h2 {
        font-size: 22px; font-weight: 800;
        display: flex; align-items: center; gap: 12px;
        position: relative; z-index: 2;
    }
    .modal-header .icon-box {
        width: 40px; height: 40px; border-radius: 11px;
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        display: grid; place-items: center; font-size: 17px;
        backdrop-filter: blur(10px);
    }
    .modal-header p { font-size: 13px; color: rgba(255,255,255,0.8); margin-top: 8px; position: relative; z-index: 2; }

    .modal-body { padding: 30px; }
    .error-msg {
        display: flex; align-items: center; gap: 10px;
        background: #fef2f1; color: #b71c1c;
        padding: 12px 16px; border-radius: 10px;
        font-size: 13px; font-weight: 500;
        margin-bottom: 20px; border: 1px solid #fecaca;
    }
    .field { margin-bottom: 20px; }
    .field label { display: block; font-size: 13px; font-weight: 600; color: #2d3f38; margin-bottom: 8px; }
    .field label .req { color: #ff6b5b; margin-left: 2px; }
    .input-wrap { position: relative; }
    .input-wrap i {
        position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
        color: #94a3a0; font-size: 14px; pointer-events: none;
    }
    .input-wrap input, .input-wrap select {
        width: 100%; height: 48px; padding: 0 16px 0 44px;
        border: 1.5px solid #e6ede9; border-radius: 11px;
        background: #fafcfb; font-size: 14px; color: #1a2c26;
        outline: none; transition: all 0.2s; appearance: none;
    }
    .input-wrap select { cursor: pointer; }
    .input-wrap input:focus, .input-wrap select:focus {
        border-color: #14b8a6; background: white;
        box-shadow: 0 0 0 4px rgba(20,184,166,0.1);
    }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .current-img-wrap {
        display: flex; align-items: center; gap: 14px;
        padding: 12px; background: #fafcfb;
        border: 1px solid #e6ede9; border-radius: 11px;
        margin-top: 8px;
    }
    .current-img-wrap img {
        width: 60px; height: 60px; border-radius: 9px; object-fit: cover;
        border: 1px solid #e6ede9;
    }
    .current-img-wrap p { font-size: 12px; color: #6b7c76; }

    .actions { display: flex; gap: 12px; margin-top: 28px; }
    .btn {
        flex: 1; height: 50px; border-radius: 11px;
        font-size: 14px; font-weight: 700;
        cursor: pointer; border: none; text-decoration: none;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: all 0.2s;
    }
    .btn-cancel { background: #f0f4f2; color: #2d3f38; }
    .btn-cancel:hover { background: #e6ede9; }
    .btn-save {
        background: linear-gradient(135deg, #0d3b2e, #14b8a6);
        color: white; box-shadow: 0 8px 20px rgba(13,59,46,0.25);
    }
    .btn-save:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13,59,46,0.35); }
</style>
</head>
<body>
    <div class="modal-card">
        <div class="modal-header">
            <h2><div class="icon-box"><i class="fa-solid fa-pen"></i></div> Edit Product</h2>
            <p>Update the details of your product below.</p>
        </div>

        <div class="modal-body">
            <?php if ($error): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

                <div class="field">
                    <label>Product Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-tag"></i>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Price (₱) <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-peso-sign"></i>
                            <input type="number" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                    </div>
                    <div class="field">
                        <label>Quantity <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-cubes"></i>
                            <input type="number" name="quantity" min="0" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Category <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-layer-group"></i>
                        <select name="category" required>
                            <?php
                            $categories = ['Fish', 'Shellfish', 'Crustacean', 'Mollusk', 'Other'];
                            foreach ($categories as $c) {
                                $sel = ($product['category'] === $c) ? 'selected' : '';
                                echo "<option value=\"$c\" $sel>$c</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Update Image</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-image"></i>
                        <input type="file" name="image" accept="image/*" style="padding-left:44px;line-height:48px;">
                    </div>
                    <?php if (!empty($product['image'])): ?>
                        <div class="current-img-wrap">
                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Current">
                            <p>Current image — leave blank to keep</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="actions">
                    <a href="../index.php" class="btn btn-cancel"><i class="fa-solid fa-xmark"></i> Cancel</a>
                    <button type="submit" class="btn btn-save"><i class="fa-solid fa-floppy-disk"></i> Update Product</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>