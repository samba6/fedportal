/*jshint camelcase:false*/

"use strict";

(function studentCourseQueryFrom() {
  var tenMostRecentSemesters = JSON.parse($('#tenMostRecentSemesters-container').text());

  $('#semester').autocomplete(
    require('./../../../utilities/js/admin-academics-utilities.js').sessionSemesterAutoComplete(
      tenMostRecentSemesters, 'label'
    )
  );

  $('#student-course-query-form').formValidation(
    {
      fields: {
        'student-course-query[semester_id]': {
          excluded  : false,
          validators: {
            notEmpty: {message: 'You may only pick from the drop down list'}
          }
        }
      }
    }
  );
})();

(function studentCourseScoreForm() {
  var $courseScores = $('.course-score').each(function() {
    var $el = $(this);

    if (/^\d{1,3}(?:\.\d{0,2})?$/.test($el.val().trim())) {
      $el.prop('disabled', true).siblings('.course-score-edit-trigger').show();
    }
  });

  $('.course-score-edit-trigger').click(function() {
    $(this).hide().siblings('.course-score').prop('disabled', false);
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
    var scoreInputted = false;
    $courseScores.not(':disabled').each(function() {
      if ($(this).val().trim()) scoreInputted = true;
    });

    if (!scoreInputted) {
      window.alert('No score was inputted or updated. You may not submit form!');
      evt.preventDefault();
    }
  });

  $('#student-course-score-form-reset-btn').click(function() {
    $form.data('formValidation').resetForm();

    $courseScores.each(function() {
      var $el = $(this);
      var existingVal = $el.data('existing-score');
      $el.val(existingVal);

      if (existingVal) {
        updateRowWithLetterGrade($el);
        $el.prop('disabled', true).siblings('.course-score-edit-trigger').show();
      }
    });
  });

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
    return scoreGrade ? [score, scoreGrade[2]] : [score, 'F'];
  }
})();
