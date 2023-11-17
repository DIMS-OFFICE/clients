<?php
	header('Access-Control-Allow-Origin: *');
	
	require("pdo_db_connect.php");

	$date=$_POST["year"]."-".addZero($_POST["month"])."-01";

	$s=$db->prepare("SELECT category, service_code, unit_price, sum, length, blocked FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->bindValue(":year", $_POST["year"]);
	$s->bindValue(":month", $_POST["month"]);
	$s->execute();

	$spended=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$s=$db->prepare("SELECT id, service, sort FROM clients.services_dict");
	$s->execute();

	$services=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$res=Array();
	
	$s=$db->prepare("SELECT type, sort FROM clients.call_types_sort WHERE operator=:operator");
	$s->bindValue(":operator", $_POST["operator"]);
	$s->execute();

	$call_types_sort=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	foreach ($spended as $category => $sp) {
		//print_r($services);
		//exit();
		if (isset($services[$sp[0]["service_code"]][0]["sort"])) {
			$sort=$services[$sp[0]["service_code"]][0]["sort"];
			$group=1;
			$name=$services[$sp[0]["service_code"]][0]["service"];
		} else if ($sp[0]["sum"]>0) {
			if ($category=="ПЕРЕАДРЕСАЦИЯ") {
				$sort=1999;
			} else {
				$sort=$call_types_sort[$category][0]["sort"]+1000;
			}
			$group=2;
			$name=$category;
		} else {
			$sort=$call_types_sort[$category][0]["sort"]+2000;
			$group=3;
			$name=$category;
		}

		$res[]=Array(
			"category" => $name,
			"unit_price" => $sp[0]["unit_price"],
			"sum" => $sp[0]["sum"],
			"length" => $sp[0]["length"],
			"sort" => $sort,
			"group" => $group,
			"blocked" => $sp[0]["blocked"]
		);
	}

	usort($res, "cmp");

	echo json_encode($res);

	function cmp($a, $b) {
		if ($a["sort"]<$b["sort"]) {
			return false;
		} else {
			return true;
		}
	}

	function addZero($n) {
		if ($n<10) {
			return "0".$n;
		} else {
			return $n;
		}
	}
?>