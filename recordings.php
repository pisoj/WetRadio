<?php
$code = $_GET["code"] ?? null;
if ($code == null) {
  http_response_code(400);
  echo "<h1>No code specified</h1>";
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
$total_recordings_stmt = $conn->prepare("SELECT count(code) FROM recordings WHERE code = :code");
$total_recordings_stmt->bindParam(":code", $code);
$total_recordings_stmt->execute();
$total_recordings = $total_recordings_stmt->fetchAll(PDO::FETCH_NUM)[0][0];
$total_pages = intdiv($total_recordings, $recordings_page_size) + (($total_recordings % $recordings_page_size == 0) ? 0 : 1);
if ($page > $total_pages) {
  http_response_code(404);
  echo "
        <h1>No recordings found</h1>
        <p>You may have entered invalid code or page number.</p>
    ";
  die();
}

$recordings_stmt = $conn->prepare("SELECT * FROM recordings WHERE code = :code ORDER BY datetime DESC LIMIT :recordings_page_size OFFSET :offset");
$recordings_stmt->bindParam(":code", $code);
$recordings_stmt->bindParam(":recordings_page_size", $recordings_page_size);
$recordings_stmt->bindParam(":offset", $offset);
$recordings_stmt->execute();
$recordings = $recordings_stmt->fetchAll(PDO::FETCH_OBJ);
$recordings_count = count($recordings);

$show_title_stmt = $conn->prepare("SELECT title FROM schedule_items WHERE code = :code");
$show_title_stmt->bindParam(":code", $code);
$show_title_stmt->execute();
$show_title = $show_title_stmt->fetchAll(PDO::FETCH_NUM)[0][0];

function page_url(int $page)
{
  global $code;
  return "?code={$code}&page={$page}";
}
?>

<!DOCTYPE html>
<html lang="hr">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $show_title ?> - Snimke</title>
  <link rel="stylesheet" href="assets/style.css" />
  <style>
    :root {
      --color-background: var(--color-background-alternative);
    }
  </style>
</head>

<body>
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
        ($recording->description ? "<p class=\"text-justify\">{$recording->description}</p>" : "") .
        ($recording->title ? "<p class=\"side-info\">{$display_datetime}" : "") .
        "</div>
            <audio src=\"assets/recordings/{$code}/{$recording->file}\" preload=\"none\" controls></audio>
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