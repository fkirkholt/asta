<?php

use URD\models\Database;

function formate_date($date) {
  $year = substr($date, 0, 4);
  $month = substr($date, 4, 2);
  $day = substr($date, 6, 2);
  $formate_date = $day.'.'.$month.'.'.$year;
  return $formate_date;
}

$base = $_GET['base'];
$arkivid = $_GET['arkivid'];
$serieid = $_GET['serieid'];

$db = new Database($base);

// Finner depotinstitusjon og arkivenhetsid til arkivet
$sql = "SELECT depinst.navn AS depinst, ae.depinstid, ae.arkivenhetid, ae.navn
        FROM   arkivenhet ae
               LEFT JOIN depotinstitusjon depinst
                      ON depinst.depinstid = ae.depinstid
        WHERE  identifikator = '$arkivid'";

$row = $db->query($sql)->fetch();

$depinst = $row->depinst;
$arkivnavn = $row->navn;
$arkiv = $row;


// Finner overliggende serier
$serier = array();
function hent_overserie($db, $id) {
  global $serier;
  $sql = "SELECT identifikator, navn, parentarkivenhetarkivenhetid AS parent
          FROM arkivenhet
          WHERE arkivenhetid = $id AND enhetstypeenhetstypeid = 1001";

  $row = $db->query($sql)->fetch();

  if ($row) {
    $serier[] = $row;
    hent_overserie($db, $row->parent);
  }
}


if ($serieid) {
  // Finner arkivenhetsid til serien
  $sql = "SELECT identifikator, sti, navn,
    parentarkivenhetarkivenhetid AS parent
    FROM   arkivenhet
    WHERE  arkivenhetid = $serieid";

  $row = $db->query($sql)->fetch();
  $serier[] = $row;
  hent_overserie($db, $row->parent);
  $nrinnenarkenhet = $serieid;
} else {
  $nrinnenarkenhet = $arkiv['arkivenhetid'];
}

$sql = "SELECT l.lagringsenhetid as id, l.identifikasjon,
               a.navn as arkivenhetnavn, depinst.navn as depinst,
               a.startdato, a.sluttdato, u.urn
        FROM   lagringsenhet l
               LEFT JOIN arkivenhet a
                 ON a.lagringsenhetlagringsenhetid = l.lagringsenhetid
                    -- AND a.identifikator = l.identifikasjon
               LEFT JOIN depotinstitusjon depinst
                 ON depinst.depinstid = a.depinstid
               LEFT JOIN arkivenheturn u
                 ON u.arkivenhet_arkivenhetid = a.arkivenhetid
        WHERE  l.nrinnenarkenhetid = $nrinnenarkenhet
        ORDER BY l.navn";

$rows = $db->query($sql);
$lagringsenheter = array();
foreach ($rows as $row) {
  $row->arkivnavn = $arkivnavn;
  $id = $row->id;
  if (!isset($lagringsenheter[$id])) {
    $lagringsenheter[$id] = array('identifikasjon' => $row->identifikasjon);
    $lagringsenheter[$id]['arkivenheter'] = array();

  }
  $lagringsenheter[$id]['arkivenheter'][] = array(
    'navn' => $row->arkivenhetnavn,
    'depinst' => $row->depinst,
    'startdato' => $row->startdato,
    'sluttdato' => $row->sluttdato,
    'urn' => $row->urn,
  );
}

$response = array();
$response['arkiv'] = $arkiv;
$response['serier'] = array_reverse($serier);
$response['lagringsenheter'] = $lagringsenheter;

echo json_encode($response);
