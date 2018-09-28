<?php

$urd = $_SERVER['DOCUMENT_ROOT'].'/URD';
$mal = $urd.'/templates/asta_5';
include $urd.'/inc/funksjoner.inc.php';
include $urd.'/inc/FirePHPCore/fb.php';

ob_start();

$base = $_GET['base'];
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

$fil = fopen($sti.'/eac.xml', 'w');

$sql = "SELECT ak.depinstid, di.navn AS depinstnavn, ah.betegnelse AS type,
        ak.identifikator, ak.navn,
        ak.startdato, ak.sluttdato, dk.betegnelse AS datokvalitet,
        ak.historikkbiografi AS historikk,
        opprettetav, UNIX_TIMESTAMP(opprettetdato) AS opprettetdato,
        ss.betegnelse AS samfunnssektor, at.betegnelse AS aktortype,
        fn.betegnelse AS forvaltningsnivaa, fo.betegnelse AS forvaltningsomraade,
        ak.yrke, kj.betegnelse AS kjoenn,
        ak.gateadresse, ak.postnr, ak.poststed, ak.telefon, ak.telefax,
        ak.epostadresse AS epost, ak.internettadresse AS internett,
        la.navn AS land, ak.gaardsbruksnrid AS bruksnr, ak.omraade,
        ak.eksternid, ak.lokalsignatur
        FROM aktor AS ak
        LEFT JOIN aktor_hovedtype AS ah
        ON ah.id = ak.hovedtype
        LEFT JOIN depotinstitusjon AS di
        ON di.depinstid = ak.depinstid
        LEFT JOIN datokvalitet AS dk
        ON dk.datokvalitetid = ak.datokvalitet
        LEFT JOIN samfunn AS ss
        ON ss.sektorid = ak.samfunnssektor
        LEFT JOIN aktortype AS at
        ON at.samfunnsektorid = ak.samfunnssektor AND at.aktortypeid = ak.aktortypeid
        LEFT JOIN forvaltning AS fn
        ON fn.samfunnsektorid = ak.samfunnssektor AND fn.nivaaid = ak.forvaltningsnivaaid
        LEFT JOIN forvaltningsomraade AS fo
        ON fo.forvaltningsomraadeid = ak.forvaltningsomraadeid
        LEFT JOIN kjoenn AS kj
        ON kj.id = ak.kjoenn
        LEFT JOIN land AS la
        ON la.landid = ak.landid
        WHERE ak.aktorid = $id";
$res = $db->query($sql);
$ak = $db->fetch_object($res);

// Finner næringskategorier:
$sql = "SELECT nk.betegnelse AS naeringskategori
        FROM aktnaer AS an
        LEFT JOIN naeringskategori AS nk
        ON nk.naeringskategoriid = an.naeringskategorinkategoriid
        WHERE an.aktoraktorid = $id";
$res = $db->query($sql);
$rader = $db->fetch_all($res);

$nk = array();
foreach ($rader as $rad) {
  $nk[] = $rad->naeringskategori;
}

// Finner organisasjonskategorier:
$sql = "SELECT ok.betegnelse AS orgkategori
        FROM aktorg AS ao
        LEFT JOIN organisasjonskategori AS ok
        ON ok.organisasjonskategoriid = ao.orgkatorgkatid
        WHERE ao.aktoraktorid = $id";
$res = $db->query($sql);
$rader = $db->fetch_all($res);

$ok = array();
foreach ($rader as $rad) {
	$ok[] = $rad->orgkategori;
}

if ($ak->type == 'Virksomhet') {
  $type = 'corporateBody';
}
else {
  $type = 'person';
}

skriv(0, '<eac-cpf>');
skriv(1, '<control>');
skriv(2, '<recordID>'.$ak->identifikator.'</recordID>'); // todo: kan jeg bruke $id her?
if ($ak->lokalsignatur) {
  skriv(2, '<otherRecordId>'.$ak->lokalsignatur.'</otherRecordId>');
}
skriv(2, '<maintenanceStatus>derived</maintenanceStatus>');
  // "derived" indikerer at den er utledet fra annet system (f.eks. Asta)
skriv(2, '<maintenanceAgency>');
skriv(3, '<agencyCode>NO-'.$ak->depinstid.'</agencyCode>');
skriv(3, '<agencyName>'.$ak->depinstnavn.'</agencyName>');
skriv(2, '</maintenanceAgency>');
skriv(2, '<maintenanceHistory>');
skriv(3, '<maintenanceEvent>');
skriv(4, '<eventType>Created</eventType>');
skriv(4, '<eventDateTime>'.date('c', $ak->opprettetdato).'</eventDateTime>'); // todo: Kanskje ha dato for opprettelse i system
skriv(4, '<agentType>human</agentType>');
skriv(4, '<agent>'.$ak->opprettetav.'</agent>'); // todo: F� p� plass en agent
skriv(3, '</maintenanceEvent>');
skriv(2, '</maintenanceHistory>');
skriv(1, '</control>');
skriv(1, '<cpfDescription>');
skriv(2, '<identity>');
if ($ak->eksternid) {
  skriv(3, '<entityId>'.$ak->eksternid.'</entityId>');
}
skriv(3, '<entityType>'.$type.'</entityType>');
// todo: Har hoppet over <nameEntryParallel>, men kan iallfall v�re aktuell for Asta
skriv(3, '<nameEntry localType="offisielt">');
skriv(4, '<part>'.$ak->navn.'</part>');
skriv(3, '</nameEntry>');

