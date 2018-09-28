$(document).ready(function() {

  $('#knapp_lag_ead').click(function() {
    lag_ead();
  });

  $('#valider').click(function() {
    valider('ead.xsd');
  });

  $('#valider_apenet').click(function() {
    valider('APEnet_EAD.xsd');
  });

  function lag_ead() {
    var param = {
      base:$('#base').val(),
      'tabell':$('#tabell').val(),
      'id':$('#id').val(),
      'sti':$('#sti').val()
    };
    $.getJSON("/asta/make_ead", param, function(data) {
    });
  }

  function valider(skjema) {
    var param = {
      'sti':$('#sti').val(),
      'skjema':skjema
    };
    $.getJSON("/asta/validate_ead", param, function(data) {

    });
  }


});
