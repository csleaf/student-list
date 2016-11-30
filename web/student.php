<?php
require_once __DIR__.'/../src/bootstrap.php';

$bs = new Bootstrap;
$db = $bs->get_database();
$template_engine = $bs->get_template_engine();

echo (new Nwoc\ProfileController($db, $template_engine))->handle();
