<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeCreatePage extends CRUDPage
{
    private ?Employee $employee;
    private ?array $errors = [];
    private int $state;
    private array $keys = [];

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Přidata nového zaměstnance";
        if ($this->state === self::STATE_FORM_REQUESTED) {
            $this->employee = new Employee();
            $stmt = PDOProvider::get()->query("SELECT `name` as rNa, room_id as rId FROM room");
            $this->keys = $stmt->fetchAll();
        } elseif ($this->state === self::STATE_DATA_SENT) {
            $this->employee = Employee::readPost();
            $this->errors = [];
            $this->keys = filter_input(INPUT_POST, 'keys', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk) {
                $this->state = self::STATE_FORM_REQUESTED;
            } else {
                $success = $this->employee->insert();
                if ($success) {
                    foreach ($this->keys as $key) {
                        $stmt = PDOProvider::get()->prepare("INSERT INTO `key` (employee, room) VALUES (:employeeId, :roomId)");
                        $success = $stmt->execute(['employeeId' => $this->employee->employee_id, 'roomId' => $key]);
                        if (!$success)
                            break;
                    }
                }
                $this->redirect(self::ACTION_INSERT, $success);
            }
        }
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'employeeFormAdmin',
            [
                'title' => $this->title,
                'employee' => $this->employee,
                'errors' => $this->errors,
                'keysUnactive' => $this->keys
            ]
        );
    }

    private function findState(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $this->state = self::STATE_DATA_SENT;
        else
            $this->state = self::STATE_FORM_REQUESTED;
    }
}
if ($_SESSION['loggedIn'] == 1 && $_SESSION['admin'] == 1) {
    $page = new EmployeeCreatePage();
    $page->render();
} else {
    header("Location: ../index.php");
}
