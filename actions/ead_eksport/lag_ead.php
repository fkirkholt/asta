<?php

// todo: Må lage funksjonalitet for å registrere sti i Asta, slik at den kommer med slik den skal.

// $urd = $_SERVER['DOCUMENT_ROOT']."/urd";
// include $urd.'/inc/funksjoner.inc.php';
use URD\models\Database;

$base = $_GET['base'];
global $db;
$db = new Database($base);
$sti = realpath($_GET['sti']);
$tabell = $_GET['tabell'];
$id = $_GET['id'];


function skriv($ant_innrykk, $txt, $linjeskift=true) {
  global $fil;
  $ant_space = $ant_innrykk*2;
  $ny_tekst = str_repeat(' ', $ant_space).$txt;
  if ($linjeskift) {
    fwrite($fil, $ny_tekst."\n");
  }
  else {
    fwrite($fil, $ny_tekst);
  }
}

function dato($row) {
  $dato = new StdClass;
  $dato->satt = true;

  if ($row->startdato != null) {
    if (substr($row->startdato,-6, 6) == '-00-00') {
      $row->startdato = substr($row->startdato, 0, 4);
    }
    $startdato_formatert = implode('.', array_reverse(explode('-', $row->startdato)));
  }
  if ($row->sluttdato != null) {
    if (substr($row->sluttdato,-6, 6) == '-00-00') {
      $row->sluttdato = substr($row->sluttdato, 0, 4);
    }
    $sluttdato_formatert = implode('.', array_reverse(explode('-', $row->sluttdato)));
  }

  if ($row->startdato != null && $row->sluttdato != null) {
    $dato->normal = $row->startdato.'/'.$row->sluttdato;
    $dato->formatert = $startdato_formatert.' - '.$sluttdato_formatert;
  }
  elseif ($row->startdato != null && $row->sluttdato == null) {
    $dato->normal = $row->startdato;
    $dato->formatert = $startdato_formatert;
  }
  elseif ($row->startdato == null && $row->sluttdato != null) {
    $dato->normal = '/'.$row->sluttdato;
    $dato->formatert = '(ukjent dato) - '.$sluttdato_formatert;
  }
  elseif ($row->startdato == null && $row->sluttdato == null) {
    $dato->satt = false;
  }

  return $dato;
}

