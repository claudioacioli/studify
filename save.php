<?php

require 'config.php';


  if ($_SERVER['REQUEST_METHOD'] === 'POST')
  {
    $track = $_FILES['file'] ?? null;
    $id = $_POST['id'] ?? '';
    $text = $_POST['text'] ?? '';


    if (!empty($track) && !empty($id) && !empty($text)) {

      $directory = realpath(SOUND_PATH);
      $tmpname = $_FILES["file"]["tmp_name"];
      $filename = basename($_FILES['file']['name']);
      $filepath = "$directory/$filename.webm";
      move_uploaded_file($tmpname, $filepath);

      $yaml = get_yaml($id);
      add_sound ($yaml, $text, $filename);
      put_yaml($id, $yaml);

    }
  }



