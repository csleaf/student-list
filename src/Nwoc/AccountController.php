<?php
namespace Nwoc;

/**
* Handles student logged-in page.
*/
class AccountController {
    private $db;
    private $student;
    private $gateway;
    private $template_engine;

    public function __construct(\PDO $db, $template_engine, StudentsTableGateway $gateway, Student $targetStudent) {
        $this->db = $db;
        $this->student = $targetStudent;
        $this->gateway = $gateway;
        $this->template_engine = $template_engine;
    }

    public function handle(): string {
        if ($this->student == null) {
            throw new Exception('Student does not exist.');
        }

        return $this->template_engine->render('account.html', array(
            'student' => $this->student,
            'is_logged_in' => true
        ));
    }
}
