<?php
include "helpers/get-only.php";
include "../../main.php";

$stations_stmt = $conn->prepare("SELECT id, title, description, endpoints, endpoint_order FROM stations ORDER BY priority DESC");
$stations_stmt->execute();
$stations = $stations_stmt->fetchAll(PDO::FETCH_OBJ);

echo json_encode($stations);
?>
