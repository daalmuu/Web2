<?php
session_start();
include("DB.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['firstName']);
    $lastName  = trim($_POST['lastName']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id FROM user WHERE emailaddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: signup.php?error=Email+is+already+registered");
        exit();
    }
    $checkBlock = $conn->prepare("SELECT id FROM blockeduser WHERE emailaddress = ?");
    $checkBlock->bind_param("s", $email);
    $checkBlock->execute();
    $blockResult = $checkBlock->get_result();

    if ($blockResult->num_rows > 0) {
        header("Location: signup.php?error=Your+account+has+been+blocked+by+Admin");
        exit();
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $photoFileName = "default.png";

    if (!empty($_FILES['profile-photo']['name'])) {
        $tmpName   = $_FILES['profile-photo']['tmp_name'];
        $origName  = basename($_FILES['profile-photo']['name']);
        $extension = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $newFileName = uniqid("user_", true) . "." . $extension;
        $uploadPath  = "uploads/" . $newFileName;

        if (move_uploaded_file($tmpName, $uploadPath)) {
            $photoFileName = $newFileName;
        }
    }

    $userType = "user";
    $stmt = $conn->prepare("INSERT INTO user (firstname, lastname, emailaddress, password, photofilename, usertype)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstName, $lastName, $email, $hashedPassword, $photoFileName, $userType);
    $stmt->execute();

    $newUserID = $conn->insert_id;
    $stmt->close();

    $_SESSION['userid']   = $newUserID;
    $_SESSION['usertype'] = $userType;

    header("Location: User-dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>BellaCucina | Sign Up</title>
        <link rel="stylesheet" href="main.css" />
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
            <div class="auth-box signup-box">
                <h1>Create Account</h1>
                <p class="auth-subtitle">Join our Italian recipe community</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">
                        ⚠️ <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form class="signup-form" action="signup.php" method="POST" enctype="multipart/form-data">

                    <div class="profile-upload-section">
                        <div class="profile-circle">👤</div>
                        <label for="profile-photo">Upload Photo</label>
                        <input type="file" id="profile-photo" name="profile-photo" accept="uploads/*">
                        <span class="optional">(Optional)</span>
                    </div>

                    <div class="signup-row">
                        <div class="input-group">
                            <label for="firstName">First Name *</label>
                            <input id="firstName" name="firstName" type="text" placeholder="Sara" required>
                        </div>
                        <div class="input-group">
                            <label for="lastName">Last Name *</label>
                            <input id="lastName" name="lastName" type="text" placeholder="Ahmed" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="email">Email Address *</label>
                        <input id="email" name="email" type="email" placeholder="sara@example.com" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password *</label>
                        <input id="password" name="password" type="password" placeholder="••••••••" required>
                    </div>

                    <button class="btn-red btn-full" type="submit">Create Account</button>

                    <div class="auth-footer">
                        Already have an account? <a href="login.php">Log in</a>
                    </div>
                </form>
            </div>
        </main>

        <footer>
            © 2026 BellaCucina. All rights reserved.
        </footer>
    </body>
</html>