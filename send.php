<?php
function message(string $title, ?string $message, string $type)
{
  // If response code is Ok make title not blank so that Javascript can clear form.
  $head_title = http_response_code() >= 200 && http_response_code() < 400 ? "Ok" : "";

  return "
  <!DOCTYPE html>
  <html lang=\"hr\">
    <head>
      <meta charset=\"UTF-8\" />
      <meta name=\"robots\" content=\"noindex, nofollow\">
      <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />
      <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
      <link rel=\"stylesheet\" href=\"assets/style.css\" />
      <title>{$head_title}</title>
    </head>
    <body class=\"message-body\">
      <div class=\"message {$type}\">
        <h4>{$title}</h4>" .
    ($message !== null ? "<p>{$message}</p>" : "") .
    "</div>
    </body>
  </html>
  ";
}

if ($_SERVER['REQUEST_METHOD'] != "POST") {
  http_response_code(405);
  echo message("Method Not Allowed", "Only post requests are allowed here.", "error");
  die();
}

$id = $_POST["id"] ?? null;
if ($id == null) {
  http_response_code(400);
  echo message("No id specified", null, "error");
  die();
}

include "main.php";

$now_date_time = new DateTimeImmutable("now");
$last_sent_date_time = new DateTimeImmutable($_SESSION["last_sent"]);
$last_sent_seconds_passed = $now_date_time->getTimestamp() - $last_sent_date_time->getTimestamp();
if ($last_sent_seconds_passed < $send_interval_seconds) {
  $last_sent_seconds_to_wait = $send_interval_seconds - $last_sent_seconds_passed;
  http_response_code(400);
  echo message("Još malo", "Pričekajte još {$last_sent_seconds_to_wait} sekundi prije ponovnog slanja.", "warn");
  die();
}

$send_type_stmt = $conn->prepare("SELECT fields, success_message FROM send_types WHERE id = :id");
$send_type_stmt->bindParam(":id", $id);
$send_type_stmt->execute();
$send_type = $send_type_stmt->fetchAll(PDO::FETCH_OBJ);
if (count($send_type) < 1) {
  http_response_code(400);
  echo message("Invalid id", null, "error");
  die();
}
$send_type_fields = json_decode($send_type[0]->fields, false);
$send_type_success_message = $send_type[0]->success_message;

$send_data = array();
foreach ($send_type_fields as $send_type_field) {
  $send_field_data = new StdClass;
  $send_field_data->id = $send_type_field->id;

  if ($send_type_field->type === "record") {

    if (!in_array($_FILES[$send_type_field->id]["type"], array_keys($audio_mime_types))) {
      http_response_code(400);
      echo message("Bad File", "File is not an allowed audio file.", "error");
      die();
    }

    if ($_FILES[$send_type_field->id]["size"] > cfg_to_num($send_file_max_size)) {
      http_response_code(413);
      echo message("File Too Large", "File is exceeding the size limit.", "error");
      die();
    }

    $file_hash_name = hash_file("sha512", $_FILES[$send_type_field->id]["tmp_name"]) . $audio_mime_types[$_FILES[$send_type_field->id]["type"]];
    $move_status = move_uploaded_file($_FILES[$send_type_field->id]["tmp_name"], "assets/sends/" . $file_hash_name);
    if (!$move_status) {
      http_response_code(500);
      echo message("Upload error", "An error ocured during the upload of: {$send_type_field->title}.", "error");
      die();
    }

    $send_field_data->value = $file_hash_name;
    array_push($send_data, $send_field_data);
    continue;
  }

  $send_field_value = $_POST[$send_type_field->id] ?? null;
  if (($send_type_field->is_required ?? false) && $send_field_value == null) {
    http_response_code(400);
    echo message("Value missing", "No vale specified for field: {$send_type_field->title}.", "error");
    die();
  }

  $send_field_data->value = htmlspecialchars($send_field_value);
  array_push($send_data, $send_field_data);
}

$send_data_json = json_encode($send_data);
$send_type_fields_stmt = $conn->prepare("INSERT INTO send_items VALUES (:id, :data, datetime('now', 'localtime'))");
$send_type_fields_stmt->bindParam(":id", $id);
$send_type_fields_stmt->bindParam(":data", $send_data_json);
$send_type_fields_stmt->execute();

http_response_code(201);
update_last_sent();
echo message("Poslano", $send_type_success_message, "success");
