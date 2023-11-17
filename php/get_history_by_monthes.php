<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT CONCAT_WS('-', `year` ,`month`) as m, balance FROM clients.history WHERE tel_nom=:tel_nom GROUP BY m ORDER BY year DESC, month DESC");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$start_dates=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$s=$db->prepare("SELECT CONCAT('".chr(34)."', MAX(update_date), '".chr(34)."') FROM clients.history WHERE tel_nom=:tel_nom GROUP BY year, month");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$dates=$s->fetchAll(PDO::FETCH_COLUMN);

	$dates=implode(",",$dates);

	$s=$db->prepare("SELECT SUBSTR(update_date,1,7) as m, spended FROM ".$_POST["operator"]."_counters_history WHERE tel_nom=:tel_nom AND update_date IN (".$dates.") GROUP BY m");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$spended_from_dinamic=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$s=$db->prepare("SELECT year, month, balance, spended FROM clients.history WHERE tel_nom=:tel_nom AND update_date IN (".$dates.") ORDER BY year DESC, month DESC");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$finish_dates=$s->fetchAll(PDO::FETCH_ASSOC);

	$s=$db->prepare("SELECT SUBSTR(payment_date,1,7) as y_m, SUM(summ) as payments FROM clients.payments WHERE tel_nom=:tel_nom AND removed=0 GROUP BY y_m");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$payments=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$s=$db->prepare("SELECT SUBSTR(payment_date,1,7) FROM clients.payments WHERE tel_nom=:tel_nom AND removed=1");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$removed_payments=$s->fetchAll(PDO::FETCH_COLUMN);

	$s=$db->prepare("SELECT CONCAT(year,'-',month), SUM(summ) as summ FROM clients.1s WHERE tel_nom=:tel_nom GROUP BY year, month");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$ss=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$result=Array();

	$s=$db->prepare("SELECT CONCAT(year, '-', month) FROM clients.spended WHERE tel_nom=:tel_nom AND blocked=1 GROUP BY year, month");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$blocked_periods=$s->fetchAll(PDO::FETCH_COLUMN);

	$s=$db->prepare("SELECT CONCAT(year, '-', month), update_date, update_time, user_name FROM clients.spended WHERE tel_nom=:tel_nom GROUP BY year, month");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$update_data=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$total_benefit=0;
	foreach ($finish_dates as $fd) {
		$month=$fd["year"]."-".$fd["month"];
		$month_with_zero=$fd["year"]."-".addZero($fd["month"]);

		if (isset($spended_from_dinamic[$month_with_zero])==false) {
			$spended_from_dinamic[$month_with_zero][0]["spended"]=0;
		}

		if (is_null($ss[$month][0]["summ"])) {
			$ss[$month][0]["summ"]="-";
		}

		if (is_null($payments[$month_with_zero][0]["payments"])) {
			$payments[$month_with_zero][0]["payments"]=0;
		}

		if (is_null($update_data[$fd["year"]."-".$fd["month"]][0]["user_name"])) {
			$user_name="-";
			$update_date="-";
			$update_time="";
		} else {
			$user_name=$update_data[$fd["year"]."-".$fd["month"]][0]["user_name"];
			$update_date=$update_data[$fd["year"]."-".$fd["month"]][0]["update_date"];
			$update_time=$update_data[$fd["year"]."-".$fd["month"]][0]["update_time"];
		}

		$total_benefit+=$fd["spended"]-$spended_from_dinamic[$month_with_zero][0]["spended"];

		$result[]=Array(
			"year" => $fd["year"],
			"month" => $fd["month"],
			"start_balance" => $start_dates[$month][0]["balance"],
			"finish_balance" => $fd["balance"],
			"spended" => $fd["spended"],
			"payments" => $payments[$month_with_zero][0]["payments"],
			"removed_payments" => $removed_payments,
			"spended_from_dinamic" => $spended_from_dinamic[$month_with_zero][0]["spended"],
			"diff" => number_format($fd["spended"]-$spended_from_dinamic[$month_with_zero][0]["spended"],2,".",""),
			"ss" => $ss[$month][0]["summ"],
			"update_date" => $update_date,
			"update_time" => $update_time,
			"user_name" => $user_name
		);
	}

	$res=Array(
		"periods" => $result,
		"blocked_periods" => $blocked_periods,
		"total_benefit" => number_format($total_benefit,2,"."," ")
	);

	echo json_encode($res);

	function addZero($num) {
    	if ($num<10) {
    		return "0".$num;
    	} else {
    		return $num;
    	}
    }
?>