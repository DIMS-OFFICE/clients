<?php
	header('Access-Control-Allow-Origin: *');
	
	require("../pdo_db_connect.php");

	if ($_POST["action"]==1) {//Список групп
		$s=$db->prepare("SELECT DISTINCT client_group FROM clients.clients WHERE client_group!=''");
		$s->execute();

		$groups=$s->fetchAll(PDO::FETCH_COLUMN);

		echo json_encode($groups);
	} else {
		$s=$db->prepare("UPDATE clients.clients SET client_group=:client_group WHERE tel_nom=:tel_nom");
		$s->bindValue(":client_group", $_POST["client_group"]);
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);

		$s->execute();

		$s1=$db->prepare("UPDATE clients.groups_history SET finish_date=date(NOW() - INTERVAL 1 DAY) WHERE tel_nom=:tel_nom AND finish_date='2100-01-01'");
		$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s1->execute();

		$s2=$db->prepare("INSERT INTO clients.groups_history (start_date, finish_date, tel_nom, client_group) VALUES (DATE(NOW()), '2100-01-01', :tel_nom, :client_group)");
		$s2->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s2->bindValue(":client_group", $_POST["client_group"]);
		$s2->execute();

		if ($s->rowCount()>0) {
			echo "OK";
		} else {
			echo "error";
		}
	} 
?>