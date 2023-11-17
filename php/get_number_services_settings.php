<?php
	require("pdo_db_connect.php");

	$s=$db->prepare("SELECT service_code, start_date, finish_date FROM clients.services_history WHERE tel_nom=:tel_nom GROUP BY service_code, `start_date`");
	$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	$s->execute();

	if ($s->rowCount()==0) {
		$codes="1";
	} else {
		$services_history=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$codes=array_keys($services_history);

		$codes=implode(",",$codes);
	}

	$s=$db->prepare("SELECT service_code, `date`, price FROM clients.services_prices WHERE operator=:operator AND service_code IN (".$codes.") GROUP BY service_code, `date` ORDER BY service_code ASC, `date` DESC");
	$s->bindValue(":operator", $_POST["operator"]);
	$s->execute();

	$services_prices_history=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	$s_exceptions=$db->prepare("SELECT exception FROM clients.exceptions");
	$s_exceptions->execute();

	$exceptions=$s_exceptions->fetchAll(PDO::FETCH_COLUMN);

	$result=Array(
		"services_history" => $services_history,
		"services_prices_history" => $services_prices_history,
		"numbers_exceptions" => $exceptions
	);

	echo json_encode($result);
?>