<?php
require_once __DIR__.'/../vendor/autoload.php';

/**
* Provides default database and template engine settings.
*/
class Bootstrap {
    private $db;
    private $template_engine;

    /**
    * Establishes connection with MySQL database using specified configuration settings.
    */
    function get_database(array $cfg = null): \PDO {
        if (!$cfg)
            $cfg = parse_ini_file(__DIR__.'/../app.ini', true)['db'];

        $db = new PDO(
            "mysql:host={$cfg['hostname']};dbname={$cfg['dbname']};charset=utf8mb4",
            $cfg['username'],
            $cfg['password'],
            array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=\'STRICT_ALL_TABLES\'',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));
        // $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // @TODO

        return $db;
    }

    /**
    * Initializes Twig template engine and returns it.
    */
    function get_template_engine(): Twig_Environment {
        $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
        $env = array(/*'cache' => __DIR__.'/../compilation_cache'*/);
        $twig = new Twig_Environment($loader, $env);
        $twig->addFunction(new Twig_SimpleFunction('urigen', 'Nwoc\TwigExtensions::generate_uri'));

        return $twig;
    }
}
