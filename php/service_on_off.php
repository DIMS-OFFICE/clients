<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT id FROM clients.services WHERE tel_nom=:tel_nom AND code=:code");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":code", $_POST["code"]);
	$s->execute();

	if ($_POST["on"]==1) {
		if (strlen($_POST["services_for_disable"])>0) {
			$s2=$db->prepare("UPDATE clients.services SET status='Не активная' WHERE tel_nom=:tel_nom AND code IN (".$_POST["services_for_disable"].")");
			$s2->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s2->execute();

			$s2=$db->prepare("SELECT id FROM clients.services_history WHERE tel_nom=:tel_nom AND service_code=:service_code AND finish_date='2030-01-01'");
			$s2->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s2->bindValue(":service_code", $_POST["services_for_disable"]);
			$s2->execute();

			$id=$s2->fetch(PDO::FETCH_COLUMN);

			$s2=$db->prepare("UPDATE clients.services_history SET finish_date=:finish_date WHERE id=:id");
			$s2->bindValue(":finish_date", date("Y-m-d", strtotime($_POST["service_start_date"])-24*3600));
			$s2->bindValue(":id", $id);
			$s2->execute();
		}

		$status="Активная";

		if ($s->rowCount()>0) {
			$s=$db->prepare("UPDATE clients.services SET status=:status, `date`= :service_start_date WHERE tel_nom=:tel_nom AND code=:code");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":code", $_POST["code"]);
			$s->bindValue(":status", $status);
			$s->bindValue(":service_start_date", $_POST["service_start_date"]);
			$s->execute();
		} else {
			$s=$db->prepare("INSERT INTO clients.services (tel_nom, code, status, `date`, operator) VALUES (:tel_nom, :code, :status, :service_start_date, :operator)");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":code", $_POST["code"]);
			$s->bindValue(":status", $status);
			$s->bindValue(":operator", $_POST["operator"]);
			$s->bindValue(":service_start_date", $_POST["service_start_date"]);
			$s->execute();
		}

		$s1=$db->prepare("INSERT INTO clients.services_history (tel_nom, service_code, start_date, operator) VALUES (:tel_nom, :service_code, :service_start_date, :operator)");
		$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s1->bindValue(":service_code", $_POST["code"]);
		$s1->bindValue(":operator", $_POST["operator"]);
		$s1->bindValue(":service_start_date", $_POST["service_start_date"]);
		$s1->execute();

		if ($s->rowCount()>0) {
			$res=Array(
				"result" => "OK",
				"date" => $_POST["service_start_date"]
			);

			echo json_encode($res);
		} else {
			echo "error";
		}
	} else {
		$s=$db->prepare("UPDATE clients.services SET status='Не активная' WHERE tel_nom=:tel_nom AND code=:code");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":code", $_POST["code"]);
		$s->execute();

		$s1=$db->prepare("SELECT id FROM clients.services_history WHERE tel_nom=:tel_nom AND service_code=:service_code ORDER BY start_date DESC LIMIT 1");
		$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s1->bindValue(":service_code", $_POST["code"]);
		$s1->execute();

		$id=$s1->fetch(PDO::FETCH_COLUMN);

		$s1=$db->prepare("UPDATE clients.services_history SET finish_date=:service_finish_date WHERE id=:id");
		$s1->bindValue(":id", $id);
		$s1->bindValue(":service_finish_date", $_POST["service_finish_date"]);
		$s1->execute();

		if ($s->rowCount()>0) {
			$res=Array(
				"result" => "OK",
				"date" => $_POST["service_finish_date"]
			);

			echo json_encode($res);
		} else {
			echo "error";
		}
	}
?>