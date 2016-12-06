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
            $page = intval($_GET['page']) - 1;
            if ($page < 0) {
                $page = 0;
            }
        } else {
            $page = 0;
        }

        if (isset($_GET['order']) && ($_GET['order'] === 'asc' || $_GET['order'] === 'desc')) {
            $order = strtolower(strval($_GET['order']));
        } else {
            $order = 'asc';
        }

        $students_found = 0;

        if (isset($_GET['query']) && !empty($_GET['query'])) {
            $search_query = strval($_GET['query']);
            $students = $gateway->find_students(
                $search_query,
                $sort_by,
                $order == 'asc' ? StudentsTableGateway::ORDER_ASC : StudentsTableGateway::ORDER_DESC,
                $page,
                50,
                $students_found);
        } else {
            $students = $gateway->get_all_students(
                $sort_by,
                // @TODO this is ugly
                $order == 'asc' ? StudentsTableGateway::ORDER_ASC : StudentsTableGateway::ORDER_DESC,
                $page,
                50);
            $search_query = NULL;
        }

        if (!isset($search_query)) {
            $students_found = $gateway->count_students();
        }

        return $this->template_engine->render('list.twig', array(
            'title' => 'Список абитуриентов',
            'sort_by' => $sort_by,
            'students' => $students,
            'curr_page' => $page,
            'max_pages' => abs(intval(($students_found - 1) / 50)),
            'total_students' => $students_found,
            'order' => $order,
            'search_query' => $search_query,
            'get_params' => $_GET,
            'is_logged_in' => isset($_COOKIE['session']) ? ($gateway->get_student_with_cookie($_COOKIE['session']) != null) : false
        ));
    }
}
