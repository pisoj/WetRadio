<?php
$nosess = 1;
include "../main.php";

if($_SERVER["REQUEST_METHOD"] === "POST") {
  include "delete.php";
  delete_send_item($_POST["id"]);
  echo "Deleted";
  die();
}

$last = $_GET["last"] ?? 10;
$last_send_id = -1;
$send_type_stmt = $conn->prepare("SELECT title, fields FROM send_types WHERE id = :id");
$send_type_stmt->bindParam(":id", $id);
$send_type_stmt->execute();
$send_type = $send_type_stmt->fetchObject();
$send_type_title = $send_type->title;
$send_type_fields = json_decode($send_type->fields);

$send_items_stmt = $conn->prepare("SELECT * FROM (SELECT i.id AS id, i.data AS data, i.datetime AS datetime, t.fields AS fields, t.title AS title FROM send_items AS i INNER JOIN send_types AS t ON t.id = i.send_type_id ORDER BY i.id DESC LIMIT :last) r ORDER BY r.id ASC");
$send_items_stmt->bindParam(":last", $last);
$send_items_stmt->execute();
$count = 0;
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
  <title>Live sends</title>
</head>

<body>
  <form action="" method="get">
    <input type="number" name="last" placeholder="Last" title="How many sends do you want to fetch before new ones.">
    <input type="submit" value="Set">
  </form>
  <br>
  <?php while(true): ?>
    <?php

    while($send_item = $send_items_stmt->fetchObject()):
      $send_item_data = json_decode($send_item->data);
      $send_type_fields = json_decode($send_item->fields);
      if($send_item->id > $last_send_id) {
        $last_send_id = $send_item->id;
      }
    ?>
      <fieldset>
        <legend><?= $send_item->title ?></legend>
        <table>
          <tr>
            <td>Send time:</td>
            <td><input type="datetime-local" value="<?= $send_item->datetime ?>" disabled></td>
          </tr>
          <?php for($i = 0; $i < count($send_type_fields); $i++): ?>
            <tr>
              <td><?= $send_type_fields[$i]->title ?></td>
              <td>
                <?php if($send_type_fields[$i]->type !== "textarea" && $send_type_fields[$i]->type !== "record"): ?>
                  <input type="<?= $send_type_fields[$i]->type ?>" value="<?= $send_item_data[$i]->value ?>" disabled>
                <?php endif ?>
                <?php if($send_type_fields[$i]->type === "textarea"): ?>
                  <textarea rows="2" disabled><?= $send_item_data[$i]->value ?></textarea>
                <?php endif ?>
                <?php if($send_type_fields[$i]->type === "record"): ?>
                  <audio src="../assets/sends/<?= $send_item_data[$i]->value ?>" preload="none" controls></audio>
                <?php endif ?>
              </td>
            </tr>
          <?php endfor ?>
        </table>
        <form action="" method="post" target="_blank">
          <input type="hidden" name="id" value="<?= $send_item->id ?>">
          <input type="submit" value="Delete">
        </form>
      </fieldset>
      <br>
    <?php endwhile ?>
  <?php
  usleep(2_000);
  if($count >= 1_000) {
    $count = 0;
    $send_items_stmt = $conn->prepare("SELECT i.id AS id, i.data AS data, i.datetime AS datetime, t.fields AS fields, t.title AS title FROM send_items AS i INNER JOIN send_types AS t ON t.id = i.send_type_id WHERE i.id > :last_id ORDER BY i.id ASC");
    $send_items_stmt->bindParam(":last_id", $last_send_id);
    $send_items_stmt->execute();
  }
  $count++;
  endwhile;
  ?>
</body>

</html>