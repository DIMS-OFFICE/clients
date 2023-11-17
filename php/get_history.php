<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT update_date, balance, spended, payments FROM clients.history WHERE tel_nom=:tel_nom AND update_date<:to_date ORDER BY update_date DESC LIMIT 100");
	$s->bindValue(":to_date", $_POST["to_date"]);
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$data=$s->fetchAll(PDO::FETCH_ASSOC);

	$s=$db->prepare("SELECT payment_date FROM clients.payments WHERE tel_nom=:tel_nom AND payment_date<:to_date AND removed=1 GROUP BY payment_date");
	$s->bindValue(":to_date", $_POST["to_date"]);
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$removed_dates=$s->fetchAll(PDO::FETCH_COLUMN);

	$result=Array(
		"data" => $data,
		"removed_dates" => $removed_dates
	);

	echo json_encode($result);
?>