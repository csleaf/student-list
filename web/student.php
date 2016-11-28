<?php
require_once __DIR__.'/../src/bootstrap.php';

$bs = new Bootstrap;
$db = $bs->get_database();
$template_engine = $bs->get_template_engine();
$show_registration = true; // this var is here to avoid usage of 'exit()'

if (isset($_COOKIE["session"])) {
    $gateway = new Nwoc\StudentsTableGateway($db);
    if ($student = $gateway->get_student_with_cookie($_COOKIE["session"])) {
        echo (new Nwoc\AccountController($db, $template_engine, $gateway, $student))->handle();
        $show_registration = false;
    }
}

if ($show_registration) {
    $controller = new Nwoc\RegistrationController($db, $bs->get_template_engine());
    echo $controller->handle();
}
