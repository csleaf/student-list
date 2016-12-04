<?php
namespace Nwoc;

/**
* Provides easy to use database queries against 'students' table.
*/
class StudentsTableGateway {
    const NAME_REGEXP = "/^[А-Яа-я' ]+$/u";
    const GROUP_REGEXP = "/^[А-Яа-я0-9]+$/u";
    const EMAIL_REGEXP = "/@/";
    const ORDER_ASC = 0;
    const ORDER_DESC = 1;

    private $db;

    public function __construct(\PDO $pdo) {
        $this->db = $pdo;
    }

    /**
    * Validates student against predefined set of rules. Throws ValidationException with
    * errors property that contains validation errors if student validation failed.
    * $check_email_registration parameter handles checking that email is already registered
    * within system.
    */
    public function validate_student(Student $student, bool $check_email_registration) {
        $errors = array();

        // forename
        $validator = new ObjectValidator('UTF-8');
        $validator->min_length(1, 'Вы не задали своё имя.')
                  ->max_length(64, 'Имя не должно быть длиннее :inparam букв (вы ввели :outparam букв).')
                  ->regexp_match(self::NAME_REGEXP, 'Имя может включать в себя только буквы от А до Я, пробел, дефис и апостроф.')
                  ->validate($student->forename, $errors, 'forename');

        // surname
        $validator->clear();
        $validator->min_length(1, 'Вы не задали свою фамилию.')
                  ->max_length(64, 'Фамилия не должна быть длиннее :inparam букв, вы ввели :outparam букв.')
                  ->regexp_match(self::NAME_REGEXP, 'Фамилия может включать в себя только буквы от А до Я, пробел, дефис и апостроф.')
                  ->validate($student->surname, $errors, 'surname');

        // email
        $validator->clear();
        $validator->min_length(1, 'Вы не задали свой e-mail.')
                  ->max_length(64, 'Вы ввели слишком длинный e-mail, максимальное кол-во символов: :inparam, вы ввели: :outparam символов.')
                  ->regexp_match(self::EMAIL_REGEXP, 'В e-mail должен присутствовать символ \'@\'.');

        if ($validator->validate($student->email, $errors, 'email') === 0 && $check_email_registration) {
            // @TODO optimize query
            $query = $this->db->prepare('SELECT COUNT(email) FROM students WHERE email=?');
            $query->execute(array(strval($student->email)));
            $result = $query->fetch(\PDO::FETCH_NUM);
            if (intval($result[0]) != 0) {
                $errors['email'] = array('Студент с таким E-mail уже зарегистрирован.');
            }
            $query->closeCursor();
        }

        // gender
        if (!($student->gender === Student::GENDER_MALE || $student->gender === Student::GENDER_FEMALE))
        {
            $errors['gender'] = array('Вы не выбрали свой пол.');
        }

        // group id
        if (!isset($student->group_id)) {
            $errors['group_id'] = array('Вы не указали название своей группы.');
        } else {
            $validator->clear();
            $validator->min_length(2, 'Название группы состоит как минимум из :inparam символов, вы ввели :outparam символов')
                      ->max_length(5, 'Название группы должно быть не более :inparam символов, вы ввели :outparam символов.')
                      ->regexp_match(self::GROUP_REGEXP, 'Название группы должно включать лишь буквы от А до Я и цифры.')
                      ->validate($student->group_id, $errors, 'group_id');
        }

        // exam_results
        if (!isset($student->exam_results)) {
            $errors['exam_results'] = array('Вы не ввели свои баллы по ЕГЭ.');
        } elseif (!ctype_digit(intval($student->exam_results))
              && (intval($student->exam_results) <= 0 || intval($student->exam_results) > 315))
        {
            $errors['exam_results'] = array('Введите свои баллы по ЕГЭ от 0 до 315.');
        }

        // birth_year
        if (!isset($student->birth_year)) {
            $errors['birth_year'] = array('Вы не указали ваш год рождения.');
        } elseif (!ctype_digit($student->birth_year)) {
            $errors['birth_year'] = array('Год рождения должен состоять только из цифр.');
        } elseif (intval($student->birth_year) < 1900 ||
                  intval($student->birth_year) > 2015)
        {
            $errors['birth_year'] = array('Год рождения должен быть указан в диапазоне от 1900 до 2015 года.');
        }

        // is_foreign
        if (!isset($student->is_foreign)
          && !(intval($student->is_foreign) === 0 /* false */
          ||   intval($student->is_foreign) === 1 /* true  */))
        {
            $errors['is_foreign'] = array('Вы не указали свой статус.');
        }

        if (count($errors) !== 0) {
            throw new ValidationException($student, $errors);
        }
    }

    /**
    * Registers student in database and returns it's cookie in &$cookie variable.
    */
    public function register_student(Student $student): string {
        $this->validate_student($student, true);
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

        return $cookie;
    }

    /**
    * Updates student with cookie $cookie with data from $student.
    */
    public function update_student(string $cookie, Student $student) {
        $this->validate_student($student, false);

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
