<?php
include "../main.php";

if($_SERVER['REQUEST_METHOD'] === "POST") {
  $title_above_sends = $_POST["title_above_sends"];
  $metadata_title = $_POST["metadata_title"];
  $metadata_description = $_POST["metadata_description"];
  $metadata_keywords = $_POST["metadata_keywords"];
  $disable_even_odd_week = $_POST["disable_even_odd_week"] ?? 0;

  $insert_stmt = $conn->prepare("INSERT OR REPLACE INTO preferences_string (key, value) VALUES ('title_above_sends', :title_above_sends), ('metadata_title', :metadata_title), ('metadata_description', :metadata_description), ('metadata_keywords', :metadata_keywords)");
  $insert_stmt->bindParam(":title_above_sends", htmlspecialchars(trim($title_above_sends)));
  $insert_stmt->bindParam(":metadata_title", htmlspecialchars(trim(preg_replace('/\s+/', ' ', $metadata_title))));
  $insert_stmt->bindParam(":metadata_description", htmlspecialchars(trim(preg_replace('/\s+/', ' ', $metadata_description))));
  $insert_stmt->bindParam(":metadata_keywords", htmlspecialchars(trim(preg_replace('/\s+/', ' ', $metadata_keywords))));
  $insert_stmt->execute();

  $insert_stmt = $conn->prepare("INSERT OR REPLACE INTO preferences_boolean (key, value) VALUES ('disable_even_odd_week', :value)");
  $insert_stmt->bindParam(":value", $disable_even_odd_week);
  $insert_stmt->execute();

  header("Location: index.php", true, 303);
  die();
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
  <title>WetRadio</title>
</head>

<?php
$preferences_boolean = $conn->query("SELECT key, value FROM preferences_boolean")->fetchAll(PDO::FETCH_KEY_PAIR);
$preferences_string = $conn->query("SELECT key, value FROM preferences_string")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<body>
  <fieldset>
    <legend>Preferences</legend>
    <menu>
      <form action="" method="post">
        <table>
          <tr>
            <td>Title above sends (optional):</td>
            <td><input type="text" name="title_above_sends" value="<?= $preferences_string['title_above_sends'] ?? '' ?>"></td>
          </tr>
          <tr>
            <td></td>
            <td>
              <input type="checkbox" name="disable_even_odd_week" id="disable_even_odd_week" <?= ($preferences_boolean['disable_even_odd_week'] ?? true) ? "checked" : "" ?>>
              <label for="disable_even_odd_week">Disable even/odd week</label>
            </td>
          </tr>
          <tr>
            <td>Website title:</td>
            <td><input type="text" name="metadata_title" value="<?= $preferences_string['metadata_title'] ?? '' ?>"></td>
          </tr>
          <tr>
            <td>Keywords (comma separated):</td>
            <td><input type="text" name="metadata_keywords" value="<?= $preferences_string['metadata_keywords'] ?? '' ?>"></td>
          </tr>
          <tr>
            <td>Description for search engines:</td>
            <td><textarea type="text" name="metadata_description"><?= $preferences_string['metadata_description'] ?? '' ?></textarea></td>
          </tr>
        </table>
        <input type="submit" value="Save">
      </form>
    </menu>
  </fieldset>
</html>