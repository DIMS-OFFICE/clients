<?php
	require("pdo_db_connect.php");

	if ($_POST["action"]=="new_history_record") {
		$s=$db->prepare("UPDATE clients.call_types_prices SET finish_date=DATE(NOW()-INTERVAL 24 HOUR) WHERE id=:id");
		$s->bindValue(":id", $_POST["id"]);
		$s->execute();

		$s=$db->prepare("SELECT call_type, type_nom, operator FROM clients.call_types_prices WHERE id=:id");
		$s->bindValue(":id", $_POST["id"]);
		$s->execute();

		$record=$s->fetch(PDO::FETCH_ASSOC);

		$s=$db->prepare("INSERT INTO clients.call_types_prices (call_type, type_nom, `date`, price, operator) VALUES (:call_type, :type_nom, CURDATE(), :price, :operator)");
		$s->bindValue(":call_type", $record["call_type"]);
		$s->bindValue(":type_nom", $record["type_nom"]);
		$s->bindValue(":operator", $record["operator"]);
		$s->bindValue(":price", $_POST["price"]);
		$s->execute();
	} else {
		$s=$db->prepare("UPDATE clients.call_types_prices SET price=:price WHERE id=:id");
		$s->bindValue(":price", $_POST["price"]);
		$s->bindValue(":id", $_POST["id"]);
		$s->execute();
	}

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>