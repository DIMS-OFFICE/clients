<?php
	require("pdo_db_connect.php");

	$s_calculated_spended=$db->prepare("SELECT SUM(sum) FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month");

	$s_calculated_spended->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s_calculated_spended->bindValue(":year", $_POST["year"]);
	$s_calculated_spended->bindValue(":month", $_POST["month"]);
	$s_calculated_spended->execute();

	$calculated_spended=$s_calculated_spended->fetch(PDO::FETCH_COLUMN);

	$diff=$calculated_spended-$_POST["summ"];

	$ss_update=$db->prepare("UPDATE clients.1s SET summ=:summ, diff=:diff WHERE tel_nom=:tel_nom AND year=:year AND month=:month");
	$ss_update->bindValue(":tel_nom", $_POST["tel_nom"]);
	$ss_update->bindValue(":year", $_POST["year"]);
	$ss_update->bindValue(":month", $_POST["month"]);
	$ss_update->bindValue(":summ", $_POST["summ"]);
	$ss_update->bindValue(":diff", $diff);
	$ss_update->execute();

	if ($ss_update->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>