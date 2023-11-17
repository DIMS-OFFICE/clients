<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT * FROM clients.countries ORDER BY country");
	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_ASSOC);

	echo json_encode($res);
?>