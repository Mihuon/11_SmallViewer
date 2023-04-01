<?php
session_start();
require_once __DIR__ . "/../../bootstrap/bootstrap.php";

class RoomCreatePage extends CRUDPage
{
    private ?Room $room;
    private ?array $errors = [];
    private int $state;

    protected function prepare(): void
    {
        parent::prepare();
        $this->findState();
        $this->title = "Založit novou místnost";

        if ($this->state === self::STATE_FORM_REQUESTED) {
            $this->room = new Room();
        } elseif ($this->state === self::STATE_DATA_SENT) {
            $this->room = Room::readPost();

            $this->errors = [];
            $isOk = $this->room->validate($this->errors);
            if (!$isOk) {
                $this->state = self::STATE_FORM_REQUESTED;
            } else {
                $success = $this->room->insert();
                $this->redirect(self::ACTION_INSERT, $success);
            }
        }
    }

    protected function pageBody()
    {
        var_dump($this->errors);
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
    $page = new RoomCreatePage();
    $page->render();
} else {
    header("Location: ../index.php");
}
