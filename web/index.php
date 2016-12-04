<?php
require_once __DIR__.'/../src/bootstrap.php';

$bs = new Bootstrap;
echo (new Nwoc\ListController($bs->get_database(), $bs->get_template_engine()))->handle();
