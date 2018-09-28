<?php

$urd = $_SERVER['DOCUMENT_ROOT'].'/urd';
include $urd.'/inc/funksjoner.inc.php';

$base = $_GET['base'];
$db = new Database($base);
$sti = $_GET['import_sti'];
$arkivenheter = new StdClass;


// ## Leser gjennom xml-filen og legger alle poster i arrayen $arkivenheter, med stien som
// nøkkel
$reader = new XMLReader();
$test = $reader->open($sti);
urd_log($test);
while ($reader->read()) {
	if ($reader->nodeType == 1 && $reader->depth == 1) {
    //urd_log($reader->name);
    $post = array();
    $reader->read();
    while ($reader->read() && $reader->depth == 2) {
      // Hvis noden er et element
      if ($reader->nodeType == 1) {
        $felt = $reader->name;
        $posisjon = strpos($felt, "_");
        $objekttype = substr($felt, 0, $posisjon);
        $felt = substr($felt, $posisjon + 1);
        //urd_log($felt);
        $reader->read();
        $verdi = $reader->value;
        //urd_log($verdi);
        if ($verdi == '') {
          $verdi = 'null';
        }
      }
      $post->$felt = $verdi;
      // $post->objekttype = $objekttype;
      //urd_log($objekttype);
    }
    //urd_log(json_encode($post));

    if ($objekttype == 'arkivenhet') {
      if ($post->enhetstype == '1000') {
        $arkiv = $post;
        $id_arkiv = $post->identifikator.'/';
        $arkivenheter->$id_arkiv = $post;
      }
      else {
        $id = $post->sti.$post->identifikator.'/';
        $arkivenheter->$id = $post;
      }
    }
  }
}

$response->log['arkivenheter'] = $arkivenheter;

// Legger arkivet inn i databasen

// todo: er det greit å legge inn med anførselstegn rundt alle verdier, også int?
foreach ($arkiv as $key=>$value) {
  $arkiv->$key = "'".mysql_real_escape_string($value)."'";
}

// Endrer felter og nøkler
$arkiv->endretdato = "STR_TO_DATE(".$arkiv->endretdato.", '%d.%m.%Y')";
$arkiv->opprettetdato = "STR_TO_DATE(".$arkiv->opprettetdato.", '%d.%m.%Y')";
// bytter ut nøkler:
$arkiv->enhetstypeenhetstypeid = $arkiv->enhetstype;
unset($arkiv->enhetstype);
$arkiv->startdato = $arkiv->fradato;
unset($arkiv->fradato);
$arkiv->sluttdato = $arkiv->tildato;
unset($arkiv->tildato);
$arkiv->maleenhetid = $arkiv->maaleenhet;
unset($arkiv->maaleenhet);
// Fjerner en nøkkel som det ikke finnes tilsvarende felt for i databasen
unset($arkiv->id_mor);

// Legger arkivet inn i databasen
$ae_felter = array_keys($arkiv);
$sql = "LOCK TABLES arkivenhet WRITE";
$res = db_query($sql, $base);
$sql= "INSERT INTO arkivenhet (".implode(', ', $ae_felter).") VALUES";
$sql.= "(".implode(", ", $arkiv).")";
urd_log($sql);
$res = $db->query($sql);

// Finner id til arkivet (satt ved autoinkrement) og legger inn i $arkivenheter
$sql = "SELECT LAST_INSERT_ID() AS id";
$res = $db->query($sql);
$last_insert = $db->fetch_column($res);
$arkivenheter->$id_arkiv->arkivenhetid = $last_insert;

// Sorterer $arkivenheter etter nøkkel, dvs. etter sti. Dermed legges alle postene
// inn i riktig rekkefølge.
ksort($arkivenheter);

// Legger inn ekstra felter som behøves for innlegging av underliggende arkivenheter
$ae_felter[] = 'arkivenhetid';
$ae_felter[] = 'parentarkivenhetarkivenhetid';
$ae_felter[] = 'arkivkodefra';
$ae_felter[] = 'arkivkodetil';

$sql = "INSERT INTO arkivenhet (".implode(', ', $ae_felter).") VALUES ";

$i = 0;
foreach ($arkivenheter as $key=>$post) {
  $post->enhetstypeenhetstypeid = $post->enhetstype;
  unset($post->enhetstype);
  $post->startdato = $post->fradato;
  unset($post->fradato);
  $post->sluttdato = $post->tildato;
  unset($post->tildato);
  $post->maleenhetid = $post->maaleenhet;
  unset($post->maaleenhet);
  $post->arkivkodefra = $post->fraarkivkode;
  unset($post->fraarkivkode);
  $post->arkivkodetil = $post->tilarkivkode;
  unset($post->tilarkivkode);
  $values = array();

  $sti = $post->sti;
  urd_log($sti);
  $arkivenheter->$key->arkivenhetid = $last_insert;
  $post->arkivenhetid = $last_insert;
  $arkivenheter->$key->parentarkivenhetarkivenhetid = $arkivenheter->$sti->arkivenhetid;
  $post->parentarkivenhetarkivenhetid = $arkivenheter->$sti->arkivenhetid;
  //$arkivenheter->$key->arkivenhetid = $last_insert;
  //$arkivenheter->$key->parentarkivenhetarkivenhetid = $arkivenheter->$sti->arkivenhetid;
  if ($post->enhetstype != '1000') {
    foreach ($ae_felter as $felt) {
      if (isset ($post->$felt)) {
        $values->$felt = "'".mysql_real_escape_string($post->$felt)."'";
      }
      else {
        $values->$felt = 'null';
      }
    }
    $values->endretdato = "STR_TO_DATE(".$values->endretdato.", '%d.%m.%Y')";
    $values->opprettetdato = "STR_TO_DATE(".$values->opprettetdato.", '%d.%m.%Y')";
    // Fjerner en nøkkel som det ikke finnes tilsvarende felt for i databasen
    unset($values->id_mor);
    if ($i > 0) {
      $sql.= '('.implode(', ', $values).'), ';
    }
  }
  $i++;
  $last_insert++;
}
$sql = substr($sql, 0, -2); // Fjerner siste komma
$res = $db->query($sql);
$sql_unlock = "UNLOCK TABLES";
$res = $db->query($sql_unlock);
$response->log['sql'] = $sql;

header('Content-Type: application/json');
echo json_encode($response);
