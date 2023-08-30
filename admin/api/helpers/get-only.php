<?php
if($_SERVER['REQUEST_METHOD'] != "GET") {
  http_response_code(405);
  echo "Only GET requests are allowed.";
  die();
}
?>