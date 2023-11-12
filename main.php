<?php
include "config.php";

if(!$nosess) {
  session_start();
  if (!($_SESSION["last_sent"] ?? false)) {
    update_last_sent();
  }
}
function update_last_sent()
{
  $now_date_time = new DateTimeImmutable("now");
  $now_date_time_formatted = $now_date_time->format("c");
  $_SESSION["last_sent"] = $now_date_time_formatted;
}

function cfg_to_num($string)
{
  sscanf($string, "%u%c", $number, $suffix);
  if (isset($suffix)) {
    $number = $number * pow(1000, strpos(" KMGT", strtoupper($suffix)));
  }
  return $number;
}
function num_to_cfg($num)
{
  $units = ['', 'K', 'M', 'G', 'T'];
  for ($i = 0; $num >= 1000; $i++) {
    $num /= 1000;
  }
  return round($num, 1) . $units[$i];
}

$conn = (new Connection($db_name))->connect();
if ($conn == null) {
  http_response_code(500);
  echo "<h1>Could not connect to the database</h1>";
  die();
}
class Connection
{
  private $conn;
  private $db_name;
  public function __construct($db_name)
  {
    $this->db_name = $db_name;
  }
  public function connect()
  {
    if ($this->conn != null)
      return $this->conn;

    try {
      $this->conn = new \PDO("sqlite:" . __DIR__ . "/" . $this->db_name);
      $this->conn->query("PRAGMA foreign_keys = ON;");
    } catch (\PDOException $e) {
      $this->conn = null;
    }
    return $this->conn;
  }
}

if (cfg_to_num($send_file_max_size) > cfg_to_num(ini_get("upload_max_filesize"))) {
  $send_file_max_size = ini_get("upload_max_filesize");
}
