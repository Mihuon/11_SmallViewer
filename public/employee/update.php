<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeUpdatePage extends CRUDPage
{
    private ?Employee $employee;
    private ?array $errors = [];
    private int $state;
    private ?array $keys = [];
    private ?array $keysActive = [];
    private ?array $keysUnactive = [];

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Upravit zaměstnance";

        if ($this->state === self::STATE_FORM_REQUESTED) {
            $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);

            if ($_SESSION['id'] != $employeeId && $_SESSION['admin'] == 0) {
                header("Location: ../index.php");
            }
            $stmt = PDOProvider::get()->query("SELECT r.name as rNa, r.room_id as rId FROM room as r");
            $this->keys = $stmt->fetchAll();


            if (!$employeeId)
                throw new BadRequestException();

            $this->employee = Employee::findByID($employeeId);
            if (!$this->employee)
                throw new NotFoundException();

            $stmt = PDOProvider::get()->prepare("SELECT r.room_id as rId, r.name as rNa FROM `key` as k JOIN room as r ON k.room = r.room_id WHERE k.employee=:employeeId");
            $stmt->execute(['employeeId' => $this->employee->employee_id]);
            $this->keysActive = $stmt->fetchAll();

            foreach ($this->keys as $key) {
                $unique = true;
                foreach ($this->keysActive as $keyActive) {
                    if ($key->rId == $keyActive->rId) {
                        $unique = false;
                        break;
                    }
                }
                if ($unique) {
                    array_push($this->keysUnactive, $key);
                }
            }
        } elseif ($this->state === self::STATE_DATA_SENT) {
            $this->employee = Employee::readPost();
            $this->errors = [];
            $this->keys = filter_input(INPUT_POST, 'keys', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            $isOk = $this->employee->validate($this->errors);
            if (!$isOk) {
                $this->state = self::STATE_FORM_REQUESTED;
            } else {
                $success = $this->employee->update();
                if ($success) {

                    $stmt = PDOProvider::get()->prepare("DELETE FROM `key` WHERE `key`.employee = :employeeId");
                    $stmt->execute(['employeeId' => $this->employee->employee_id]);

                    foreach ($this->keys as $key) {
                        $stmt = PDOProvider::get()->prepare("INSERT INTO `key` (employee, room) VALUES (:employeeId, :roomId)");
                        $success = $stmt->execute(['employeeId' => $this->employee->employee_id, 'roomId' => $key]);
                        if (!$success)
                            break;
                    }
                }
                $this->redirect(self::ACTION_UPDATE, $success);
            }
        }
    }

    protected function pageBody()
    {
        if ($_SESSION['admin'] == 1) {
            return MustacheProvider::get()->render(
                'employeeFormAdmin',
                [
                    'title' => $this->title,
                    'employee' => $this->employee,
                    'errors' => $this->errors,
                    'keysUnactive' => $this->keysUnactive,
                    'keysActive' => $this->keysActive
                ]
            );
        } else {
            return MustacheProvider::get()->render(
                'employeeFormUser',
                [
                    'title' => $this->title,
                    'employee' => $this->employee,
                    'errors' => $this->errors,
                    'keysUnactive' => $this->keysUnactive,
                    'keysActive' => $this->keysActive
                ]
            );
        }
    }

    private function findState(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $this->state = self::STATE_DATA_SENT;
        else
            $this->state = self::STATE_FORM_REQUESTED;
    }
}
if ($_SESSION['loggedIn'] == 1) {
    $page = new EmployeeUpdatePage();
    $page->render();
} else {
    header("Location: ../index.php");
}
