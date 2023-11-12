<?php
include "../main.php";

if($_SERVER['REQUEST_METHOD'] === "POST") {
  $delete = $_POST["delete"] ?? 0;
  if($delete) {
    include "delete.php";
    delete_show_category($_POST["id"]);
    echo "Deleted";
    die();
  }

  $id = $_POST["id"] ?? null;
  $title = $_POST["title"];
  $priority = $_POST["priority"];
  $disabled = $_POST["disabled"] ?? 0;

  if(empty($title)) {
    http_response_code(400);
    echo "Title cannot be empty.";
    die();
  }
  if(empty($priority)) {
    http_response_code(400);
    echo "Priority must be a number.";
    die();
  }

  // Update an existing show category
  if($id !== null) {
    $update_stmt = $conn->prepare("UPDATE show_categories SET title = :title, priority = :priority, disabled = :disabled WHERE id = :id");
    $update_stmt->bindParam(":id", htmlspecialchars($id));
    $update_stmt->bindParam(":title", htmlspecialchars($title));
    $update_stmt->bindParam(":priority", $priority);
    $update_stmt->bindParam(":disabled", $disabled);
    $update_stmt->execute();
    die();
  }

  $insert_stmt = $conn->prepare("INSERT INTO show_categories (title, priority, disabled) VALUES (:title, :priority, :disabled)");
  $insert_stmt->bindParam(":title", htmlspecialchars($title));
  $insert_stmt->bindParam(":priority", $priority);
  $insert_stmt->bindParam(":disabled", $disabled);
  $insert_stmt->execute();

  http_response_code(201);
  die();
}

$id = $_GET["id"] ?? null;
$title = "";
$priority = "";
$disabled = "";

if($id !== null) {
  $show_category_stmt = $conn->prepare("SELECT title, priority, disabled FROM show_categories WHERE id = :id");
  $show_category_stmt->bindParam(":id", $id);
  $show_category_stmt->execute();
  $show_category = $show_category_stmt->fetchObject();
  $title = $show_category->title;
  $priority = $show_category->priority;
  $disabled = $show_category->disabled;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
  <title><?= $id === null ? "New show category" : "Edit show category" ?></title>
</head>

<body>
  <fieldset>
    <legend><?= $id === null ? "New show category" : "Edit show category" ?></legend>
    <form action="" method="post">
      <table>
        <?php if($id !== null): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif ?>
        <tr>
          <td>Title:</td>
          <td><input type="text" name="title" value="<?= $title ?>" required></td>
        </tr>
        <tr>
          <td>Priority:</td>
          <td><input type="number" name="priority" value="<?= $priority ?>" title="Where the show category will be positioned relative to other stations. i.e. Higher or lower" required></td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type="checkbox" name="disabled" id="disabled" <?= $disabled ? "checked" : "" ?>>
            <label for="disabled" title="If a show category is disabled it and its shows won't be accessible by a user.">Disabled</label>
          </td>
        </tr>
      </table>
      <input type="submit" value="Save">
    </form>
    <?php if($id !== null): ?>
    <form action="" method="post">
      <input type="hidden" name="id" value="<?= $id ?>">
      <input type="hidden" name="delete" value="1">
      <input type="submit" value="Delete">
    </form>
    <?php endif ?>
  </fieldset>
</body>

</html>