<?php
namespace Nwoc;

/**
* Provides easy to use database queries against 'students' table.
*/
class StudentsTableGateway {
    const ORDER_ASC = 0;
    const ORDER_DESC = 1;

    private $db;

    public function __construct(\PDO $pdo) {
        $this->db = $pdo;
    }

    /**
    * Registers student in database and returns it's cookie in &$cookie variable.
    */
    public function register_student(Student $student): string {
        $cookie = SecurityUtil::generate_session_id();

        $stmt = $this->db->prepare('INSERT INTO students(forename, surname, email, group_id, exam_results, birth_year, is_foreign, gender, cookie) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute(array(strval($student->forename),
                                    strval($student->surname),
                                    strval($student->email),
                                    strval($student->group_id),
                                    intval($student->exam_results),
                                    intval($student->birth_year),
                                    intval($student->is_foreign),
                                    intval($student->gender),
                                    $cookie));
        $stmt->closeCursor();
        return $cookie;
    }

    /**
    * Updates student with cookie $cookie with data from $student.
    */
    public function update_student(string $cookie, Student $student) {
        $stmt = $this->db->prepare('UPDATE students SET forename=?, surname=?, email=?, group_id=?, exam_results=?, birth_year=?, is_foreign=?, gender=? WHERE cookie=?');
        $stmt->execute(array(strval($student->forename),
                                    strval($student->surname),
                                    strval($student->email),
                                    strval($student->group_id),
                                    intval($student->exam_results),
                                    intval($student->birth_year),
                                    intval($student->is_foreign),
                                    intval($student->gender),
                                    $cookie));
        $stmt->closeCursor();
    }

    /**
    * Parses result from database query and returns complete Student class instance.
    */
    private static function create_student_from_row(array $row): Student {
        $student = new Student;
        $student->forename =     strval($row[0]);
        $student->surname =      strval($row[1]);
        $student->email =        strval($row[2]);
        $student->gender =       intval($row[3]);
        $student->group_id =     strval($row[4]);
        $student->exam_results = intval($row[5]);
        $student->birth_year =   intval($row[6]);
        $student->is_foreign =   intval($row[7]);
        return $student;
    }

    /**
    * Returns student that has specified cookie associated with it
    * or null if student with such cookie does not exist.
    */
    public function get_student_with_cookie(string $cookie): Student {
        $query = $this->db->prepare('SELECT * FROM students WHERE cookie=?');
        $query->execute(array($cookie));
        if (($row = $query->fetch(\PDO::FETCH_NUM))) {
            $query->closeCursor();
            return StudentsTableGateway::create_student_from_row($row);
        } else {
            $query->closeCursor();
            return null;
        }
    }

    /**
    * Queries database to get students ordered by field $order_field, sorted in
    * ascending or descending order, with page and entries limit.
    */
    public function get_all_students(string $order_field = 'forename', int $order_dir = self::ORDER_ASC, int $page = 0, int $limit = 50): array {
        // @TODO CRITICAL: fix SQL injection
        $query = $this->db->query('SELECT forename, surname, group_id, exam_results
            FROM students
            ORDER BY ' . $order_field . ' ' . ($order_dir == self::ORDER_ASC ? 'ASC' : 'DESC') . ' ' .
            'LIMIT ' . ($page * $limit) . ', ' . $limit);

        $students = array();
        while (($row = $query->fetch(\PDO::FETCH_NUM))) {
            $student = new Student;
            $student->forename     = $row[0];
            $student->surname      = $row[1];
            $student->group_id     = $row[2];
            $student->exam_results = $row[3];
            array_push($students, $student);
        }

        $query->closeCursor();
        return $students;
    }

    public function count_students(): int {
        $query = $this->db->query('SELECT COUNT(*) FROM students');
        $result = intval($query->fetch(\PDO::FETCH_NUM)[0]);
        $query->closeCursor();
        return $result;
    }

    /**
    * Searches all students by keyword, ordered by field and has page and limit.
    */
    public function find_students(string $keyword, string $order_by = 'forename', $order_dir = self::ORDER_ASC, $page = 0, $limit = 50) {
        // @TODO CRITICAL: fix SQL injection
        $s = 'SELECT forename, surname, group_id, exam_results
            FROM students
            WHERE CONCAT(forename, \' \', surname, \' \', group_id, \' \', exam_results)
            LIKE :keyword
            ORDER BY ' . $order_by . ' ' . ($order_dir == self::ORDER_ASC ? 'ASC' : 'DESC') . ' ' .
            'LIMIT ' . ($page * $limit) . ', ' . $limit;
        $query = $this->db->prepare($s);

        $keyword = '%'.$keyword.'%';
        $query->bindParam(':keyword', $keyword, \PDO::PARAM_STR);
        $query->execute();

        $students = array();
        while ($row = $query->fetch(\PDO::FETCH_NUM)) {
            $student = new Student;
            $student->forename     = $row[0];
            $student->surname      = $row[1];
            $student->group_id     = $row[2];
            $student->exam_results = $row[3];
            array_push($students, $student);
        }

        $query->closeCursor();
        return $students;
    }
}
