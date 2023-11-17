<?php
	require("../pdo_db_connect.php");

	$_POST["old_unit_price"]=str_replace(",",".",$_POST["old_unit_price"]);
	
	$s=$db->prepare("SELECT id FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND call_length=:call_length AND call_date=:call_date AND call_time=:call_time AND phone=:phone AND call_type=:call_type AND service=:service");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":call_length", $_POST["call_length"]);
	$s->bindValue(":call_date", $_POST["date"]);
	$s->bindValue(":call_time", $_POST["time"]);
	$s->bindValue(":phone", $_POST["phone"]);
	$s->bindValue(":call_type", $_POST["call_type"]);
	$s->bindValue(":service", $_POST["service"]);
	$s->execute();

	if ($_POST["action"]=="save") {
		if ($s->rowCount()>0) {
			$id=$s->fetch(PDO::FETCH_COLUMN);

			$s=$db->prepare("UPDATE clients.edited_calls_prices SET unit_price=:unit_price WHERE id=:id");
			$s->bindValue(":unit_price", $_POST["unit_price"]);
			$s->bindValue(":id", $id);
			$s->execute();
		} else {
			$s=$db->prepare("INSERT INTO clients.edited_calls_prices (tel_nom, call_length, call_date, call_time, phone, call_type, service, unit_price, old_unit_price, operator) VALUES (:tel_nom, :call_length, :call_date, :call_time, :phone, :call_type, :service, :unit_price, :old_unit_price, :operator)");
			$s->bindValue(":unit_price", $_POST["unit_price"]);
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":call_length", $_POST["call_length"]);
			$s->bindValue(":operator", $_POST["operator"]);
			$s->bindValue(":call_date", $_POST["date"]);
			$s->bindValue(":call_time", $_POST["time"]);
			$s->bindValue(":phone", $_POST["phone"]);
			$s->bindValue(":call_type", $_POST["call_type"]);
			$s->bindValue(":service", $_POST["service"]);
			$s->bindValue(":old_unit_price", $_POST["old_unit_price"]);
			$s->execute();
		}
	} else {
		$id=$s->fetch(PDO::FETCH_COLUMN);
		
		$s=$db->prepare("DELETE FROM clients.edited_calls_prices WHERE id=:id");
		$s->bindValue(":id", $id);
		$s->execute();
	}

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>