<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class RoomDeletePage extends CRUDPage
{

    protected function prepare(): void
    {
        parent::prepare();

        $roomId = filter_input(INPUT_POST, 'roomId', FILTER_VALIDATE_INT);
        if (!$roomId)
            throw new BadRequestException();

        $stmt = PDOProvider::get()->prepare("DELETE FROM `key` WHERE `key`.room = :roomId");
        $stmt->execute(['roomId' => $roomId]);
        $success = Room::deleteByID($roomId);
        $this->redirect(self::ACTION_DELETE, $success);
    }

    protected function pageBody()
    {
        return "";
    }
}

if ($_SESSION['loggedIn'] == 1 && $_SESSION['admin'] == 1) {
    $page = new RoomDeletePage();
    $page->render();
} else {
    header("Location: ../index.php");
}
