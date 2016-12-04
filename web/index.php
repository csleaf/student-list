<?php
require_once __DIR__.'/../src/bootstrap.php';

use Nwoc\StudentsTableGateway;
use Nwoc\Student;
use Nwoc\ListController;

$bs = new Bootstrap;
$controller = new ListController($bs->get_database(), $bs->get_template_engine());
echo $controller->handle();
