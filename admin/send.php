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
if($_SERVER['REQUEST_METHOD'] === "POST") {
  $id = $_POST["id"] ?? null;
  $number_of_fields = $_POST["fields"] ?? 0;
  $title = $_POST["title"];
  $priority = $_POST["priority"];
  $submit_text = $_POST["submit_text"] ?? null;
  $success_message = $_POST["success_message"] ?? null;
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

  if($id !== null) {
    $update_stmt = $conn->prepare("UPDATE send_types SET title = :title, priority = :priority, submit_text = :submit_text, success_message = :success_message, disabled = :disabled WHERE id = :id");
    $update_stmt->bindParam(":id", htmlspecialchars($id));
    $update_stmt->bindParam(":title", htmlspecialchars($title));
    $update_stmt->bindParam(":priority", htmlspecialchars($priority));
    $update_stmt->bindParam(":submit_text", htmlspecialchars($submit_text));
    $update_stmt->bindParam(":success_message", htmlspecialchars($success_message));
    $update_stmt->bindParam(":disabled", $disabled);
    $update_stmt->execute();
    die();
  }

  $fields = [];
  for($i = 1; $i <= $number_of_fields; $i++) {
    array_push($fields, [
      "id" => "{$i}",
      "title" => htmlspecialchars($_POST["field{$i}_title"]),
      "type" => htmlspecialchars($_POST["field{$i}_type"]),
      "required" => boolval($_POST["field{$i}_required"])
    ]);
  }

  $insert_stmt = $conn->prepare("INSERT INTO send_types (title, fields, priority, submit_text, success_message, disabled) VALUES (:title, :fields, :priority, :submit_text, :success_message, :disabled)");
  $insert_stmt->bindParam(":title", htmlspecialchars($title));
  $insert_stmt->bindParam(":fields", json_encode($fields));
  $insert_stmt->bindParam(":priority", htmlspecialchars($priority));
  $insert_stmt->bindParam(":submit_text", htmlspecialchars($submit_text));
  $insert_stmt->bindParam(":success_message", htmlspecialchars($success_message));
  $insert_stmt->bindParam(":disabled", $disabled);
  $insert_stmt->execute();

  http_response_code(201);
  die();
}

$id = $_GET["id"] ?? null;
$number_of_fields = $_GET["fields"] ?? 0;
$title = $_GET["title"] ?? "";
$priority = $_GET["priority"] ?? "";
$submit_text = $_GET["submit_text"] ?? "";
$success_message = $_GET["success_message"] ?? "";
$disabled = $_GET["disabled"] ?? 0;

if($id !== null) {
  $send_type_stmt = $conn->prepare("SELECT title, priority, submit_text, success_message, disabled FROM send_types WHERE id = :id");
  $send_type_stmt->bindParam(":id", $id);
  $send_type_stmt->execute();
  
  $send_type = $send_type_stmt->fetchObject();
  $number_of_fields = -1;
  $title = $send_type->title;
  $priority = $send_type->priority;
  $submit_text = $send_type->submit_text;
  $success_message = $send_type->success_message;
  $disabled = $send_type->disabled;
}
?>

<body>
  <fieldset>
    <legend><?= $id === null ? "New send" : "Edit send" ?></legend>
    <form action="" method="<?= $number_of_fields === 0 ? "get" : "post"?>">
      <table>
        <tr>
          <td>Title:</td>
          <td><input type="text" name="title" value="<?= $title ?>" required></td>
        </tr>
        <tr>
          <td>Priority:</td>
          <td><input type="number" name="priority" value="<?= $priority ?>" title="Sends position relative to other sends." required></td>
        </tr>
        <tr>
          <td>Custom submit text:</td>
          <td><input type="text" name="submit_text" value="<?= $submit_text ?>" title="Custom text of the submit button of this send. If none provided default will be used."></td>
        </tr>
        <tr>
          <td>Custom success message:</td>
          <td><input type="text" name="success_message" value="<?= $success_message ?>" title="Custom text of the success message for this send. If none provided default will be used."></td>
        </tr>
        <tr>
          <td></td>
          <td>
            <input type="checkbox" name="disabled" id="disabled" <?= $disabled ? "checked" : "" ?>>
            <label for="disabled" title="If a send is disabled its data is kept, but it's not accessible by users.">Disabled</label>
          </td>
        </tr>
        <?php
        if($number_of_fields == 0) {
          echo "
          <tr>
            <td>Number of fields:</td>
            <td><input type=\"number\" name=\"fields\" title=\"Number of fields this send will have.\" required></td>
          </tr>";
        } else if ($id === null) {
          echo "<input type=\"hidden\" name=\"fields\" value=\"{$number_of_fields}\" required>";
        }
        ?>
      </table>
      <?php if($id !== null): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <p>You cannot edit fields after you've created a send, however, you can disable this send to prevent users from using it, but keep its data. Then you can create a new send with a different fieldset.</p>
      <?php endif ?>
      <?php for($i = 1; $i <= $number_of_fields; $i++): ?>
      <fieldset>
        <legend>Field <?= $i ?></legend>
        <table>
          <tr>
            <td>Title:</td>
            <td><input type="text" name="field<?= $i ?>_title" required></td>
          </tr>
          <tr>
            <td>Type:</td>
            <td>
              <select name="field<?= $i ?>_type" title="What kind of data will the user be entering in this field. Redord -> The user will be present witn an audio recorder to record whatever they want and send it to you.">
                <option value="text">Text</option>
                <option value="number">Number</option>
                <option value="textarea">Long text</option>
                <option value="record">Record</option>
              </select>
            </td>
          </tr>
        </table>
        <input type="checkbox" name="field<?= $i ?>_required" id="field<?= $i ?>_required">
        <label for="field<?= $i ?>_required" title="Is the field required for the user to submit the send.">Required</label>
      </fieldset>
      <br>
      <?php endfor ?>
      <input type="submit" value="<?= $number_of_fields === 0 ? "Next" : "Save" ?>">
    </form>
  </fieldset>
</body>

</html>