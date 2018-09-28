<?php

$base = $_POST['base'];
$tabell = $_POST['tabell'];
$prim_nokkel_json = $_POST['prim_nokkel'];
$prim_nokkel_arr = json_decode($prim_nokkel_json);
$id = $prim_nokkel_arr[0];

?>

<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="/urd/lib/js/jquery.js"></script>
    <script type="text/javascript" src="/urd/js/funksjoner.js"></script>

    <script type="text/javascript" src="lag_eac.js"></script>

    <link rel="stylesheet" href="stil.css" type="text/css" media="screen" />

    <title>Lag EAC</title>
  </head>
  <body>
    <?php
      echo '<input id="base" type="hidden" value="'.$base.'">';
      echo '<input id="tabell" type="hidden" value="'.$tabell.'"/>';
      echo '<input id="id" type="hidden" value="'.$id.'"/>';
    ?>
    Legg inn sti til mappen hvor eac-filen skal legges<br/>
    <input id="sti">
    <input type="button" id="knapp_lag_eac" value="Lag EAC-fil" /><br/>
  </body>
 </html>
