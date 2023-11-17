<?php
	require("pdo_db_connect.php");

	if ($_POST["operator"]=="bee") {
		$_POST["operator"]="beeline";
	}
	if ($_POST["operator"]=="meg") {
		$_POST["operator"]="megafon";
	}

	$s=$db->prepare("SELECT @i:=@i+1 num, name FROM logins, (SELECT @i:=0) X WHERE operator=:operator");
	$s->bindValue(":operator", $_POST["operator"]);
	$s->execute();

	$res=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

	echo json_encode($res);
?>