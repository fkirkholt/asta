$('#run').click(get_data);

$('input[name="arkivid"]').change(function() {
  get_series();
});

if ($('input[name="arkivid"]').val() !== '') {
  get_series();
}

function get_series() {
  var arkivid = $('input[name="arkivid"]').val();
  var basenavn = $('input[name="databasenavn"').val();

  $.get("hent_serier", {arkivid: arkivid, base: basenavn},  function(data) {

    if (!data.success) {
      alert(data.message);
      return;
    }

    $('select[name="serieid"]').html('');

    var option = '';
    for (var i=0;i<data.series.length;i++){
         option += '<option value="'+ data.series[i].id + '">' + data.series[i].path + '</option>';

    }
    $('select[name="serieid"]').append(option);
  }, 'json');
}

function get_data() {

  var arkivid = $('input[name="arkivid"]').val();
  var basenavn = $('input[name="databasenavn"').val();
  var serie_sti = $('select[name="serieid"]').text();
  var sti = arkivid + '/' + serie_sti;

  $.getJSON('lag_saksomslag', {sti: sti, base: basenavn}, function(data) {
    console.log(data);
    var html = '';

    if (data.arkivenheter.length > 1000) {
      html += 'Intervall: <select name="intervall">';
      var antall_om_gangen = 500;
      var count = data.arkivenheter.length/antall_om_gangen;
      var i = 0;
      var min;
      var max;
      while (i < count) {
        min = i*antall_om_gangen + 1;
        if (i > count-1) {
          max = data.arkivenheter.length
        } else {
          max = i*antall_om_gangen + antall_om_gangen;
        }
        html += '<option value="' + i + '">' + min + '-' + max + '</option>';
        i++;
      }

      $('#intervall').html(html);

      tegn_saksomslag(data.arkivenheter, 0, antall_om_gangen);

      $('#intervall select').on('change', function() {
        var i = $(this).val();
        console.log(i);
        var min = i*antall_om_gangen + 1;
        var max = i*antall_om_gangen + antall_om_gangen;
        console.log(min);
        console.log(max);
        tegn_saksomslag(data.arkivenheter, min, max);
      });
    } else {
      tegn_saksomslag(data.arkivenheter, 0, 2*antall_om_gangen);
    }



  });
}

function tegn_saksomslag(arkivenheter, min, max) {
  var html = '';
  $.each(arkivenheter, function(i, ae) {
    if (i < min || i >= max) return;

    html += '<div class="page portrait">';
    html += '<div class="barcode"><img name="barcode" data-urn="' + ae.urn + '"/></div>';
    html += '<div class="qrcode" id="qr' + i + '" data-urn="' + ae.urn + '"></div>';
    html += '<div class="descr">Saksomslag<br>' + ae.navn + '</div>';
    html += '</div>';
  });
  $('#saksomslag').html(html);

  $('img[name="barcode"]').each(function() {
    var urn = $(this).data('urn');
    $(this).JsBarcode(urn, {
      height:   40,
      width:     2,
      fontSize: 16,
      displayValue: true
    });
  });

  $('div[class="qrcode"]').each(function(i) {
    var qrcode = new QRCode('qr' + i, $(this).data('urn'));
  });
}
