<?php
include "../main.php";

function upload_audio(): string {
  global $audio_mime_types;
  if (!in_array($_FILES["file"]["type"], array_keys($audio_mime_types))) {
    http_response_code(400);
    echo "File is not an allowed audio file.";
    die();
  }

  $file_hash_name = hash_file("md5", $_FILES["file"]["tmp_name"]) . $audio_mime_types[$_FILES["file"]["type"]];
  $move_status = move_uploaded_file($_FILES["file"]["tmp_name"], "../assets/" . $file_hash_name);
  if (!$move_status) {
    http_response_code(500);
    echo "An error ocured during the upload of the file.";
    die();
  }
  return $file_hash_name;
}

if($_SERVER['REQUEST_METHOD'] === "POST") {
  $delete = $_POST["delete"] ?? 0;
  if($delete) {
    include "delete.php";
    delete_show_recording($_POST["id"]);
    echo "Deleted";
    die();
  }

  $id = $_POST["id"] ?? null;
  $show_id = $_POST["show_id"] ?? null;
  $title = $_POST["title"];
  $description = $_POST["description"];
  $datetime = $_POST["datetime"];
  $disabled = $_POST["disabled"] ?? 0;

  if($show_id === null) {
    http_response_code(400);
    echo "You must specify a show id";
    die();
  }
  if(empty($datetime)) {
    http_response_code(400);
    echo "Datetime must be provided.";
    die();
  }

  if(!empty($id)) {
    $update_stmt = $conn->prepare("UPDATE show_recordings SET title = :title, description = :description," . (!empty($_FILES["file"]["tmp_name"]) ? "file = :file," : "") . "datetime = :datetime, disabled = :disabled WHERE id = :id");
    $update_stmt->bindParam(":id", htmlspecialchars($id));
    $update_stmt->bindParam(":title", htmlspecialchars($title));
    $update_stmt->bindParam(":description", htmlspecialchars($description));
    $update_stmt->bindParam(":datetime", htmlspecialchars($datetime));
    $update_stmt->bindParam(":disabled", $disabled);
    if(!empty($_FILES["file"]["tmp_name"])) {
      $file_hash_name = upload_audio();
      $select_stmt = $conn->prepare("SELECT file FROM show_recordings WHERE id = :id");
      $select_stmt->bindParam(":id", htmlspecialchars($id));
      $select_stmt->execute();
      $file = $select_stmt->fetchObject()->file;
      unlink("../assets/{$file}");

      $update_stmt->bindParam(":file", $file_hash_name);
    }
    $update_stmt->execute();
    die();
  }

  $file_hash_name = upload_audio();

  $insert_stmt = $conn->prepare("INSERT INTO show_recordings (show_item_id, title, description, file, datetime, disabled) VALUES (:show_item_id, :title, :description, :file, :datetime, :disabled)");
  $insert_stmt->bindParam(":show_item_id", htmlspecialchars($show_id));
  $insert_stmt->bindParam(":title", htmlspecialchars($title));
  $insert_stmt->bindParam(":description", htmlspecialchars($description));
  $insert_stmt->bindParam(":file", $file_hash_name);
  $insert_stmt->bindParam(":datetime", htmlspecialchars($datetime));
  $insert_stmt->bindParam(":disabled", $disabled);
  $insert_stmt->execute();

  http_response_code(201);
  die();
}

$show_id = $_GET["id"] ?? null;
$new = $_GET["new"] ?? 0;
$page_size = $_GET["page_size"] ?? 10;
$page = $_GET["page"] ?? 1;
$page_offset = ($page - 1) * $page_size;
$total_pages = 0;

if($show_id === null) {
  http_response_code(400);
  echo "You must specify a show id";
  die();
}
$show_item_stmt = $conn->prepare("SELECT title FROM show_items WHERE id = :id");
$show_item_stmt->bindParam(":id", $show_id);
$show_item_stmt->execute();
$show_title = $show_item_stmt->fetchObject()->title;
if(!$new) {
  $show_recordings_stmt = $conn->prepare("SELECT id, title, description, file, datetime, disabled FROM show_recordings WHERE show_item_id = :id LIMIT :page_size OFFSET :offset");
  $show_recordings_stmt->bindParam(":id", $show_id);
  $show_recordings_stmt->bindParam(":page_size", $page_size);
  $show_recordings_stmt->bindParam(":offset", $page_offset);
  $show_recordings_stmt->execute();

  $show_recordings_total_pages_stmt = $conn->prepare("SELECT count(*) FROM show_recordings WHERE show_item_id = :id");
  $show_recordings_total_pages_stmt->bindParam(":id", $show_id);
  $show_recordings_total_pages_stmt->execute();
  $show_recordings_total = $show_recordings_total_pages_stmt->fetchAll(PDO::FETCH_NUM)[0][0];
  $total_pages = intdiv($show_recordings_total, $page_size) + (($show_recordings_total % $page_size == 0) ? 0 : 1);
}

