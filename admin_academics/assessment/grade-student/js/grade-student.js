/*jshint camelcase:false*/

"use strict";

var $modal = $('#current-semester-form-modal')
var $modalBody = $('#current-semester-form-modal-body')
var $studentQueryForm = $('#student-course-query-form')

$('.just-graded-courses-close').click(function() {
  $studentQueryForm.show()
});

(function studentCourseQueryFrom() {
  var tenMostRecentSemesters = JSON.parse($('#tenMostRecentSemesters-container').text());

  $('#semester').autocomplete(
    require('./../../../utilities').sessionSemesterAutoComplete(
      tenMostRecentSemesters, 'label'
    )
  );

  $studentQueryForm.formValidation(
    {
      fields: {
        'student-course-query[semester_id]': {
          excluded: false,
          validators: {
            notEmpty: {message: 'You may only pick from the drop down list'}
          }
        }
      }
    }
  );
})();

(function studentCourseScoreForm() {
  var $courseScores = $('.course-score');
  var invalidFormMsg = '<div class="modal-body-caption no-valid">\n' +
                       '  No score was inputted or updated. You may not submit form!\n' +
                       '</div>';
  var studentRegNo = $('#student-reg-number').text();

  $('.course-score-edit-trigger').click(function() {
    $(this).hide()
      .siblings('.course-score').prop('disabled', false)
      .siblings('.course-score-view-only-trigger').show();
  });

  $('.course-score-view-only-trigger').click(function() {
    var $input = $(this).hide()
      .siblings('.course-score').prop('disabled', true);

    $form.data('formValidation').resetField($input);
    resetExisting($input);

    $input.siblings('.course-score-edit-trigger').show();
  });

  var scoreGradeMapping = JSON.parse($('#scoreGradeMapping-container').text());

  var $form = $('#student-course-score-form').formValidation(
    {
      row: {
        selector: 'td'
      }
    }
  );

  $form.on('success.field.fv', '.course-score', function(e, data) {
    updateRowWithLetterGrade(data.element);
  });

  $form.on('success.form.fv', function(evt) {
    evt.preventDefault();
    var grades = getFreshAndUpdatedGrades();

    if (grades.length === 0) {
      $modalBody.html('');
      $modalBody.html(invalidFormMsg);

    } else {
      $modalBody.html('');
      $modalBody.append(buildValidForm(grades));
    }

    $modal.modal('show');
    $form.data('formValidation').resetForm();
  });

  $('#student-course-score-form-reset-btn').click(function() {
    $form.data('formValidation').resetForm();

    $courseScores.each(function() {
      var $el = $(this);

      if ($el.is('.already-graded')) resetExisting($el);

      else $el.val('').parent().siblings('.grade').text('');
    });
  });

  /**
   * Reset a row to score existing in the database if course for the row has been scored.
   *
   * @param {jQuery} $el
   */
  function resetExisting($el) {
    var existingVal = $el.data('existing-score');
    $el.val(existingVal);

    if (existingVal) {
      updateRowWithLetterGrade($el);
      $el.prop('disabled', true)
        .siblings('.course-score-view-only-trigger').hide()
        .siblings('.course-score-edit-trigger').show();
    }
  }

  /**
   *
   * @param {jQuery} $el
   */
  function updateRowWithLetterGrade($el) {
    var val = $el.val().trim();

    if (val) {
      var scoreGrade = scoreToLetterGrade(val);

      if (scoreGrade) {
        var
          score = scoreGrade[0],
          grade = scoreGrade[1];

        $el.val(score);
        $el.parent().next().text(grade);
      }

    }
  }

  /**
   *
   * @param {String|number} score - student's score in course. Must be a number or numeric string
   * @returns {Array|null}
   */
  function scoreToLetterGrade(score) {
    score = Number(score);

    if (isNaN(score)) {
      return null;
    }

    var scoreGrade = _.find(scoreGradeMapping, function(minMax) {
      var
        min = minMax[0],
        max = minMax[1];

      return min <= score && score <= max;
    });

    score = score.toFixed(2);
    return [score, scoreGrade ? scoreGrade[2] : 'F'];
  }

  function getFreshAndUpdatedGrades() {
    var grades = [];
    var $el, newScore, oldScore, updated;

    $courseScores.each(function() {
      $el = $(this);
      newScore = $el.val().trim();

      if ($el.is('.already-graded')) {
        newScore = Number(newScore);
        oldScore = Number($el.data('existing-score').trim());
        updated = newScore !== oldScore;

        if (updated) grades.push($el.closest('tr').clone());

      } else {
        if (newScore) grades.push($el.closest('tr').clone());
      }
    });

    return grades;
  }

  /**
   *
   * @param {Object} grades
   */
  function buildValidForm(grades) {
    var $scoreInputValidForm = $($('#scores-input-valid-form-template').html());
    var $tdExistingScore, $scoreInputTd, $scoreInput, $children;

    var $tBody = $scoreInputValidForm.find('tbody');

    _.each(grades, function($tr, index) {
      $children = $tr.children();

      $children.first().text(index + 1);

      $scoreInputTd = $children.filter('.fresh-score');
      $scoreInput = $scoreInputTd.children('.course-score').attr('type', 'hidden');
      $scoreInput.siblings().remove();
      $scoreInputTd.append($scoreInput.val());

      $tdExistingScore = $children.filter('.td-existing-score');

      if ($tdExistingScore.is('.already-graded-td')) $tdExistingScore.text($tdExistingScore.children().val());
      else $tdExistingScore.text('');

      $tBody.append($tr);
    });

    var $table = $scoreInputValidForm.children('table').clone();

    var lenGrades = grades.length;
    var coursesPluralize = 'course' + (lenGrades > 1 ? 's' : '');

    $table.children('caption').text(
      lenGrades + ' ' + coursesPluralize + ' newly graded/updated for student registration number "' + studentRegNo + '"'
    );
    $scoreInputValidForm
      .find('#student-score-table-text')
      .val($table.get(0).outerHTML);

    return $scoreInputValidForm;
  }
})();
