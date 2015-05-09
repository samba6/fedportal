<div class="student-course-score-form-container">
  <form id="student-course-score-form"
        class="form-horizontal student-course-score-form"
        method="post"
        role="form"
        data-fv-trigger="blur"
        data-fv-framework="bootstrap"
        data-fv-message="This value is not valid"
        data-fv-icon-valid="glyphicon glyphicon-ok"
        data-fv-icon-invalid="glyphicon glyphicon-remove"
        data-fv-icon-validating="glyphicon glyphicon-refresh">

    <fieldset>
      <legend>Courses Student Registered <br/> Enter score for each course.</legend>

      <table class="table table-striped table-condensed table-bordered student-course-score-form-table">
        <thead>
          <tr>
            <th>S/N</th>
            <th>Code</th>
            <th>Title</th>
            <th class="student-courses-display-existing-score">Existing Score</th>
            <th>New Score</th>
            <th>Grade</th>
          </tr>
        </thead>

        <tbody>
          <?php
          $count = 1;

          foreach ($studentCoursesData['courses'] as $course) {
            echo "
            <tr>
                <td>{$count}</td>
                <td>{$course['code']}</td>
                <td>{$course['title']}</td>

                <td>
                <input disabled value='{$course['score']}' class='form-control existing-score' />
                </td>

                <td>
                    <input class='form-control course-score' name='student-courses[{$course['id']}]' maxlength='6'
                        value='{$course['score']}' data-existing-score='{$course['score']}'

                        data-fv-regexp
                        data-fv-regexp-regexp='^\d{1,3}(?:\.\d{0,2})?$'
                        data-fv-regexp-message='Score must be between 0.00 and 100.00'

                        data-fv-between
                        data-fv-between-message='Score must be between 0.00 and 100.00'
                        data-fv-between-min='0'
                        data-fv-between-max='100'
                      />

                     <span class='glyphicon glyphicon-edit course-score-edit-trigger'></span>
                </td>

                <td></td>
            </tr>
           ";
            $count++;
          }

          $studentCoursesDataJSONEncoded = json_encode($studentCoursesData);
          echo "<input type='hidden' value='{$studentCoursesDataJSONEncoded}' name='student-courses-data' />";
          ?>
        </tbody>
      </table>
    </fieldset>

    <div class="form-group">
      <div class="col-sm-5 col-sm-offset-4">
        <div class="btn-group">
          <button class="btn btn-success" type="submit" name="student-course-score-form-submit">Submit</button>
          <button class="btn btn-default" type="button" id="student-course-score-form-reset-btn">Reset</button>
        </div>
      </div>
    </div>
  </form>
</div>
