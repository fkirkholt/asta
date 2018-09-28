<?php

$sti = realpath($_GET['sti']);
$skjema = $_GET['skjema'];
$form = true;
if (!file_exists($sti.'/log')) {
  mkdir($sti.'/log');
}
$logg = fopen($sti."/log/feil.log", "w");

function valider_XMLReader_error($errno, $errstr, $errfile, $errline) {
  global $n;
  global $form;
  global $xml;
  global $logg;

  //$a = $xml->readOuterXML();

  fwrite($logg, $errno.$errstr.': '.$a."\n");
  $form = false;
  if ($n == 10000) {
    die;
  }
}

set_error_handler("valider_XMLReader_error");

echo getcwd()."\n";
//chdir('../schema');
$xml = new XMLReader();
$xml->open($sti.'/ead.xml');
echo $skjema."\n";
$xml->setSchema($skjema);


$n = 1;
while ($xml->next()) {
  // Leser hele filen for å kunne finne feil. next() går rundt dobbelt så
  // raskt som read().
  $n++;

}

if ($xml->isValid() && $form) {
  echo 'EAD-eksporten er gyldig';
}
else {
  echo 'EAD-eksporten er ikke gyldig';
}


?>
