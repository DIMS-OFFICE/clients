<?php
	require("../pdo_db_connect.php");

	if ($_POST["today"]==1) {
		$s=$db->prepare("SELECT * FROM comments WHERE operator=:operator AND comment_date<=CURDATE() AND user_name='' ORDER BY comment_date DESC, tel_nom ASC");
		$s->bindValue(":operator", $_POST["operator"]);
	} else {
		$s=$db->prepare("SELECT * FROM comments WHERE operator=:operator AND comment_date=:comment_date ORDER BY user_name ASC, tel_nom ASC");
		$s->bindValue(":operator", $_POST["operator"]);
		$s->bindValue(":comment_date", $_POST["date"]);
	}

	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_ASSOC);
	
	echo json_encode($res);
?>