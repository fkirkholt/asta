<?php

use URD\models\Database;

/**
 * Finner underliggende serier eller arkivdeler
 */
function find_subseries($conn, $arkivenhetid, $path = '', $series = array()) {
  $sql = "SELECT arkivenhetid, identifikator
          FROM arkivenhet
          WHERE parentarkivenhetarkivenhetid = $arkivenhetid
                AND enhetstypeenhetstypeid in (1001, 1007)";

  $rows = $conn->query($sql);

  foreach ($rows as $row) {
    $new_path = $path == '' ? $row->identifikator : $path . '/' . $row->identifikator;
    $id = $row->arkivenhetid;
    $sql = "SELECT count(*) as ant
            FROM lagringsenhet
            WHERE nrinnenarkenhetid = $id";

    $ant = $conn->query($sql)->fetchSingle();

    if ($ant) {
      $series[] = array(
        'path'=>$new_path,
        'id'=>$id
      );
    } else {
      $series = find_subseries($conn, $id, $new_path, $series);
    }
  };

  return $series;
}

$base = $_GET['base'];
$identifikator = $_GET['arkivid'];

$return = array();

$db = new Database($base);

$sql = "SELECT arkivenhetid
        FROM arkivenhet
        WHERE identifikator = '$identifikator'";

$arkivid = $db->query($sql)->fetchSingle();

if (!$arkivid) {
  $return['message'] = "Arkivet finnes ikke";
  $return['success'] = false;
} else {

  // TODO: Finner ut om lagringsenhetene er nummerert innenfor arkivet
  $sql = "SELECT count(*) as ant
    FROM lagringsenhet
    WHERE nrinnenarkenhetid = $arkivid";

  $ant = $db->query($sql)->fetchSingle();

  if ($ant) {
    $series = array();
  } else {
    $series = find_subseries($db->conn, $arkivid);
  }

  sort($series);

  $return['success'] = true;
  $return['series'] = $series;
}

echo json_encode($return);

