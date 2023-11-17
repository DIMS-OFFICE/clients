<?php
	require("pdo_db_connect.php");

	$_POST["price"]=str_replace(",",".",$_POST["price"]);
	
	if (isset($_POST["service_name"])) {
		$s=$db->prepare("UPDATE clients.services_dict SET service=:service WHERE id=:service_id");
		$s->bindValue(":service_id", $_POST["service_id"]);
		$s->bindValue(":service", $_POST["service_name"]);
		$s->execute();

		$s1=$db->prepare("UPDATE clients.spended SET category=:new_category WHERE service_code=:service_id");
		$s1->bindValue(":service_id", $_POST["service_id"]);
		$s1->bindValue(":new_category", $_POST["service_name"]);
		$s1->execute();

		if ($s->rowCount()>0 && $s1->rowCount()>0) {
			echo "OK";
		} else {
			echo "error";
		}
		exit();
	} else {
		if ($_POST["action"]=="add") {
			$s=$db->prepare("SELECT MAX(sort) FROM clients.services_dict WHERE operator=:operator");
			$s->bindValue(":operator", $_POST["operator"]);
			$s->execute();

			$max_sort=$s->fetch(PDO::FETCH_COLUMN);
			$new_sort=$max_sort++;

			$s=$db->prepare("INSERT INTO clients.services_dict (service, sort, operator) VALUES (:service, :sort, :operator)");
			$s->bindValue(":service", $_POST["service"]);
			$s->bindValue(":sort", $new_sort);
			$s->bindValue(":operator", $_POST["operator"]);
			$s->execute();

			$s=$db->prepare("SELECT MAX(id) FROM clients.services_dict");
			$s->execute();

			$new_code=$s->fetch(PDO::FETCH_COLUMN);

			$s=$db->prepare("INSERT INTO clients.services_prices (service_code, `date`, price, operator) VALUES (:service_code, :date, :price, :operator)");
			$s->bindValue(":service_code", $new_code);
			$s->bindValue(":date", date("Y-m-d", time()));
			$s->bindValue(":operator", $_POST["operator"]);
			$s->bindValue(":price", $_POST["price"]);
			$s->execute();
		} else {
			$s=$db->prepare("SELECT id FROM clients.services_prices WHERE service_code=:service_code ORDER BY `date` DESC LIMIT 1");
			$s->bindValue(":service_code", $_POST["service_id"]);
			$s->execute();

			$id=$s->fetch(PDO::FETCH_COLUMN);

			$s=$db->prepare("UPDATE clients.services_prices SET finish_date=DATE(NOW()) WHERE id=:id");
			$s->bindValue(":id", $id);
			$s->execute();

			$s=$db->prepare("INSERT INTO clients.services_prices (service_code, `date`, price, operator) VALUES (:service_code, :date, :price, :operator)");
			$s->bindValue(":service_code", $_POST["service_id"]);
			$s->bindValue(":price", $_POST["price"]);
			$s->bindValue(":date", date("Y-m-d", time()));
			$s->bindValue(":operator", $_POST["operator"]);
			$s->execute();
		}
	}

	if ($s->rowCount()>0) {
		echo "OK";
	} else {
		echo "error";
	}
?>