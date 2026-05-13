<?php
require_once("session.php");
require_once("DB.php");

if ($_SESSION['usertype'] != "user") {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$userID     = $_SESSION['userid'];
$categoryID = isset($_POST['categoryID']) ? intval($_POST['categoryID']) : 0;

if ($categoryID === 0) {
    $sql = "SELECT r.*, u.firstname, u.lastname,
                   u.photofilename as userphoto, c.categoryname
            FROM recipe r
            JOIN user u ON r.userid = u.id
            JOIN recipecategory c ON r.categoryid = c.id";
    $result = $conn->query($sql);
} else {
    $stmt = $conn->prepare(
        "SELECT r.*, u.firstname, u.lastname,
                u.photofilename as userphoto, c.categoryname
         FROM recipe r
         JOIN user u ON r.userid = u.id
         JOIN recipecategory c ON r.categoryid = c.id
         WHERE r.categoryid = ?"
    );
    $stmt->bind_param("i", $categoryID);
    $stmt->execute();
    $result = $stmt->get_result();
}

$recipes = [];
while ($row = $result->fetch_assoc()) {
    $rid = $row['id'];
    $row['likes'] = $conn->query("SELECT COUNT(*) as total FROM likes WHERE recipeid=$rid")
                         ->fetch_assoc()['total'];
    $recipes[] = $row;
}

header('Content-Type: application/json');
echo json_encode($recipes);
?>
