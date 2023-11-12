<?php
include "../main.php";

if($_SERVER['REQUEST_METHOD'] === "POST") {
  $id = $_POST["id"] ?? null;
  $title = $_POST["title"];
  $description = $_POST["description"];
  $raw_endpoints = $_POST["endpoints"];
  $raw_endpoint_names = $_POST["endpoint_names"];
  $endpoint_order = $_POST["endpoint_order"];
  $priority = $_POST["priority"];
  $disabled = $_POST["disabled"] ?? 0;

  if(empty($title)) {
    http_response_code(400);
    echo "Title cannot be empty.";
    die();
  }
  if(empty($description)) {
    http_response_code(400);
    echo "Description cannot be empty.";
    die();
  }
  if(empty($raw_endpoints)) {
    http_response_code(400);
    echo "You must provide at least one endpoint.";
    die();
  }
  if($endpoint_order != "ordered" && $endpoint_order != "random") {
    http_response_code(400);
    echo "Endpoint order must be either ordered or random.";
    die();
  }
  if(empty($priority)) {
    http_response_code(400);
    echo "Priority must be a number.";
    die();
  }

  $endpoints = explode(",", htmlspecialchars($raw_endpoints));
  $endpoint_names = explode(",", htmlspecialchars($raw_endpoint_names));
  if(count($endpoints) !== count($endpoint_names)) {
    http_response_code(400);
    echo "The length of endpoint URLs and endpoint names should match.";
    die();
  }

  // Update an existng station
  if($id !== null) {
    $update_stmt = $conn->prepare("UPDATE stations SET title = :title, description = :description, endpoints = :endpoints, endpoint_names = :endpoint_names, endpoint_order = :endpoint_order, priority = :priority, disabled = :disabled WHERE id = :id");
    $update_stmt->bindParam(":id", htmlspecialchars($id));
    $update_stmt->bindParam(":title", htmlspecialchars($title));
    $update_stmt->bindParam(":description", htmlspecialchars($description));
    $update_stmt->bindParam(":endpoints", json_encode($endpoints));
    $update_stmt->bindParam(":endpoint_names", json_encode($endpoint_names));
    $update_stmt->bindParam(":endpoint_order", $endpoint_order);
    $update_stmt->bindParam(":priority", $priority);
    $update_stmt->bindParam(":disabled", $disabled);
    $update_stmt->execute();
    die();
  }

  $insert_stmt = $conn->prepare("INSERT INTO stations (title, description, endpoints, endpoint_names, endpoint_order, priority, disabled) VALUES (:title, :description, :endpoints, :endpoint_names, :endpoint_order, :priority, :disabled)");
  $insert_stmt->bindParam(":title", htmlspecialchars($title));
  $insert_stmt->bindParam(":description", htmlspecialchars($description));
  $insert_stmt->bindParam(":endpoints", json_encode($endpoints));
  $insert_stmt->bindParam(":endpoint_names", json_encode($endpoint_names));
  $insert_stmt->bindParam(":endpoint_order", $endpoint_order);
  $insert_stmt->bindParam(":disabled", $disabled);
  $insert_stmt->execute();

  http_response_code(201);
  die();
}

$id = $_GET["id"] ?? null;
$title = "";
$description = "";
$endpoints = "";
$endpoint_names = "";
$endpoint_order = "";
$priority = "";
$disabled = "";

if($id !== null) {
  $station_stmt = $conn->prepare("SELECT title, description, endpoints, endpoint_names, endpoint_order, priority, disabled FROM stations WHERE id = :id");
  $station_stmt->bindParam(":id", $id);
  $station_stmt->execute();
  $station = $station_stmt->fetchObject();

  $title = $station->title;
  $description = $station->description;
  $endpoints = implode(",", json_decode($station->endpoints));
  $endpoint_names = implode(",", json_decode($station->endpoint_names));
  $endpoint_order = $station->endpoint_order;
  $priority = $station->priority;
  $disabled = $station->disabled;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
  <title><?= $id === null ? "New station" : "Edit station" ?></title>
</head>

<body>
  <fieldset>
    <legend><?= $id === null ? "New station" : "Edit station" ?></legend>
    <form action="" method="post">
      <table>
        <tr>
          <td>Title:</td>
          <td><input type="text" name="title" value="<?= $title ?>" required></td>
        </tr>
        <tr>
          <td>Description:</td>
          <td><input type="text" name="description" value="<?= $description ?>" required></td>
        </tr>
        <tr>
          <td>Endpoint URLs:</td>
          <td><input type="text" name="endpoints" value="<?= $endpoints ?>" title="A comma-sepparated list of endpoint URLs" placeholder="http://my.radio/stream1,http://my.radio/stream2" required></td>
        </tr>
        <tr>
          <td>Endpoint names:</td>
          <td><input type="text" name="endpoint_names" value="<?= $endpoint_names ?>" title="A comma-sepparated list of endpoint names witch will be displayed to the user when choosing an endpoint. You should specify a name for every endpoint URL." placeholder="Mobile MP3,Hight quality FLAC" required></td>
        </tr>
        <tr>
          <td>Endpoint order:</td>
          <td>
            <input type="radio" name="endpoint_order" value="ordered" id="ordered" required <?= $endpoint_order === "ordered" ? "checked" : "" ?>>
            <label for="ordered" title="The user will be able to choose the endpont they want. Select this if you have multiple endpoints of a different quality.">Ordered</label>
            <input type="radio" name="endpoint_order" value="random" id="random" required <?= $endpoint_order === "random" ? "checked" : "" ?>>
            <label for="random" title="The endpoint selection won't be avaible to the user, instead, endpoints are goint to be choosen randomly. Select this for load balancing multiple endpoints of the same quality.">Random</label>
          </td>
        </tr>
        <tr>
          <td>Priority:</td>
          <td><input type="number" name="priority" value="<?= $priority ?>" title="Where the station will be positioned relative to other stations. i.e. Higher or lower" required></td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type="checkbox" name="disabled" id="disabled" <?= $disabled ? "checked" : "" ?>>
            <label for="disabled" title="If a station is disabled it won't be accessible by a user.">Disabled</label>
          </td>
        </tr>
      </table>
      <?php if($id !== null): ?>
      <input type="hidden" name="id" value="<?= $id ?>">
      <?php endif ?>
      <input type="submit" value="Save">
    </form>
  </fieldset>
</body>

</html>