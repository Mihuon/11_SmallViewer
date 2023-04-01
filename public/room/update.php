<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class RoomUpdatePage extends CRUDPage
{
    private ?Room $room;
    private ?array $errors = [];
    private int $state;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Upravit místnost";
        if ($this->state === self::STATE_FORM_REQUESTED) {
            $roomId = filter_input(INPUT_GET, 'roomId', FILTER_VALIDATE_INT);
            if (!$roomId)
                throw new BadRequestException();
            $this->room = Room::findByID($roomId);
            if (!$this->room)
                throw new NotFoundException();
        } elseif ($this->state === self::STATE_DATA_SENT) {
            $this->room = Room::readPost();
            $this->errors = [];
            $isOk = $this->room->validate($this->errors);
            if (!$isOk) {
                $this->state = self::STATE_FORM_REQUESTED;
            } else {
                $success = $this->room->update();
                $this->redirect(self::ACTION_UPDATE, $success);
            }
        }
    }

    protected function pageBody()
    {
        return MustacheProvider::get()->render(
            'roomForm',
            [
                'title' => $this->title,
                'room' => $this->room,
                'errors' => $this->errors
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
    $page = new RoomUpdatePage();
    $page->render();
} else {
    header("Location: index.php");
}
