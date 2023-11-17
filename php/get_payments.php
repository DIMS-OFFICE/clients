<?php
	require("pdo_db_connect.php");

	if (isset($_POST["year"])) {
		$s=$db->prepare("SELECT id, summ, payment_date, payment_time, append_time, remove_time, removed, source FROM clients.payments WHERE tel_nom=:tel_nom AND YEAR(payment_date)=:year AND MONTH(payment_date)=:month");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":year", $_POST["year"]);
		$s->bindValue(":month", $_POST["month"]);
	} else {
		$s=$db->prepare("SELECT id, summ, payment_date, payment_time, append_time, remove_time, removed, source FROM clients.payments WHERE tel_nom=:tel_nom AND payment_date=:payment_date");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":payment_date", $_POST["payment_date"]);
	}

	$s->execute();

	$payments=$s->fetchAll(PDO::FETCH_ASSOC);
	
	echo json_encode($payments);
?>