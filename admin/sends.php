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
$id = $_GET["id"];
$page_size = $_GET["page_size"] ?? 10;
$page = $_GET["page"] ?? 1;
$page_offset = ($page - 1) * $page_size;
$send_type_stmt = $conn->prepare("SELECT title, fields FROM send_types WHERE id = :id");
$send_type_stmt->bindParam(":id", $id);
$send_type_stmt->execute();
$send_type = $send_type_stmt->fetchObject();
$send_type_title = $send_type->title;
$send_type_fields = json_decode($send_type->fields);

$send_items_stmt = $conn->prepare("SELECT data, datetime FROM send_items WHERE send_type_id = :id LIMIT :page_size OFFSET :page_offset");
$send_items_stmt->bindParam(":id", $id);
$send_items_stmt->bindParam(":page_size", $page_size);
$send_items_stmt->bindParam(":page_offset", $page_offset);
$send_items_stmt->execute();

$send_items_total_pages_stmt = $conn->prepare("SELECT count(*) FROM send_items WHERE send_type_id = :id");
$send_items_total_pages_stmt->bindParam(":id", $id);
$send_items_total_pages_stmt->execute();
$send_items_total = $send_items_total_pages_stmt->fetchAll(PDO::FETCH_NUM)[0][0];
$total_pages = intdiv($send_items_total, $page_size) + (($send_items_total % $page_size == 0) ? 0 : 1);

function page_url(int $page)
{
  global $id, $page_size;
  return "?id={$id}&page_size={$page_size}&page={$page}";
}
?>

<body>
  <form action="" method="get">
    <input type="number" name="page_size" placeholder="Page size">
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="submit" value="Set">
  </form>
  <br>
  <?php
    while($send_item = $send_items_stmt->fetchObject()):
      $datetime = new DateTimeImmutable($send_item->datetime);
      $datetime_formated = $datetime->format("d.m.Y H:i:s");
      $send_item_data = json_decode($send_item->data);
    ?>
      <fieldset>
        <legend><?= $datetime_formated ?></legend>
        <table>
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
      </fieldset>
      <br>
    <?php endwhile ?>
  <?php
  // Pagination
      if ($total_pages > 1) {
        if ($page <= 3) {
          for ($i = ($page - 1 <= 2 ? 1 : $page - 1); $i <= 3 && $i <= $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a> | " : "<a href=\"" . page_url($i) . "\">{$i}</a> | ";
          }

          if ($page == 1) {
            echo $page + 3 <= $total_pages ? "<a href=\"" . page_url($page + 3) . "\">" . $page + 3 . "</a> | " : "";
          } elseif ($page == 2) {
            echo $page + 2 <= $total_pages ? "<a href=\"" . page_url($page + 2) . "\">" . $page + 2 . "</a> | " : "";
          } elseif ($page == 3) {
            echo ($page + 1 <= $total_pages ? "<a href=\"" . page_url($page + 1) . "\">" . $page + 1 . "</a> | " : "") .
              ($page + 2 <= $total_pages ? "<a href=\"" . page_url($page + 2) . "\">" . $page + 2 . "</a> | " : "");
          }

          if (4 < $total_pages - 1) {
            echo ($page + 2 < $total_pages ? "<span>...</span>" : "") . "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a> | ";
          }
        } elseif ($page < $total_pages - 3) {
          echo "<a href=\"" . page_url(1) . "\">1</a> | " .
            ($page - 3 >= 2 ? "<span>...</span>" : "<a href=\"" . page_url(2) . "\">2</a> | ");

          for ($i = $page - 1; $i <= $page + 2 && $i <= $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a> | " : "<a href=\"" . page_url($i) . "\">{$i}</a> | ";
          }

          echo ($page + 4 < $total_pages ? "<span>...</span>" : ($page + 2 < $total_pages ? "<a href=\"" . page_url($total_pages - 1) . "\">" . $total_pages - 1 . "</a> | " : "")) . "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a> | ";
        } else {
          echo "<a href=\"" . page_url(1) . "\">1</a> | " .
            ($page - 3 >= 2 ? "<span>...</span>" : "<a href=\"" . page_url(2) . "\">2</a> | ");

          if ($page - 2 > 3) {
            if ($page == $total_pages) {
              echo "<a href=\"" . page_url($page - 4) . "\">" . $page - 4 . "</a> | " .
                "<a href=\"" . page_url($page - 3) . "\">" . $page - 3 . "</a> | " .
                "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a> | ";
            } elseif ($page == $total_pages - 1) {
              echo "<a href=\"" . page_url($page - 3) . "\">" . $page - 3 . "</a> | " .
                "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a> | ";
            } elseif ($page == $total_pages - 2 && $page > 4) {
              echo "<a href=\"" . page_url($page - 2) . "\">" . $page - 2 . "</a> | ";
            }
          }

          for ($i = $page - 1; $i <= $page + 2 && $i < $total_pages; $i++) {
            echo $i == $page ? "<a aria-current=\"page\">{$i}</a> | " : "<a href=\"" . page_url($i) . "\">{$i}</a> | ";
          }

          echo ($page + 3 < $total_pages ? "<span>...</span>" : "") .
            ($i == $page ? "<a aria-current=\"page\">{$total_pages}</a> | " : "<a href=\"" . page_url($total_pages) . "\">{$total_pages}</a> | ");
        }
      }
      ?>
</body>

</html>