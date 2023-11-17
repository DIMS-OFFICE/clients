<?php
	require("../../php/pdo_db_connect.php");

	$s=$db->prepare("SELECT tel_nom FROM clients.clients WHERE tel_nom=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);

	$s->execute();

	if ($s->rowCount()==0) {
		$res=Array(
			"status" => "error",
			"desc" => "Данный номер не зарегистрирован в ДиМС"
		);

		echo json_encode($res);
	} else {
		$s=$db->prepare("SELECT id FROM clients.clients_logins WHERE tel_nom=:tel_nom");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);

		$s->execute();

		if ($s->rowCount()>0) {
			$res=Array(
				"status" => "error",
				"desc" => "Данный номер уже зарегистрирован. Попробуйте восстановить пароль"
			);

			echo json_encode($res);

			exit();
		}

		$s=$db->prepare("INSERT INTO clients.clients_logins (tel_nom, password, email, reg_date_time) VALUES (:tel_nom, :password, :email, NOW())");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":password", md5($_POST["password"]));
		$s->bindValue(":email", $_POST["email"]);

		$s->execute();

		if ($s->rowCount()==0) {
			$res=Array(
				"status" => "error",
				"desc" => "Ошибка регистрации"
			);

			echo json_encode($res);
		} else {
			$res=Array(
				"status" => "ОК",
				"desc" => "Регистрация прошла успешно"
			);

			echo json_encode($res);
		}
	}
?>