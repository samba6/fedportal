<?php

require_once(__DIR__ . '/../../helpers/databases.php');
require_once(__DIR__ . '/../../helpers/app_settings.php');
require_once(__DIR__ . '/AcademicSession.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

use Carbon\Carbon;

Class Semester
{

  /**
   * @param array $post - array of columns names as keys and column values as array values
   *                      $post = [
   *                                'number' => number,
   *                                'start_date' => Y-m-d,
   *                                'end_date' => Y-m-d,
   *                                'id' => numeric
   *                                'session_id' => numeric
   *                               ]
   * @return array|null
   */
  public static function update(array $post)
  {
    $query = "UPDATE semester SET
                number = :number,
                start_date = :start_date,
                end_date = :end_date,
                session_id = :session_id
                WHERE id = :id";

    self::logger()->addInfo("About to update semester using query: {$query} and params: ", $post);

    $oldStartDate = Carbon::createFromFormat('d-m-Y', $post['start_date']);
    $oldEndDate = Carbon::createFromFormat('d-m-Y', $post['end_date']);

    $post['start_date'] = $oldStartDate->format('Y-m-d');
    $post['end_date'] = $oldEndDate->format('Y-m-d');

    $stmt = get_db()->prepare($query);

    if ($stmt->execute($post)) {
      $post['start_date'] = $oldStartDate;
      $post['end_date'] = $oldEndDate;
      $post['session'] = AcademicSession::get_session_by_id($post['session_id']);

      self::logger()->addInfo("Semester successfully updated.");

      return $post;
    }

    self::logger()->addWarning('Could not update semester');
    return null;
  }

  /**
   * @return \Monolog\Logger
   */
  private static function logger()
  {
    return get_logger('SemesterModel');
  }

  /**
   * @param array $post
   * @return array|null
   */
  public static function create(array $post)
  {
    $db = get_db();

    $now = Carbon::now();

    $query = "INSERT INTO semester(number, start_date, end_date, created_at, updated_at, session_id)
              VALUES (:number, :start_date, :end_date, '$now', '$now', :session_id)";

    self::logger()->addInfo("About to create a new semester using query: {$query} and params: ", $post);

    $old_start_date = Carbon::createFromFormat('d-m-Y', $post['start_date']);
    $old_end_date = Carbon::createFromFormat('d-m-Y', $post['end_date']);

    $post['start_date'] = $old_start_date->format('Y-m-d');
    $post['end_date'] = $old_end_date->format('Y-m-d');

    $stmt = $db->prepare($query);

    if ($stmt->execute($post)) {
      $post['id'] = $db->lastInsertId();
      $post['created_at'] = $now;
      $post['updated_at'] = $now;
      $post['start_date'] = $old_start_date;
      $post['end_date'] = $old_end_date;
      $post['session'] = AcademicSession::get_session_by_id($post['session_id']);

      self::logger()->addInfo("Semester successfully created as: ", $post);

      return $post;
    }

    self::logger()->addError("Query to create semester failed to execute");

    return null;
  }

  public static function getImmediatePastSemester()
  {
    $query1 = "SELECT * FROM semester WHERE end_date < ? ORDER BY end_date DESC LIMIT 1";

    $param = [Carbon::now()->format('Y-m-d')];

    self::logger()->addInfo(
      "About to get immediate past semester with query {$query1}, and param: ", $param
    );

    $stmt = get_db()->prepare($query1);

    if ($stmt->execute($param)) {
      $semester = $stmt->fetch();

      if ($semester) {
        self::logger()->addInfo(
          "Query executed successfully. Immediate past semester is: ", $semester
        );

        $semester = self::dbDatesToCarbon($semester);

        $semester['session'] = AcademicSession::getCurrentSession();

        return $semester;
      }
    }

    self::logger()->addWarning("Immediate past semester not found.");
    return null;
  }

  /**
   * @param array $data
   * @return array
   */
  private static function dbDatesToCarbon(array $data)
  {
    foreach (['start_date', 'end_date', 'created_at', 'updated_at'] as $column) {
      if (isset($data[$column])) {
        $data[$column] = Carbon::parse($data[$column]);
      }
    }
    return $data;
  }


  /**
   * @return array|mixed|null
   */
  public static function getCurrentSemester()
  {
    $session = AcademicSession::getCurrentSession();

    if (!$session) {
      self::logger()->addWarning('Current session not set. Current semester will not be available');
      return null;
    }

    $today = date('Y-m-d', time());

    $query = "SELECT * FROM semester
              WHERE :today1 >= start_date
              AND :today2 <= end_date
              ORDER BY start_date LIMIT 1";

    $query_param = [
      'today1' => $today,
      'today2' => $today
    ];

    self::logger()->addInfo(
      "About to get current semester with query: {$query} and params: ", $query_param
    );

    $stmt = get_db()->prepare($query);

    if ($stmt->execute($query_param)) {
      $semester = $stmt->fetch();

      if ($semester) {
        self::logger()->addInfo("Query successfully ran. semester is: ", $semester);

        $semester = self::dbDatesToCarbon($semester);

        $semester['session'] = $session;

        return $semester;
      }
    }

    self::logger()->addWarning("Current semester not found!");
    return null;
  }

  /**
   * @param string|int $number
   * @param string|int $session
   * @return array|null
   */
  public static function getSemesterByNumberAndSession($number, $session)
  {
    $query1 = "SELECT id FROM session_table WHERE session = ?";

    $query2 = "SELECT * FROM semester
               WHERE number = ?
               AND session_id = ({$query1})";

    $params = [$number, $session];

    self::logger()->addInfo("About to get semester using query: {$query2} and params: ", $params);

    $stmt = get_db()->prepare($query2);

    if ($stmt->execute($params)) {
      $result = $stmt->fetch();

      if ($result) {
        self::logger()->addInfo("Statement executed successfully, result is: ", $result);
        return $result;
      }
    }

    self::logger()->addWarning("Can not get semester.");
    return null;
  }

  /**
   * Validates start and end dates of semester
   *
   * @param array $data - an array that must have two keys: start_date and end_date for semester database date columns
   *
   * @param bool $newSession - a flag indicating whether the data will be used to create a new session or update
   * an existing session.
   *
   * @return array - we return the data array without modification
   */
  public static function validateDates(array $data, $newSession = false)
  {
    $returnedVal['valid'] = false;

    if (!(isset($data['start_date']) && isset($data['end_date']))) {
      $returnedVal['messages'] = ['Start date and end date can not be null'];
      return $returnedVal;
    }

    $start_date = trim($data['start_date']);

    if (!$start_date) {
      $returnedVal['messages'] = ['Start date can not be empty'];
      return $returnedVal;
    }

    $end_date = trim($data['end_date']);

    if (!$end_date) {
      $returnedVal['messages'] = ['End date can not be empty'];
      return $returnedVal;
    }

    try {
      $dt_start = Carbon::createFromFormat('d-m-Y', $start_date);
      $dt_end = Carbon::createFromFormat('d-m-Y', $end_date);

      if ($dt_start >= $dt_end) {
        $returnedVal['messages'] = ['End date must be after start date'];
        return $returnedVal;
      }

      if ($newSession) {
        $latest_end_date = self::getLatestSemesterEndDate();

        if ($latest_end_date && $latest_end_date > $dt_start) {
          $returnedVal['messages'] = [
            "A new semester may only start after "
            . $latest_end_date->format('d-M-Y')
            . " But you specified " . $dt_start->format('d-M-Y')
          ];

          return $returnedVal;
        }
      }

    } catch (InvalidArgumentException $e) {

      $returnedVal['messages'] = ["Start date or end date has invalid date format. Allowed format is 'DD-MM-YYYY'"];
      return $returnedVal;

    } catch (PDOException $e) {
      $returnedVal['messages'] = ['Start or end date could not be validated due to database error'];
      return $returnedVal;
    }

    return ['valid' => true];
  }

  /**
   * @return null|\Carbon\Carbon
   */
  public static function getLatestSemesterEndDate()
  {
    $query = "SELECT MAX(end_date) FROM semester";

    self::logger()->addInfo("About to get latest semester end date with query: {$query}");

    $stmt = get_db()->query($query);

    if ($stmt) {
      $result = $stmt->fetch(PDO::FETCH_NUM);

      if ($result && $result[0]) {
        $dt = Carbon::createFromFormat('Y-m-d', $result[0]);
        self::logger()->addInfo(
          "query executed successfully. Latest semester end date is {$dt}"
        );
        return $dt;
      }
    }

    self::logger()->addInfo("Latest semester date not found may be no semester set yet.");
    return null;
  }

  /**
   * Validates whether the semester number is 1 or 2. Also enforces other business rules on semester number column
   *
   * @param array $data - the data array that will be passed to the database. Contains a key 'number'
   *
   * @param bool $newSemester - indicates whether data will be used to create new semester or update an existing semester
   * Some business rules e.g existence rule, can not be enforced for update
   *
   * @return array - we return back the data to the caller unmodified.
   */
  public static function validateNumberColumn(array $data, $newSemester = false)
  {

    $returnedVal['valid'] = false;

    if (!isset($data['number'])) {
      $returnedVal['messages'] = ["'Semester number' can not be null"];
      return $returnedVal;
    }

    $number = trim($data['number']);

    if (!$number) {
      $returnedVal['messages'] = ["'Semester number' can not be empty"];
      return $returnedVal;
    }

    if (!preg_match("/^[12]$/", $number)) {
      $returnedVal['messages'] = ['"Semester number" can only take two values: "1" or "2"'];
      return $returnedVal;
    }

    if ($newSemester) {
      if (self::semesterExists($number, $data['session_id'])) {
        $returnedVal['messages'] = [
          'The specified semester exists for the specified session: ' .
          self::renderSemesterNumber($number) . ' semester!'
        ];
        return $returnedVal;
      }
    }

    return ['valid' => true];
  }

  /**
   * @param string|int $number
   * @param string|int $sessionId
   * @return bool|null
   */
  public static function semesterExists($number, $sessionId)
  {
    $query = "SELECT COUNT(*) FROM semester
              WHERE number = ?
              AND session_id = ?";

    $param = [$number, $sessionId];

    self::logger()->addInfo("About to confirm is semester exists with query: {$query} and params: ", $param);

    $stmt = get_db()->prepare($query);

    if ($stmt->execute($param)) {
      $returnedVal = $stmt->fetchColumn() ? true : false;

      self::logger()->addInfo("Query ran successfully. Result is: {$returnedVal}");

      return $returnedVal;
    }

    self::logger()->addWarning("Query failed to run or empty result.");

    return null;
  }

  /**
   * Takes a semester number and turns 1 into 1st and 2 into 2nd
   * @param int|string $number - the semester number (whether 1 or 2)
   * @return string
   */
  public static function renderSemesterNumber($number)
  {
    return $number == 1 ? '1st' : '2nd';
  }

  /**
   * @param array $data
   * @return array
   */
  public static function validateSessionIdColumn(array $data)
  {
    $returnedVal['valid'] = false;

    if (!isset($data['session_id'])) {
      $returnedVal['messages'] = ["Session ID can not be null."];
      return $returnedVal;
    }

    $id = trim($data['session_id']);

    if (!$id) {
      $returnedVal['messages'] = ["Session ID can not be empty."];
      return $returnedVal;
    }

    if (!is_numeric($id)) {
      $returnedVal['messages'] = ["Session ID can only take numeric characters."];
      return $returnedVal;
    }

    if (!AcademicSession::session_exists_by_id($id)) {
      $returnedVal['messages'] = ["Session ID does not exist."];
      return $returnedVal;
    }

    try {

      $session = AcademicSession::get_session_by_id($id);

      $sessionStart = $session['start_date'];
      $semesterStart = Carbon::createFromFormat('d-m-Y', $data['start_date']);
      $semesterStart->hour = 0;
      $semesterStart->minute = 0;
      $semesterStart->second = 0;

      if ($semesterStart < $sessionStart) {
        $returnedVal['messages'] = ['Semester can not start before session.'];
        return $returnedVal;
      }

      $sessionEnd = $session['end_date'];
      $semesterEnd = Carbon::createFromFormat('d-m-Y', $data['end_date']);
      $semesterEnd->hour = 0;
      $semesterEnd->minute = 0;
      $semesterEnd->second = 0;

      if ($semesterEnd > $sessionEnd) {
        $returnedVal['messages'] = ['Semester can not end after session.'];
        return $returnedVal;
      }

    } catch (InvalidArgumentException $e) {
      $returnedVal['messages'] = ['Semester start or end dates invalid'];
      return $returnedVal;

    } catch (PDOException $e) {
      logPdoException($e, 'Database error occurred while validating session for semester', self::logger());
    }

    return ['valid' => true];
  }
}
