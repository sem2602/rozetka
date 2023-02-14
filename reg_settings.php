<?php

require_once 'services/rest.php';

if (empty($_POST['auth'])) {header('Location: ./index.php');}

$result = Rest::addClient($_POST);

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"><title>Налаштування програми:</title>
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<script src="//api.bitrix24.com/api/v1/"></script>
    </head>
    	
	<body>
		<div class="wrapper">
			<div class="header">
    			<h3 class="sign-in">Програма успішно встановлена!</h3>
  			</div>
			<div>
				<input class="del" type="submit" onclick="openApplication()" value="На головну..." />
			</div>
		</div>
        <script>function openApplication() {
			BX24.closeApplication();
			BX24.openApplication();
			}</script>
	</body>
</html>