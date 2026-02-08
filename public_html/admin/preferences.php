<?php
include "../main.php";

if($_SERVER['REQUEST_METHOD'] === "POST") {
  $header_message = $_POST["header_message"];
  $header_variant = $_POST["header_variant"];
  $title_above_sends = $_POST["title_above_sends"];
  $metadata_title = $_POST["metadata_title"];
  $metadata_description = $_POST["metadata_description"];
  $metadata_keywords = $_POST["metadata_keywords"];
  $disable_even_odd_week = boolval($_POST["disable_even_odd_week"] ?? 0);
  $send_recorder_enable_agc = boolval($_POST["send_recorder_enable_agc"] ?? 0);
  $send_recorder_bits_per_second = intval($_POST["send_recorder_bits_per_second"] ?? 0);
  $send_recorder_bits_per_second = $send_recorder_bits_per_second == 0 ? "" : $send_recorder_bits_per_second;

  if (!is_numeric($send_recorder_bits_per_second) && $send_recorder_bits_per_second != "") {
    http_response_code(400);
    echo "Send recorder bits per second should wither be left empty or specify a pure numeric value";
    die();
  }

  switch ($header_variant) {
    case "":
    case "info":
    case "success":
    case "warn":
    case "error":
      break;
    default:
      http_response_code(400);
      echo "Invalid header_variant, must be one of: '', 'info', 'success', 'warn' or 'error'";
      die();
  }

  $insert_stmt = $conn->prepare("INSERT OR REPLACE INTO preferences_string (key, value) VALUES ('title_above_sends', :title_above_sends), ('metadata_title', :metadata_title), ('metadata_description', :metadata_description), ('metadata_keywords', :metadata_keywords), ('header_message', :header_message), ('header_variant', :header_variant), ('send_recorder_bits_per_second', :send_recorder_bits_per_second)");
  $insert_stmt->bindParam(":title_above_sends", htmlspecialchars(trim($title_above_sends)));
  $insert_stmt->bindParam(":metadata_title", htmlspecialchars(trim(preg_replace('/\s+/', ' ', $metadata_title))));
  $insert_stmt->bindParam(":metadata_description", htmlspecialchars(trim(preg_replace('/\s+/', ' ', $metadata_description))));
  $insert_stmt->bindParam(":metadata_keywords", htmlspecialchars(trim(preg_replace('/\s+/', ' ', $metadata_keywords))));
  $insert_stmt->bindParam(":header_message", htmlspecialchars(trim($header_message)));
  $insert_stmt->bindParam(":header_variant", htmlspecialchars(trim($header_variant)));
  $insert_stmt->bindParam(":send_recorder_bits_per_second", $send_recorder_bits_per_second);
  $insert_stmt->execute();

  $insert_stmt = $conn->prepare("INSERT OR REPLACE INTO preferences_boolean (key, value) VALUES ('disable_even_odd_week', :disable_even_odd_week), ('send_recorder_enable_agc', :send_recorder_enable_agc)");
  $insert_stmt->bindParam(":disable_even_odd_week", $disable_even_odd_week);
  $insert_stmt->bindParam(":send_recorder_enable_agc", $send_recorder_enable_agc);
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
            <td>Header message (empty to disable):</td>
            <td><input type="text" name="header_message" value="<?= $preferences_string['header_message'] ?? '' ?>"></td>
          </tr>
          <tr>
            <td>Header variant:</td>
            <td>
              <select name="header_variant">
                <option value="">Default</option>
                <option value="info" <?= $preferences_string['header_variant'] == 'info' ? 'selected="selected"' : '' ?>>Info</option>
                <option value="success" <?= $preferences_string['header_variant'] == 'success' ? 'selected="selected"' : '' ?>>Success</option>
                <option value="warn" <?= $preferences_string['header_variant'] == 'warn' ? 'selected="selected"' : '' ?>>Warning</option>
                <option value="error" <?= $preferences_string['header_variant'] == 'error' ? 'selected="selected"' : '' ?>>Error</option>
              </select>
            </td>
          </tr>
          <tr>
            <td>Title above sends (empty to disable):</td>
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
          <tr>
            <td></td>
            <td>
              <input type="checkbox" name="send_recorder_enable_agc" id="send_recorder_enable_agc" <?= ($preferences_boolean['send_recorder_enable_agc'] ?? false) ? "checked" : "" ?>>
              <label for="send_recorder_enable_agc" title="Enables automatic gain control when users record themselves using the built-in recorder. Can improve legibility at the cost of reduced dynamic range.">Enable AGC when recording a send</label>
            </td>
          </tr>
          <tr>
            <td>Send recorder bits per second<br>(empty for browser default):</td>
            <td><input title="Browsers usually default to: 128000" placeholder="128000" type="number" name="send_recorder_bits_per_second" value="<?= $preferences_string['send_recorder_bits_per_second'] ?? '' ?>"></td>
          </tr>
        </table>
        <input type="submit" value="Save">
      </form>
    </menu>
  </fieldset>
</html>