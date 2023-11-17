<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT code, status, `date`, kef FROM clients.services WHERE tel_nom=:tel_nom ORDER BY status");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	$serv=$s->fetchAll(PDO::FETCH_ASSOC);

	$s=$db->prepare("SELECT id, service, sort FROM clients.services_dict");
	$s->execute();

	$dict=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$s=$db->prepare("SELECT service_code, price FROM clients.services_prices WHERE id IN (SELECT MAX(id) FROM clients.services_prices GROUP BY service_code)");
	$s->execute();

	$last_prices=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$services=Array();
	$i=0;

	$s1=$db->prepare("SELECT finish_date FROM clients.services_history WHERE tel_nom=:tel_nom AND service_code=:service_code ORDER BY finish_date DESC LIMIT 1");

	foreach ($serv as $s) {
		$code=$s["code"];

		$services[$i]["code"]=$s["code"];
		$services[$i]["service"]=$dict[$code][0]["service"];
		$services[$i]["price"]=$last_prices[$code][0]["price"];
		$services[$i]["sort"]=$dict[$code][0]["sort"];
		$services[$i]["status"]=$s["status"];
		$services[$i]["kef"]=$s["kef"];

		if ($s["status"]=="Активная") {
			$services[$i]["date"]=$s["date"];
		} else {
			$s1->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s1->bindValue(":service_code", $code);
			$s1->execute();

			$services[$i]["date"]=$s1->fetch(PDO::FETCH_COLUMN);
		}

		$i++;
	}

	usort($services, "cmp");

	echo json_encode($services);

	function cmp($a, $b) {
		if ($a["status"]<$b["status"]) {
			return false;
		} else if ($a["status"]==$b["status"]) {
			if ($a["sort"]<$b["sort"]) {
				return false;
			} else {
				return true;
			}
		} else {
			return true;
		}
	}
?>