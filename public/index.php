<!DOCTYPE html>
<html>

<head>
	<title>Přihlášení</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>

<body>
	<form style="margin: 5% 25% 5% 25%" action="login.php" method="post">
		<div class="form-group">
			<label for="login">Uživatelské jméno</label>
			<input type="text" name="login" class="form-control" id="login" placeholder="Uživatelské jméno">
		</div>
		<div class="form-group">
			<label for="password">Heslo</label>
			<input type="password" name="password" class="form-control" id="password" placeholder="Heslo">
		</div>
		<button type="submit" class="btn btn-primary">Přihlásit</button>
	</form>
</body>

</html>