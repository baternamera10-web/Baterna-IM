<?php
session_start();
require_once "connection.php";
require_once "classes/user.php";

if (isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$username || !$email || !$password || !$confirm) { $error = "All fields are required."; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = "Please enter a valid email address."; }
    elseif ($password !== $confirm) { $error = "Passwords do not match."; }
    elseif (strlen($password) < 6) { $error = "Password must be at least 6 characters."; }
    else {
        $userObj = new User($conn);
        $result = $userObj->register($username, $email, $password, 'seller');
        if ($result === true) { $success = "Account created! You can now sign in."; }
        else { $error = $result; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account · Fresh Catch</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    body { min-height: 100vh; display: grid; grid-template-columns: 1.1fr 1fr; background: #f8faf9; }

    .hero {
        position: relative;
        background:
            radial-gradient(circle at 20% 20%, rgba(20,184,166,0.35), transparent 55%),
            radial-gradient(circle at 80% 80%, rgba(255,107,91,0.28), transparent 55%),
            linear-gradient(135deg, #0a2e24 0%, #0d3b2e 60%, #062018 100%);
        color: white; padding: 60px;
        display: flex; flex-direction: column; justify-content: space-between;
        overflow: hidden;
    }
    .hero::before {
        content: ''; position: absolute; inset: 0;
        background-image:
            radial-gradient(circle at 10% 30%, rgba(255,255,255,0.08) 1px, transparent 1px),
            radial-gradient(circle at 70% 60%, rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 30px 30px, 45px 45px; pointer-events: none;
    }
    .brand { display: flex; align-items: center; gap: 14px; font-size: 24px; font-weight: 800; position: relative; z-index: 2; }
    .brand-icon {
        width: 48px; height: 48px;
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        border-radius: 14px; display: grid; place-items: center; font-size: 22px;
        box-shadow: 0 8px 20px rgba(20,184,166,0.4);
    }
    .hero-body { position: relative; z-index: 2; max-width: 440px; }
    .hero-body h1 { font-size: 44px; line-height: 1.12; letter-spacing: -0.03em; font-weight: 800; margin-bottom: 20px; }
    .hero-body h1 span { background: linear-gradient(120deg, #14b8a6, #5eead4); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
    .hero-body p { font-size: 16px; color: #b8d4cc; line-height: 1.7; }
    .feature-list { display: flex; flex-direction: column; gap: 16px; margin-top: 40px; }
    .feature { display: flex; align-items: center; gap: 14px; font-size: 14px; color: #d1e7dd; font-weight: 500; }
    .feature i {
        width: 36px; height: 36px; background: rgba(20,184,166,0.15);
        border: 1px solid rgba(20,184,166,0.3); border-radius: 10px;
        display: grid; place-items: center; color: #5eead4; font-size: 14px;
    }
    .hero-footer { font-size: 12px; color: #6b8a80; position: relative; z-index: 2; }

    .form-side { display: flex; align-items: center; justify-content: center; padding: 40px; background: #f8faf9; overflow-y: auto; }
    .form-card { width: 100%; max-width: 400px; }
    .form-card h2 { font-size: 28px; font-weight: 800; color: #0d3b2e; letter-spacing: -0.02em; margin-bottom: 8px; }
    .form-card .subtitle { color: #6b7c76; font-size: 14px; margin-bottom: 28px; }

    .error-msg, .success-msg {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 500;
        margin-bottom: 20px;
    }
    .error-msg { background: #fef2f1; color: #b71c1c; border: 1px solid #fecaca; }
    .success-msg { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }

    .field { margin-bottom: 18px; }
    .field label { display: block; font-size: 13px; font-weight: 600; color: #2d3f38; margin-bottom: 8px; }
    .input-wrap { position: relative; }
    .input-wrap i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3a0; font-size: 14px; }
    .input-wrap input {
        width: 100%; height: 50px; padding: 0 16px 0 44px;
        border: 1.5px solid #e2e8e5; border-radius: 12px; background: white;
        font-size: 14px; color: #1a2c26; outline: none; transition: all 0.2s;
    }
    .input-wrap input:focus { border-color: #14b8a6; box-shadow: 0 0 0 4px rgba(20,184,166,0.12); }
    .input-wrap input::placeholder { color: #a3b1ac; }

    .btn-submit {
        width: 100%; height: 52px;
        background: linear-gradient(135deg, #0d3b2e, #14b8a6);
        color: white; border: none; border-radius: 12px;
        font-size: 15px; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        transition: all 0.25s; box-shadow: 0 8px 20px rgba(13,59,46,0.25);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(13,59,46,0.35); }

    .divider { display: flex; align-items: center; gap: 14px; margin: 24px 0; color: #94a3a0; font-size: 12px; }
    .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8e5; }
    .footer-link { text-align: center; font-size: 14px; color: #6b7c76; }
    .footer-link a { color: #0d9488; font-weight: 700; text-decoration: none; }
    .footer-link a:hover { text-decoration: underline; }

    @media (max-width: 900px) { body { grid-template-columns: 1fr; } .hero { display: none; } }
</style>
</head>
<body>
    <aside class="hero">
        <div class="brand">
            <div class="brand-icon"><i class="fa-solid fa-fish"></i></div>
            <span>Fresh Catch</span>
        </div>
        <div class="hero-body">
            <h1>Start selling <span>smarter.</span></h1>
            <p>Join hundreds of seafood vendors who trust Fresh Catch to manage their daily catch, inventory, and orders.</p>
            <div class="feature-list">
                <div class="feature"><i class="fa-solid fa-rocket"></i> Get set up in under a minute</div>
                <div class="feature"><i class="fa-solid fa-mobile-screen"></i> Manage from any device</div>
                <div class="feature"><i class="fa-solid fa-gift"></i> Free forever for small sellers</div>
            </div>
        </div>
        <div class="hero-footer">© <?php echo date('Y'); ?> Fresh Catch · All rights reserved</div>
    </aside>

    <main class="form-side">
        <div class="form-card">
            <h2>Create your account</h2>
            <p class="subtitle">Register as a seller and start managing your catch</p>

            <?php if ($error): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" required>
                    </div>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
                    </div>
                </div>
                <div class="field">
                    <label for="confirm">Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-shield-halved"></i>
                        <input type="password" id="confirm" name="confirm" placeholder="Re-enter password" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="divider">ALREADY A MEMBER?</div>
            <div class="footer-link">
                Have an account? <a href="login.php">Sign in →</a>
            </div>
        </div>
    </main>
</body>
</html>