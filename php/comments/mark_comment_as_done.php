<?php
	require("../pdo_db_connect.php");

	if ($_POST["unmark"]==0) {
		$s=$db->prepare("UPDATE comments SET user_name=:user_name WHERE id=:comment_id");
		$s->bindValue(":user_name", $_POST["user_name"]);
		$s->bindValue(":comment_id", $_POST["comment_id"]);
	} else {
		$s=$db->prepare("UPDATE comments SET user_name='' WHERE id=:comment_id");
		$s->bindValue(":comment_id", $_POST["comment_id"]);
	}

	$s->execute();

	if ($s->rowCount()>0) {
		echo "OK";
	}
?>