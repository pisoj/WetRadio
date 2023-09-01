<?php
include "helpers/get-only.php";
include "../../main.php";

$categories_stmt = $conn->prepare("SELECT id, title, priority FROM show_categories");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll(PDO::FETCH_OBJ);

echo json_encode($categories);
?>
