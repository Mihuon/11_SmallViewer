<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class EmployeeDeletePage extends CRUDPage
{

    protected function prepare(): void
    {
        parent::prepare();

        $employeeId = filter_input(INPUT_POST, 'employeeId', FILTER_VALIDATE_INT);
        if (!$employeeId)
            throw new BadRequestException();

        $stmt = PDOProvider::get()->prepare("DELETE FROM `key` WHERE `key`.employee = :employeeId");
        $stmt->execute(['employeeId' => $employeeId]);
        $success = Employee::deleteByID($employeeId);
        $this->redirect(self::ACTION_DELETE, $success);
    }

    protected function pageBody()
    {
        return "";
    }
}
if ($_SESSION['loggedIn'] == 1 && $_SESSION['admin'] == 1) {
    $page = new EmployeeDeletePage();
    $page->render();
} else {
    header("Location: ../index.php");
}
?>

?>