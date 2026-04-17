<?php
session_start();
include("DB.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE emailaddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: login.php?error=Invalid+email+or+password");
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();
    
    $checkBlock = $conn->prepare("SELECT id FROM blockeduser WHERE emailaddress = ?");
    $checkBlock->bind_param("s", $email);
    $checkBlock->execute();
    $blockResult = $checkBlock->get_result();

    if ($blockResult->num_rows > 0) {
        header("Location: login.php?error=Your+account+has+been+blocked+by+Admin");
        exit();
    }

    if ($user['usertype'] === 'blocked') {
        header("Location: login.php?error=Your+account+has+been+blocked");
        exit();
    }

    if (!password_verify($password, $user['password'])) {
        header("Location: login.php?error=Invalid+email+or+password");
        exit();
    }

    $_SESSION['userid']   = $user['id'];
    $_SESSION['usertype'] = $user['usertype'];

    if ($user['usertype'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: User-dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>BellaCucina | Login</title>
        <link rel="stylesheet" href="main.css" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    </head>
    <body>
        <header>
            <div class="header-container">
                <img src="uploads/logo.png" alt="BellaCucina Logo" class="logo">
                <nav class="nav-menu">
                    <a href="index.html" class="signout-link">
                        <span class="homepage-icon text">↩ HomePage</span>
                    </a>
                </nav>
            </div>
        </header>

        <main class="auth-page">
            <div class="auth-box">
                <img src="uploads/logo.png" class="auth-logo" alt="BellaCucina" />
                <h1>Welcome Back</h1>
                <p class="auth-subtitle">Log in to your BellaCucina account</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        ⚠️ <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input id="email" name="email" type="email" placeholder="your@email.com" required />
                    </div>
                    <div class="input-group">
                        <label for="pass">Password</label>
                        <input id="pass" name="password" type="password" placeholder="••••••••" required />
                    </div>
                    <button type="submit" class="btn-green btn-full">Log in</button>
                </form>

                <div class="auth-footer">
                    New User? <a href="signup.php">Sign up</a>
                </div>
            </div>
        </main>

        <footer>
            © 2026 BellaCucina. All rights reserved.
        </footer>
    </body>
</html>