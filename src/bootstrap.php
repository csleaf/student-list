<?php
require_once __DIR__.'/../vendor/autoload.php';

/**
* Provides default database and template engine settings.
*/
class Bootstrap {
    private $db;
    private $template_engine;
    private $db_cfg;

    public function __construct(array $cfg = null) {
        $this->db_cfg = $cfg;
    }

        /**
    * Establishes connection with MySQL database using specified configuration settings.
    */
    private function create_database(array $cfg = null): \PDO {
        if (!$cfg)
            $cfg = parse_ini_file(__DIR__.'/../app.ini', true)['db'];

        $db = new PDO(
            "mysql:host={$cfg['hostname']};dbname=students;charset=utf8mb4",
            $cfg['username'],
            $cfg['password'],
            array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=\'STRICT_ALL_TABLES\'',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));

        return $db;
    }

    /**
    * Initializes Twig template engine and returns it.
    */
    function create_template_engine(): Twig_Environment {
        $loader = new Twig_Loader_Filesystem(__DIR__.'/../templates');
        $env = array(/*'cache' => __DIR__.'/../compilation_cache'*/);
        $twig = new Twig_Environment($loader, $env);
        $twig->addFunction(new Twig_SimpleFunction('urigen', 'Nwoc\TwigExtensions::generate_uri'));

        return $twig;
    }

    public function get_database(): \PDO {
        if (!isset($this->db))
            $this->db = $this->create_database($this->db_cfg);
        return $this->db;
    }

    public function get_template_engine(): Twig_Environment {
        if (!isset($this->template_engine))
            $this->template_engine = $this->create_template_engine();
        return $this->template_engine;
    }
}
