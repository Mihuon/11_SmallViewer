<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class RoomDetailPage extends BasePage
{
    private $room;
    private $employees;
    private $keys;

    protected function prepare(): void
    {
        parent::prepare();
        $roomId = filter_input(INPUT_GET, 'roomId', FILTER_VALIDATE_INT);
        if (!$roomId)
            throw new BadRequestException();
        $this->room = Room::findByID($roomId);
        if (!$this->room)
            throw new NotFoundException();


        $stmt = PDOProvider::get()->prepare("SELECT `surname`, `name`, `employee_id` FROM `employee` WHERE `room`= :roomId ORDER BY `surname`, `name`");
        $stmt->execute(['roomId' => $roomId]);
        $this->employees = $stmt->fetchAll();

        $stmt = PDOProvider::get()->prepare("SELECT e.name eNa, e.surname eSu, e.employee_id eId FROM employee e JOIN `key` k ON e.employee_id=k.employee WHERE k.room = :roomId");
        $stmt->execute(['roomId' => $roomId]);
        $this->keys = $stmt->fetchAll();

        $this->title = "Detail místnosti {$this->room->no}";
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'roomDetail',
            ['room' => $this->room, 'employees' => $this->employees, 'keys' => $this->keys]
        );
    }
}

if ($_SESSION['loggedIn'] == 1) {
    $page = new RoomDetailPage();
    $page->render();
} else {
    header("Location: ../index.php");
}
