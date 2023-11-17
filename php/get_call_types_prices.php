<?php
	require("pdo_db_connect.php");

	if ($_POST["type_nom"]==0) {
		$s=$db->prepare("SELECT type_nom, id, sort FROM clients.call_types_sort WHERE operator=:operator");
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$sort=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		if ($_POST["for_calls_list"]==0) {
			$s=$db->prepare("SELECT id, type_nom, call_type, price FROM clients.call_types_prices WHERE id IN (SELECT MAX(id) FROM clients.call_types_prices WHERE operator=:operator GROUP BY type_nom) ORDER BY call_type ASC, id DESC");
			$s->bindValue(":operator", $_POST["operator"]);
			$s->execute();

			$types=$s->fetchAll(PDO::FETCH_ASSOC);

			$res=Array();
			$i=0;
			foreach ($types as $type) {
				$res[$i]["id"]=$type["id"];
				$res[$i]["type_nom"]=$type["type_nom"];
				$res[$i]["call_type"]=$type["call_type"];
				$res[$i]["price"]=$type["price"];
				$res[$i]["sort"]=$sort[$type["type_nom"]][0]["sort"];
				$res[$i]["sort_id"]=$sort[$type["type_nom"]][0]["id"];

				$i++;
			}

			usort($res, "cmp");
		} else {
			$s=$db->prepare("SELECT call_type, `date`, price FROM clients.call_types_prices WHERE operator=:operator GROUP BY call_type, `date` ORDER BY call_type ASC, `date` DESC");
			$s->bindValue(":operator", $_POST["operator"]);
			$s->execute();

			$res=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
		}
	} else {
		$s=$db->prepare("SELECT id, type_nom, call_type, price, `date`, finish_date FROM clients.call_types_prices WHERE type_nom=:type_nom ORDER BY date DESC, finish_date DESC");
		$s->bindValue(":type_nom", $_POST["type_nom"]);
		$s->execute();

		$res=$s->fetchAll(PDO::FETCH_ASSOC);
	}

	echo json_encode($res);

	function cmp($a, $b) {
		if ($a["sort"]<$b["sort"]) {
			return false;
		} else {
			return true;
		}
	}
?>