<?php
require_once __DIR__.'/../vendor/autoload.php';

class Bootstrap {
	private $db;
	private $template_engine;

	function get_database($cfg = null) {
		if (!$cfg)
			$cfg = parse_ini_file(__DIR__.'/../app.ini', true)['db'];

		$db = new PDO(
			"mysql:host={$cfg['hostname']};dbname={$cfg['dbname']}",
			$cfg['username'],
			$cfg['password'],
			array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=\'STRICT_ALL_TABLES\'',
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			));
		// $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // @TODO

		return $db;
	}

	function get_session() {
		return new Nwoc\Session;
	}

	function get_template_engine() {
		$loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
		$env = array(/*'cache' => __DIR__.'/../compilation_cache'*/);
		$twig = new Twig_Environment($loader, $env);
		$twig->addFunction(new Twig_SimpleFunction('urigen', 'Nwoc\TwigExtensions::generate_uri'));

		return $twig;
	}
}