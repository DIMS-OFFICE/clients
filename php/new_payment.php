<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT account FROM clients.clients WHERE tel_nom=:tel_nom");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$account=$s->fetch(PDO::FETCH_COLUMN);

	$s=$db->prepare("INSERT INTO clients.payments (account, tel_nom, summ, payment_date, append_time, source, operator) VALUES (:account, :tel_nom, :summ, :payment_date, NOW(), 2, :operator)");
	$s->bindValue(":account", $account);
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":summ", $_POST["summ"]);
	$s->bindValue(":payment_date", $_POST["payment_date"]);
	$s->bindValue(":operator", $_POST["operator"]);

	$s->execute();

	if ($s->rowCount()>0) {
		$s=$db->prepare("UPDATE clients.history SET balance=balance+:summ, payments=payments+:summ WHERE tel_nom=:tel_nom AND update_date=:update_date");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":update_date", $_POST["payment_date"]);
		$s->bindValue(":summ", $_POST["summ"]);
		$s->execute();

		$s1=$db->prepare("UPDATE clients.history SET balance=balance+:summ WHERE tel_nom=:tel_nom AND update_date>:update_date");
		$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s1->bindValue(":update_date", $_POST["payment_date"]);
		$s1->bindValue(":summ", $_POST["summ"]);
		$s1->execute();
	} else {
		echo "error";
		exit();
	}

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>