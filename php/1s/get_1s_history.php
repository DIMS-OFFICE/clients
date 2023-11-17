<?php
	$dir=realpath(dirname(__FILE__)."/../..");

    require($dir."/php/pdo_db_connect.php");

    $s=$db->prepare("SELECT date_time, revision FROM clients.1s_history GROUP BY revision ORDER BY revision DESC");
    $s->execute();

    $revisions=$s->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($revisions);
?>