// Skriver info om den enkelte arkivenhet
//$n - innrykk
function skriv_info($n, $post) {
  $dato = dato($post);
  skriv($n, '<did>');
  // Hvis øverste arkivenhet, tas med navn på depotinstitusjonen
  // TODO: Dette er den institusjonen som har intellektuell råderett over materialet,
  // noe som kan være forskjellig fra depotinstitusjonen. TODO: Har foreløpig disablet
  // dette, da jeg ikke vet om det skal med.
  // if ($n == 2) {
  //  skriv($n+1, '<repository>'.$post->depinstnavn.'</repository>');
  // }
  skriv($n+1, '<unittitle>'.$post->navn.'</unittitle>');
  if ($post->alternativenavn) {
    skriv($n+1, '<unittitle type="alternate">'.$post->alternativenavn.'</unittitle>');
  }
  if ($dato->satt) {
    if ($post->datokvalitet) {
      $certainty = ' certainty="'.$post->certainty.'"';
    }
    else {
      $certainty = '';
    }
    skriv($n+1, '<unitdate normal="'.$dato->normal.'"'.$certainty.'>'
      .$dato->formatert.'</unitdate>');
  }
  skriv($n+1, '<unitid countrycode="NO" repositorycode="'.$post->depinstid
    .'" identifier="'.$post->sti.$post->identifikator.'">'
    .$post->sti.$post->identifikator.'</unitid>');

  if ($post->lagringsenhet != null) {
    skriv($n+1, '<container type="'.$post->container_type.'">'
      .$post->lagringsenhet.'</container>');
  }
  if ($post->omfang || in_array($post->level, ['item', 'file'])) {
    skriv($n+1, '<physdesc>');
    if ($post->omfang) {
      skriv($n+2, '<extent>'.$post->omfang.' '.$post->maleenhet.'</extent>');
    }
    // Har disablet omfangsmerknad. Neppe behov for den
    // if ($post->omfangsmerknad) {
    //   skriv($n+2, '<extent>'.$post->omfangsmerknad.'</extent>');
    // }
    if ($post->level == 'item') {
      skriv($n+2, '<genreform source="ASTA">'.$post->type.'</genreform>');
    }
    // Disablet alt som ligger i teknikkfanen. Neppe aktuelt å bruke
    /* if ($post->bunnmateriale) { */
    /*   skriv($n+2, '<physfacet type="material">'.$post->bunnmateriale.'</physfacet>'); */
    /* } */
    /* if ($post->teknikk) { */
    /*   skriv($n+2, '<physfacet type="technique">'.$post->teknikk.'</physfacet>'); */
    /* } */
    /* if ($post->fargebilde != null) { */
    /*   $fargebilde = ($post->fargebilde ? 'Ja' : 'Nei'); */
    /*   skriv($n+2, '<physfacet type="color">'.$fargebilde.'</physfacet>'); */
    /* } */
    /* if ($post->storrelse) { */
    /*   skriv($n+2, '<dimensions type="size">'.$post->storrelse.'</dimensions>'); */
    /* } */
    /* if ($post->malestokk) { */
    /*   skriv($n+2, '<dimensions type="scale">'.$post->malestokk.'</dimensions>'); */
    /* } */
    skriv($n+1, '</physdesc>');
    if ($post->merknad) {
      skriv($n+1, '<note><p>'.$post->merknad.'</p></note>');
    }
  }
  skriv($n, '</did>');
  if ($post->innhold) {
    skriv($n, '<scopecontent>');
    if ($post->innhold != null) {
      skriv($n+1, '<p>'.$post->innhold.'</p>');
    }
    // Har disablet alt som har med adresse å gjøre. Vil neppe bli brukt.
    /* if ($post->adresse || $post->omraade || $post->fylke) { */
    /*   skriv($n+1, '<address>'); */
    /*   if ($post->adresse) { */
    /*     skriv($n+2, '<addressline>'.$post->adresse.'</addressline>'); */
    /*   } */
    /*   if ($post->omraade) { */
    /*     skriv($n+2, '<addressline>'.$post->omraade.'</addressline>'); */
    /*   } */
    /*   if ($post->fylke) { */
    /*     skriv($n+2, '<addressline>'.$post->fylke.'</addressline>'); */
    /*   } */
    /*   skriv($n+1, '</address>'); */
    /* } */
    skriv($n, '</scopecontent>');
  }
  if ($post->kassasjon) {
    skriv($n, '<appraisal>');
    if ($post->kassasjon) {
      skriv($n+1, '<head>Kassasjonsvurdering</head>');
      skriv($n+1, '<p>'.$post->kassasjon.'</p>');
    }
    if ($post->bevaringstid) {
      skriv($n+1, '<head>Bevaringstid</head>');
      skriv($n+1, '<p>'.$post->bevaringstid.' år</p>');
    }
    if ($post->kassasjonsar) {
      skriv($n+1, '<head>Kassasjonsår</head>');
      skriv($n+1, '<p>'.$post->kassasjonsar.'</p>');
    }
    skriv($n, '</appraisal>');
  }
  // Disablet alt som har med fysisk tilstand å gjøre, da det neppe er aktuelt å bruke
  /* if ($post->fysisktilstand || $post->beskrivelsefysisktilstand || $post->planlagtkonservering */
  /*     || $post->teknikkmerknad) { */
  /*   skriv($n, '<phystech>'); */
  /*   if ($post->fysisktilstand) { */
  /*     skriv($n+1, '<p>Fysisk tilstand: '.$post->fysisktilstand.'</p>'); */
  /*   } */
  /*   if ($post->beskrivelsefysisktilstand) { */
  /*     skriv($n+1, '<p>Beskrivelse: '.$post->beskrivelsefysisktilstand.'</p>'); */
  /*   } */
  /*   if ($post->planlagtkonservering) { */
  /*     skriv($n+1, '<p>Planlagt konservering: '.$post->planlagtkonservering.'</p>'); */
  /*   } */
  /*   if ($post->teknikkmerknad) { */
  /*     skriv($n+1, '<note>'); */
  /*     skriv($n+2, '<p>'.$post->teknikkmerknad.'</p>'); */
  /*     skriv($n+1, '</note>'); */
  /*   } */
  /*   skriv($n, '</phystech>'); */
  /* } */
  // Har foreløpig kuttet ut opplysninger om ordning. Er vel ikke relevant for elektronisk materiale
  /* if ($post->ordningsgrad != null || $post->ordnetav != null || $post->ordnetdato != null) { */
  /*   skriv($n, '<processinfo>'); */

  /*   // todo: istedenfor slik jeg har gjort det nedenfor, gir ead-skjemaet et eksempel på at */
  /*   // informasjonen blir trukket sammen i én beskrivelse. */
  /*   if ($post->ordningsgrad) { */
  /*     skriv($n+1, '<p>Ordningsgrad: '.$post->ordningsgrad.'</p>'); */
  /*   } */
  /*   if ($post->ordnetav) { */
  /*     skriv($n+1, '<p>Ordnet av: '.$post->ordnetav.'</p>'); */
  /*   } */
  /*   if ($post->ordnetdato) { */
  /*     skriv($n+1, '<p>Ordnet dato: <date>'.$post->ordnetdato.'</date></p>'); */
  /*   } */
  /*   skriv($n, '</processinfo>'); */
  /* } */
  if ($post->merknad) {
    skriv($n, '<odd>');
    skriv($n+1, '<note>');
    skriv($n+2, '<p>'.$post->merknad.'</p>');
    skriv($n+1, '</note>');
    skriv($n, '</odd>');
  }
}

