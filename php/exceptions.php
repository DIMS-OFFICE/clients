<?php
	require("pdo_db_connect.php");

	if ($_POST["action"]==0) {
		$s=$db->prepare("DELETE FROM clients.exceptions WHERE id=:id");
		$s->bindValue(":id", $_POST["id"]);
		$s->execute();

		if ($s->rowCount()>0) {
			echo "OK";
		} else {
			echo "error";
		}
	} else if ($_POST["action"]==1) {
		$s=$db->prepare("INSERT INTO clients.exceptions (exception, comment, `date`) VALUES (:new_exception, :comment, DATE(NOW()))");
		$s->bindValue(":new_exception", $_POST["new_exception"]);
		$s->bindValue(":comment", $_POST["new_exception_comment"]);
		$s->execute();

		if ($s->rowCount()>0) {
			echo "OK";
		} else {
			echo "error";
		}
	} else if ($_POST["action"]==2) {
		$s=$db->prepare("UPDATE clients.exceptions SET `comment`=:comment WHERE id=:id");
		$s->bindValue(":id", $_POST["id"]);
		$s->bindValue(":comment", $_POST["comment"]);
		$s->execute();

		if ($s->rowCount()>0) {
			echo "OK";
		} else {
			echo "error";
		}		
	} else {
		$s=$db->prepare("SELECT * FROM clients.exceptions ORDER BY exception");
		$s->execute();

		$res=$s->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode($res);
	}
?>