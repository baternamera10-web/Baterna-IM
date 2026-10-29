<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once "../connection.php";
require_once "../classes/product.php";

$productObj = new Product($conn);
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
        if ($productObj->add($name, $price, $category, $quantity, $image)) {
            header("Location: ../index.php"); exit;
        }
        $error = "Failed to add product.";
    } else { $error = "Please fill in all required fields."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product · Fresh Catch</title>
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
        background: linear-gradient(135deg, #0a2e24, #0d3b2e);
        color: white; position: relative; overflow: hidden;
    }
    .modal-header::after {
        content: ''; position: absolute; top: -40px; right: -40px;
        width: 160px; height: 160px; border-radius: 50%;
        background: radial-gradient(circle, rgba(20,184,166,0.3), transparent 70%);
    }
    .modal-header h2 {
        font-size: 22px; font-weight: 800; letter-spacing: -0.01em;
        display: flex; align-items: center; gap: 12px; position: relative; z-index: 2;
    }
    .modal-header .icon-box {
        width: 40px; height: 40px; border-radius: 11px;
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        display: grid; place-items: center; font-size: 17px;
        box-shadow: 0 6px 16px rgba(20,184,166,0.4);
    }
    .modal-header p { font-size: 13px; color: #b8d4cc; margin-top: 8px; position: relative; z-index: 2; }

    .modal-body { padding: 30px; }
    .error-msg {
        display: flex; align-items: center; gap: 10px;
        background: #fef2f1; color: #b71c1c; padding: 12px 16px;
        border-radius: 10px; font-size: 13px; font-weight: 500;
        margin-bottom: 20px; border: 1px solid #fecaca;
    }

    .field { margin-bottom: 20px; }
    .field label {
        display: block; font-size: 13px; font-weight: 600;
        color: #2d3f38; margin-bottom: 8px;
    }
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
    .input-wrap input::placeholder { color: #a3b1ac; }

    .file-drop {
        border: 2px dashed #d4dfda; border-radius: 11px;
        padding: 24px; text-align: center; cursor: pointer;
        transition: all 0.2s; background: #fafcfb;
        display: flex; flex-direction: column; align-items: center; gap: 8px;
    }
    .file-drop:hover { border-color: #14b8a6; background: #f0fbf9; }
    .file-drop i { font-size: 28px; color: #94a3a0; }
    .file-drop p { font-size: 13px; color: #6b7c76; font-weight: 500; }
    .file-drop small { font-size: 11px; color: #a3b1ac; }
    .file-drop input { display: none; }

    .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

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
            <h2><div class="icon-box"><i class="fa-solid fa-plus"></i></div> Add New Product</h2>
            <p>Fill in the details to add a fresh catch to your inventory.</p>
        </div>

        <div class="modal-body">
            <?php if ($error): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="field">
                    <label>Product Name <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-tag"></i>
                        <input type="text" name="name" placeholder="e.g. Fresh Bangus" required>
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <label>Price (₱) <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-peso-sign"></i>
                            <input type="number" name="price" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="field">
                        <label>Quantity <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-cubes"></i>
                            <input type="number" name="quantity" min="0" value="0" placeholder="0" required>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label>Category <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-layer-group"></i>
                        <select name="category" required>
                            <option value="">Select a category</option>
                            <option value="Fish">🐟 Fish</option>
                            <option value="Shellfish">🦪 Shellfish</option>
                            <option value="Crustacean">🦐 Crustacean</option>
                            <option value="Mollusk">🦑 Mollusk</option>
                            <option value="Other">📦 Other</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Product Image</label>
                    <label class="file-drop" for="imageInput">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Click to upload an image</p>
                        <small>PNG, JPG up to 5MB</small>
                        <input type="file" name="image" id="imageInput" accept="image/*">
                    </label>
                </div>

                <div class="actions">
                    <a href="../index.php" class="btn btn-cancel"><i class="fa-solid fa-xmark"></i> Cancel</a>
                    <button type="submit" class="btn btn-save"><i class="fa-solid fa-floppy-disk"></i> Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const label = this.closest('.file-drop');
            const p = label.querySelector('p');
            if (e.target.files.length) {
                p.textContent = '✓ ' + e.target.files[0].name;
                label.style.borderColor = '#14b8a6';
                label.style.background = '#f0fbf9';
            }
        });
    </script>
</body>
</html>