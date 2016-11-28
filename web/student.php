<?php
require_once __DIR__.'/../src/bootstrap.php';

$bs = new Bootstrap;
$db = $bs->get_database();

if (isset($_COOKIE["session"])) {
	$gateway = new Nwoc\StudentsTableGateway($db);
	if ($student = $gateway->get_student_with_cookie($_COOKIE["session"])) {
		// @TODO: LoggedInController
		echo $student->forename;
		exit();
	}
}

$controller = new Nwoc\RegistrationController($db, $bs->get_template_engine());
echo $controller->handle();