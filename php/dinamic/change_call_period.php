<?php
	require("../pdo_db_connect.php");

	error_reporting(E_ALL);

	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	require("../pdo_db_connect.php");

	$success=0;

	if ($_POST["action"]=="save_from_file") {
		$dir=realpath(dirname(__FILE__) . '/../..');

		$file=file_get_contents($dir."/temp/change_call_period.csv");

		$calls=explode(PHP_EOL, $file);

		foreach ($calls as $call) {
			$parts=explode(";", $call);

			if (count($parts)<5) {
				continue;
			}

			$tel_nom=$parts[0];
			$date_time=$parts[1];
			$date_time=explode("T", $date_time);
			$date=$date_time[0];
			$time=$date_time[1];

			if ($tel_nom=="7".$parts[3]) { 
				$phone=$parts[4];
			} else {
				$phone=$parts[3];
			}

			$call_type=$parts[5];
			$service=$parts[6];

			$call_length=$parts[8];
			if (strlen($call_length)<8) {
				$call_length="0".$call_length;
			}

			$parts=explode("-", $date);
			$old_year=$parts[0];
			$old_month=$parts[1];

			$s=$db->prepare("SELECT id FROM clients.edited_calls_periods WHERE tel_nom=:tel_nom AND call_date=:call_date AND call_time=:call_time AND phone=:phone AND call_type=:call_type AND service=:service");
			$s->bindValue(":tel_nom", $tel_nom);
			$s->bindValue(":call_date", $date);
			$s->bindValue(":call_time", $time);
			$s->bindValue(":phone", $phone);
			$s->bindValue(":call_type", $call_type);
			$s->bindValue(":service", $service);
			$s->execute();

			if ($s->rowCount()>0) {
				$id=$s->fetch(PDO::FETCH_COLUMN);

				$s=$db->prepare("UPDATE clients.edited_calls_periods SET year_edited=:year_edited, month_edited=:month_edited WHERE id=:id");
				$s->bindValue(":year_edited", $_POST["year_edited"]);
				$s->bindValue(":month_edited", $_POST["month_edited"]);
				$s->bindValue(":id", $id);
				$s->execute();
			} else {
				$s=$db->prepare("INSERT INTO clients.edited_calls_periods (call_length, tel_nom, call_date, call_time, phone, call_type, service, year_old, month_old, year_edited, month_edited, operator) VALUES (:call_length, :tel_nom, :call_date, :call_time, :phone, :call_type, :service, :year_old, :month_old, :year_edited, :month_edited, :operator)");
				$s->bindValue(":year_edited", $_POST["year_edited"]);
				$s->bindValue(":month_edited", $_POST["month_edited"]);
				$s->bindValue(":year_old", $old_year);
				$s->bindValue(":month_old", $old_month);
				$s->bindValue(":tel_nom", $tel_nom);
				$s->bindValue(":call_length", $call_length);
				$s->bindValue(":operator", $_POST["operator"]);
				$s->bindValue(":call_date", $date);
				$s->bindValue(":call_time", $time);
				$s->bindValue(":phone", $phone);
				$s->bindValue(":call_type", $call_type);
				$s->bindValue(":service", $service);
				$s->execute();
			}

			if ($s->rowCount()>0) {
				$success++;
			}
		}

		unlink($dir."/temp/change_call_period.csv");
	} else {
		$calls=json_decode($_POST["calls"], true);

		foreach ($calls as $call) {
	 		$s=$db->prepare("SELECT year, month, call_length FROM ".$_POST["operator"]."_detal WHERE id=:id");
			$s->bindValue(":id", $call["call_id"]);
			$s->execute();

			$old=$s->fetch(PDO::FETCH_ASSOC);

			$s=$db->prepare("SELECT id FROM clients.edited_calls_periods WHERE call_length=:call_length AND tel_nom=:tel_nom AND call_date=:call_date AND call_time=:call_time AND phone=:phone AND call_type=:call_type AND service=:service");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":call_length", $old["call_length"]);
			$s->bindValue(":call_date", $call["call_date"]);
			$s->bindValue(":call_time", $call["call_time"]);
			$s->bindValue(":phone", $call["phone"]);
			$s->bindValue(":call_type", $call["call_type"]);
			$s->bindValue(":service", $call["service"]);
			$s->execute();

			if ($_POST["action"]=="save") {
				if ($s->rowCount()>0) {
					$id=$s->fetch(PDO::FETCH_COLUMN);

					$s=$db->prepare("UPDATE clients.edited_calls_periods SET year_edited=:year_edited, month_edited=:month_edited WHERE id=:id");
					$s->bindValue(":year_edited", $_POST["year_edited"]);
					$s->bindValue(":month_edited", $_POST["month_edited"]);
					$s->bindValue(":id", $id);
					$s->execute();
				} else {
					$s=$db->prepare("INSERT INTO clients.edited_calls_periods (call_length, tel_nom, call_date, call_time, phone, call_type, service, year_old, month_old, year_edited, month_edited, operator) VALUES (:call_length, :tel_nom, :call_date, :call_time, :phone, :call_type, :service, :year_old, :month_old, :year_edited, :month_edited, :operator)");
					$s->bindValue(":year_edited", $_POST["year_edited"]);
					$s->bindValue(":month_edited", $_POST["month_edited"]);
					$s->bindValue(":year_old", $old["year"]);
					$s->bindValue(":month_old", $old["month"]);
					$s->bindValue(":tel_nom", $_POST["tel_nom"]);
					$s->bindValue(":call_length", $old["call_length"]);
					$s->bindValue(":operator", $_POST["operator"]);
					$s->bindValue(":call_date", $call["call_date"]);
					$s->bindValue(":call_time", $call["call_time"]);
					$s->bindValue(":phone", $call["phone"]);
					$s->bindValue(":call_type", $call["call_type"]);
					$s->bindValue(":service", $call["service"]);
					$s->execute();
				}
			} else {//Вернуть старый период
				$id=$s->fetch(PDO::FETCH_COLUMN);

				$s=$db->prepare("DELETE FROM clients.edited_calls_periods WHERE id=:id");
				$s->bindValue(":id", $id);
				$s->execute();
			}

			if ($s->rowCount()>0) {
				$success++;
			}
		}
	}

	$res=Array(
		"status" => "OK",
		"desc" => $success
	);

	echo json_encode($res);
?>