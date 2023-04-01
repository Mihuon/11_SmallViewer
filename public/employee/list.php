<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

session_start();

class EmployeesPage extends CRUDPage
{
    private $alert = [];

    public function __construct()
    {
        $this->title = "Výpis zaměstnanců";
    }

    protected function prepare(): void
    {
        parent::prepare();
        $crudResult = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
        $crudAction = filter_input(INPUT_GET, 'action');

        if (is_int($crudResult)) {
            $this->alert = [
                'alertClass' => $crudResult === 0 ? 'danger' : 'success'
            ];

            $message = '';
            if ($crudResult === 0) {
                $message = 'Operace nebyla úspěšná';
            } else if ($crudAction === self::ACTION_DELETE) {
                $message = 'Smazání proběhlo úspěšně';
            } else if ($crudAction === self::ACTION_INSERT) {
                $message = 'Zaměstnanec přidán úspěšně';
            } else if ($crudAction === self::ACTION_UPDATE) {
                $message = 'Úprava zaměstnance byla úspěšná';
            }

            $this->alert['message'] = $message;
        }
    }


    protected function pageBody()
    {
        $html = "";
        if ($this->alert) {
            $html .= MustacheProvider::get()->render('crudResult', $this->alert);
        }

        $employees = Employee::getAll(['name' => 'ASC']);

        if ($_SESSION['admin'] == 1) {
            $html .= MustacheProvider::get()->render('employeeListAdmin', ['employees' => $employees]);
        } else {
            $html .= MustacheProvider::get()->render('employeeListUser', ['employees' => $employees]);
        }
        return $html;
    }
}
if ($_SESSION['loggedIn'] == 1) {
    $page = new EmployeesPage();
    $page->render();
} else {
    header("Location: ../index.php");
}
