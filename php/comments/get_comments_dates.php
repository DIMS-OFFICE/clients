<?php
	require("../pdo_db_connect.php");

	$start_date=$_POST["year"]."-".$_POST["month"]."-01";
	$finish_date=$_POST["year"]."-".$_POST["month"]."-31";

	$s=$db->prepare("SELECT DISTINCT DATE_FORMAT(comment_date, '%d') as comment_day FROM comments WHERE comment_date BETWEEN :start_date AND :finish_date AND operator=:operator");
	$s->bindValue(":start_date", $start_date);
	$s->bindValue(":finish_date", $finish_date);
	$s->bindValue(":operator", $_POST["operator"]);
	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_COLUMN);

	echo json_encode($res);
?>