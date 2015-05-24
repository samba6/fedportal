<?php
//require_once(__DIR__ . '/../../login/auth.php');
require(__DIR__ . '/TranscriptToPDF.php');

class AssessmentTranscriptController extends AssessmentController
{
  public static function post()
  {
    if (isset($_POST['student-transcript-query-submit'])) {
      $oldStudentTranscriptQueryData = $_POST['student-transcript-query'];

      $valid = self::validatePostedRegNo($oldStudentTranscriptQueryData);

      if (isset($valid['errors'])) {
        self::renderPage(
          $oldStudentTranscriptQueryData, ['messages' => $valid['errors'], 'posted' => false]
        );
        return;

      } else {

        $regNo = $oldStudentTranscriptQueryData['reg-no'];
        $coursesGrades = self::_groupCourses(
          StudentCourses::getStudentCourses(['reg_no' => $regNo], true, true),
          $regNo
        );

        $profile = (new StudentProfile($regNo))->getCompleteCurrentDetails();

        self::renderPage(
          null, null, ['student' => $profile, 'sessions_semesters_courses_grades' => $coursesGrades]
        );
        return;
      }

    } else if (isset($_POST['student-transcript-download-submit'])) {
      $studentScoresData = json_decode($_POST['student-scores-data'], true);

      new TranscriptToPDF($studentScoresData);
    }
  }

  public static function renderPage(
    array $oldStudentTranscriptQueryData = null,
    array $postStatus = null,
    array $studentScoresData = null
  )
  {
    $currentPage = [
      'title' => 'assessment',

      'link' => 'transcripts'
    ];

    $link_template = __DIR__ . '/transcript-partial.php';

    $pageJsPath = path_to_link(__DIR__ . '/js/transcript.min.js');

    $pageCssPath = path_to_link(__DIR__ . '/css/grade-student-transcript.min.css');

    require(__DIR__ . '/../../home/container.php');
  }

  /**
   * Group student courses into sessions and semesters
   *
   * @param array $courses
   *
   * @param string $regNo - Registration number of the student whose transcript we wish to get
   *
   * @return array - with the following structure:
   * [
   * 'session_code' => [
   *                      'current_level_dept' => []
   *
   *                      'semesters' => [
   *
   *                                        'semester_number' => [
   *                                                                'courses' => [courses...],
   *                                                                'semester_data' => [id=>id, created_at=> etc.],
   *                                                                'gpa_data' => [
   *                                                                                'sum_units' => ,
   *                                                                                'sum_points' => ,
   *                                                                                'gpa' =>
   *                                                                              ]
   *                                                            ]
   *                                    ]
   *                  ]
   * ]
   * @private
   */
  private static function _groupCourses(array $courses, $regNo)
  {
    $coursesBySemester = [];

    foreach ($courses as $course) {
      $semesterId = $course['semester_id'];

      if (!isset($coursesBySemester[$semesterId])) $coursesBySemester[$semesterId] = ['courses' => [$course]];

      else $coursesBySemester[$semesterId]['courses'][] = $course;
    }

    $coursesBySessionsBySemester = [];

    foreach (Semester::getSemesterByIds(array_keys($coursesBySemester), true) as $semester) {
      $session = $semester['session'];
      unset($semester['session']);

      $sessionCode = $session['session'];

      $semesterId = $semester['id'];
      $semesterCoursesData = $coursesBySemester[$semesterId];
      $semesterCoursesData['semester_data'] = $semester;

      if (!isset($coursesBySessionsBySemester[$sessionCode])) {
        $coursesBySessionsBySemester[$sessionCode] = [
          'current_level_dept' => StudentProfile::getCurrentForSession($regNo, $sessionCode),

          'semesters' => [
            $semester['number'] => $semesterCoursesData
          ]
        ];

      } else {
        $coursesBySessionsBySemester[$sessionCode]['semesters'][$semester['number']] = $semesterCoursesData;
      }
    }

    return $coursesBySessionsBySemester;
  }
}
