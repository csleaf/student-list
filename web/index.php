<?php
require_once __DIR__.'/../src/bootstrap.php';

use Nwoc\StudentsTableGateway;
use Nwoc\Student;
use Nwoc\ListController;

$bs = new Bootstrap;
$controller = new ListController($bs->get_database(), $bs->get_template_engine());
echo $controller->handle();

// $bs = new Bootstrap;
// $db = $bs->get_database();
// $stg = new StudentsTableGateway($db);
// // $student = new Student();
// // $student->forename = 'Ivan';
// // $student->surname = 'Petrovich';
// // $student->email = 'hello@world.com';
// // $student->gender = Student::GENDER_MALE;
// // $student->group_id = "hello";
// // $student->exam_results = 304;
// // $student->birth_year = 1997;
// // $student->is_foreign = false;
// // $res = $stg->registerStudent($student);

// $students = $stg->get_all_students();
// if (!$bs->get_session()->is_logged_in()) {
// 	echo "you are not logged in.<br>";
// }
// echo "Count: " . count($students);
// foreach ($students as $student) {
// 	echo "<p>Student: $student->forename</p>";
// }