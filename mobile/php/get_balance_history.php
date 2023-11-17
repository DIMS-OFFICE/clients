<?php
	require("../../php/pdo_db_connect.php");

	$s=$db->prepare("SELECT id FROM clients.clients_logins WHERE hash=:hash");
	$s->bindValue(":hash", $_POST["hash"]);

	$s->execute();

	if ($s->rowCount()==0) {
		$res=Array(
			"status" => "error",
			"desc" => "wrong_hash"
		);

		echo json_encode($res);

		exit();
	} else {
		$id=$s->fetch(PDO::FETCH_COLUMN);

		$s=$db->prepare("UPDATE clients.clients_logins SET last_activity=NOW() WHERE id=:id");
		$s->bindValue(":id", $id);

		$s->execute();
	}


	$from_date=$_POST["year"]."-".addZero($_POST["month"])."-01";
	$to_date=$_POST["year"]."-".addZero($_POST["month"])."-".cal_days_in_month(CAL_GREGORIAN, $_POST["month"], $_POST["year"]);

	//echo $from_date."/".$to_date;
//	exit();

	$s=$db->prepare("SELECT update_date, balance, spended, payments FROM clients.history WHERE tel_nom=:tel_nom AND update_date BETWEEN :from_date AND :to_date ORDER BY update_date DESC");
	$s->bindValue(":from_date", $from_date);
	$s->bindValue(":to_date", $to_date);
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($res);

    function addZero($num) {
    	if ($num<10) {
    		return "0".$num;
    	} else {
    		return $num;
    	}
    }	
?>