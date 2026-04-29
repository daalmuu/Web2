<?php
require_once("session.php");
require_once("DB.php");

$adminID = (int)$_SESSION['userid'];

$stmtAdmin = mysqli_prepare($conn, "SELECT id, firstname, lastname, emailaddress FROM user WHERE id = ? AND usertype = 'admin'");
mysqli_stmt_bind_param($stmtAdmin, "i", $adminID);
mysqli_stmt_execute($stmtAdmin);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAdmin));
mysqli_stmt_close($stmtAdmin);

if (!$admin) {
    header("Location: login.php?error=Invalid+admin+session");
    exit();
}

$resultReports = mysqli_query($conn, "SELECT report.id AS reportid, report.recipeid,
                                             recipe.userid AS creatorid, recipe.name AS recipename,
                                             user.firstname, user.lastname
                                      FROM report
                                      INNER JOIN recipe ON report.recipeid = recipe.id
                                      INNER JOIN user ON recipe.userid = user.id
                                      ORDER BY report.id DESC");

$resultBlocked = mysqli_query($conn, "SELECT id, firstname, lastname, emailaddress FROM blockeduser ORDER BY id DESC");
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
        <img src="uploads/logo.png" alt="Logo" class="logo">
        <nav class="nav-menu">
            <a href="logout.php" class="logout-link">
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
        <h1>Welcome, <?= htmlspecialchars($admin['firstname']) ?>! 👋</h1>
        <p>Manage reported content and users</p>
    </div>

    <div class="info-card admin-info">
        <h3>👤 My Information</h3>
        <div class="admin-details">
            <div class="detail-item red-bg">
                <span class="detail-label">Name</span>
                <span class="detail-value"><?= htmlspecialchars($admin['firstname'] . " " . $admin['lastname']) ?></span>
            </div>
            <div class="detail-item red-bg">
                <span class="detail-label">Email Address</span>
                <span class="detail-value"><?= htmlspecialchars($admin['emailaddress']) ?></span>
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
                        <tr data-creatorid="<?= (int)$reportRow['creatorid'] ?>">
                            <td>
                                <a href="view_recipe.php?id=<?= (int)$reportRow['recipeid'] ?>" class="red-link">
                                    <?= htmlspecialchars($reportRow['recipename']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="creator-info">
                                    <div class="creator-avatar">
                                        <?= strtoupper(substr($reportRow['firstname'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($reportRow['firstname'] . " " . $reportRow['lastname']) ?></span>
                                </div>
                            </td>
                            <td>
                                <form class="action-form">
                                    <input type="hidden" name="reportid"  value="<?= (int)$reportRow['reportid'] ?>">
                                    <input type="hidden" name="recipeid"  value="<?= (int)$reportRow['recipeid'] ?>">
                                    <input type="hidden" name="creatorid" value="<?= (int)$reportRow['creatorid'] ?>">

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
                    <tr><td colspan="3">No reported recipes found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-section">
        <h2>🚫 Blocked Users List</h2>
        <table class="data-table blocked-users-table">
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
                            <td><?= htmlspecialchars($blockedRow['firstname'] . " " . $blockedRow['lastname']) ?></td>
                            <td><?= htmlspecialchars($blockedRow['emailaddress']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="no-blocked-row"><td colspan="2">No blocked users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<footer>
    © 2026 BellaCucina. All rights reserved.
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {

    $(".action-form").submit(function (e) {
        e.preventDefault();

        let form = $(this);
        let row = form.closest("tr");
        let creatorid = row.data("creatorid");
        let action = form.find("input[name='action']:checked").val();

        $.ajax({
            url: "process_report_action.php",
            type: "POST",
            data: form.serialize(),
            dataType: "json",

            success: function (response) {
                if (response.success === true) {

                    $("tr[data-creatorid='" + creatorid + "']").remove();

                    if ($(".data-table:first tbody tr[data-creatorid]").length === 0) {
                        $(".data-table:first tbody").html(
                            "<tr>" +
                                "<td colspan='3'>No reported recipes found.</td>" +
                            "</tr>"
                        );
                    }

                    if (action === "block") {
                        $(".no-blocked-row").remove();

                        let alreadyExists = false;

                        $(".blocked-users-table tbody tr").each(function () {
                            let email = $(this).find("td:eq(1)").text().trim();

                            if (email === response.emailaddress) {
                                alreadyExists = true;
                            }
                        });

                        if (!alreadyExists) {
                            $(".blocked-users-table tbody").prepend(
                                "<tr>" +
                                    "<td>" + response.firstname + " " + response.lastname + "</td>" +
                                    "<td>" + response.emailaddress + "</td>" +
                                "</tr>"
                            );
                        }
                    }

                } else {
                    alert("Action failed");
                }
            },

            error: function () {
                alert("AJAX request failed");
            }
        });

    });

});
</script>

</body>
</html>