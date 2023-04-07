<?php

define('FILE_PATH', './store/yaml');
define('SOUND_PATH', './store/audio');

function playlist ($content) {
  echo "<div class='accordion'>";
  $count = 0;
  foreach ($content as $key => $value) {
    echo "<div class='accordion-item border-white'>";
    echo "<button class='accordion-button collapsed bg-white border-white text-dark' style='box-shadow:none!important;' data-bs-toggle=\"collapse\" data-bs-target=\"#ol-${count}\">";
    echo  $key;
    echo '<span class="badge ms-2 bg-dark rounded-pill">play</span>';
    echo "</button>";
    echo "<div id='ol-${count}' class=\"collapse\">";
    echo "<ol class='ms-3'>";
    foreach ($value as $key => $value) {
      echo "<li>";
      if (is_array($value) && count($value) && file_exists(SOUND_PATH . '/' . $value[0]) )
        echo '<a href="javascript:void(0);" class="text-success" data-data=\'', json_encode($value)  ,'\'>', $key, '</a>';
      else
        echo '<strong>', $key, '</strong>';
      echo "</li>";
    }
    echo "</ol>","</div>", "</div>";
    $count++;
  }
  echo "</div>";
}

function get_yaml ($id) {
  return  yaml_parse(file_get_contents(FILE_PATH . "/${id}.yaml"));
}

function put_yaml ($id, $yaml) {
  file_put_contents( FILE_PATH . "/${id}.yaml", yaml_emit($yaml));
}

function add_sound (&$yaml, $text, $filename) {
  foreach ($yaml as $section => $articles) {
    foreach ($articles as $article => $assets) {
      if ($article == $text) {
        $yaml[$section][$article] = [$filename . '.webm'];
        return;
      }
    }
  }
}


function get_options () {
  $dir = realpath('./store/yaml');
  $files = scandir($dir);
  $opt = [];
  for ($i = 2; $i < count($files); $i++)
    array_push($opt, "<option>" . str_replace('.yaml', '', $files[$i]) . "</option>");
  return implode('', $opt);
}
