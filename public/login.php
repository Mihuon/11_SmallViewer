<?php
session_start();
include "../config/config_login.php";
global $connection;

if (isset($_POST['login']) && isset($_POST['password'])) {

	function validate($data)
	{
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return $data;
	}

	$login = validate($_POST['login']);
	$password = validate($_POST['password']);

	if (empty($login)) {
		header("Location: index.php?error=Uživatelksé jméno nesmí být prázdné");
		exit();
	} else if (empty($password)) {
		header("Location: index.php?error=Heslo nesmí být prázdné");
		exit();
	} else {
		$sql = "SELECT * FROM employee WHERE login='$login' AND password='$password'";

		$result = mysqli_query($connection, $sql);

		if (mysqli_num_rows($result) === 1) {
			$row = mysqli_fetch_assoc($result);
			if ($row['login'] === $login && $row['password'] === $password) {
				$_SESSION['login'] = $row['login'];
				$_SESSION['id'] = $row['employee_id'];
				$_SESSION['loggedIn'] = 1;
				$_SESSION['admin'] = $row['admin'];
				header("Location: menu.php");
				exit();
			} else {
				$_SESSION['loggedIn'] = 0;
				header("Location: index.php?error=Nesprávné jméno nebo heslo");
				exit();
			}
		} else {
			$_SESSION['loggedIn'] = 0;
			header("Location: index.php?error=Nesprávné jméno nebo heslo");
			exit();
		}
	}
} else {
	$_SESSION['loggedIn'] = 1;
	header("Location: index.php");
	exit();
}
