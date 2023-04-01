<?php

session_start(); 
require_once __DIR__ . "/../bootstrap/bootstrap.php";

class IndexPage extends BasePage
{
    public function __construct()
    {
        $this->title = "Prohlížeč databáze firmy";
    }

    protected function pageBody()
    {
    }

}
if($_SESSION['loggedIn'] == 1){
$page = new IndexPage();
$page->render();
}
else
{
	header("Location: index.php");
}
