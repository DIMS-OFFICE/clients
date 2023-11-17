<?php
	require("pdo_db_connect.php");

	if ($_POST["service_id"]==0) {
		$s=$db->prepare("SELECT code, COUNT(id) as c FROM clients.services WHERE operator=:operator AND status='Активная' GROUP BY code");
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$totals=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT id, service, sort, periodic FROM clients.services_dict WHERE operator=:operator GROUP BY service ORDER BY sort ASC");
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$services=$s->fetchAll(PDO::FETCH_ASSOC);

		$s=$db->prepare("SELECT service_code, price FROM clients.services_prices WHERE id IN (SELECT MAX(id) FROM clients.services_prices GROUP BY service_code)");
		$s->execute();

		$last_prices=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$result=Array();
		$i=0;
		foreach ($services as $service) {
			$result[$i]["service_code"]=$service["id"];
			if (isset($last_prices[$service["id"]])) {
				$result[$i]["price"]=$last_prices[$service["id"]][0]["price"];
			} else {
				$result[$i]["price"]=0;
			}
			$result[$i]["service"]=$service["service"];
			$result[$i]["sort"]=$service["sort"];
			$result[$i]["periodic"]=(int)$service["periodic"];
			$result[$i]["count"]=(int)$totals[$service["id"]][0]["c"];
			$i++;
		}

		usort($result, "cmp");

		echo json_encode($result);
	} else {
		$s=$db->prepare("SELECT id, `date`, finish_date, price FROM clients.services_prices WHERE service_code=:service_id ORDER BY id DESC");
		$s->bindValue(":service_id", $_POST["service_id"]);

		$s->execute();

		$res=$s->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode($res);
	}

	function cmp($a, $b) {
		if ($a["sort"]<$b["sort"]) {
			return false;
		} else {
			return true;
		}
	}
?>