<?php
require_once __DIR__.'/../src/bootstrap.php';

$bs = new Bootstrap;
echo (new Nwoc\ProfileController($bs->get_database(), $bs->get_template_engine()))->handle();
