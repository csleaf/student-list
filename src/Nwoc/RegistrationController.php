<?php
namespace Nwoc;

class RegistrationController {
    private $db;
    private $template_engine;

    public function __construct(\PDO $pdo, $template_engine) {
        $this->db = $pdo;
        $this->template_engine = $template_engine;
    }

    private function create_student(array &$dict) {
        $student = new Student;
        $student->forename =         isset($dict['forename']) ? strval($dict['forename'])     : NULL;
        $student->surname =           isset($dict['surname']) ? strval($dict['surname'])      : NULL;
        $student->email =               isset($dict['email']) ? strval($dict['email'])        : NULL;
        $student->group_id =         isset($dict['group_id']) ? strval($dict['group_id'])     : NULL;
        $student->exam_results = isset($dict['exam_results']) ? intval($dict['exam_results']) : NULL;
        $student->birth_year =     isset($dict['birth_year']) ? intval($dict['birth_year'])   : NULL;

        if (!isset($dict['gender']))
            $student->gender = NULL;
        else
            $student->gender = DataConversionUtil::map_values(
                strval($dict['gender']),
                array('male'   => Student::GENDER_MALE,
                      'female' => Student::GENDER_FEMALE),
                NULL);

        if (!isset($dict['is_foreign']))
            $student->is_foreign = NULL;
        else
            $student->is_foreign = DataConversionUtil::map_values(
                strval($dict['is_foreign']),
                array('true'  => intval(1),
                      'false' => intval(0)),
                NULL);

        return $student;
    }

    private function register_student(Student $student, string &$cookie) {
        $gateway = new StudentsTableGateway($this->db);
        $gateway->register_student($student, $cookie);
    }

    public function handle() {
        $env = array('title' => 'Регистрация');
        $student = null;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // @TODO XSRF
            try {
                $cookie = "";
                $student = $this->create_student($_POST);
                $this->register_student($student, $cookie);
                setcookie('session', $cookie, time() + 60*60*24*365*10);
                // @TODO change domain
                header('Location: http://localhost/index.php', true, 303);
                return;
            } catch (ValidationException $e) {
                $env['entered_form'] = $student;
                $env['error_fields'] = $e->errors;
            }
        }

        return $this->template_engine->render('register.html', $env);
    }
}