function page_url(int $page)
{
  global $show_id, $page_size;
  return "?id={$show_id}&page_size={$page_size}&page={$page}";
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
  <title>Recordings of <?= $show_title ?></title>
</head>

<body>
  <?php if(!$new): ?>
  <form action="" method="get">
    <input type="number" name="page_size" placeholder="Page size">
    <input type="hidden" name="id" value="<?= $show_id ?>">
    <input type="submit" value="Set">
  </form>
  <br>
  <?php endif; do { if(!$recording && !$new) continue ?>
  <fieldset>
  <?= !$recording ? "<legend>New recording</legend>" : "" ?>
    <form action="" method="post" enctype="multipart/form-data">
      <table>
        <input type="hidden" name="id" value="<?= $recording->id ?>">
        <input type="hidden" name="show_id" value="<?= $show_id ?>">
        <tr>
          <td>Title (optional):</td>
          <td><input type="text" name="title" value="<?= $recording->title ?>" title="If not provided, publish date will be used."></td>
        </tr>
        <tr>
          <td>Description (optional):</td>
          <td><input type="text" name="description" value="<?= $recording->description ?>"></td>
        </tr>
        <tr>
          <td><?= empty($recording->file) ? "File:" : "Change file (optional):" ?></td>
          <td><input type="file" name="file" accept="<?= $allowed_audio_types ?>" <?= empty($recording->file) ? "required" : "" ?>></td>
        </tr>
        <?php if(!empty($recording->file)): ?>
        <tr>
          <td>Current file:</td>
          <td><audio src="../assets/<?= $recording->file ?>" preload="auto" controls></audio></td>
        </tr>
        <?php endif ?>
        <tr>
          <td>Publish date:</td>
          <td><input type="datetime-local" name="datetime" value="<?= $recording->datetime ?>" title="If in future, the recording won't be accessible to users until its publish date." required></td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type="checkbox" name="disabled" id="disabled" <?= $recording->disabled ? "checked" : "" ?>>
            <label for="disabled" title="If disabled, the recording won't be accessible by a user until enabled.">Disabled</label>
          </td>
        </tr>
      </table>
      <input type="submit" value="Save">
    </form>
    <form action="" method="post">
      <input type="hidden" name="id" value="<?= $recording->id ?>">
      <input type="hidden" name="delete" value="1">
      <input type="submit" value="Delete">
    </form>
  </fieldset>
  <br>
  <?php } while($recording = $new ? 0 : $show_recordings_stmt->fetchObject()); ?>

  <?php
  // Pagination
      if ($total_pages > 1) {
        if ($page <= 3) {
          for ($i = ($page - 1 <= 2 ? 1 : $page - 1); $i <= 3 && $i <= $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a> | " : "<a href=\"" . page_url($i) . "\">{$i}</a> | ";
          }

          if ($page == 1) {
            echo $page + 3 <= $total_pages ? "<a href=\"" . page_url($page + 3) . "\">" . $page + 3 . "</a> | " : "";
          } elseif ($page == 2) {
            echo $page + 2 <= $total_pages ? "<a href=\"" . page_url($page + 2) . "\">" . $page + 2 . "</a> | " : "";
          } elseif ($page == 3) {
            echo ($page + 1 <= $total_pages ? "<a href=\"" . page_url($page + 1) . "\">" . $page + 1 . "</a> | " : "") .
              ($page + 2 <= $total_pages ? "<a href=\"" . page_url($page + 2) . "\">" . $page + 2 . "</a> | " : "");
          }

          if (4 < $total_pages - 1) {
            echo ($page + 2 < $total_pages ? "<span>...</span>" : "") . "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a> | ";
          }
        } elseif ($page < $total_pages - 3) {
          echo "<a href=\"" . page_url(1) . "\">1</a> | " .
            ($page - 3 >= 2 ? "<span>...</span>" : "<a href=\"" . page_url(2) . "\">2</a> | ");

          for ($i = $page - 1; $i <= $page + 2 && $i <= $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a> | " : "<a href=\"" . page_url($i) . "\">{$i}</a> | ";
          }

          echo ($page + 4 < $total_pages ? "<span>...</span>" : ($page + 2 < $total_pages ? "<a href=\"" . page_url($total_pages - 1) . "\">" . $total_pages - 1 . "</a> | " : "")) . "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a> | ";
        } else {
          echo "<a href=\"" . page_url(1) . "\">1</a> | " .
            ($page - 3 >= 2 ? "<span>...</span>" : "<a href=\"" . page_url(2) . "\">2</a> | ");

          if ($page - 2 > 3) {
            if ($page == $total_pages) {
              echo "<a href=\"" . page_url($page - 4) . "\">" . $page - 4 . "</a> | " .
                "<a href=\"" . page_url($page - 3) . "\">" . $page - 3 . "</a> | " .
                "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a> | ";
            } elseif ($page == $total_pages - 1) {
              echo "<a href=\"" . page_url($page - 3) . "\">" . $page - 3 . "</a> | " .
                "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a> | ";
            } elseif ($page == $total_pages - 2 && $page > 4) {
              echo "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a> | ";
            }
          }

          for ($i = $page - 1; $i <= $page + 2 && $i < $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a> | " : "<a href=\"" . page_url($i) . "\">{$i}</a> | ";
          }

          echo ($page + 3 < $total_pages ? "<span>...</span>" : "") .
            ($i == $page ? "<a aria-current=\"page\">{$total_pages}</a> | " : "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a> | ");
        }
      }
      ?>
</body>

</html>