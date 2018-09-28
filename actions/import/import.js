$(document).ready(function() {

  var resultat = '<p style="color:gray">(Ikke kjørt noe skript ennå)<p>';
  // holder resultat fra siste skript
  $('#resultat').html(resultat);

  $('#neste_handling').show();

  $('#import_lenke').click(function() {
    path = "/urd/templates/asta_5/handlinger/import";
    import_sti = $('input[name="sti_importfil"]').val();
    liste = urd.get_list();
    console.log(liste);
    base = liste.base_navn;
    $.get(path+"/import.php", {import_sti: import_sti, base: base}, function(html) {
      resultat = html;
      $('#resultat_fane').trigger('click');
      // $('#import_lenke').addClass('inaktiv').unbind();
    });
  });

  $('#resultat_fane').click(function() {
    console.log('klikket på resultatfane');
    $('#resultat').html(resultat);
    $('#visning_header a').removeClass('aktiv_fane').addClass('inaktiv_fane');
    $(this).removeClass('inaktiv_fane').addClass('aktiv_fane');
  });

  $('#hjelp_fane').click(function() {
    console.log('hjelp!');
    path = "/urd/templates/depotstyring/handlinger/testing";
    $('#resultat').load(path+"/hjelp.htm");
    $('#visning_header a').removeClass('aktiv_fane').addClass('inaktiv_fane');
    $(this).removeClass('inaktiv_fane').addClass('aktiv_fane');
  });

    $('#lukk_dialog').click(function(e) {
        $('#handling_dialog').hide();
        e.preventDefault();

    })

});
