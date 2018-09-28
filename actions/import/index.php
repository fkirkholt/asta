<style>

div#handling_dialog {
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
}

input#sti {
  width: 400px;
}

div#skript {
  float: left;
  width: 40%;
}

div#visning {
  float: left;
  width: 60%;
  margin-top: 7px;
}

div#visning_header {
  margin-left: 10px;
}

div#resultat {
  border: 1px solid #D2D2D2;
  margin-left: 10px;
  margin-top: 3px;
  padding: 10px;
}

div#info {
  border: 1px solid #D2D2D2;
  margin-left: 10px;
  margin-top: 3px;
  padding: 10px;
  /*font-family: monospace;*/
}

a {
  padding: 3px 5px;
}

a.aktiv_fane {
  border: 1px solid #D2D2D2;
  border-bottom: 1px solid white;
  color: black;
  background-color: white;
}

a.inaktiv_fane {
  border: 1px solid #D2D2D2;
  background-color: #E5E5E5;
  color: black;
}

a.inaktiv {
  color: #C0C8C0;
}
</style>


<script type="text/javascript"><?php echo file_get_contents(__DIR__ . '/import.js') ?></script>

<div><a id="lukk_dialog" href="#"><i class="fa fa-chevron-left"></i>Tilbake</a></div>
<hr>

<div id="skript">
  <form id="importform">
  <table>
    <tr>
      <td>Sti til importfil</td>
      <td><input name="sti_importfil"/></td>
    </tr>
  </table>
  </form>

  <a id="import_lenke" href="#">Importer</a>
</div>

<div id="visning">
  <div id="visning_header">
    <a id="resultat_fane" href="#" class="aktiv_fane">Resultat siste skript</a>
    <a id="hjelp_fane" href="#" class="inaktiv_fane">Hjelp</a>
  </div>
  <div id="resultat"></div>
  <div id="info" style="display: none"></div>
</div>
