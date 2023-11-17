<?php
	require("pdo_db_connect.php");

	if ($_POST["to_row_id"]>$_POST["from_row_id"]) {//Перемещение вниз, остальных двигаем вверх
		$s=$db->prepare("UPDATE clients.services_dict SET sort=sort-1 WHERE sort BETWEEN :old_sort+1 AND :new_sort");
		$s->bindValue(":new_sort", $_POST["to_row_id"]);
		$s->bindValue(":old_sort", $_POST["from_row_id"]);
		$s->execute();

		$s1=$db->prepare("UPDATE clients.services_dict SET sort=:new_sort WHERE id=:id");
		$s1->bindValue(":new_sort", $_POST["to_row_id"]);
		$s1->bindValue(":id", $_POST["id"]);
		$s1->execute();
	} else {//Перемещение вверх, остальных двигаем вниз
		$s=$db->prepare("UPDATE clients.services_dict SET sort=sort+1 WHERE sort BETWEEN :new_sort AND :old_sort-1");
		$s->bindValue(":new_sort", $_POST["to_row_id"]);
		$s->bindValue(":old_sort", $_POST["from_row_id"]);
		$s->execute();

		$s1=$db->prepare("UPDATE clients.services_dict SET sort=:new_sort WHERE id=:id");
		$s1->bindValue(":new_sort", $_POST["to_row_id"]);
		$s1->bindValue(":id", $_POST["id"]);
		$s1->execute();
	}

	if ($s->rowCount()>0 && $s1->rowCount()>0) {
		echo "OK";
	} else {
		echo "Error";
	}
?>