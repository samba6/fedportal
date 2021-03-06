(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({"C:\\wamp\\www\\fedportal\\admin_academics\\semester\\js\\semester-raw.js":[function(require,module,exports){
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

},{"./../../utilities/js/admin-academics-utilities.js":"C:\\wamp\\www\\fedportal\\admin_academics\\utilities\\js\\admin-academics-utilities.js"}],"C:\\wamp\\www\\fedportal\\admin_academics\\utilities\\js\\admin-academics-utilities.js":[function(require,module,exports){
"use strict";

/**
 *
 * @param {Array} source
 * @param {String} fieldToDisplay - the field from the source that will be set as value
 * of form control been auto-completed
 *
 * @returns {{minLength: number, source: Array, select: Function}}
 */
function sessionSemesterAutoComplete(source, fieldToDisplay) {
  return {
    minLength: 1,

    source: source,

    select: function(evt, ui) {
      var
        $el      = $(this),
        $related = $($el.data('related-input-id'));

      $related.val(ui.item.id);

      if (evt.originalEvent.which === 1) {
        window.setTimeout(function() {
                            $el.val(ui.item[fieldToDisplay]);
                          }
        );
      }

      window.setTimeout(function() {
                          $el.closest('form').formValidation('revalidateField', $el);
                          $el.closest('form').formValidation('revalidateField', $related);
                        }
      );

      return false;
    }
  };
}

module.exports = {
  sessionSemesterAutoComplete: sessionSemesterAutoComplete
};

},{}]},{},["C:\\wamp\\www\\fedportal\\admin_academics\\semester\\js\\semester-raw.js","C:\\wamp\\www\\fedportal\\admin_academics\\utilities\\js\\admin-academics-utilities.js"]);
