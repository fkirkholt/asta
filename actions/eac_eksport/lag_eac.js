$(document).ready(function() {

//  $.getJSON('../../php/hent_config.php', function(data) {
//    if (data.online_mode) {
//      $('#sti').val(data.arch_desc_path).attr('disabled','disabled');
//      console.log('online mode');
//    }
//    else {
//      $('#sti').val('').removeAttr('disabled');
//    }
//  });

  $('#knapp_lag_eac').click(function() {
    console.log('hei');
    lag_eac();
  });

  function lag_eac(tabell, id, sti) {
    var param = {
      'base':$('#base').val(),
      'tabell':$('#tabell').val(),
      'id':$('#id').val(),
      'sti':$('#sti').val()
    };
    $.getJSON("lag_eac.php", param, function(data) {
    });
  }
});
