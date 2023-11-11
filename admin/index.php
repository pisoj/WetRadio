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
$show_category_stmt = $conn->prepare("SELECT title, priority FROM show_categories");
$show_category_stmt->execute();
?>

<body>
  <fieldset>
    <legend>Stations</legend>
    <menu>
      <li><a href="">Žuti Radio</a></li>
      <li><a href="">Žuta Sreća</a></li>
      <li><a href="">➕ New station</a></li>
    </menu>
  </fieldset>

  <fieldset>
    <legend>Shows</legend>
    <menu>
      <li><a href="">Parnim tjednima</a></li>
      <ul>
        <li><a href="">Glazbene želje</a></li>
        <li><a href="">➕ New show</a></li>
      </ul>

      <li><a href="">Neparnim tjednima</a></li>
      <ul>
        <li><a href="">Žuta minuta</a></li>
        <li><a href="">➕ New show</a></li>
      </ul>

      <li><a href="">➕ New show categorty</a></li>
    </menu>
  </fieldset>

  <fieldset>
    <legend>Sends</legend>
    <menu>
      <li><a href="">Uključite se u čutu minutu</a></li>
      <li><a href="">➕ New send type</a></li>
    </menu>
  </fieldset>
</body>

</html>