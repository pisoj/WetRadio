<?php
include "../main.php";
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
</head>

<?php
function upload_image(): string {
  global $image_mime_types;
  if (!in_array($_FILES["image"]["type"], array_keys($image_mime_types))) {
    http_response_code(400);
    echo message("Bad File", "File is not an allowed image file.", "error");
    die();
  }

  $file_hash_name = hash_file("md5", $_FILES["image"]["tmp_name"]) . $image_mime_types[$_FILES["image"]["type"]];
  $move_status = move_uploaded_file($_FILES["image"]["tmp_name"], "../assets/" . $file_hash_name);
  if (!$move_status) {
    http_response_code(500);
    echo "An error ocured during the upload of the thumbnail.";
    die();
  }
  return $file_hash_name;
}

if($_SERVER['REQUEST_METHOD'] === "POST") {
  $id = $_POST["id"] ?? null;
  $title = $_POST["title"];
  $subtitle = $_POST["subtitle"] ?? null;
  $is_replayable = $_POST["is_replayable"];
  $category_id = $_POST["category_id"];
  $priority = $_POST["priority"];

  if(empty($title)) {
    http_response_code(400);
    echo "Title cannot be empty.";
    die();
  }
  if(empty($category_id)) {
    http_response_code(400);
    echo "Category id must be spacified.";
    die();
  }
  if(empty($priority)) {
    http_response_code(400);
    echo "Priority must be a number.";
    die();
  }
  // Update an existing show
  if($id !== null) {
    $update_stmt = $conn->prepare("UPDATE show_items SET title = :title, subtitle = :subtitle," . (!empty($_FILES["image"]["tmp_name"]) ? "image = :image," : "") . "is_replayable = :is_replayable, category_id = :category_id, priority = :priority WHERE id = :id");
    $update_stmt->bindParam(":id", htmlspecialchars($id));
    $update_stmt->bindParam(":title", htmlspecialchars($title));
    $update_stmt->bindParam(":subtitle", htmlspecialchars($subtitle));
    $update_stmt->bindParam(":is_replayable", $is_replayable);
    $update_stmt->bindParam(":category_id", htmlspecialchars($category_id));
    $update_stmt->bindParam(":priority", $priority);
    if(!empty($_FILES["image"]["tmp_name"])) {
      $file_hash_name = upload_image();
      $update_stmt->bindParam(":image", $file_hash_name);
    }
    $update_stmt->execute();
    die();
  }

  $file_hash_name = upload_image();

  $insert_stmt = $conn->prepare("INSERT INTO show_items (title, subtitle, image, is_replayable, category_id, priority) VALUES (:title, :subtitle, :image, :is_replayable, :category_id, :priority)");
  $insert_stmt->bindParam(":title", htmlspecialchars($title));
  $insert_stmt->bindParam(":subtitle", htmlspecialchars($subtitle));
  $insert_stmt->bindParam(":image", $file_hash_name);
  $insert_stmt->bindParam(":is_replayable", $is_replayable);
  $insert_stmt->bindParam(":category_id", htmlspecialchars($category_id));
  $insert_stmt->bindParam(":priority", $priority);
  $insert_stmt->execute();

  http_response_code(201);
  die();
}

$allowed_image_types = implode(",", array_keys($image_mime_types));
$show_categories_stmt = $conn->prepare("SELECT id, title FROM show_categories ORDER BY priority DESC");
$show_categories_stmt->execute();

$title = "";
$subtitle = "";
$image = "";
$is_replayable = "";
$category_id = "";
$priority = "";

$id = $_GET["id"] ?? null;
if($id !== null) {
  $show_stmt = $conn->prepare("SELECT title, subtitle, image, is_replayable, category_id, priority FROM show_items WHERE id = :id");
  $show_stmt->bindParam(":id", $id);
  $show_stmt->execute();
  $show = $show_stmt->fetchObject();
  $title = $show->title;
  $subtitle = $show->subtitle;
  $image = $show->image;
  $is_replayable = $show->is_replayable;
  $category_id = $show->category_id;
  $priority = $show->priority;
}
?>

<body>
  <fieldset>
    <legend><?= $id !== null ? "Edit show" : "New show" ?></legend>
    <form action="" method="post" enctype="multipart/form-data">
      <table>
        <tr>
          <td>Title:</td>
          <td><input type="text" name="title" value="<?= $title ?>" required></td>
        </tr>
        <tr>
          <td>Subtitle (optional):</td>
          <td><input type="text" name="subtitle" value="<?= $subtitle ?>"></td>
        </tr>
        <tr>
          <td><?= empty($image) ? "Thumbnail:" : "New thumbnail (optional):" ?></td>
          <td><input type="file" name="image" accept="<?= $allowed_image_types ?>" <?= empty($image) ? "required" : "" ?>></td>
        </tr>
        <?php if(!empty($image)): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <tr>
          <td>Current thumbnail:</td>
          <td>
            <img src="../assets/<?= $image ?>">
          </td>
        </tr>
        <?php endif ?>
        <tr>
          <td>Is replayable:</td>
          <td>
            <input type="radio" name="is_replayable" id="is_replayable_yes" value="1" required <?= $is_replayable == 1 ? "checked" : "" ?>>
            <label for="is_replayable_yes" title="Users will be able to listen to recordings of this show.">Yes</label>
            <input type="radio" name="is_replayable" id="is_replayable_no" value="0" required <?= $is_replayable == 0 ? "checked" : "" ?>>
            <label for="is_replayable_no" title="You won't be able to uplaod recording for this show, recordings button won't be shown to users.">No</label>
          </td>
        </tr>
        <tr>
          <td>Show category:</td>
          <td>
            <select name="category_id" value="<?= $category_id ?>" required>
              <?php while($show_category = $show_categories_stmt->fetchObject()): ?>
              <option value="<?= $show_category->id ?>"><?= $show_category->title ?></option>
              <?php endwhile ?>
            </select>
          </td>
        </tr>
        <tr>
          <td>Priority:</td>
          <td><input type="number" name="priority" value="<?= $priority ?>" title="Where the show category will be positioned relative to other stations. i.e. Higher or lower" required></td>
        </tr>
      </table>
      <input type="submit" value="Save">
    </form>
  </fieldset>
</body>

</html>