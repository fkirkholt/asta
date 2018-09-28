<?php

use URD\models\Database;

$base = $_POST['base'];
$tabell = $_POST['table'];

$db = new Database($base);

if (isset($_POST['primary_key'])) {
  $pk = json_decode($_POST['primary_key'], true);
  $arkivenhetid = $pk['arkivenhetid'];

  // Finn info om arkivenhet
  $sql = "SELECT rotnode FROM arkivenhet
          WHERE arkivenhet.arkivenhetid = $arkivenhetid";

  $row = $db->query($sql)->fetch();

  $arkivid = $row->rotnode !== 1 ? $row->rotnode : $arkivenhetid;


  $sql = "SELECT identifikator
          FROM arkivenhet
          WHERE arkivenhetid = $arkivid";

  $arkiv_identifikator = $db->query($sql)->fetchSingle();
} else {
  $arkiv_identifikator = '';
}

?>

<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript">
      <?php echo file_get_contents(__DIR__ . '/../etiketter/jquery.min.js') ?>
    </script>
    <script type="text/javascript">
      <?php echo file_get_contents(__DIR__ . '/../etiketter/JsBarcode.all.min.js') ?>
    </script>
    <script type="text/javascript">
      <?php echo file_get_contents(__DIR__ . '/qrcode.min.js') ?>
    </script>
    <style><?php echo file_get_contents(__DIR__ . '/styles.css') ?></style>
  </head>
  <body>
    <input type="hidden" name="databasenavn" value="<?php echo $base ?>">
    <form onsubmit="return false;">
      Arkiv-id: <input type="text" name="arkivid" value="<?php echo $arkiv_identifikator ?>">
      Serieid: <select name="serieid"></select>
      &nbsp;<input type="button" id="run" value="Lag sakssomslag">
    </form>
    <div id="intervall"></div>
    <div id="saksomslag"></div>
    <script>
      <?php echo file_get_contents(__DIR__ . '/saksomslag.js') ?>
    </script>
  </body>
</html>
