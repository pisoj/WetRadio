<?php
$id = $_GET["id"] ?? null;
if ($id == null) {
  http_response_code(400);
  echo "<h1>No id specified</h1>";
  die();
}
$page = (int) ($_GET["page"] ?? 1);
if ($page < 1) {
  http_response_code(400);
  echo "<h1>Invalid page number</h1>";
  die();
}

include "main.php";

$offset = $recordings_page_size * ($page - 1);
$total_recordings_stmt = $conn->prepare("SELECT count(*) FROM show_recordings WHERE show_item_id = :id AND datetime <= datetime('now', 'localtime')");
$total_recordings_stmt->bindParam(":id", $id);
$total_recordings_stmt->execute();
$total_recordings = $total_recordings_stmt->fetchAll(PDO::FETCH_NUM)[0][0];
$total_pages = intdiv($total_recordings, $recordings_page_size) + (($total_recordings % $recordings_page_size == 0) ? 0 : 1);
if ($page > $total_pages && $total_recordings > 0) {
  http_response_code(404);
  echo "
        <h1>No recordings found</h1>
        <p>You may have entered an invalid id or page number.</p>
    ";
  die();
}

$recordings_stmt = $conn->prepare("SELECT * FROM show_recordings WHERE show_item_id = :id AND disabled = 0 ORDER BY datetime DESC LIMIT :recordings_page_size OFFSET :offset");
$recordings_stmt->bindParam(":id", $id);
$recordings_stmt->bindParam(":recordings_page_size", $recordings_page_size);
$recordings_stmt->bindParam(":offset", $offset);
$recordings_stmt->execute();
$recordings = $recordings_stmt->fetchAll(PDO::FETCH_OBJ);
$recordings_count = count($recordings);

$show_title_stmt = $conn->prepare("SELECT title FROM show_items WHERE id = :id");
$show_title_stmt->bindParam(":id", $id);
$show_title_stmt->execute();
$show_title = $show_title_stmt->fetchAll(PDO::FETCH_NUM)[0][0];
if (!$show_title) {
  http_response_code(404);
  echo "
        <h1>A show with id of {$id} does not exist</h1>
        <p>You may have entered an invalid id.</p>
    ";
  die();
}

function page_url(int $page)
{
  global $id;
  return "?id={$id}&page={$page}";
}
?>

<!DOCTYPE html>
<html lang="hr">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $show_title ?></title>
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    :root {
      --color-background: var(--color-background-alternative);
    }
  </style>
</head>

