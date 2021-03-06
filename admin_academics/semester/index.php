<?php
require_once(__DIR__ . '/../login/auth.php');
require_once(__DIR__ . '/../../helpers/databases.php');
require_once(__DIR__ . '/../../helpers/app_settings.php');
require_once(__DIR__ . '/../models/Semester.php');
require_once(__DIR__ . '/../models/AcademicSession.php');

class SemesterController
{
  public function post()
  {
    if (isset($_POST['new-semester-form-submit'])) {
      $newSemester = $_POST['new_semester'];

      $status = self::createNewSemester($newSemester);

      $oldNewSemesterData = $status['posted'] ? null : $newSemester;

      $postStatus['new_semester'] = $status;

      $this->renderPage(null, $oldNewSemesterData, $postStatus);

    } else if (isset($_POST['current-semester-form-submit'])) {
      $currentSemester = $_POST['current_semester'];

      $status = self::updateSemester($currentSemester);

      $oldCurrentSemesterData = $status['posted'] ? null : $currentSemester;

      $postStatus['current_semester'] = $status;

      $this->renderPage($oldCurrentSemesterData, null, $postStatus);

    }
  }

  private static function createNewSemester($post)
  {
    $valid = self::validatePost($post, true);

    if ($valid !== true) {
      return [
        'posted' => false,

        'messages' => $valid
      ];
    }

    if (isset($post['session'])) {
      unset($post['session']);
    }

    try {
      $semester = Semester::create($post);

      if ($semester) {
        $number = Semester::renderSemesterNumber($semester['number']);

        return [
          'posted' => true,

          'messages' => [
            "{$number} semester for {$semester['session']['session']} session successfully created."
          ]
        ];
      }


    } catch (PDOException $e) {
      logPdoException($e, "Error occurred while creating new semester.", self::logger());
    }

    return [
      'posted' => false,

      'messages' => ['Database error. Unable to create semester']
    ];
  }

  private static function validatePost($post, $isNewSemester = false)
  {
    $valid = Semester::validateNumberColumn($post, $isNewSemester);

    if (!$valid['valid']) {
      return $valid['messages'];
    }

    $valid = Semester::validateSessionIdColumn($post);

    if (!$valid['valid']) {
      return $valid['messages'];
    }

    $valid = Semester::validateDates($post, $isNewSemester);

    if (!$valid['valid']) {
      return $valid['messages'];
    }

    return true;
  }

  private static function logger()
  {
    return get_logger('AdminAcademicsSemesterController');
  }

  /**
   * Callers of this function will call it with zero or 2 arguments. Callers using 2 arguments are
   * those involved in executing http method 'POST'. The 2 arguments are required to re-render post data
   * back to users and indicate whether post succeeded or not.
   *
   * @param array|null $oldCurrentSemesterData - if data needed to update current semester not valid, then
   *    $oldCurrentSemesterData refers to that invalid data. This will then be re-rendered to user.
   *
   * @param array|null $oldNewSemester - if data needed to create current semester not valid, then $oldNewSemester
   *    refers to that invalid data. This will then be re-rendered to user.
   *
   * @param array|null $postStatus -   whether semester successfully updated or created.
   */
  public function renderPage(array $oldCurrentSemesterData = null, array $oldNewSemester = null, array $postStatus = null)
  {
    $current_semester = $oldCurrentSemesterData ? null : self::getCurrentSemester();

    $semestersInCurrentSession = null;

    if (!$current_semester) {
      list($semestersInCurrentSession, $currentSession) = self::getSemestersInCurrentSession();
    }

    $twoMostRecentSessions = self::getSessionsForJSAutoComplete(2);

    $currentPage = [
      'title' => 'semester',

      'link' => 'new-semester'
    ];

    $link_template = __DIR__ . '/semester-partial.php';

    $pageJsPath = path_to_link(__DIR__ . '/js/semester.min.js', true);

    $pageCssPath = path_to_link(__DIR__ . '/css/semester.min.css', true);

    require(__DIR__ . '/../home/container.php');
  }

  private static function getCurrentSemester()
  {
    try {
      $semester = Semester::getCurrentSemester();

      if ($semester) {
        return $semester;
      }
    } catch (PDOException $e) {
      logPdoException($e, 'Error occurred while getting current semester', self::logger());
    }

    return null;
  }

  /**
   *
   * @return array
   */
  private static function getSemestersInCurrentSession()
  {
    try {
      $currentSession = AcademicSession::getCurrentSession();

      if ($currentSession) {
        $sessionId = $currentSession['id'];
        return [Semester::getSemestersInSession($sessionId), $currentSession];
      }

    } catch (PDOException $e) {
      logPdoException($e, "Database error while retrieving semesters in current session", self::logger());
    }

    return [null, null];
  }

  /**
   * Jquery UI autocomplete plugin requires the source to be an object with keys 'label' and 'value'
   *
   * @param int|string $howMany
   * @return array
   */
  private static function getSessionsForJSAutoComplete($howMany)
  {
    $academicSessions = [];

    try {
      $academicSessions = AcademicSession::getSessions($howMany);

      if ($academicSessions) {
        $labelledAcademicSession = [];

        foreach ($academicSessions as $aSession) {
          $aSession['label'] = $aSession['session'];
          $aSession['value'] = $aSession['session'];

          $labelledAcademicSession[] = $aSession;
        }

        $academicSessions = $labelledAcademicSession;

        self::logger()->addInfo('Two most recent academic sessions for jquery ui autocomplete: ', $academicSessions);
      }

    } catch (PDOException $e) {

      logPdoException(
        $e, 'Error occurred while retrieving the two most recent academic sessions', self::logger());
    }

    return $academicSessions;
  }

  private static function updateSemester(array $post)
  {
    $valid = self::validatePost($post);

    if ($valid !== true) {
      return [
        'posted' => false,

        'messages' => $valid
      ];
    }

    if (isset($post['session'])) {
      unset($post['session']);
    }

    try {

      $semester = Semester::update($post);

      if ($semester) {
        $number = Semester::renderSemesterNumber($semester['number']);

        return [
          'posted' => true,

          'messages' => [
            "{$number} semester for {$semester['session']['session']} session successfully updated."
          ]
        ];
      }

    } catch (PDOException $e) {

      logPdoException($e, "DB error occurred while updating semester", self::logger());
    }

    return [
      'posted' => false,

      'messages' => ['Database error. Unable to update semester']
    ];
  }
}

$semester = new SemesterController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $semester->renderPage();

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $semester->post();
}
