<?php
include "../main.php";
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
$stations = $conn->query("SELECT id, title FROM stations")->fetchAll(PDO::FETCH_OBJ);
$show_categories = $conn->query("SELECT id, title FROM show_categories")->fetchAll(PDO::FETCH_OBJ);
for($i = 0; $i < count($show_categories); $i++) {
  $shows_stmt = $conn->prepare("SELECT id, title FROM show_items WHERE category_id = :category_id");
  $shows_stmt->bindParam(":category_id", $show_categories[$i]->id);
  $shows_stmt->execute();
  $show_categories[$i]->shows = $shows_stmt->fetchAll(PDO::FETCH_OBJ);
}
$send_types = $conn->query("SELECT id, title FROM send_types")->fetchAll(PDO::FETCH_OBJ);
?>

<body>
  <fieldset>
    <legend>Stations</legend>
    <menu>
      <?php foreach($stations as $station): ?>
        <li><a href="station.php?id=<?= $station->id ?>"><?= $station->title ?></a></li>
      <?php endforeach ?>
      <li><a href="station.php">➕ New station</a></li>
    </menu>
  </fieldset>

  <fieldset>
    <legend>Shows</legend>
    <menu>
      <?php foreach($show_categories as $category): ?>
        <li><a href="show-category.php?id=<?= $category->id ?>"><?= $category->title ?></a></li>
        <?php foreach($category->shows as $show): ?>
          <ul>
            <li><a href="show.php?id=<?= $show->id ?>"><?= $show->title ?></a></li>
            <ul>
              <li><a href="recordings.php?id=<?= $show->id ?>">Recordings</a></li>
              <li><a href="recordings.php?id=<?= $show->id ?>&new=1">➕ New recording</a></li>
            </ul>
          </ul>
        <?php endforeach ?>
      <?php endforeach ?>
      <li><a href="show-category.php">➕ New show categorty</a></li>
      <li><a href="show.php">➕ New show</a></li>
    </menu>
  </fieldset>

  <fieldset>
    <legend>Sends</legend>
    <menu>
      <li><a href="live-sends.php">Live sends</a></li>
      <?php foreach($send_types as $send_type): ?>
        <li><a href="send.php?id=<?= $send_type->id ?>"><?= $send_type->title ?></a></li>
        <ul>
          <li><a href="sends.php?id=<?= $send_type->id ?>">View sends</a></li>
        </ul>
      <?php endforeach ?>
      <li><a href="send.php">➕ New send type</a></li>
    </menu>
  </fieldset>

  <fieldset>
    <legend>Preferences</legend>
    <menu>
      <li><a href="preferences.php">Preferences</a></li>
    </menu>
  </fieldset>
</body>

</html>