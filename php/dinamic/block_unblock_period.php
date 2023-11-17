<?php
	require("../pdo_db_connect.php");

	if ($_POST["action"]==1) {
		$i=0;
		for ($year=2021; $year<$_POST["year"]+1; $year++) {
			if ($year==$_POST["year"]) {
				for ($month=1; $month<$_POST["month"]+1; $month++) {
					$periods[$i]["year"]=$year;
					$periods[$i]["month"]=$month;

					$i++;
				}
			} else {
				for ($month=1; $month<13; $month++) {
					$periods[$i]["year"]=$year;
					$periods[$i]["month"]=$month;

					$i++;
				}
			}
		}
	} else {
		$current_year=date("Y", time());
		$current_month=date("m", time());

		$i=0;

		if ($_POST["year"]==$current_year) {
			for ($month=$_POST["month"]; $month<$current_month+1; $month++) {
				$periods[$i]["year"]=$_POST["year"];
				$periods[$i]["month"]=$month;

				$i++;
			}
		} else {
			for ($year=$_POST["year"]; $year<$current_year+1; $year++) {
				if ($year>$_POST["year"]) {
					for ($month=1; $month<$current_month+1; $month++) {
						$periods[$i]["year"]=$year;
						$periods[$i]["month"]=$month;

						$i++;
					}
				} else {
					for ($month=$_POST["month"]; $month<13; $month++) {
						$periods[$i]["year"]=$year;
						$periods[$i]["month"]=$month;

						$i++;
					}
				}
			}
		}
	}

	//print_r($periods);
	//exit();
	
	$s=$db->prepare("UPDATE clients.spended SET blocked=:action WHERE tel_nom=:tel_nom AND year=:year AND month=:month");

	foreach ($periods as $period) {
		$s->bindValue(":action", $_POST["action"]);
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":year", $period["year"]);
		$s->bindValue(":month", $period["month"]);

		$s->execute();
	}
?>