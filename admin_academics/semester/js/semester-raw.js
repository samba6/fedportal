/*jshint camelcase:false*/

"use strict";

var
  thisYear = new Date().getFullYear(),
  lastYear = thisYear - 1;

var sessionValidation = {
  callback: function(value) {
    var regExp = /^(\d{4})\/(\d{4})$/.exec(value);

    if (regExp) {
      var
        start = parseInt(regExp[1]),
        end = parseInt(regExp[2]);

      if ((end - start) === 1) {
        var
          thisYear = new Date().getFullYear(),
          startDiff = Math.abs(start - thisYear),
          endDiff = Math.abs(end - thisYear);

        return startDiff < 3 && endDiff < 3;
      }
    }
    return false;
  },

  message: 'Please match pattern e.g ' + lastYear + '/' + thisYear
};

$(document.body).on(
  {
    'click': function() {
      var $el = $(this),
        $fieldSet = $el.closest('.current-semester-panel').find('fieldset'),
        $formControls = $fieldSet.find('.form-control').not('#current-semester-session'),
        $semesterBtn = $('.current-semester-form-btn');

      $fieldSet.closest('.current-semester-form').data('formValidation').resetForm();

      if ($el.is('.glyphicon-edit')) {
        $formControls.each(function() {
                             $(this).prop('disabled', false);
                           }
        );

        $el
          .removeClass('glyphicon-edit')
          .addClass('glyphicon-eye-open')
          .attr('title', 'View only');

        $semesterBtn.show();

      } else {

        $formControls.each(function() {
                             $(this).prop('disabled', true);
                           }
        );

        $el
          .removeClass('glyphicon-eye-open')
          .addClass('glyphicon-edit')
          .attr('title', 'Edit semester');

        $semesterBtn.hide();
      }
    }
  }, '.current-semester-edit-trigger'
);

var twoMostRecentSessions = JSON.parse($('#two-most-recent-sessions').text());

$('.semester-session').autocomplete(
  require('./../../utilities/js/admin-academics-utilities.js').sessionSemesterAutoComplete(twoMostRecentSessions, 'session')
);

(function currentSemesterForm() {
  var $form = $('.current-semester-form').formValidation(
    {
      fields: {
        'current_semester[session]': {
          validators: {
            callback: {
              callback: sessionValidation.callback,
              message: sessionValidation.message
            }
          }
        },

        'current_semester[session_id]': {
          excluded: false,
          validators: {
            notEmpty: {message: 'You may only pick from the drop down list'}
          }
        }
      }
    }
  );

  $form.on('err.field.fv', '#current-semester-session', function(evt) {
             $form.formValidation('revalidateField', $($(evt.target).data('related-input-id')).val(''));
           }
  );

})();

(function newSemesterForm() {
  var $form = $('.new-semester-form').formValidation(
    {
      fields: {
        'new_semester[session]': {
          validators: {
            callback: {
              callback: sessionValidation.callback,
              message: sessionValidation.message
            }
          }
        },

        'new_semester[session_id]': {
          excluded: false,
          validators: {
            notEmpty: {message: 'You may only pick from the drop down list'}
          }
        }
      }
    }
  );

  $form.on('err.field.fv', '#new-semester-session', function(evt) {
             $form.formValidation('revalidateField', $($(evt.target).data('related-input-id')).val(''));
           }
  );
})();

(function currentSemesterSelectUpdate() {
  var $form = $('.current-semester-select-update-form').formValidation(
    {
      fields: {
        'current_semester[session]': {
          validators: {
            callback: {
              callback: sessionValidation.callback,
              message: sessionValidation.message
            }
          }
        },

        'current_semester[session_id]': {
          excluded: false,
          validators: {
            notEmpty: {message: 'You may only pick from the drop down list'}
          }
        }
      }
    }
  );

  var
    $tr,
    trBgColor;

  $(document.body).on(
    {
      'click': function() {
        var
          $el = $(this),
          data = JSON.parse($el.next().text());

        $tr = $el.closest('tr');
        trBgColor = $tr.css('background-color');

        $tr.css('background-color', '#DAC3DB').siblings().css('background-color', trBgColor);

        $('#current-semester-select-update-id').val(data.id);
        $form.find('#number').val(String(data.number));
        $form.find('#start_date').val(moment(data.start_date.date).format('DD-MM-YYYY'));
        $form.find('#end_date').val(moment(data.end_date.date).format('DD-MM-YYYY'));

        $form.show();
      }
    }, '.current-semester-select-update-trigger'
  );

  $('#current-semester-select-update-clear-btn').click(function() {
    $tr.css('background-color', trBgColor);
    $form.hide();
  }
  );
})();
