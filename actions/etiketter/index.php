<?php

use URD\models\Database;

$base = $_POST['base'];
$tabell = $_POST['table'];

$db = new Database($base);

if (isset($_POST['primary_key'])) {
  $pk = json_decode($_POST['primary_key'], true);
  $arkivenhetid = $pk['arkivenhetid'];

  // Finn tilhørende arkiv
  $sql = "SELECT rotnode
    FROM   arkivenhet
    WHERE  arkivenhetid = $arkivenhetid";

  $row = $db->query($sql)->fetch();

  $arkivid = $row->rotnode !== 1 ? $row->rotnode : $arkivenhetid;

  $sql = "SELECT identifikator
          FROM arkivenhet
          WHERE arkivenhetid = $arkivid";

  $row = $db->query($sql)->fetch();

  $arkiv_identifikator = $row->identifikator;
} else {
  $arkiv_identifikator = '';
}

?>

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Etiketter</title>
    <script type="text/javascript"><?php echo file_get_contents(__DIR__ . '/jquery.min.js') ?></script>
    <script type="text/javascript"><?php echo file_get_contents(__DIR__ . '/etiketter.js') ?></script>
    <script type="text/javascript"><?php echo file_get_contents(__DIR__ . '/JsBarcode.all.min.js') ?></script>
    <style><?php echo file_get_contents(__DIR__ . '/etiketter.css') ?></style>
    <!--
    <link rel="stylesheet" href="etiketter_utskrift.css"
          type="text/css" media="print"/>-->
  </head>
  <body>
    <form>
      <div style="float:left">
        <input type="hidden" name="databasenavn" value="<?php echo $base ?>">
        <table>
          <tr><th colspan="2">Arkiv/serie</th></tr>
          <tr><td>Arkivid: </td><td><input type="text" name="arkivid" value="<?php echo $arkiv_identifikator ?>"></td></tr>
          <tr><td>Serieid: </td><td><select name="serieid"></td></tr>
          <tr><td>&nbsp;</td></tr>
          <tr><th style="text-align: left">Side</th></tr>
          <tr>
            <td>Side-format:</td>
            <td>
              <select name="format">
                <option value="portrait">Stående A4</option>
                <option value="landscape">Liggende A4</option>
              </select>
            </td>
          </tr>
          <tr><td>Marger</td><td><input type="text" name="sidemarg" style="width: 40px" value="0.5"> cm</td></tr>
        </table>
      </div>
      <div style="float:left" name="elements">
        <b>Velg elementer</b><br>
        <input name="depotinst" type="checkbox" data-height="1"/> Depotinstitusjon<br>
        <input name="innhold" type="checkbox" data-height="4"/> Arkiv/serie<br>
        <input name="lagringsenhet" type="checkbox" data-height="1" checked/> Lagringsenhet<br>
        <input type="checkbox" name="barcode" data-height="1"/> Strekkode<br>
        <input type="checkbox" name="barcode_text" data-height="0" disabled="disabled"/>Vis tekst<br>
      </div>
      <div style="float:left" name="layout">
        <b>Størrelse og marger</b>
        <table>
          <tr><td>Bredde: </td><td><input type="text" name="width" value="10"> cm</td></tr>
          <tr><td>Høyde: </td><td><input type="text" name="height" value="1"> cm</td></tr>
          <tr><td>Venstre marg: </td><td><input type="text" name="margin_left" value="0.5"> cm</td></tr>
          <tr><td>Høyre marg: </td><td><input type="text" name="margin_right" value="0.5"> cm</td></tr>
          <tr><td>Toppmarg: </td><td><input type="text" name="margin_top" value="0.5"> cm</td></tr>
          <tr><td>Bunnmarg: </td><td><input type="text" name="margin_bottom" value="0.5"> cm</td></tr>
        </table>
      </div>
      <div style="float:left">
        <input type="button" id="run" value="Lag etiketter">
      </div>
    </form>
    <div id="etiketter"></div>
  </body>
</html>
