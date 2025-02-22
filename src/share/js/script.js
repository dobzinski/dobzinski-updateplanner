let idtimeout = null; 
let progressInterval = null;
let progress = 0;
const reloadInterval = 60000;

var delay = 1000;
var loader = '<div class="mt-4 mb-4"><div class="spinner-grow spinner-grow-sm text-primary" role="status"></div>&nbsp;&nbsp;<div class="spinner-grow spinner-grow-sm text-info" role="status"></div>&nbsp;&nbsp;<div class="spinner-grow spinner-grow-sm text-secondary" role="status"></div></div>';
var loader2 = '<div class="spinner-border" role="status"></div>';

function openCollection(api) {
  $.ajax({
    type: 'GET',
    url: "api/action.php?resource=portal&collection="+ api,
    datatype: "html",
    beforeSend: function() {
      $('#area').html(loader);
    },
    success: function(html) {
      setTimeout(function() {
        $('#area').html(html);
      }, delay);
    }
  });
  $('[data-bs-target="#sidebarMenu"]').removeClass('btn-toggle-nav-actived');
  $('#menu_'+ api).addClass('btn-toggle-nav-actived');
  return false;
}

function getApi(api) {
  $.ajax({
    type: 'GET',
    url: "api/"+ api +".php?resource=portal",
    datatype: "html",
    beforeSend: function() {
      $('#area').html(loader);
    },
    success: function(html) {
      setTimeout(function() {
        $('#area').html(html);
      }, delay);
    }
  });
  return false;
}

function postApi(api, action, v) {
  if (v.length > 0){
    form = '';
    for (i=0; i<v.length; i++) {
      val = '';
      if (!$('#'+ v[i]).is(':checkbox')) {
        val = $('#'+ v[i]).val();
      } else {
        if ($('#' + v[i]).is(':checked')) {
          val = '1';
        } else {
          val = '0';
        }
      }
      form += '&'+ v[i] +'='+ encodeURIComponent(val);
    }
    if (typeof $("#formEdit") != "undefined") {
      $.ajax({
        type: 'POST',
        url: "api/"+ api +".php?resource=portal",
        data: 'action='+ action + form,
        datatype: "html",
        beforeSend: function() {
          $('[data-toggle="tooltip"]').tooltip('hide');
          $('#area').html(loader);
        },
        success: function(html) {
          setTimeout(function() {
            $('#area').html(html);
          }, delay);
        }
      });
    }
  }
  return false;
}

function actionApi(collection, action, vid) {
  if (collection != '') {
    if (typeof $("#formEdit") != "undefined") {
      $.ajax({
        type: 'POST',
        url: "api/action.php?resource=portal",
        data: 'action='+ action + '&collection='+ collection +'&v-id='+ vid,
        datatype: "html",
        beforeSend: function() {
          $('[data-toggle="tooltip"]').tooltip('hide');
          $('#area').html(loader);
        },
        success: function(html) {
          setTimeout(function() {
            $('#area').html(html);
          }, delay);
        }
      });
    }
  }
  return false;
}

function prepareDelete(table, v) {
  if (table != '' && v != '') {
    $('#'+ table +'-delete').val(v);
  }
  return false;
}

function actionDeleteApi(collection, table) {
  if (collection != '' && table != '') {
    if (typeof $("#formEdit") != "undefined") {
      vid = $('#'+ table +'-delete').val();
      if (vid != '') {
          $.ajax({
            type: 'POST',
            url: "api/action.php?resource=portal",
            data: 'action=delete&collection='+ collection +'&v-id='+ vid,
            datatype: "html",
            beforeSend: function() {
              $('[data-toggle="tooltip"]').tooltip('hide');
              $('#area').html(loader);
            },
            success: function(html) {
              setTimeout(function() {
                $('#area').html(html);
              }, delay);
            }
          });
        }
      }
  }
  return false;
}

function postDownload(v) {
  if (v != '') {
    $('#v-file').val(v);
    $('#form-download').submit();
  }
  return false;
}

function reloadReports() {
  if (idtimeout) {
    clearTimeout(idtimeout);
  }
  if (progressInterval) {
    clearInterval(progressInterval);
    progressInterval = null;
  }
  progress = 0;
  if ($('#b-available').length) {
    updateProgressBar();
    progressInterval = setInterval(() => {
      progress += 100 / (reloadInterval / 100);
      updateProgressBar();
      if (progress >= 100) {
        clearInterval(progressInterval);
        progressInterval = null;
      }
    }, 100);
    if ($('#b-queue').length) {
      openReport('queue');
    }
    openReport('available');
    idtimeout = setTimeout(function () {
      clearInterval(progressInterval);
      progress = 100;
      updateProgressBar();
      reloadReports();
    }, reloadInterval);
  }
  return false;
}

function updateProgressBar() {
  let widthPercent = Math.min(progress, 100);
  if ($('#b-available').length) {
      $("#progress-bar-two").css("width", widthPercent + "%");
      if ($('#b-queue').length) {
      $("#progress-bar-three").css("width", widthPercent + "%");
    }
  }
  return false;
}

