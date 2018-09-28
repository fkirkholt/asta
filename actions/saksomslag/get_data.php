<?php

use URD\models\Database;

$base = $_GET['base'];
$sti = $_GET['sti'];

$db = new Database($base);

// todo: feil
$sql = "SELECT a.navn, u.urn
        FROM arkivenhet a
             LEFT JOIN arkivenheturn u
               ON u.arkivenhet_arkivenhetid = a.arkivenhetid
        WHERE a.sti LIKE '$sti%'
              AND a.enhetstypeenhetstypeid IN (1002, 1009)
        ORDER BY a.sti, a.identifikator";

$rows = $db->query($sql);

$response = array();
$response['arkivenheter'] = array();

foreach ($rows as $row) {
  $response['arkivenheter'][] = $row;
}

echo json_encode($response);



