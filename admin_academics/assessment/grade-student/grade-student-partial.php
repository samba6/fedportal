<?php
//$gradeCoursesContext is exported by the php script that renders this page ie.
//GradeStudent.php -> class AssessmentGradeStudentController -> method renderPage

$tenMostRecentSemesters = $gradeCoursesContext['ten_most_recent_semesters'];
$scoreGradeMapping = $gradeCoursesContext['score_grade_mapping'];
$postStatus = isset($gradeCoursesContext['post_status']) ? $gradeCoursesContext['post_status'] : null;

$oldStudentCourseQueryData = isset($gradeCoursesContext['old_student_course_query_data']) ?
  $gradeCoursesContext['old_student_course_query_data'] : null;

$studentCoursesData = isset($gradeCoursesContext['student_courses_data']) ?
  $gradeCoursesContext['student_courses_data'] : null;

$justGradedCourses = isset($gradeCoursesContext['just_graded_courses']) ? $gradeCoursesContext['just_graded_courses'] : '';

$displayJustGradedCoursesStyle = $justGradedCourses ? 'block' : 'none';
$initialDisplayFormStyle = $justGradedCourses ? 'none' : 'block';
?>

<span style="display: none" id="tenMostRecentSemesters-container">
  <?php echo json_encode($tenMostRecentSemesters) ?>
</span>

<span style="display: none" id="scoreGradeMapping-container">
  <?php echo json_encode($scoreGradeMapping) ?>
</span>

<div style="display: <?php echo $displayJustGradedCoursesStyle ?>"
     class="just-graded-courses-alert alert alert-dismissible alert-success">

  <button type="button" class="close just-graded-courses-close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
  <?php echo $justGradedCourses ?>
</div>

<form id="student-course-query-form" style="display: <?php echo $initialDisplayFormStyle ?>"
      class="form-horizontal"
      method="post"
      role="form"
      data-fv-framework="bootstrap"
      data-fv-message="This value is not valid"
      data-fv-icon-valid="glyphicon glyphicon-ok"
      data-fv-icon-invalid="glyphicon glyphicon-remove"
      data-fv-icon-validating="glyphicon glyphicon-refresh">

  <fieldset>
    <legend>Input student data to get courses</legend>

    <div>
      <?php AssessmentGradeStudentController::renderPostStatus($postStatus); ?>
    </div>

    <div class="form-group">
      <label class="control-label col-sm-4" for="reg-no">Registration Number</label>

      <div class="col-sm-7">
        <input class="form-control" id="reg-no" required
               name="student-course-query[reg-no]"
               value="<?php echo $oldStudentCourseQueryData ? $oldStudentCourseQueryData['reg-no'] : '' ?>"
               placeholder="Student registration Number"/>
      </div>
    </div>

    <div class="form-group">
      <label class="control-label col-sm-4" for="semester">Semester</label>

      <div class="col-sm-7">
        <input class="form-control semester" required
               placeholder="Type session e.g 2012/2013 to select semesters"
               name="student-course-query[semester]" maxlength="24"
               id="semester" data-related-input-id="#semester-id"
               value="<?php echo $oldStudentCourseQueryData ? $oldStudentCourseQueryData['semester'] : '' ?>"

               data-fv-regexp
               data-fv-regexp-regexp="\d{4}/\d{4} - \d[stnd]{2} semester"
               data-fv-regexp-message="Please enter a pattern such as 2012/2013..."
          >

        <input type="hidden" id="semester-id"
               name="student-course-query[semester_id]"
               value="<?php echo $oldStudentCourseQueryData ? $oldStudentCourseQueryData['semester_id'] : '' ?>"/>
      </div>
    </div>
  </fieldset>

  <div class="form-group">
    <div class="current-semester-form-btn col-sm-5 col-sm-offset-4">
      <button class="btn btn-default" type="submit" name="reg-no-submit">Submit</button>
    </div>
  </div>
</form>

<?php
if ($studentCoursesData) {
  require __DIR__ . '/student-courses-score-display-input.php';
}
?>