// Legger til alternative navn:
$sql = "SELECT alternativtnavn, fraaar, tilaar, offisielt
        FROM aktornavn
        WHERE aktoraktorid = $id";

$res = $db->query($sql);
$altnavn_arr = $db->fetch_all($res);
foreach ($altnavn_arr as $altnavn) {
	$localtype = ($altnavn->offisielt ? 'offisielt' : 'uoffisielt');
	skriv(3, '<nameEntry localType="'.$localtype.'">');
	skriv(4, '<part>'.$altnavn->alternativtnavn.'</part>');
	if ($altnavn->fraaar || $altnavn->tilaar) {
		skriv(5, '<dateRange>');
		if ($altnavn->fraaar) {
			skriv(6, '<fromDate>'.$altnavn->fraaar.'</fromDate>');
			// todo: attributtet standardDate
		}
		if ($altnavn->tilaar) {
			skriv(6, '<toDate>'.$altnavn->tilaar.'</toDate>');
		}
		skriv(5, '</dateRange>');
	}
	skriv(3, '</nameEntry>');
}
// Har hoppet over <descriptiveNote>, som kan hjelpe med identifiseringen
skriv(2, '</identity>');
skriv(2, '<description>');
skriv(3, '<existDates>');
if ($ak->startdato == null) {
  $ak->startdato = '(ikke registrert)';
}
if ($ak->sluttdato == null) {
  $ak->sluttdato = '(ikke registrert)';
}
skriv(4, '<dateRange>');
skriv(5, '<fromDate>'.$ak->startdato.'</fromDate>');
skriv(5, '<toDate>'.$ak->sluttdato.'</toDate>');
skriv(4, '</dateRange>');
skriv(4, '<descriptiveNote>'.$ak->datokvalitet.'</descriptiveNote>');
skriv(3, '</existDates>');
$adressefelter = array('gateadresse','postnr','poststed','telefon','telefax',
											 'epost', 'internett', 'land');
foreach ($adressefelter as $felt) {
	if ($ak->$felt) {
		$adresse = true;
	}
}
if ($ak->omraade || $ak->bruksnr || $ak->land) {
  $topografi = true;
}
if ($adresse || $topografi) {
  skriv(3, '<place>');
  if ($adresse) {
    skriv(4, '<address>');
		foreach ($adressefelter as $felt) {
			if ($ak->$felt) {
				skriv(5, '<addressLine localType="'.$felt.'">'
							.$ak->$felt.'</addressLine>');
			}
    }
    skriv(4, '</address>');
  }
  if ($topografi) {
    $txt_arr = array();
    if ($ak->bruksnr) {
      $txt_arr[] = 'Bruksnr. '.$ak->bruksnr;
    }
    if ($ak->omraade) {
      $txt_arr[] = $ak->omraade;
    }
    if ($ak->land) {
      $txt_arr[] = $ak->land;
    }
    $txt = implode(', ', $txt_arr);
    skriv(4, '<placeEntry>'.$txt.'</placeEntry>');

  }
	skriv(3, '</place>');
}
if ($ak->yrke) {
  skriv(3, '<occupation>');
  skriv(4, '<term>'.$ak->yrke.'</term>');
  skriv(3, '</occupation>');
}
if ($ak->samfunnssektor) {
  skriv(3, '<generalContext localType="samfunnssektor">');
  skriv(4, '<p>'.$ak->samfunnssektor.'</p>');
  skriv(3, '</generalContext>');
}
if ($ak->aktortype) {
  skriv(3, '<generalContext localType="aktortype">');
  skriv(4, '<p>'.$ak->aktortype.'</p>');
  skriv(3, '</generalContext>');
}
if ($ak->forvaltningsnivaa) {
  skriv(3, '<generalContext localType="forvaltningsnivaa">');
  skriv(4, '<p>'.$ak->forvaltningsnivaa.'</p>');
  skriv(3, '</generalContext>');
}
if ($ak->forvaltningsomraade) {
  skriv(3, '<generalContext localType="forvaltningsomraade">');
  skriv(4, '<p>'.$ak->forvaltningsomraade.'</p>');
  skriv(3, '</generalContext>');
}
if ($ak->kjoenn) {
  skriv(3, '<generalContext localType="kjoenn">');
  skriv(4, '<p>'.$ak->kjoenn.'</p>');
  skriv(3, '</generalContext>');
}
if (count($nk) > 0) {
  skriv(3, '<generalContext localType="naeringskategori">');
  skriv(4, '<list>');
  foreach($nk AS $item) {
    skriv(5, '<item>'.$item.'</item>');
  }
  skriv(4, '</list>');
  skriv(3, '</generalContext>');
}
if (count($ok) > 0) {
	skriv(3, '<generalContext localType="organisasjonskategori">');
	skriv(4, '<list>');
	foreach($ok as $item) {
		skriv(5, '<item>'.$item.'</item>');
	}
	skriv(4, '</list>');
	skriv(3, '</generalContext>');
}

// todo: Legg inn <place>. Beh�ves iallfall i Asta.
// todo: Hoppet over flere. S�rlig kan <structureOrGenealogy> v�re aktuell.
skriv(3, '<biogHist>');
skriv(4, '<p>'.$ak->historikk.'</p>');
skriv(3, '</biogHist>');
// Hoppet over <relations>. Blir kanskje vanskelig � ta det med i en pakkestruktur.
skriv(2, '</description>');
skriv(1, '</cpfDescription>');
skriv(0, '</eac-cpf>', $linjeskift=false);
?>
