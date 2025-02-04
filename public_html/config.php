<?php

$send_interval_seconds = 15;
$send_file_max_size = "2M";

$recordings_page_size = 20;

$audio_mime_types = array(
  "audio/3gpp" => ".3gp",
  "video/3gpp" => ".3gp",
  "audio/aac" => ".aac",
  "audio/amr" => ".amr",
  "audio/flac" => ".flac",
  "audio/x-flac" => ".flac",
  "audio/mp4" => ".m4a",
  "audio/m4a" => ".m4a",
  "audio/x-m4a" => ".m4a",
  "audio/mpeg" => ".mp3",
  "audio/ogg" => ".oga",
  "video/ogg" => ".ogg",
  "audio/opus" => ".opus",
  "audio/aiff" => ".aif",
  "audio/x-aiff" => ".aif",
  "audio/wave" => ".wav",
  "audio/wav" => ".wav",
  "audio/x-wav" => ".wav",
  "audio/vnd.wave" => ".wav"
);

$image_mime_types = array(
  "image/png" => ".png",
  "image/webp" => ".webp",
  "image/jpeg" => ".jpg",
  "image/avif" => ".avif"
);

$db_name = "db";
?>
<?php ?>