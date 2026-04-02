<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "admin_session.php";
require_once "DB.php";

$adminID = (int) $_SESSION['userID'];

$sqlAdmin = "SELECT id, firstname, lastname, emailaddress
             FROM user
             WHERE id = ? AND usertype = 'admin'";

$stmtAdmin = mysqli_prepare($conn, $sqlAdmin);
mysqli_stmt_bind_param($stmtAdmin, "i", $adminID);
mysqli_stmt_execute($stmtAdmin);
$resultAdmin = mysqli_stmt_get_result($stmtAdmin);
$admin = mysqli_fetch_assoc($resultAdmin);
mysqli_stmt_close($stmtAdmin);

if (!$admin) {
    header("Location: login.php?error=Invalid+admin+session");
    exit();
}


$sqlReports = "SELECT 
                    report.id AS reportid,
                    report.recipeid,
                    recipe.userid AS creatorid,
                    recipe.name AS recipename,
                    user.firstname,
                    user.lastname
               FROM report
               INNER JOIN recipe ON report.recipeid = recipe.id
               INNER JOIN user ON recipe.userid = user.id
               ORDER BY report.id DESC";

$resultReports = mysqli_query($conn, $sqlReports);


$sqlBlocked = "SELECT id, firstname, lastname, emailaddress
               FROM blockeduser
               ORDER BY id DESC";

$resultBlocked = mysqli_query($conn, $sqlBlocked);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BellaCucina | Admin Dashboard</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>

    <header>
        <div class="header-container">
            <img src="images/logo.png" alt="Logo" class="logo">

            <nav class="nav-menu">
                <a href="signout.php" class="logout-link">
                    <span class="icon">
                        <span class="bracket"></span>
                        <span class="arrow">➜</span>
                    </span>
                    <span class="text">Sign out</span>
                </a>
            </nav>
        </div>
    </header>

    <main class="dashboard-page">

        <div class="welcome-banner admin-banner">
            <span class="welcome-lable">Admin Panel</span>
            <h1>Welcome, <?php echo htmlspecialchars($admin['firstname']); ?>! 👋</h1>
            <p>Manage reported content and users</p>
        </div>

        <div class="info-card admin-info">
            <h3>👤 My Information</h3>
            <div class="admin-details">
                <div class="detail-item red-bg">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($admin['firstname'] . " " . $admin['lastname']); ?>
                    </span>
                </div>
                <div class="detail-item red-bg">
                    <span class="detail-label">Email Address</span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($admin['emailaddress']); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="table-section">
            <h2>🚩 Reported Recipes</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Recipe Name</th>
                        <th>Recipe Creator</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultReports && mysqli_num_rows($resultReports) > 0): ?>
                        <?php while ($reportRow = mysqli_fetch_assoc($resultReports)): ?>
                            <tr>
                                <td>
                                    <a href="view_recipe.php?id=<?php echo (int)$reportRow['recipeid']; ?>" class="red-link">
                                        <?php echo htmlspecialchars($reportRow['recipename']); ?>
                                    </a>
                                </td>

                                <td>
                                    <div class="creator-info">
                                        <div class="creator-avatar">
                                            <?php echo strtoupper(substr($reportRow['firstname'], 0, 1)); ?>
                                        </div>
                                        <span>
                                            <?php echo htmlspecialchars($reportRow['firstname'] . " " . $reportRow['lastname']); ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <form action="process_report_action.php" method="POST" class="action-form">
                                        <input type="hidden" name="reportid" value="<?php echo (int)$reportRow['reportid']; ?>">
                                        <input type="hidden" name="recipeid" value="<?php echo (int)$reportRow['recipeid']; ?>">
                                        <input type="hidden" name="creatorid" value="<?php echo (int)$reportRow['creatorid']; ?>">

                                        <label>
                                            <input type="radio" name="action" value="block" required>
                                            <span class="red-text">Block User</span>
                                        </label>

                                        <label>
                                            <input type="radio" name="action" value="dismiss" required>
                                            Dismiss
                                        </label>

                                        <button type="submit" class="btn-red btn-small">Submit</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No reported recipes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-section">
            <h2>🚫 Blocked Users List</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultBlocked && mysqli_num_rows($resultBlocked) > 0): ?>
                        <?php while ($blockedRow = mysqli_fetch_assoc($resultBlocked)): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($blockedRow['firstname'] . " " . $blockedRow['lastname']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($blockedRow['emailaddress']); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No blocked users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        © 2026 BellaCucina. All rights reserved.
    </footer>

</body>
</html>