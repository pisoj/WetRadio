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
</head>

<body>
  <iframe title="Status slanja" name="message" class="message-frame" src="data:text/html;charset=utf-8,%3Chtml%3E%3Chead%3E%3Cstyle%3E%3Aroot%7Bcolor%2Dscheme%3A%20dark%3B%7D%3C%2Fstyle%3E%3C%2Fhead%3E%3Cbody%3E%3C%2Fbody%3E%3C%2Fhtml%3E"></iframe>
  <?php
  $items_stmt = $conn->prepare("SELECT code, title, subtitle, image, is_replayable, category_id FROM schedule_items ORDER BY priority DESC");
  $items_stmt->execute();
  $schedule_items = $items_stmt->fetchAll(PDO::FETCH_OBJ);
  foreach ($schedule_items as $item) {
    if (!$item->is_replayable)
      continue;
    echo "
    <div id=\"replay-{$item->code}\" class=\"window\">
      <div class=\"content\">
        <h4>{$item->title}</h4>" .
      ($item->is_replayable ? "<a href=\"#{$item->code}\" class=\"icon icon-button fa-solid fa-xmark\" aria-label=\"Snimke\"></a>" : "") .
      "</div>
      <iframe src=\"recordings.php?code={$item->code}\" loading=\"lazy\" title=\"Snimke emisije: {$item->title}\"></iframe>
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
    <script src="assets/BenzaAMRRecorder.min.js"></script>
    <script>
      Number.prototype.toDigets = function(n = 2) {
        return (
          (this.toString().length < n ?
            "0".repeat(n - this.toString().length) :
            "") + this
        );
      };
      let amr;
      let recorderPlayerTimeUpdateIntervalId;
      const recorder = document.querySelector(".recorder");
      const recorderRecord = recorder.querySelector(".main > .icon-main");
      const recorderStop = recorder.querySelector(".controls > .icon-main");
      const recorderClose = recorder.querySelector(".controls > button:first-child");
      const recorderDone = recorder.querySelector(".controls > button:last-child");
      const recorderPlayer = recorder.querySelector(".main > .player");
      const recorderPlayerInfo = recorderPlayer.querySelector(".info");
      const recorderPlayerPlayStop = recorderPlayer.querySelector(".play-stop");
      const recorderPlayerSeek = recorderPlayer.querySelector(
        'input[type="range"]'
      );
      const recorderPlayerPlay =
        recorderPlayerPlayStop.querySelector(":first-child");
      const recorderPlayerStop =
        recorderPlayerPlayStop.querySelector(":last-child");

      function formatTime(seconds) {
        return Math.floor(seconds / 60) + ":" + (seconds % 60).toDigets();
      }

      function recorderPlayerCurrentTimeUpdate() {
        recorderPlayerInfo.setAttribute(
          "data-position-current",
          formatTime(Math.ceil(amr.getCurrentPosition()))
        );
        recorderPlayerSeek.value = amr.getCurrentPosition();
      }

      function recorderPlayerReset() {
        recorderPlayerPlayStop.setAttribute("data-status", "stopped");
        recorderPlayerInfo.setAttribute("data-position-current", "0:00");
        recorderPlayerSeek.value = 0;
      }

      function setRecorderPlayerTimeUpdateInterval() {
        if(amr) {
          recorderPlayerCurrentTimeUpdate();
        }
        recorderPlayerTimeUpdateIntervalId = setInterval(() => {
          if (amr) {
            recorderPlayerCurrentTimeUpdate();
          }
          console.log("now-wow");
        }, 1000);
      }

      function recorderDestroy() {
        amr.destroy();
        amr = null;
        clearInterval(recorderPlayerTimeUpdateIntervalId);
        recorderPlayerReset();
      }

      function setupRecorderListeners() {
        amr.onPlay(() => {
          setRecorderPlayerTimeUpdateInterval();
          recorderPlayerPlayStop.setAttribute("data-status", "playing");
        });
        amr.onResume(() => {
          setRecorderPlayerTimeUpdateInterval();
          recorderPlayerPlayStop.setAttribute("data-status", "playing");
        });
        amr.onStop(() => {
          recorderPlayerPlayStop.setAttribute("data-status", "stopped");
          clearInterval(recorderPlayerTimeUpdateIntervalId);
        });
        amr.onPause(() => {
          recorderPlayerPlayStop.setAttribute("data-status", "stopped");
          clearInterval(recorderPlayerTimeUpdateIntervalId);
        });
        amr.onStartRecord(() => {
          recorder.setAttribute("data-status", "recording");
        });
        amr.onFinishRecord(() => {
          recorderPlayerReset();
          recorderPlayerSeek.setAttribute("max", amr.getDuration());
          recorderPlayerInfo.setAttribute(
            "data-position-end",
            formatTime(Math.ceil(amr.getDuration()))
          );
          recorder.setAttribute("data-status", "playable");
        });
      }

      recorderClose.onclick = () => {
        recorderWindow.removeAttribute("open");
        recorderDestroy();
        recorder.setAttribute("data-status", "init");
      };
      recorderDone.onclick = () => {
        console.log(recorderTargetFileInput);
        recorderDestroy();
      };
      recorderRecord.onclick = () => {
        if (amr && amr.isRecording()) return;
        if (amr) amr.stop();
        amr = new BenzAMRRecorder();
        setupRecorderListeners();
        amr
          .initWithRecord()
          .then(() => {
            amr.startRecord();
          })
          .catch(function(e) {
            alert(e.message || e.name || JSON.stringify(e));
          });
      };
      recorderStop.onclick = () => amr.finishRecord();
      recorderPlayerPlay.onclick = () => amr.playOrResume();
      recorderPlayerStop.onclick = () => amr.pause();
      recorderPlayerSeek.onchange = (event) => {
        amr.setPosition(event.target.value);
        recorderPlayerCurrentTimeUpdate();
      };
    </script>
  </div>
  <section id="live" class="transition sun">
    <div id="zuti" class="player">
      <div class="info">
        <h4><b>Žuti Radio</b></h4>
        <p class="artist">Veselje u svakodnevnom životu!</p>
      </div>
      <div class="controls js-only">
        <div class="play-stop" data-status="stopped">
          <button class="icon round fa-solid fa-circle-play" aria-label="Pokreni"></button>
          <button class="icon round fa-classic fa-circle-stop" aria-label="Zaustavi"></button>
        </div>
      </div>
      <!--noscript>
        TODO, html5 audio
      </noscript-->
    </div>
    <span class="curve"></span>
  </section>
  <section id="form">
    <h2>Sudjeluj u emisijama</h2>
    <?php
    $send_types_stmt = $conn->prepare("SELECT * FROM send_types ORDER BY priority DESC");
    $send_types_stmt->execute();
    while ($send_type = $send_types_stmt->fetchObject()) {
      $fields = json_decode($send_type->fields, false);
      echo "
      <form action=\"send.php\" method=\"post\" target=\"message\" enctype=\"multipart/form-data\">
        <input type=\"hidden\" name=\"code\" value=\"{$send_type->code}\">
        <fieldset>
          <legend>{$send_type->title}</legend>";
      foreach ($fields as $field) {
        $required = $field->is_required ?? false ? "required" : "";
        if ($field->type == "textarea") {
          echo "<textarea name=\"{$field->code}\" placeholder=\"{$field->title}\" {$required}></textarea>";
        } else if ($field->type == "record") {
          $allowed_mime_types = implode(",", array_keys($audio_mime_types));
          echo "
          <div class=\"record\">
            <input type=\"file\" name=\"{$field->code}\" accept=\"{$allowed_mime_types}\" {$required} />
            <button type=\"button\">Snimi <i class=\"fa-solid fa-microphone\"></i></button>
            <button type=\"button\" class=\"icon icon-button fa-regular fa-folder-open\"></button>
          </div>
          <p></p>
          ";
        } else {
          echo "<input type=\"{$field->type}\" name=\"{$field->code}\" placeholder=\"{$field->title}\" {$required}>";
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
  <section id="schedule" class="box">
    <p>Ovaj tjedan: <b>
        <?php echo (date("W", strtotime(date_default_timezone_get())) % 2 == 0) ? "Parni" : "Neparni" ?>
      </b></p>
    <?php
    $category_stmt = $conn->prepare("SELECT id, title FROM schedule_categories ORDER BY priority DESC");
    $category_stmt->execute();
    while ($category = $category_stmt->fetchObject()) {
      echo "
      <h3>{$category->title}</h3>
      <div class=\"card-grid\">
      ";
      foreach ($schedule_items as $item) {
        if ($item->category_id != $category->id)
          continue;
        echo "
        <div id=\"{$item->code}\" class=\"card\">
          <img src=\"assets/img/{$item->image}\" loading=\"lazy\" alt=\"\" />
          <div class=\"content\">
            <div>
              <h4>{$item->title}</h4>" .
          ($item->subtitle ? "<p class=\"text-muted\">{$item->subtitle}</p>" : "") .
          "</div>" .
          ($item->is_replayable ? "<a href=\"#replay-{$item->code}\" class=\"icon icon-button fa-solid fa-file-audio\" aria-label=\"Snimke\"></a>" : "") .
          "</div>
        </div>
        ";
      }
      echo "</div>";
    }
    ?>
  </section>
  <script>
    let recorderTargetFileInput;
    const recorderWindow = document.querySelector("#recorder");
    const records = document.querySelectorAll(".record");
    records.forEach((record, index) => {
      const fileInput = record.querySelector("input[type=\"file\"]");
      const recordButton = record.querySelector("button:first-of-type");
      const selectFileButton = record.querySelector("button:last-of-type");
      const fileLabel = document.querySelectorAll(".record + p")[index];

      recordButton.onclick = () => {
        recorderTargetFileInput = fileInput;
        recorderWindow.setAttribute("open", "");
      }
      selectFileButton.onclick = () => {
        fileInput.click();
      }
    });
  </script>
</body>

</html>