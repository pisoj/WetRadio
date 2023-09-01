<?php include 'main.php'; ?>
<!DOCTYPE html>
<html lang="hr">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
  <meta name="description" content="Slušajte vrhunsku glazbu i budite dio zabavnih emisija. Na redovnom programu Žutog Radija svatko će pronaći nešto za sebe, a za one koji samo žele glazbu tu je Žuta Sreća.">
  <link rel="stylesheet" href="assets/style.css" />
  <noscript>
    <link rel="stylesheet" href="no-js.css" />
  </noscript>
  <link rel="stylesheet" href="assets/fontawesome/css/fontawesome.min.css" />
  <link rel="stylesheet" href="assets/fontawesome/css/regular.min.css" />
  <link rel="stylesheet" href="assets/fontawesome/css/solid.min.css" />
  <title>Žuti Radio</title>
  <noscript><link rel="stylesheet" href="assets/no-js.css" /></noscript>
</head>

<body>
  <iframe title="Status slanja" name="message" class="message-frame" src="data:text/html;charset=utf-8,%3Chtml%3E%3Chead%3E%3Cstyle%3E%3Aroot%7Bcolor%2Dscheme%3A%20dark%3B%7D%3C%2Fstyle%3E%3C%2Fhead%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E"></iframe>
  <?php
  $items_stmt = $conn->prepare("SELECT id, title, subtitle, image, is_replayable, category_id FROM show_items ORDER BY priority DESC");
  $items_stmt->execute();
  $show_items = $items_stmt->fetchAll(PDO::FETCH_OBJ);
  foreach ($show_items as $item) {
    if (!$item->is_replayable)
      continue;
    echo "
    <div id=\"recordings-{$item->id}\" class=\"window\">
      <div class=\"content\">
        <h4>{$item->title}</h4>" .
      ($item->is_replayable ? "<a href=\"#show-{$item->id}\" class=\"icon icon-button fa-solid fa-xmark\" aria-label=\"Snimke\"></a>" : "") .
      "</div>
      <iframe src=\"recordings.php?id={$item->id}\" loading=\"lazy\" title=\"Snimke emisije: {$item->title}\"></iframe>
    </div>
    ";
  }
  ?>
  <div id="recorder" class="window">
    <div class="recorder" data-status="init">
      <div class="main">
        <div class="player">
          <div class="info" data-position-current="0:13" data-position-end="0:47">
            <div class="play-stop" data-status="stopped">
              <button class="icon fa-solid fa-play"></button>
              <button class="icon fa-solid fa-pause"></button>
            </div>
          </div>
          <div class="controls">
            <input type="range" min="0" max="1" step="any" value="0" />
          </div>
        </div>
        <button class="icon icon-main round fa-solid fa-circle main-icon-button" aria-label="Snimi"></button>
      </div>
      <div class="controls">
        <button class="icon-button icon fa-classic fa-circle-xmark" aria-label="Odustani"></button>
        <button class="icon-main icon round fa-solid fa-circle-stop" aria-label="Zaustavi snimanje"></button>
        <button class="icon-button icon fa-classic fa-circle-check" aria-label="Nastavi"></button>
      </div>
    </div>
  </div>
  <section id="live" class="transition sun">
    <?php
    $stations_stmt = $conn->prepare("SELECT * FROM stations ORDER BY priority DESC");
    $stations_stmt->execute();
    while ($station = $stations_stmt->fetchObject()) {
      echo "
          <div class=\"player\">
            <div class=\"info\">
              <h4><b>{$station->title}&nbsp;</b></h4>
              <p>{$station->description}&nbsp;</p>
            </div>
            <div class=\"controls\">
              <div class=\"play-stop js-only\" data-status=\"stopped\">
                <data class=\"endpoints\" data-order=\"{$station->endpoint_order}\">{$station->endpoints}</data>
                <button class=\"icon round fa-solid fa-circle-play\" aria-label=\"Pokreni\"></button>
                <button class=\"icon round fa-classic fa-circle-stop\" aria-label=\"Zaustavi\"></button>
              </div>";
              $endpoints = json_decode($station->endpoints, true);
              $endpoint_names = json_decode($station->endpoint_names, true);
              if($station->endpoint_order == "random") {
                $endpoints_order = range(1, count($endpoints));
                shuffle($endpoints_order);
                array_multisort($endpoints_order, $endpoints, $endpoint_names);
              } else {
                echo "<select>";
                for ($i = 0; $i < count($endpoint_names); $i++) {
                  echo "<option value=\"{$endpoints[$i]}\">{$endpoint_names[$i]}</option>";
                }
                echo "</select>";
              }
        echo "<noscript>
                <audio controls>
                ";
              foreach($endpoints as $endpoint) {
                echo "
                  <source src=\"{$endpoint}\" />
                ";
              }
      echo      "</audio>
              </noscript>
            </div>
          </div>
        ";
    }
    ?>
    <span class="curve"></span>
  </section>
  <section id="form">
    <h2>Sudjeluj u emisijama</h2>
    <?php
    $send_types_stmt = $conn->prepare("SELECT id, title, fields, submit_text FROM send_types ORDER BY priority DESC");
    $send_types_stmt->execute();
    while ($send_type = $send_types_stmt->fetchObject()) {
      $fields = json_decode($send_type->fields, false);
      echo "
      <form action=\"send.php\" method=\"post\" target=\"message\" enctype=\"multipart/form-data\">
        <input type=\"hidden\" name=\"id\" value=\"{$send_type->id}\">
        <fieldset>
          <legend>{$send_type->title}</legend>";
      foreach ($fields as $field) {
        $required = $field->required ?? false ? "required" : "";
        if ($field->type == "textarea") {
          echo "<textarea name=\"{$field->id}\" placeholder=\"{$field->title}\" {$required}></textarea>";
        } else if ($field->type == "record") {
          $allowed_mime_types = implode(",", array_keys($audio_mime_types));
          echo "
          <div class=\"record\">
            <input type=\"file\" name=\"{$field->id}\" accept=\"{$allowed_mime_types}\" {$required} />
            <button type=\"button\">Snimi <i class=\"fa-solid fa-microphone\"></i></button>
            <button type=\"button\" class=\"icon icon-button fa-regular fa-folder-open\"></button>
          </div>
          <p></p>
          ";
        } else {
          echo "<input type=\"{$field->type}\" name=\"{$field->id}\" placeholder=\"{$field->title}\" {$required}>";
        }
      }
      echo "
          <button type=\"submit\">" . ($send_type->submit_text ? $send_type->submit_text : "Pošalji") . "</button>
        </fieldset>
      </form>
      ";
    }
    ?>
  </section>
  <section id="show" class="box">
    <p>Ovaj tjedan: <b>
        <?php echo (date("W", strtotime(date_default_timezone_get())) % 2 == 0) ? "Parni" : "Neparni" ?>
      </b></p>
    <?php
    $category_stmt = $conn->prepare("SELECT id, title FROM show_categories ORDER BY priority DESC");
    $category_stmt->execute();
    while ($category = $category_stmt->fetchObject()) {
      echo "
      <h3>{$category->title}</h3>
      <div class=\"card-grid\">
      ";
      foreach ($show_items as $item) {
        if ($item->category_id != $category->id)
          continue;
        echo "
        <div id=\"show-{$item->id}\" class=\"card\">
          <img src=\"assets/s-{$item->image}\" loading=\"lazy\" alt=\"\" />
          <div class=\"content\">
            <div>
              <h4>{$item->title}</h4>" .
          ($item->subtitle ? "<p class=\"text-muted\">{$item->subtitle}</p>" : "") .
          "</div>" .
          ($item->is_replayable ? "<a href=\"#recordings-{$item->id}\" class=\"icon icon-button fa-solid fa-file-audio\" aria-label=\"Snimke\"></a>" : "") .
          "</div>
        </div>
        ";
      }
      echo "</div>";
    }
    ?>
  </section>
  <script src="assets/icecast-metadata-player/icecast-metadata-player-1.16.5.main.min.js"></script>
  <script src="assets/BenzaAMRRecorder.min.js"></script>
  <script src="assets/app.js"></script>
</body>
</html>