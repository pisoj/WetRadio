<?php include 'main.php'; ?>
<!DOCTYPE html>
<html lang="hr">

<?php
$preferences_boolean = $conn->query("SELECT key, value FROM preferences_boolean")->fetchAll(PDO::FETCH_KEY_PAIR);
$preferences_string = $conn->query("SELECT key, value FROM preferences_string")->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, user-scalable=yes" />
  <meta name="description" content="<?= $preferences_string["metadata_description"] ?? '' ?>">
  <meta name="keywords" content="<?= $preferences_string["metadata_keywords"] ?? '' ?>">
  <link rel="stylesheet" href="assets/style.css" />
  <noscript>
    <link rel="stylesheet" href="no-js.css" />
  </noscript>
  <title><?= $preferences_string["metadata_title"] ?? '' ?></title>
  <noscript><link rel="stylesheet" href="assets/no-js.css" /></noscript>
</head>

<body>
  <iframe title="Status slanja" name="message" class="message-frame" src="data:text/html;charset=utf-8,%3Chtml%3E%3Chead%3E%3Cstyle%3E%3Aroot%7Bcolor%2Dscheme%3A%20dark%3B%7D%3C%2Fstyle%3E%3C%2Fhead%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E"></iframe>
  <div id="recorder" class="window">
    <div class="recorder" data-status="init">
      <div class="main">
        <div class="player">
          <div class="info" data-position-current="0:13" data-position-end="0:47">
            <div class="play-stop" data-status="stopped">
              <button class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/></svg></button>
              <button class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M48 64C21.5 64 0 85.5 0 112L0 400c0 26.5 21.5 48 48 48l32 0c26.5 0 48-21.5 48-48l0-288c0-26.5-21.5-48-48-48L48 64zm192 0c-26.5 0-48 21.5-48 48l0 288c0 26.5 21.5 48 48 48l32 0c26.5 0 48-21.5 48-48l0-288c0-26.5-21.5-48-48-48l-32 0z"/></svg></button>
            </div>
          </div>
          <div class="controls">
            <input type="range" min="0" max="1" step="any" value="0" />
          </div>
        </div>
        <button class="icon icon-main round main-icon-button" aria-label="Snimi"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512z"/></svg></button>
      </div>
      <div class="controls">
        <button class="icon-button icon" aria-label="Odustani"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg></button>
        <button class="icon-main icon round" aria-label="Zaustavi snimanje"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM192 160l128 0c17.7 0 32 14.3 32 32l0 128c0 17.7-14.3 32-32 32l-128 0c-17.7 0-32-14.3-32-32l0-128c0-17.7 14.3-32 32-32z"/></svg></button>
        <button class="icon-button icon" aria-label="Nastavi"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM369 209c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-111 111-47-47c-9.4-9.4-24.6-9.4-33.9 0s-9.4 24.6 0 33.9l64 64c9.4 9.4 24.6 9.4 33.9 0L369 209z"/></svg></button>
      </div>
    </div>
  </div>
  <?php
    $stations_stmt = $conn->prepare("SELECT * FROM stations WHERE disabled = 0 ORDER BY priority DESC");
    $stations_stmt->execute();
    $stations = $stations_stmt->fetchAll(PDO::FETCH_OBJ);
    if (count($stations) > 0):
  ?>
  <section id="live" class="transition sun">
    <?php
    foreach ($stations as $station) {
      echo "
          <div class=\"player\">
            <div class=\"info\">
              <h4><b>{$station->title}&nbsp;</b></h4>
              <p>{$station->description}&nbsp;</p>
            </div>
            <div class=\"controls\">
              <div class=\"play-stop js-only\" data-status=\"stopped\">
                <data class=\"endpoints\" data-order=\"{$station->endpoint_order}\">{$station->endpoints}</data>
                <button class=\"icon round\" aria-label=\"Pokreni\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d=\"M0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zM188.3 147.1c-7.6 4.2-12.3 12.3-12.3 20.9l0 176c0 8.7 4.7 16.7 12.3 20.9s16.8 4.1 24.3-.5l144-88c7.1-4.4 11.5-12.1 11.5-20.5s-4.4-16.1-11.5-20.5l-144-88c-7.4-4.5-16.7-4.7-24.3-.5z\"/></svg></button>
                <button class=\"icon round\" aria-label=\"Zaustavi\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 512 512\"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d=\"M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm192-96l128 0c17.7 0 32 14.3 32 32l0 128c0 17.7-14.3 32-32 32l-128 0c-17.7 0-32-14.3-32-32l0-128c0-17.7 14.3-32 32-32z\"/></svg></button>
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
  <?php endif ?>
  <section id="form">
    <?php
    if ($preferences_string['title_above_sends'] ?? '') {
      echo "<h2>{$preferences_string['title_above_sends']}</h2>";
    }
    ?>
    <?php
    $send_types_stmt = $conn->prepare("SELECT id, title, fields, submit_text FROM send_types WHERE disabled = 0 ORDER BY priority DESC");
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
            <button type=\"button\">Snimi <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d=\"M192 0C139 0 96 43 96 96l0 160c0 53 43 96 96 96s96-43 96-96l0-160c0-53-43-96-96-96zM64 216c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40c0 89.1 66.2 162.7 152 174.4l0 33.6-48 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l72 0 72 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-48 0 0-33.6c85.8-11.7 152-85.3 152-174.4l0-40c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40c0 70.7-57.3 128-128 128s-128-57.3-128-128l0-40z\"/></svg></button>
            <button type=\"button\" class=\"icon-button icon\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 576 512\"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d=\"M384 480l48 0c11.4 0 21.9-6 27.6-15.9l112-192c5.8-9.9 5.8-22.1 .1-32.1S555.5 224 544 224l-400 0c-11.4 0-21.9 6-27.6 15.9L48 357.1 48 96c0-8.8 7.2-16 16-16l117.5 0c4.2 0 8.3 1.7 11.3 4.7l26.5 26.5c21 21 49.5 32.8 79.2 32.8L416 144c8.8 0 16 7.2 16 16l0 32 48 0 0-32c0-35.3-28.7-64-64-64L298.5 96c-17 0-33.3-6.7-45.3-18.7L226.7 50.7c-12-12-28.3-18.7-45.3-18.7L64 32C28.7 32 0 60.7 0 96L0 416c0 35.3 28.7 64 64 64l23.7 0L384 480z\"/></svg></button>
          </div>
          <p></p>
          ";
        } else {
          echo "<input type=\"{$field->type}\" name=\"{$field->id}\" placeholder=\"{$field->title}\" {$required}>";
        }
      }
      echo "
          <button type=\"submit\">" . ($send_type->submit_text ? $send_type->submit_text : "Pošalji") . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480l0-83.6c0-4 1.5-7.8 4.2-10.8L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z"/></svg>' . "</button>
        </fieldset>
      </form>
      ";
    }
    ?>
  </section>
  <section id="show" class="box">
    <?php if (!($preferences_boolean['disable_even_odd_week'] ?? true)) : ?>
    <p>Ovaj tjedan: <b>
        <?php echo (date("W", strtotime(date_default_timezone_get())) % 2 == 0) ? "Parni" : "Neparni" ?>
      </b></p>
    <?php endif ?>
    <?php
    $category_stmt = $conn->prepare("SELECT id, title FROM show_categories WHERE disabled = 0 ORDER BY priority DESC");
    $category_stmt->execute();
    $items_stmt = $conn->prepare("SELECT id, title, subtitle, image, is_replayable, category_id FROM show_items WHERE disabled = 0 ORDER BY priority DESC");
    $items_stmt->execute();
    $show_items = $items_stmt->fetchAll(PDO::FETCH_OBJ);
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
          <img src=\"assets/{$item->image}\" loading=\"lazy\" alt=\"\" />
          <div class=\"content\">
            <div>
              <h4>{$item->title}</h4>" .
          ($item->subtitle ? "<p class=\"text-muted\">{$item->subtitle}</p>" : "") .
          "</div>" .
          ($item->is_replayable ? "<a href=\"recordings.php?id={$item->id}\" class=\"icon-button icon\" aria-label=\"Snimke\"><svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 384 512\"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d=\"M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-288-128 0c-17.7 0-32-14.3-32-32L224 0 64 0zM256 0l0 128 128 0L256 0zm2 226.3c37.1 22.4 62 63.1 62 109.7s-24.9 87.3-62 109.7c-7.6 4.6-17.4 2.1-22-5.4s-2.1-17.4 5.4-22C269.4 401.5 288 370.9 288 336s-18.6-65.5-46.5-82.3c-7.6-4.6-10-14.4-5.4-22s14.4-10 22-5.4zm-91.9 30.9c6 2.5 9.9 8.3 9.9 14.8l0 128c0 6.5-3.9 12.3-9.9 14.8s-12.9 1.1-17.4-3.5L113.4 376 80 376c-8.8 0-16-7.2-16-16l0-48c0-8.8 7.2-16 16-16l33.4 0 35.3-35.3c4.6-4.6 11.5-5.9 17.4-3.5zm51 34.9c6.6-5.9 16.7-5.3 22.6 1.3C249.8 304.6 256 319.6 256 336s-6.2 31.4-16.3 42.7c-5.9 6.6-16 7.1-22.6 1.3s-7.1-16-1.3-22.6c5.1-5.7 8.1-13.1 8.1-21.3s-3.1-15.7-8.1-21.3c-5.9-6.6-5.3-16.7 1.3-22.6z\"/></svg></a>" : "") .
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