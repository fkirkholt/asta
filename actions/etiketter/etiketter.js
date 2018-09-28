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

$(document).ready(function() {

  if ($('input[name="arkivid"]').val() !== '') {
    get_series();
  }

  if ($('input[name="barcode"]').prop('checked') == false) {
    $('input[name="lagringsenhet"]').prop('checked', true).attr('disabled', true);
  }

  var cssPagedMedia = (function () {
    var style = document.createElement('style');
    document.head.appendChild(style);
    return function (rule) {
      style.innerHTML = rule;

    };

  }());

  cssPagedMedia.size = function (size) {
    if (size == 'portrait') {
      cssPagedMedia('@page {size: 8.5in 11in; margin:0cm}');
    } else {
      cssPagedMedia('@page {size: 11in 8.5in; margin:0cm}');
    }
    // cssPagedMedia('@page {size: ' + size + '}');

  };

  cssPagedMedia.size('portrait');

  // events

  $('input[name="arkivid"]').change(function() {
    get_series();
  });

  $('input[name="barcode"]').change(function() {
    if ($(this).prop('checked')) {
      $('div[name="barcode"]').show();
      $('input[name="lagringsenhet"]').removeAttr('disabled');
    } else {
      $('div[name="barcode"]').hide();
      $('input[name="lagringsenhet"]').attr('disabled', true);
    }
  }).prop('checked', false);

  $('select[name="format"]').change(function() {
    cssPagedMedia.size($(this).val());
  });


  $('div[name="elements"] :input[type="checkbox"]').change(function() {
    console.log('endrer checkbox');
    var height = Number($('input[name="height"]').val());
    if ($(this).prop('checked')) {
      height += $(this).data('height');
    } else {
      console.log($(this)[0].name);
      console.log($('input[name="lagringsenhet"]').prop('checked'));
      if ($(this)[0].name === 'barcode' && $('input[name="lagringsenhet"]').prop('checked') === false) {
        $('input[name="lagringsenhet"]').prop('checked', true);
      } else {
        height -= $(this).data('height');
      }
    }

    $('input[name="height"]').val(height);

    if ($(this)[0].name === 'barcode') {
      console.log($('input[name="barcode_text"]')[0].disabled = !$(this).prop('checked'));
    }
  });

  $('#run').click(function() {
    var p = {};
    p.base = $('input[name="databasenavn"]').val();
    p.arkivid = $('input[name="arkivid"]').val();
    p.serieid = $('select[name="serieid"]').val();

    var format = $('select[name="format"]').val();

    // Beregner hva det er plass til
    var page_margin = parseFloat($('input[name="sidemarg"]').val());
    var paper_width = format === 'portrait' ? 21 : 29.7;
    var paper_height = format === 'portrait' ? 29.7 : 21;
    paper_width = paper_width - (2 * page_margin);
    paper_height = paper_height - (2 * page_margin);
    var width = parseFloat($('input[name="width"]').val());
    var height = parseFloat($('input[name="height"]').val());
    var margin_left = parseFloat($('input[name="margin_left"]').val());
    var margin_right = parseFloat($('input[name="margin_right"]').val());
    var total_width = width + margin_left + margin_right;
    var margin_top = parseFloat($('input[name="margin_top"]').val());
    var margin_bottom = parseFloat($('input[name="margin_bottom"]').val());
    var total_height = height + margin_top + margin_bottom;
    var ant_i_bredde = Math.floor(paper_width/total_width);
    var ant_i_hoyde = Math.floor(paper_height/total_height);
    var ant_per_side = ant_i_bredde * ant_i_hoyde;

    var depotinst = $('input[name="depotinst"]').prop('checked');
    var innhold = $('input[name="innhold"]').prop('checked');
    var barcode = $('input[name="barcode"]').prop('checked');
    var lagringsenhet = $('input[name="lagringsenhet"]').prop('checked');

    var max_number_barcodes = 0;


    $.getJSON('lag_etiketter', p, function(data) {
      var html = '<div class="page ' + format + '">';
      var i = 0;
      $.each(data.lagringsenheter, function(id, lagrenh) {
        i++;
        var pbreak;
        if (i/ant_per_side == Math.floor(i/ant_per_side) && i < Object.keys(data.lagringsenheter).length) {
          pbreak = true;
        } else {
          pbreak = false;
        }
        console.log('ant_per_side', ant_per_side);
        console.log('lagringsenheter_length', Object.keys(data.lagringsenheter).length);
        console.log('pbreak', pbreak);
        html += '<div class="etikett" style="border: 1px solid black">';
        if (depotinst) {
          html += '<div class="depinst"><span>'+data.arkiv.depinst+'</span></div>';
        }

        // innhold
        if (innhold || barcode) {
          html += '<div class="innhold">';

          if (innhold) {
            html += '<b>Arkiv:</b><br><b>'+data.arkiv.navn+'</b><br><br>';
            if (data.serier.length) {
              html += '<b>Serie:</b><br>';
              $.each(data.serier, function(i, serie) {
                html += serie.identifikator + ' - ' + serie.navn + '<br>';
              });
            }
            if (lagrenh.arkivenhetnavn) {
              html += '<br>'+lagrenh.arkivenhetnavn+'<br>';
            }
            if (lagrenh.startdato) {
              html += '<br>'+lagrenh.startdato;
              if (lagrenh.sluttdato) {
                html += ' - ' + lagrenh.sluttdato;
              }
            }
          }

          // strekkode
          if (barcode) {
            html += '<div class="barcode">';
            $.each(lagrenh.arkivenheter, function(i, ae) {
              html += '<img name="barcode" data-urn="' + ae.urn + '"/>';
            });
            html += '</div>'

            if (lagrenh.arkivenheter.length > max_number_barcodes) {
              max_number_barcodes = lagrenh.arkivenheter.length;
            }
          }

          html += '</div>';
        }


        // lagringsenhet
        if (lagringsenhet) {
          html += '<div class="lagringsenhet">';
          if (data.serier.length) {
            var serie = data.serier[data.serier.length -1];
            path = data.arkiv.depinstid + '/' + serie.sti + serie.identifikator + '/' + lagrenh.identifikasjon;
          } else {
            path = data.arkiv.depinstid + '/' + p.arkivid + '/' + lagrenh.identifikasjon;
          }

          html += '<div>' + path + '</div></div>';
        }

        html += '</div>';
        if (pbreak) {
          html += '</div>';
          html += '<div class="page ' + format + '">';
        }
      });
      html += '</div></div>';
      $('#etiketter').html(html);

      if (barcode) {
        $('img[name="barcode"]').each(function() {
          var urn = $(this).data('urn');
          $(this).JsBarcode(urn, {
            height:   20,
            width:    2,
            fontSize: 16,
            displayValue: $('input[name=barcode_text]').prop('checked')
          })
        });

        // Set max-height
        var max_height = Number($('input[name="height"]').val());
        if ($('input[name=depotinst]').prop('checked')) max_height = max_height - 1;
        if ($('input[name=innhold]').prop('checked')) max_height = max_height - 5;
        $('.barcode').css('max-height', max_height+'cm');

        console.log('max height', max_height);
        console.log('max nmbr', max_number_barcodes);

        if (max_height < max_number_barcodes) {
          var diff = max_number_barcodes - max_height;
          var msg = 'Lagringsenhetene har flere strekkoder, og ikke alle får plass med valgt høyde.';
          msg +=  ' Du må øke høyden med ' + diff + 'cm for å få plass';
          alert(msg);
        }

      }

      console.log(height);
      $('.page').css('padding', page_margin+'cm');
      $('.etikett').css('width', width+'cm');
      var height_header = $('input[name=depotinst]').prop('checked') ? 1 : 0;
      var height_footer = $('input[name=lagringsenhet]').prop('checked') ? 1 : 0;
      var innhold_hoyde = height - height_header - height_footer;
      $('.innhold').css('height', innhold_hoyde+'cm');
      $('.etikett').css('margin-left', margin_left+'cm');
      $('.etikett').css('margin-right', margin_right+'cm');
      $('.etikett').css('margin-top', margin_top+'cm');
      $('.etikett').css('margin-bottom', margin_bottom+'cm');
    });
  });

});
