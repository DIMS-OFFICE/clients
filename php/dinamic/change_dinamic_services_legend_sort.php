<?php
	require("../pdo_db_connect.php");

	//echo $_POST["from_service_id"].":".$_POST["to_service_id"]."\n";

	$s=$db->prepare("SELECT sort FROM clients.services_dict WHERE id=:from_service_id");
	$s->bindValue(":from_service_id", $_POST["from_service_id"]);
	$s->execute();

	$from_sort=$s->fetch(PDO::FETCH_COLUMN);

	$s=$db->prepare("SELECT sort FROM clients.services_dict WHERE id=:to_service_id");
	$s->bindValue(":to_service_id", $_POST["to_service_id"]);
	$s->execute();

	$new_sort=$s->fetch(PDO::FETCH_COLUMN);

	//echo $from_sort.":".$new_sort."\n";

	if ($new_sort>$from_sort) {//Перемещение вниз, остальных двигаем вверх
		//echo "UPDATE services_dict SET sort=sort-1 WHERE sort BETWEEN ".$_POST["from_row_id"]."+1 AND ".$_POST["to_row_id"]." AND operator='mts'";

		$s=$db->prepare("UPDATE clients.services_dict SET sort=sort-1 WHERE sort BETWEEN :old_sort+1 AND :new_sort AND operator=:operator");
		$s->bindValue(":new_sort", $new_sort);
		$s->bindValue(":old_sort", $from_sort);
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$s1=$db->prepare("UPDATE clients.services_dict SET sort=:new_sort WHERE id=:id AND operator=:operator");
		$s1->bindValue(":new_sort", $new_sort);
		$s1->bindValue(":id", $_POST["from_service_id"]);
		$s1->bindValue(":operator", $_POST["operator"]);
		$s1->execute();
	} else {//Перемещение вверх, остальных двигаем вниз
		$s=$db->prepare("UPDATE clients.services_dict SET sort=sort+1 WHERE sort BETWEEN :new_sort AND :old_sort-1 AND operator=:operator");
		$s->bindValue(":new_sort", $new_sort);
		$s->bindValue(":old_sort", $from_sort);
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$s1=$db->prepare("UPDATE clients.services_dict SET sort=:new_sort WHERE id=:id AND operator=:operator");
		$s1->bindValue(":new_sort", $new_sort);
		$s1->bindValue(":id", $_POST["from_service_id"]);
		$s1->bindValue(":operator", $_POST["operator"]);
		$s1->execute();
	}

	if ($s->rowCount()>0 && $s1->rowCount()>0) {
		echo "OK";
	} else {
		echo "Error";
	}
?>