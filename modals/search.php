<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
require_once "../connection.php";
require_once "../classes/product.php";

$productObj = new Product($conn);
$keyword = trim($_GET['keyword'] ?? '');
$products = $keyword ? $productObj->search($keyword) : $productObj->getAll();
$isSeller = ($_SESSION['role'] ?? 'seller') === 'seller';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Results · Fresh Catch</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    body { background: #f6f9f8; padding: 32px 40px 60px; min-height: 100vh; }
    .results-header {
        display: flex; justify-content: space-between; align-items: center;
        background: white; padding: 24px 28px;
        border-radius: 16px; border: 1px solid #e6ede9;
        margin-bottom: 28px;
    }
    .results-header h1 { font-size: 22px; font-weight: 800; color: #0d1f1a; margin-bottom: 6px; }
    .results-header p { font-size: 14px; color: #6b7c76; }
    .results-header p strong { color: #0d3b2e; }
    .back-btn {
        background: #f0f4f2; color: #2d3f38;
        padding: 12px 20px; border-radius: 10px;
        text-decoration: none; font-size: 13px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.2s;
    }
    .back-btn:hover { background: #0d3b2e; color: white; }

    .products { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 22px; }
    .card {
        background: white; border-radius: 16px; overflow: hidden;
        border: 1px solid #e6ede9; display: flex; flex-direction: column;
        transition: all 0.25s;
    }
    .card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(13,59,46,0.1); }
    .card-media { position: relative; height: 190px; overflow: hidden; background: #f0f4f2; }
    .card-image { width: 100%; height: 100%; object-fit: cover; }
    .no-image {
        width: 100%; height: 100%;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 8px; color: #a3b1ac; font-size: 13px;
        background: linear-gradient(135deg, #f0f4f2, #e6ede9);
    }
    .no-image i { font-size: 32px; opacity: 0.5; }
    .category-tag {
        position: absolute; top: 12px; left: 12px;
        padding: 5px 10px; border-radius: 8px;
        font-size: 10px; font-weight: 700;
        text-transform: uppercase;
        background: rgba(13,59,46,0.85); color: white;
    }
    .card-body { padding: 18px; display: flex; flex-direction: column; flex: 1; }
    .product-name { font-size: 16px; font-weight: 700; color: #0d1f1a; margin-bottom: 6px; }
    .price { font-size: 20px; font-weight: 800; color: #0d3b2e; margin-bottom: 14px; }
    .price small { font-size: 12px; font-weight: 600; color: #6b7c76; }
    .info-row { font-size: 13px; color: #6b7c76; margin-bottom: 4px; }
    .info-row strong { color: #2d3f38; }
    .divider-line { height: 1px; background: #e6ede9; margin: 14px 0; }
    .buttons { display: flex; gap: 8px; margin-top: auto; }
    .btn-edit, .btn-del {
        flex: 1; padding: 10px 12px; border-radius: 9px;
        font-size: 12px; font-weight: 700;
        text-decoration: none; text-align: center;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.2s;
    }
    .btn-edit { background: #f0f4f2; color: #2d3f38; }
    .btn-edit:hover { background: #0d3b2e; color: white; }
    .btn-del { background: #fef2f1; color: #dc2626; }
    .btn-del:hover { background: #dc2626; color: white; }

    .empty {
        background: white; padding: 70px 30px; text-align: center;
        border-radius: 18px; border: 1.5px dashed #d4dfda;
    }
    .empty-icon {
        width: 80px; height: 80px; border-radius: 20px;
        background: linear-gradient(135deg, #f0f4f2, #e6ede9);
        display: grid; place-items: center; margin: 0 auto 20px;
        font-size: 32px; color: #a3b1ac;
    }
    .empty h3 { font-size: 18px; font-weight: 800; color: #0d1f1a; margin-bottom: 8px; }
    .empty p { font-size: 14px; color: #6b7c76; }
</style>
</head>
<body>
    <div class="results-header">
        <div>
            <h1><i class="fa-solid fa-magnifying-glass" style="color:#14b8a6;"></i> Search Results</h1>
            <p>Keyword: <strong>"<?php echo htmlspecialchars($keyword); ?>"</strong> · <?php echo count($products); ?> result(s) found</p>
        </div>
        <a href="../index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if (count($products) > 0): ?>
        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="card">
                    <div class="card-media">
                        <?php if (!empty($product['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-image">
                        <?php else: ?>
                            <div class="no-image"><i class="fa-regular fa-image"></i> No Image</div>
                        <?php endif; ?>
                        <span class="category-tag"><?php echo htmlspecialchars($product['category']); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="price">₱<?php echo number_format($product['price'], 2); ?> <small>per kg</small></div>
                        <div class="info-row"><strong>Available:</strong> <?php echo htmlspecialchars($product['quantity']); ?> units</div>
                        <?php if ($isSeller): ?>
                            <div class="divider-line"></div>
                            <div class="buttons">
                                <a href="update.php?id=<?php echo $product['product_id']; ?>" class="btn-edit"><i class="fa-solid fa-pen"></i> Edit</a>
                                <a href="delete.php?id=<?php echo $product['product_id']; ?>" class="btn-del" onclick="return confirm('Delete this product?');"><i class="fa-solid fa-trash"></i> Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <div class="empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <h3>No products match your search</h3>
            <p>Try a different keyword or check your spelling.</p>
        </div>
    <?php endif; ?>
</body>
</html>