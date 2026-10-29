<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
require_once "connection.php";
require_once "classes/product.php";

$productObj = new Product($conn);
$products = $productObj->getAll();

$isSeller = ($_SESSION['role'] ?? 'seller') === 'seller';
$currentUser = $_SESSION['username'] ?? 'Admin';
$totalValue = 0;
foreach ($products as $p) { $totalValue += ($p['price'] * $p['quantity']); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard · Fresh Catch</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --ink: #0d1f1a;
        --ink-2: #1a2c26;
        --muted: #6b7c76;
        --line: #e6ede9;
        --bg: #f6f9f8;
        --white: #ffffff;
        --teal: #14b8a6;
        --teal-dark: #0d3b2e;
        --coral: #ff6b5b;
        --amber: #f59e0b;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    body { background: var(--bg); min-height: 100vh; color: var(--ink); }

    .sidebar {
        position: fixed; top: 0; left: 0; bottom: 0;
        width: 260px;
        background: linear-gradient(180deg, #0a2e24 0%, #0d3b2e 100%);
        color: white;
        display: flex; flex-direction: column;
        z-index: 100;
        box-shadow: 4px 0 24px rgba(13,59,46,0.15);
    }
    .sidebar::before {
        content: ''; position: absolute; inset: 0;
        background-image: radial-gradient(circle at 50% 0%, rgba(20,184,166,0.15), transparent 60%);
        pointer-events: none;
    }
    .brand {
        display: flex; align-items: center; gap: 12px;
        padding: 26px 22px;
        font-size: 19px; font-weight: 800; letter-spacing: -0.01em;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        position: relative; z-index: 2;
    }
    .brand-icon {
        width: 40px; height: 40px;
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        border-radius: 11px; display: grid; place-items: center;
        font-size: 18px;
        box-shadow: 0 6px 16px rgba(20,184,166,0.4);
    }
    .nav-section {
        padding: 18px 14px 8px;
        font-size: 11px; font-weight: 700;
        color: #6b8a80; letter-spacing: 0.1em; text-transform: uppercase;
        position: relative; z-index: 2;
    }
    .nav-menu { list-style: none; padding: 0 14px; flex: 1; position: relative; z-index: 2; }
    .nav-link {
        display: flex; align-items: center; gap: 14px;
        padding: 12px 14px; margin-bottom: 4px;
        color: #b8d4cc; text-decoration: none;
        border-radius: 10px; font-size: 14px; font-weight: 500;
        transition: all 0.2s; position: relative;
    }
    .nav-link i { width: 18px; text-align: center; font-size: 15px; }
    .nav-link:hover { background: rgba(255,255,255,0.06); color: white; }
    .nav-link.active {
        background: linear-gradient(135deg, rgba(20,184,166,0.25), rgba(20,184,166,0.1));
        color: white; font-weight: 600;
        border: 1px solid rgba(20,184,166,0.3);
    }
    .nav-link.active::before {
        content: ''; position: absolute; left: -14px; top: 50%;
        transform: translateY(-50%);
        width: 3px; height: 20px; background: #14b8a6; border-radius: 0 4px 4px 0;
    }
    .sidebar-footer {
        padding: 16px; position: relative; z-index: 2;
        border-top: 1px solid rgba(255,255,255,0.08);
    }
    .user-card {
        display: flex; align-items: center; gap: 12px;
        padding: 10px; margin-bottom: 10px;
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
    }
    .avatar {
        width: 36px; height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        display: grid; place-items: center;
        font-weight: 700; font-size: 14px; color: white;
    }
    .user-info { flex: 1; overflow: hidden; }
    .user-info strong { display: block; font-size: 13px; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-info span { font-size: 11px; color: #6b8a80; }
    .logout-btn {
        display: flex; align-items: center; gap: 10px;
        color: #ff9f95; text-decoration: none;
        padding: 10px 12px; border-radius: 10px; font-size: 13px; font-weight: 600;
        transition: all 0.2s;
    }
    .logout-btn:hover { background: rgba(255,107,91,0.1); color: #ffb3ab; }

    .main-content {
        margin-left: 260px;
        padding: 32px 40px 60px;
        min-height: 100vh;
    }

    .page-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 32px;
    }
    .greeting h1 {
        font-size: 28px; font-weight: 800; color: var(--ink);
        letter-spacing: -0.02em; margin-bottom: 6px;
    }
    .greeting h1 .wave { display: inline-block; }
    .greeting p { color: var(--muted); font-size: 14px; }
    .header-actions { display: flex; align-items: center; gap: 14px; }
    .status-pill {
        display: flex; align-items: center; gap: 8px;
        background: white; padding: 10px 16px;
        border-radius: 24px; border: 1px solid var(--line);
        font-size: 12px; font-weight: 600; color: var(--ink-2);
    }
    .status-pill .dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 0 4px rgba(16,185,129,0.15);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(16,185,129,0.15); }
        50% { box-shadow: 0 0 0 6px rgba(16,185,129,0.05); }
    }

    .stats {
        display: grid; grid-template-columns: repeat(3, 1fr);
        gap: 20px; margin-bottom: 32px;
    }
    .stat-card {
        background: white; padding: 24px;
        border-radius: 16px; border: 1px solid var(--line);
        position: relative; overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13,59,46,0.08); }
    .stat-card::after {
        content: ''; position: absolute; right: -30px; bottom: -30px;
        width: 120px; height: 120px; border-radius: 50%;
        opacity: 0.08;
    }
    .stat-card.c1::after { background: #14b8a6; }
    .stat-card.c2::after { background: #f59e0b; }
    .stat-card.c3::after { background: #ff6b5b; }
    .stat-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; position: relative; z-index: 2; }
    .stat-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: grid; place-items: center; font-size: 18px; color: white;
    }
    .c1 .stat-icon { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .c2 .stat-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .c3 .stat-icon { background: linear-gradient(135deg, #ff6b5b, #e54b3a); }
    .stat-label { font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--ink); letter-spacing: -0.02em; position: relative; z-index: 2; }
    .stat-value small { font-size: 14px; font-weight: 600; color: var(--muted); margin-left: 4px; }

    .toolbar {
        display: flex; justify-content: space-between; align-items: center;
        gap: 16px; margin-bottom: 24px; flex-wrap: wrap;
    }
    .search-form {
        display: flex; gap: 0; flex: 1; max-width: 440px;
        background: white; border-radius: 12px;
        border: 1.5px solid var(--line);
        overflow: hidden;
        transition: all 0.2s;
    }
    .search-form:focus-within { border-color: var(--teal); box-shadow: 0 0 0 4px rgba(20,184,166,0.1); }
    .search-form i {
        color: var(--muted); padding: 0 0 0 16px;
        display: flex; align-items: center; font-size: 14px;
    }
    .search-input {
        flex: 1; height: 48px; padding: 0 12px;
        border: none; background: transparent;
        font-size: 14px; outline: none; color: var(--ink);
    }
    .search-input::placeholder { color: #a3b1ac; }
    .search-btn {
        padding: 0 22px; background: var(--ink-2); color: white;
        border: none; font-size: 13px; font-weight: 700;
        cursor: pointer; letter-spacing: 0.03em;
        transition: background 0.2s;
    }
    .search-btn:hover { background: var(--ink); }

    .add-btn {
        display: inline-flex; align-items: center; gap: 10px;
        background: linear-gradient(135deg, #ff6b5b, #e54b3a);
        color: white; text-decoration: none;
        padding: 14px 22px; border-radius: 12px;
        font-size: 14px; font-weight: 700;
        box-shadow: 0 8px 20px rgba(255,107,91,0.3);
        transition: all 0.25s;
    }
    .add-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(255,107,91,0.4); }

    .section-title {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 18px;
    }
    .section-title h2 { font-size: 18px; font-weight: 800; color: var(--ink); letter-spacing: -0.01em; }
    .section-title .count {
        font-size: 12px; font-weight: 600; color: var(--muted);
        background: white; padding: 6px 12px;
        border-radius: 8px; border: 1px solid var(--line);
    }

    .products {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 22px;
    }
    .card {
        background: white; border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--line);
        display: flex; flex-direction: column;
        transition: all 0.25s;
        position: relative;
    }
    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 32px rgba(13,59,46,0.1);
        border-color: #d4dfda;
    }
    .card-media { position: relative; height: 190px; overflow: hidden; background: #f0f4f2; }
    .card-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
    .card:hover .card-image { transform: scale(1.05); }
    .no-image {
        width: 100%; height: 100%;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 8px; color: #a3b1ac; font-size: 13px; font-weight: 500;
        background: linear-gradient(135deg, #f0f4f2, #e6ede9);
    }
    .no-image i { font-size: 32px; opacity: 0.5; }

    .stock-badge {
        position: absolute; top: 12px; right: 12px;
        padding: 6px 10px; border-radius: 8px;
        font-size: 11px; font-weight: 700;
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.9);
        color: var(--ink-2);
        display: flex; align-items: center; gap: 5px;
    }
    .stock-badge.low { background: rgba(255,107,91,0.95); color: white; }
    .stock-badge.ok { background: rgba(20,184,166,0.95); color: white; }

    .category-tag {
        position: absolute; top: 12px; left: 12px;
        padding: 5px 10px; border-radius: 8px;
        font-size: 10px; font-weight: 700;
        letter-spacing: 0.05em; text-transform: uppercase;
        background: rgba(13,59,46,0.85);
        color: white;
        backdrop-filter: blur(10px);
    }

    .card-body { padding: 18px; display: flex; flex-direction: column; flex: 1; }
    .product-name {
        font-size: 16px; font-weight: 700; color: var(--ink);
        letter-spacing: -0.01em; margin-bottom: 6px;
        line-height: 1.3;
    }
    .price {
        font-size: 20px; font-weight: 800; color: var(--teal-dark);
        letter-spacing: -0.02em; margin-bottom: 14px;
        display: flex; align-items: baseline; gap: 4px;
    }
    .price small { font-size: 12px; font-weight: 600; color: var(--muted); }
    .divider-line { height: 1px; background: var(--line); margin-bottom: 14px; }
    .buttons { display: flex; gap: 8px; margin-top: auto; }
    .btn-edit, .btn-del {
        flex: 1; padding: 10px 12px; border-radius: 9px;
        font-size: 12px; font-weight: 700;
        text-decoration: none; text-align: center;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.2s; border: none; cursor: pointer;
    }
    .btn-edit { background: #f0f4f2; color: var(--ink-2); }
    .btn-edit:hover { background: var(--ink-2); color: white; }
    .btn-del { background: #fef2f1; color: #dc2626; }
    .btn-del:hover { background: #dc2626; color: white; }

    .empty {
        background: white; padding: 70px 30px; text-align: center;
        border-radius: 18px; border: 1.5px dashed #d4dfda;
        display: flex; flex-direction: column; align-items: center;
    }
    .empty-icon {
        width: 80px; height: 80px; border-radius: 20px;
        background: linear-gradient(135deg, #f0f4f2, #e6ede9);
        display: grid; place-items: center;
        font-size: 32px; color: #a3b1ac;
        margin-bottom: 20px;
    }
    .empty h3 { font-size: 18px; font-weight: 800; color: var(--ink); margin-bottom: 8px; }
    .empty p { font-size: 14px; color: var(--muted); margin-bottom: 22px; max-width: 340px; }

    @media (max-width: 1100px) { .stats { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 900px) {
        .sidebar { width: 72px; }
        .brand span, .nav-link span, .nav-section, .user-info, .logout-btn span { display: none; }
        .brand { justify-content: center; padding: 20px 0; }
        .brand-icon { width: 36px; height: 36px; }
        .nav-link { justify-content: center; padding: 12px 0; }
        .nav-link.active::before { left: -14px; }
        .sidebar-footer { padding: 10px; }
        .user-card { justify-content: center; padding: 8px; }
        .logout-btn { justify-content: center; }
        .main-content { margin-left: 72px; padding: 20px; }
    }
    @media (max-width: 700px) {
        .stats { grid-template-columns: 1fr; }
        .page-header { flex-direction: column; align-items: flex-start; gap: 14px; }
        .toolbar { flex-direction: column; align-items: stretch; }
        .search-form { max-width: 100%; }
        .add-btn { justify-content: center; }
    }
</style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon"><i class="fa-solid fa-fish"></i></div>
        <span>Fresh Catch</span>
    </div>

    <div class="nav-section">Main Menu</div>
    <ul class="nav-menu">
        <li><a href="index.php" class="nav-link active"><i class="fa-solid fa-table-cells-large"></i><span>Dashboard</span></a></li>
        <li><a href="#" class="nav-link"><i class="fa-solid fa-boxes-stacked"></i><span>Inventory</span></a></li>
        <li><a href="#" class="nav-link"><i class="fa-solid fa-users"></i><span>Customers</span></a></li>
        <li><a href="#" class="nav-link"><i class="fa-solid fa-clipboard-list"></i><span>Orders</span></a></li>
        <li><a href="#" class="nav-link"><i class="fa-solid fa-chart-pie"></i><span>Analytics</span></a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="avatar"><?php echo strtoupper(substr($currentUser, 0, 1)); ?></div>
            <div class="user-info">
                <strong><?php echo htmlspecialchars($currentUser); ?></strong>
                <span><?php echo $isSeller ? 'Seller Account' : 'Customer'; ?></span>
            </div>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Sign Out</span></a>
    </div>
</aside>

<main class="main-content">

    <div class="page-header">
        <div class="greeting">
            <h1>Good <?php
                $h = (int)date('H');
                echo $h < 12 ? 'morning' : ($h < 18 ? 'afternoon' : 'evening');
            ?>, <?php echo htmlspecialchars($currentUser); ?> <span class="wave">👋</span></h1>
            <p>Here's what's fresh in your inventory today.</p>
        </div>
        <div class="header-actions">
            <div class="status-pill"><span class="dot"></span> System Live</div>
        </div>
    </div>

    <div class="stats">
        <div class="stat-card c1">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo count($products); ?><small>items</small></div>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-fish"></i></div>
            </div>
        </div>
        <div class="stat-card c2">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Inventory Value</div>
                    <div class="stat-value">₱<?php echo number_format($totalValue, 0); ?><small>total</small></div>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
            </div>
        </div>
        <div class="stat-card c3">
            <div class="stat-top">
                <div>
                    <div class="stat-label">Low Stock Alerts</div>
                    <div class="stat-value">
                        <?php
                        $low = 0;
                        foreach ($products as $p) if ($p['quantity'] < 10) $low++;
                        echo $low;
                        ?><small>items</small>
                    </div>
                </div>
                <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            </div>
        </div>
    </div>

    <div class="toolbar">
        <form action="modals/search.php" method="GET" class="search-form">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="keyword" class="search-input" placeholder="Search by name, category, or price..." required>
            <button type="submit" class="search-btn">SEARCH</button>
        </form>

        <?php if ($isSeller): ?>
            <a href="modals/add.php" class="add-btn">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
        <?php endif; ?>
    </div>

    <div class="section-title">
        <h2>Fresh Catches</h2>
        <span class="count"><?php echo count($products); ?> products</span>
    </div>

    <?php if (count($products) > 0): ?>
        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="card">
                    <div class="card-media">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-image">
                        <?php else: ?>
                            <div class="no-image"><i class="fa-regular fa-image"></i> No Image</div>
                        <?php endif; ?>
                        <span class="category-tag"><?php echo htmlspecialchars($product['category']); ?></span>
                        <?php
                        $qty = (int)$product['quantity'];
                        $stockClass = $qty < 10 ? 'low' : 'ok';
                        $stockLabel = $qty < 10 ? 'Low Stock' : 'In Stock';
                        ?>
                        <span class="stock-badge <?php echo $stockClass; ?>">
                            <i class="fa-solid fa-circle" style="font-size:6px;"></i>
                            <?php echo $qty; ?> <?php echo $stockLabel; ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="price">₱<?php echo number_format($product['price'], 2); ?> <small>per kg</small></div>
                        <div class="divider-line"></div>

                        <?php if ($isSeller): ?>
                            <div class="buttons">
                                <a href="modals/update.php?id=<?php echo $product['product_id']; ?>" class="btn-edit"><i class="fa-solid fa-pen"></i> Edit</a>
                                <a href="modals/delete.php?id=<?php echo $product['product_id']; ?>" class="btn-del" onclick="return confirm('Delete this product?');"><i class="fa-solid fa-trash"></i> Delete</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty">
            <div class="empty-icon"><i class="fa-solid fa-fish"></i></div>
            <h3>No products yet</h3>
            <p>Your inventory is empty. Add your first fresh catch to get started.</p>
            <?php if ($isSeller): ?>
                <a href="modals/add.php" class="add-btn"><i class="fa-solid fa-plus"></i> Add Your First Product</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>
</body>
</html>