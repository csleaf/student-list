<?php
namespace Nwoc;

/**
* Manages student profile, handles registering and editing student information.
*/
class ProfileController {
    private $db;
    private $template_engine;

    public function __construct(\PDO $pdo, $template_engine) {
        $this->db = $pdo;
        $this->template_engine = $template_engine;
    }

    /**
    * Creates new student from array, usually rethieved from $_POST global variable.
    * All fields that are not set or has invalid type, will have value of NULL.
    */
    private function create_student(array &$dict): Student {
        $student = new Student;
        $student->forename =         isset($dict['forename']) ? trim(strval($dict['forename'])) : NULL;
        $student->surname =           isset($dict['surname']) ? trim(strval($dict['surname']))  : NULL;
        $student->email =               isset($dict['email']) ? trim(strval($dict['email']))    : NULL;
        $student->group_id =         isset($dict['group_id']) ? trim(strval($dict['group_id'])) : NULL;
        $student->exam_results = isset($dict['exam_results']) ? intval($dict['exam_results'])   : NULL;
        $student->birth_year =     isset($dict['birth_year']) ? intval($dict['birth_year'])     : NULL;

        if (!isset($dict['gender']))
            $student->gender = NULL;
        else
            $student->gender = DataConversionUtil::map_values(
                strval($dict['gender']),
                array('0' => Student::GENDER_MALE,
                      '1' => Student::GENDER_FEMALE),
                NULL);

        if (!isset($dict['is_foreign']))
            $student->is_foreign = NULL;
        else
            $student->is_foreign = DataConversionUtil::map_values(
                strval($dict['is_foreign']),
                array('1' => intval(1),
                      '0' => intval(0)),
                NULL);

        return $student;
    }

    /**
    * Handles user request and returns view representation.
    */
    public function handle() {
        $env = array();
        $gateway = new StudentsTableGateway($this->db);
        if (isset($_COOKIE['session'])) {
            $student = $gateway->get_student_with_cookie(strval($_COOKIE['session']));
            $env['entered_form'] = $student;
        }
        $env['is_logged_in'] = isset($student);

        $token = NULL;
        if (!isset($_COOKIE['token'])) {
            $token = SecurityUtil::generate_token();
            setcookie('token', $token, time() + 60*60*4, "", "", false, true);
        } else {
            $token = strval($_COOKIE['token']);
            setcookie('token', $token, time() + 60*60*4, "", "", false, true);
        }
        $env['token'] = $token;

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            $post_token = isset($_POST['token']) ? $_POST['token'] : NULL;
            if (!isset($post_token) || $post_token != $token) {
                $form = $this->create_student($_POST);
                $env['notify'] = 'xsrf_check_failed';
            } else {
                $action = strval($_POST['action']);
                if ($action == 'register' && !isset($student)) {
                    try {
                        $student = $this->create_student($_POST);
                        StudentValidator::validate($student, $gateway, true);
                        $cookie = $gateway->register_student($student, $cookie);
                        $origin = SiteUtil::get_server_origin_url($_SERVER);
                        setcookie('session', $cookie, time() + 60*60*24*365*10, "", "", false, true);
                        header("Location: $origin/index.php?notify=registered", true, 303);
                        return;
                    } catch (ValidationException $e) {
                        $env['entered_form'] = $student;
                        $env['error_fields'] = $e->errors;
                    }
                }
                else if ($action == 'edit' && isset($student)) {
                    try {
                        $student = $this->create_student($_POST);
                        StudentValidator::validate($student, $gateway, false);
                        $gateway->update_student($_COOKIE['session'], $student);
                        $env['entered_form'] = $student;
                        $env['notify'] = 'edit_successful';
                    } catch (ValidationException $e) {
                        $env['entered_form'] = $e->target;
                        $env['error_fields'] = $e->errors;
                    }
                }
            }
        }

        return $this->template_engine->render('profile.twig', $env);
    }
}