function openReport(report) {
  if (report != '') {
    if ($('#b-'+ report).length) {
      $.ajax({
        type: 'POST',
        url: "api/report.php?resource=portal",
        data: 'report='+ report,
        datatype: "html",
        beforeSend: function() {
          $('[data-toggle="tooltip"]').tooltip('hide');
          $('#b-'+ report).html(loader2);
        },
        success: function(html) {
          setTimeout(function() {
            $('#b-'+ report).html(html);
          }, 300);
        }
      });
    }
  }
  return false;
}

function openCalendar(year, month) {
  if (month != '' && year != '') {
    $.ajax({
      type: 'POST',
      url: "api/calendar.php?resource=portal",
      data: 'action=view&year='+ year +'&month='+ month,
      datatype: "html",
      beforeSend: function() {
        $('[data-toggle="tooltip"]').tooltip('hide');
        $('#area2').html(loader2);
      },
      success: function(html) {
        setTimeout(function() {
          $('#area2').html(html);
        }, 300);
      }
    });
  }
  return false;
}

function openGraph(year, month) {
  if (month != '' && year != '') {
    $.ajax({
      type: 'POST',
      url: "api/graph.php?resource=portal",
      data: 'action=view&year='+ year +'&month='+ month,
      datatype: "html",
      beforeSend: function() {
        $('#area3').html(loader2);
      },
      success: function(html) {
        setTimeout(function() {
          $('#area3').html(html);
        }, 300);
      }
    });
  }
  return false;
}

function addDateCompleted(date) {
  if (date != '') {
    if ($('#v-complete').length) {
      $('#v-complete').val(date);
    }
  }
  return false;
}

function controlLabelCheck(id, enable, disable) {
  if (id != '') {
    val = $('#'+ id).is(":checked");
    console.log(val);
    if (val == 1) {
      $('#'+ id +'-label').html(enable);
    } else {
      $('#'+ id +'-label').html(disable);
    }
  }
  return false;
}

function controlCluster() {
  if ($("#v-type").is(":checked")) {
    $("#v-box-cluster").show();
    $("#v-box-environment").hide();
  } else {
    $("#v-box-environment").show();
    $("#v-box-cluster").hide();
  }
  return false;
}

function controlAbstract() {
  if ($("#v-type").is(":checked")) {
    $("#v-box-withplanning").show();
    $("#v-box-withoutplanning").hide();
  } else {
    $("#v-box-withoutplanning").show();
    $("#v-box-withplanning").hide();
  }
  return false;
}

function controlLdap() {
  if ($("#v-ldap").is(":checked")) {
    $("#v-box-internaluser").hide();
  } else {
    $("#v-box-internaluser").show();
  }
  return false;
}

function controlPassword() {
  if ($("#v-updatepassword").is(":checked")) {
    $("#v-box-password").show();
  } else {
    $("#v-box-password").hide();
    $("#v-password").val('');
    $("#v-confirm").val('');
  }
  return false;
}

function controlValidPassword() {
  var vpassword = $("#v-password").val();
  var regex = /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/;
  if (vpassword != '') {
    if (regex.test(vpassword)) {
      $("#v-password").addClass('text-success');
      $("#v-password").removeClass('text-danger');
      $("#extra-label-v-password").html('<i>(Passed!)</i>');
    } else {
      $("#v-password").addClass('text-danger');
      $("#v-password").removeClass('text-success');
      $("#extra-label-v-password").html('<i>(Failed!)</i>');
    }
  } else {
    $("#extra-label-v-password").html('');
    $("#v-password").removeClass('text-danger');
    $("#v-password").removeClass('text-success');
  }
  controlConfirmPassword();
  return false;
}

function controlConfirmPassword() {
  var vpassword = $("#v-password").val();
  var vconfirm = $("#v-confirm").val();
  if (vpassword != '' && vconfirm != '') {
    if (vpassword == vconfirm) {
      $("#v-confirm").addClass('text-success');
      $("#v-confirm").removeClass('text-danger');
      $("#extra-label-v-confirm").html('<i>(Passed!)</i>');
    } else {
      $("#v-confirm").addClass('text-danger');
      $("#v-confirm").removeClass('text-success');
      $("#extra-label-v-confirm").html('<i>(Failed!)</i>');
    }
  } else {
    $("#extra-label-v-confirm").html('');
    $("#v-confirm").removeClass('text-success');
    $("#v-confirm").removeClass('text-danger');
  }
  return false;
}

function controlRangeText(id, v) {
  if (v == 'text') {
    $("#range_"+ id).val($("#"+ id).val());
  } else {
    $("#"+ id).val($("#range_"+ id).val());
  }
  return false;
}

function controlRange(id) {
  $("#range_value_"+ id).html($("#range_"+ id).val());
  $("#"+ id).val($("#range_"+ id).val());
  return false;
}

function reloadPortal() {
  window.location.reload(true);
  return false;
}
