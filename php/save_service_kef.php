<?php
	require("pdo_db_connect.php");

	$_POST["kef"]=str_replace(",",".",$_POST["kef"]);

	if ($_POST["history_id"]==0) {
		$s=$db->prepare("UPDATE clients.services SET kef=:kef WHERE tel_nom=:tel_nom AND code=:service_code");
		$s->bindValue(":kef", $_POST["kef"]);
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":service_code", $_POST["code"]);
		$s->execute();

		$s=$db->prepare("SELECT id FROM clients.services_history WHERE tel_nom=:tel_nom AND service_code=:service_code ORDER BY start_date DESC LIMIT 1");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":service_code", $_POST["code"]);
		$s->execute();

		$history_id=$s->fetch(PDO::FETCH_COLUMN);
	} else {
		$history_id=$_POST["history_id"];

		if ($_POST["row_nom"]==0) {//Если изменяем вырхнюю строчку таблицы, т.е последнюю запись, то меняем значение КЭФа и в таблице подключенных услуг
			$s=$db->prepare("UPDATE clients.services SET kef=:kef WHERE tel_nom=:tel_nom AND code=:service_code");
			$s->bindValue(":kef", $_POST["kef"]);
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":service_code", $_POST["code"]);
			$s->execute();
		}
	}
	
	$s=$db->prepare("UPDATE clients.services_history SET kef=:kef WHERE id=:id");
	$s->bindValue(":kef", $_POST["kef"]);
	$s->bindValue(":id", $history_id);
	$s->execute();

	echo "OK";
?>