// SQL-setning som henter ut all info om arkivenheten som det skal lages EAD-fil av
function hent_sql() {
    $sql = "SELECT a.arkivenhetid, a.depinstid, di.navn AS depinstnavn,
          a.sti, a.identifikator, a.navn, a.alternativenavn,
          a.startdato, a.sluttdato, a.datokvalitet,
          CASE
            WHEN dk.betegnelse = 'ca' THEN 'approximate'
            WHEN dk.betegnelse = 'eksakt' THEN 'accurate'
            WHEN dk.betegnelse = 'innen tiår' THEN 'decade'
          END as certainty,
          a.omfang, m.betegnelse AS maleenhet, a.omfangsmerknad, a.innhold,
          e.enhetsnavn AS type,
          l.identifikasjon AS lagringsenhet,
          CASE
            WHEN le.enhetsnavn='Boks' THEN 'box'
            WHEN le.enhetsnavn='Pakke' THEN 'package'
            WHEN le.enhetsnavn='Protokoll' THEN 'book'
            WHEN le.enhetsnavn='Mikrofilmrull' THEN 'roll microfilm'
          END as container_type,
          o.betegnelse AS ordningsgrad,
          a.ordnetdato,
          ka.betegnelse AS kassasjon, a.bevaringstid, a.kassasjonsar,
          a.merknad, a.parentarkivenhetarkivenhetid AS mor
          FROM arkivenhet a
          LEFT JOIN depotinstitusjon di
          ON a.depinstid = di.depinstid
          LEFT JOIN datokvalitet dk
          ON a.datokvalitet = dk.datokvalitetid
          LEFT JOIN maaleenhet m
          ON a.maleenhetid = m.maaleenhetid
          LEFT JOIN enhetstype e
          ON a.enhetstypeenhetstypeid = e.enhetstypeid
          LEFT JOIN lagringsenhet l
          ON a.lagringsenhetlagringsenhetid = l.lagringsenhetid
          LEFT JOIN enhetstype le
          ON l.enhetstypeenhetstypeid = le.enhetstypeid
          LEFT JOIN ordningskategori o
          ON o.ordningskategoriid = a.ordningsgrad
          LEFT JOIN kassasjonskode ka
          ON ka.kasskodeid = a.kassasjonskodeid";

  return $sql;
}

function hent_mor($id_mor, $overordnede=array()) {
  global $db;
  $sql = "SELECT a.arkivenhetid, a.navn, e.enhetsnavn AS type,
          a.parentarkivenhetarkivenhetid AS parent
          FROM arkivenhet AS a
          LEFT JOIN enhetstype AS e
          ON a.enhetstypeenhetstypeid = e.enhetstypeid
          WHERE a.arkivenhetid = $id_mor";
  $mor = $db->query($sql)->fetch();
  $overordnede[] = $mor;
  if ($mor->type != 'Arkiv') {
    hent_mor($mor->parent, $overordnede);
  }
  return $overordnede;
}

global $fil;
$fil = fopen($sti.'/ead.xml', 'w');

// Henter ut data om arkivenheten
$sql = hent_sql()." WHERE a.arkivenhetid = $id";

$row = $db->query($sql)->fetch();

// Lager en array med normaldato, formatert dato og info om datoer er satt
$dato = dato($row);