<body>
  <div class="content" style="background-color: var(--color-background-default);">
    <h4><?= $show_title ?></h4>
  </div>
  <?php
  if ($total_recordings == 0) {
    echo "<div class=\"message-screen\"><svg xmlns=\"http://www.w3.org/2000/svg\" height=\"1em\" viewBox=\"0 0 512 512\"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d=\"M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120V256c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2V120c0-13.3-10.7-24-24-24s-24 10.7-24 24z\"/></svg><h1>Još nema snimki</h1></div>";
    die();
  }
  ?>
  <div class="recordings-grid">
    <?php
    for ($i = 0; $i < $recordings_count; $i++) {
      $recording = $recordings[$i];

      $datetime_formatter = new DateTimeImmutable($recording->datetime);
      $date = $datetime_formatter->format("Y-m-d");

      if ($i < $recordings_count - 1) { // If not last
        $next_recording = $recordings[$i + 1];
        $next_datetime_formatter = new DateTimeImmutable($next_recording->datetime);
        $next_date = $next_datetime_formatter->format("Y-m-d");
        $recording->show_time = $next_recording->show_time = $date === $next_date;
      }
      $display_datetime = $recording->show_time ?? false ? $datetime_formatter->format("j.n.Y. G:i") : $datetime_formatter->format("j.n.Y.");

      echo "
          <div>
            <div class=\"box\">
              <h2>" . ($recording->title ? $recording->title : $display_datetime) . "</h2>" .
        ($recording->description ? "<p class=\"text-justify respect-newline\">{$recording->description}</p>" : "") .
        ($recording->title ? "<p class=\"side-info\">{$display_datetime}" : "") .
        "</div>
            <audio src=\"assets/{$recording->file}\" preload=\"metadata\" controls></audio>
          </div>
          ";
    }
    ?>
  </div>
  <div class="pagination">
    <div>
      <?php
      if ($total_pages > 1)
        echo ($page > 1 ? "<a href=\"" . page_url($page - 1) . "\" aria-label=\"Predhodna\">" : "<a aria-current=\"page\">") . "<</a>" .
          ($page < $total_pages ? "<a href=\"" . page_url($page + 1) . "\" aria-label=\"Sljedeća\">" : "<a aria-current=\"page\">") . "></a>";
      ?>
    </div>
    <div>
      <?php
      if ($total_pages > 1) {
        if ($page <= 3) {
          for ($i = ($page - 1 <= 2 ? 1 : $page - 1); $i <= 3 && $i <= $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a>" : "<a href=\"" . page_url($i) . "\">{$i}</a>";
          }

          if ($page == 1) {
            echo $page + 3 <= $total_pages ? "<a href=\"" . page_url($page + 3) . "\">" . $page + 3 . "</a>" : "";
          } elseif ($page == 2) {
            echo $page + 2 <= $total_pages ? "<a href=\"" . page_url($page + 2) . "\">" . $page + 2 . "</a>" : "";
          } elseif ($page == 3) {
            echo ($page + 1 <= $total_pages ? "<a href=\"" . page_url($page + 1) . "\">" . $page + 1 . "</a>" : "") .
              ($page + 2 <= $total_pages ? "<a href=\"" . page_url($page + 2) . "\">" . $page + 2 . "</a>" : "");
          }

          if (4 < $total_pages - 1) {
            echo ($page + 2 < $total_pages ? "<p>...</p>" : "") . "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a>";
          }
        } elseif ($page < $total_pages - 3) {
          echo "<a href=\"" . page_url(1) . "\">1</a>" .
            ($page - 3 >= 2 ? "<p>...</p>" : "<a href=\"" . page_url(2) . "\">2</a>");

          for ($i = $page - 1; $i <= $page + 2 && $i <= $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a>" : "<a href=\"" . page_url($i) . "\">{$i}</a>";
          }

          echo ($page + 4 < $total_pages ? "<p>...</p>" : ($page + 2 < $total_pages ? "<a href=\"" . page_url($total_pages - 1) . "\">" . $total_pages - 1 . "</a>" : "")) . "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a>";
        } else {
          echo "<a href=\"" . page_url(1) . "\">1</a>" .
            ($page - 3 >= 2 ? "<p>...</p>" : "<a href=\"" . page_url(2) . "\">2</a>");

          if ($page - 2 > 3) {
            if ($page == $total_pages) {
              echo "<a href=\"" . page_url($page - 4) . "\">" . $page - 4 . "</a>" .
                "<a href=\"" . page_url($page - 3) . "\">" . $page - 3 . "</a>" .
                "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a>";
            } elseif ($page == $total_pages - 1) {
              echo "<a href=\"" . page_url($page - 3) . "\">" . $page - 3 . "</a>" .
                "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a>";
            } elseif ($page == $total_pages - 2 && $page > 4) {
              echo "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a>";
            }
          }

          for ($i = $page - 1; $i <= $page + 2 && $i < $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a>" : "<a href=\"" . page_url($i) . "\">{$i}</a>";
          }

          echo ($page + 3 < $total_pages ? "<p>...</p>" : "") .
            ($i == $page ? "<a aria-current=\"page\">{$total_pages}</a>" : "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a>");
        }
      }
      ?>
    </div>
  </div>
</body>

</html>