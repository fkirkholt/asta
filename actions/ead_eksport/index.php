<?php

$base = $_POST['base'];
$tabell = $_POST['table'];
$prim_nokkel_json = $_POST['primary_key'];
$prim_nokkel_arr = json_decode($prim_nokkel_json, true);
$id = $prim_nokkel_arr['arkivenhetid'];

?>

<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <!--<script type="text/javascript" src="/urd/js/funksjoner.js"></script>-->

    <script type="text/javascript"><?php echo file_get_contents(__DIR__ . '/lag_ead.js') ?></script>

    <style>
    input#sti {
      width: 400px;
    }
    </style>

    <title>Eksporter til EAD</title>
  </head>
  <body>
    <?php
      echo '<input id="base" type="hidden" value="'.$base.'"/>';
      echo '<input id="tabell" type="hidden" value="'.$tabell.'"/>';
      echo '<input id="id" type="hidden" value="'.$id.'"/>';
    ?>
    Legg inn sti til mappen hvor ead-filen skal legges<br/>
    <input id="sti">
    <input type="button" id="knapp_lag_ead" value="Lag EAD-fil" /><br/>
    <p><a id="valider" href="#">Valider mot offisielt skjema</a></p>
    <p><a id="valider_apenet" href="#">Valider mot APEnet-skjema</a><p>
  </body>
 </html>
