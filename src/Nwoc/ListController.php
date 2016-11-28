<?php
namespace Nwoc;

class ListController {
    const SORT_OPTIONS = array('forename', 'surname', 'group_id', 'exam_results');

    private $db;
    private $template_engine;

    public function __construct(\PDO $db, $template_engine) {
        $this->db = $db;
        $this->template_engine = $template_engine;
    }

    public function handle() {
        $gateway = new StudentsTableGateway($this->db);
        $env = array(
            'title' => 'Спиcок абитуриентов'
        );
        $students = null;

        if (isset($_GET['sort']) && in_array($_GET['sort'], self::SORT_OPTIONS, true)) {
            $sort_by = strval($_GET['sort']);
        } else {
            $sort_by = self::SORT_OPTIONS[0];
        }

        if (isset($_GET['page']) && ctype_digit($_GET['page'])) {
            $page = intval($_GET['page']);
        } else {
            $page = 0;
        }

        if (isset($_GET['order']) && ($_GET['order'] === 'asc' || $_GET['order'] === 'desc')) {
            $order = strtolower(strval($_GET['order']));
        } else {
            $order = 'asc';
        }

        if (isset($_GET['query'])) {
            $search_query = strval($_GET['query']);
            $students = $gateway->find_students(
                $search_query,
                $sort_by,
                $order == 'asc' ? StudentsTableGateway::ORDER_ASC : StudentsTableGateway::ORDER_DESC);
        } else {
            $students = $gateway->get_all_students(
                $sort_by,
                // @TODO this is ugly
                $order == 'asc' ? StudentsTableGateway::ORDER_ASC : StudentsTableGateway::ORDER_DESC,
                $page);
            $search_query = NULL;
        }

        return $this->template_engine->render('list.html', array(
            'title' => 'Список абитуриентов',
            'sort_by' => $sort_by,
            'students' => $students,
            'page' => $page + 1,
            'order' => $order,
            'search_query' => $search_query,
            'get_params' => $_GET
        ));
    }
}