// ## Skriver ead-filen
skriv(0, '<?xml version="1.0" encoding="utf-8"?>');
skriv(0, '<ead xmlns="urn:isbn:1-931666-22-9" xmlns:xlink="http://www.w3.org/1999/xlink"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="urn:isbn:1-931666-22-9 http://www.loc.gov/ead/ead.xsd">');
skriv(1, '<eadheader>');
// mainagencycode er ISO 15511-koden for institusjonen som vedlikeholder framfinningsmidlet
// TODO: Har RA en egen ISO 15511-kode?
// TODO: skal <eadid> være $id? Hvordan blir dette for serier?
// TODO: Anbefales å bruke i tillegg PUBLICID, URL el. IDENTIFIER for å gjøre <eadid> unik
skriv(2, '<eadid countrycode="NO" mainagencycode="'.$row->depinstid.'">'
      .$row->sti.$row->identifikator.'</eadid>');
// bibliografiske data om arkivbeskrivelsen
skriv(2, '<filedesc>');
// Grupperer informasjon om navnet til arkivbeskrivelsen og de ansvarlige for dens innhold.
skriv(3, '<titlestmt>');

$title = '';
if ($row->type != 'Arkiv') {
  $overordnede = hent_mor($row->mor);
  $mor = $overordnede[0];
  foreach ($overordnede as $ae) {
    $title = $ae->navn.', '.$title;
  }
}
$title .= $row->navn;
if ($dato->satt) {
  $title .= ', <date normal="'.$dato->normal.'">';
  $title .= $dato->formatert.'</date>';
}
skriv(4, '<titleproper>'.$title.'</titleproper>');
// todo: Skal det være den som sist har endret arkivet (el. serien) som settes som forfatter?
// Betyr ikke det at Anne Riise her på huset vil stå som forfatter på nesten alt?
// Elementet er ikke obligatorisk, så jeg har fjernet det foreløpig.
// skriv(4, '<author>'.$row->endretav.'</author>');
skriv(3, '</titlestmt>');
// Har hoppet over <editionstmt>, som beskriver versjonen av arkivbeskrivelsen. Ikke vanlig med
// versjonsbeskrivelse i norsk arkivpraksis.
// Har hoppet over <publicationstmt>, som beskriver publisering og distribusjon av arkivbeskrivelsen.
// Har hoppet over <notestmt>, som inneholder deskriptiv informasjon om arkivbeskrivelsen.
skriv(2, '</filedesc>');
// Hoppet over <revisiondsc>, som inneholder info om endringer gjort i arkivbeskrivelsen
skriv(1, '</eadheader>');
// Hoppet over <frontmatter>, som inneholder innledende tekst før arkivbeskrivelsen

// ### Skriver arkivenheten

if ($row->type == 'Arkiv') {
  $row->level = 'fonds';
} else if ($row->type == 'Arkivdel') {
  $row->level = 'subfonds';
} else if ($row->type == 'Serie') {
  if ($mor->type == 'Serie') {
    $row->level = 'subseries';
  } else {
    $row->level = 'series';
  }
}
skriv(1, '<archdesc level="'.$row->level.'">');

skriv_info(2, $row);
// todo: Se hvilke andre elementer fra m.desc.full som skal med
skriv(2, '<dsc type="combined">');
  // type er et obligatorisk attributt. "combined" indikerer at innholdet i en serie er beskrevet
  // rett under den serien, dvs. rent hierarki.

// ### Skriver underliggende nivåer:

skriv_underliggende($id, 3);


function skriv_underliggende($id_mor, $innrykk) {

  global $db;

  echo "\n".$id_mor.":";

  $sql = hent_sql()." WHERE a.parentarkivenhetarkivenhetid = '$id_mor'";

  $resultat = $db->query($sql);

  foreach ($resultat as $post) {

    switch ($post->type) {
      case 'Arkivdel':
        $post->level = 'fonds';
        break;
      case 'Serie':
        $post->level = 'series';
        break;
      case 'Stykke':
        $post->level = 'todo';
        break;
      case 'Mappe':
        $post->level = 'file';
        break;
      case 'Listeserie':
      case 'Dokumentserie':
      case 'Fotoserie':
      case 'Kartserie':
      case 'Tegningsserie':
        $post->level = 'todo';
        break;
      default:
        $post->level = 'item';
    }


    skriv($innrykk, '<c level="'.$post->level.'">');
    skriv_info($innrykk+1, $post);
    skriv_underliggende($post->arkivenhetid, $innrykk+1);
    skriv($innrykk, '</c>');
  }

}


skriv(2, '</dsc>');
skriv(1, '</archdesc>');
skriv(0, '</ead>', $linjeskift=false);

?>
