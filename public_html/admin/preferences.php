<?php
include "../main.php";

if($_SERVER['REQUEST_METHOD'] === "POST") {
  $title_above_sends = $_POST["title_above_sends"];
  $disable_even_odd_week = $_POST["disable_even_odd_week"] ?? 0;

  $insert_stmt = $conn->prepare("INSERT OR REPLACE INTO preferences_string (key, value) VALUES ('title_above_sends', :value)");
  $insert_stmt->bindParam(":value", htmlspecialchars(trim($title_above_sends)));
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
        </table>
        <input type="submit" value="Save">
      </form>
    </menu>
  </fieldset>
</html>