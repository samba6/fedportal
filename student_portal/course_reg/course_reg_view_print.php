<div class="view-print-courses"
  <?php echo !$already_registered ? 'style="display: none;"' : ''; ?>
  >

  <h4 class="text-center college-name">THE FEDERAL COLLEGE OF DENTAL TECHNOLOGY &amp; THERAPY ENUGU</h4>

  <h4 class="text-center">
    <strong>COURSE REGISTRATION FORM</strong> - DEPARTMENT OF <?php echo $dept_name; ?>
  </h4>

  <table class="img-and-name">
    <tbody>
      <tr>
        <td>
          <img src="<?php echo get_photo($reg_no, true); ?>" alt="<?php echo $student['names']; ?>"/>
        </td>

        <td class="names">
                        <span>
                          <strong>NAMES:</strong> <?php echo strtoupper($student['names']); ?>
                          <p><strong>MATRIC NO:</strong> <?php echo $reg_no; ?> </p>
                        </span>
        </td>
      </tr>
    </tbody>
  </table>

  <div class="row">
    <table class="table table-striped table-condensed table-bordered view-print-course-table">

      <thead>
        <tr>
          <th>#</th>
          <th>Course Code</th>
          <th>Course Title</th>
          <th>Credit Unit</th>
          <th>Lecturer Sign</th>
        </tr>
      </thead>

      <tbody>
        <?php
        $course_seq = 1;

        foreach ($course_data as $courses) {
          $code = $courses['code'];
          $title = $courses['title'];
          $unit = sprintf('%.2f', $courses['unit']);

          echo "<tr>\n" .

               "<td>$course_seq</td>\n" .

               "<td>$code</td>\n" .

               "<td>$title</td>\n" .

               "<td class='text-center'>$unit</td>\n" .

               '<td></td>' .

               "</tr>";

          $course_seq++;
        }
        ?>
      </tbody>
    </table>

    <table class="table table-bordered signature-table">
      <tbody>
        <tr>
          <th>HOD'S CONFIRMATION</th>

          <td></td>

          <th>ACADEMIC ADVISER</th>

          <td></td>

          <th>DEPUTY RECTOR</th>

          <td></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
