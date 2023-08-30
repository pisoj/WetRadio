<?php
include "helpers/get-only.php";
include "../../main.php";

$send_types_stmt = $conn->prepare("SELECT id, title FROM send_types ORDER BY priority DESC");
$send_types_stmt->execute();
$send_types = $send_types_stmt->fetchAll(PDO::FETCH_OBJ);

echo json_encode($send_types);
?>