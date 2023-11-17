<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT periodic FROM clients.services_dict WHERE id=:service_id");
	$s->bindValue(":service_id", $_POST["service_code"]);
	$s->execute();

	$periodic=$s->fetch(PDO::FETCH_COLUMN);

	if ($periodic==1) {
		$last_month=date("Y-m-01", strtotime($_POST["finish_date"]));
		$this_month=date("Y-m-t", strtotime($_POST["finish_date"]));

		$days_to_last_month=dateDiff($last_month, $_POST["finish_date"]);
		$days_to_this_month=dateDiff($_POST["finish_date"], $this_month);

		if ($days_to_last_month<$days_to_this_month) {
			$_POST["finish_date"]=date("Y-m-d", strtotime($last_month)-24*3600);
		} else {
			$_POST["finish_date"]=$this_month;
		}
	}

	if (strtotime($_POST["finish_date"])>=time()) {
		$status="Активная";
	} else {
		$status="Не активная";
	}

	$s=$db->prepare("UPDATE clients.services SET `date`=:start_date, status=:status WHERE tel_nom=:tel_nom AND code=:service_code");
	$s->bindValue(":start_date", $_POST["start_date"]);
	$s->bindValue(":status", $status);
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":service_code", $_POST["service_code"]);
	$s->execute();

	$s=$db->prepare("UPDATE clients.services_history SET start_date=:start_date, finish_date=:finish_date WHERE id=:history_id");
	$s->bindValue(":start_date", $_POST["start_date"]);
	$s->bindValue(":finish_date", $_POST["finish_date"]);
	$s->bindValue(":history_id", $_POST["history_id"]);
	$s->execute();

	echo "OK";

	function dateDiff ($d1, $d2) {
   
    	return round(abs(strtotime($d1) - strtotime($d2))/86400);

	}

?>