<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDetailPage extends BasePage
{
    private $employee;
    private $keys;
    private $room;

    protected function prepare(): void
    {
        parent::prepare();
        $employeeId = filter_input(INPUT_GET, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        $this->employee = Employee::findByID($employeeId);
        if (!$this->employee)
            throw new NotFoundException();

        $stmt = PDOProvider::get()->prepare("SELECT k.room as kRo, r.name as rNa FROM `key` as k JOIN room as r ON k.room = r.room_Id WHERE k.employee = :employeeId");
        $stmt->execute(['employeeId' => $employeeId]);
        $this->keys = $stmt->fetchAll();

        $stmt = PDOProvider::get()->prepare("SELECT r.name, r.room_id FROM room as r JOIN employee as e ON e.room = r.room_id WHERE e.employee_id = :employeeId");
        $stmt->execute(['employeeId' => $employeeId]);
        $this->room = $stmt->fetch();

        $this->title = "Detail zaměstnance {$this->employee->name}";
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'employeeDetail',
            ['employee' => $this->employee, 'room' => $this->room, 'keys' => $this->keys]
        );
    }
}
if ($_SESSION['loggedIn'] == 1) {
    $page = new EmployeeDetailPage();
    $page->render();
} else {
    header("Location: ../index.php");
}
