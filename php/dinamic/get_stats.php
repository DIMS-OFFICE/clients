<?php
	require("../pdo_db_connect.php");

	$result=Array();

	$i=0;

	if ($_POST["action"]=="loss") {
		if ($_POST["type"]=="by_year") {
			$month1=1;
			$month2=12;
			$year=$_POST["year"];
		} else {
			$month1=$_POST["month"];
			$month2=$_POST["month"]+1;
			$year=$_POST["year"];
		}

		$tel_noms_list=Array();

		for ($month=$month1; $month<$month2; $month++) {

			$max_day=cal_days_in_month(CAL_GREGORIAN, $month,  $_POST["year"]);

			$date=$_POST["year"]."-".addZero($month)."-".addZero($max_day);

			if ($_POST["year"]==date("Y") && addZero($month)==date("m", time()) && date("d", time())!=$max_day) {//Текущий месяц и не последний день - берём текущую дату
				$date=date("Y-m-d", time());
			}

			$s=$db->prepare("SELECT tel_nom, spended FROM clients.history WHERE update_date=:update_date AND operator=:operator");
			$s->bindValue(":update_date", $date);
			$s->bindValue(":operator", $_POST["operator"]);
			$s->execute();

			$spended_clients=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			$s=$db->prepare("SELECT tel_nom, spended FROM ".$_POST["operator"]."_counters_history WHERE update_date=:update_date GROUP BY tel_nom");
			$s->bindValue(":update_date", $date);
			$s->execute();

			$spended_from_dinamic=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			foreach ($spended_from_dinamic as $tel_nom => $spended) {
				if (isset($spended_clients[$tel_nom][0]["spended"])==false || $spended_from_dinamic[$tel_nom][0]["spended"]==false) {
					continue;
				}

				$spended_diff=$spended_from_dinamic[$tel_nom][0]["spended"]-$spended_clients[$tel_nom][0]["spended"];

				if ($spended_diff>0) {
					$result[$i]["tel_nom"]=$tel_nom;
					$result[$i]["spended_diff"]=$spended_diff;
					$result[$i]["spended_diff_formated"]=number_format($spended_diff, 2, ",", " ");
					$result[$i]["year_month"]=$_POST["year"]."-".addZero($month);
					$result[$i]["month"]=$month;

					if (in_array($tel_nom, $tel_noms_list)==false) {
						$tel_noms_list[]=$tel_nom;
					}

					$i++;
				}
			}
		}
	}

	if ($_POST["action"]=="1S") {
		if ($_POST["type"]=="by_year") {
			$month1=1;
			$month2=12;
			$year=$_POST["year"];
		} else {
			$month1=$_POST["month"];
			$month2=$_POST["month"];
			$year=$_POST["year"];
		}

		$tel_noms_list=Array();

	 	$s=$db->prepare("SELECT tel_nom, month, summ FROM clients.1s WHERE year=:year AND (month>=:month1 AND month<=:month2) AND diff!=0");
		$s->bindValue(":year", $year);
		$s->bindValue(":month1", $month1);
		$s->bindValue(":month2", $month2);
		$s->execute();

		if ($s->rowCount()>0) {
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

			$spended_diff_tel_noms=Array();
			$spended_diff_tel_noms_summs=Array();

			foreach ($tel_noms as $tn) {
				$spended_diff_tel_noms[$tn["tel_nom"]."-".$tn["month"]]=$tn["tel_nom"];
				$spended_diff_tel_noms_summs[$tn["tel_nom"]."-".$tn["month"]]["summ"]=$tn["summ"];
				$spended_diff_tel_noms_summs[$tn["tel_nom"]."-".$tn["month"]]["year_month"]=$year."-".addZero($tn["month"]);
				$spended_diff_tel_noms_summs[$tn["tel_nom"]."-".$tn["month"]]["month"]=$tn["month"];
			}

			$tel_noms=implode(",", $spended_diff_tel_noms);

			$s=$db->prepare("SELECT CONCAT(tel_nom, '-', month) as `key`, SUM(`sum`) as spended, month, operator FROM clients.spended WHERE year=:year AND (month>=:month1 AND month<=:month2) AND tel_nom IN (".$tel_noms.") GROUP BY tel_nom, year, month");
			$s->bindValue(":year", $year);
			$s->bindValue(":month1", $month1);
			$s->bindValue(":month2", $month2);
			$s->execute();

			$spended_from_dinamic=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			$s=$db->prepare("SELECT tel_nom, operator FROM clients.clients WHERE tel_nom IN (".$tel_noms.")");
			$s->execute();

			$operators=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			$i=0;

			foreach ($spended_diff_tel_noms as $tn_month => $tel_nom) {
				if (isset($spended_from_dinamic[$tn_month][0]["spended"])==false) {
					$result[$i]["diff"]=number_format($spended_diff_tel_noms_summs[$tn_month]["summ"], 2, ",", " ");
					$result[$i]["spended"]="0,00";
				} else {
					$result[$i]["diff"]=number_format($spended_diff_tel_noms_summs[$tn_month]["summ"]-$spended_from_dinamic[$tn_month][0]["spended"], 2, ",", " ");
					$result[$i]["spended"]=number_format($spended_from_dinamic[$tn_month][0]["spended"], 2, ",", " ");
				}

				$result[$i]["tel_nom"]=$tel_nom;
				$result[$i]["1s"]=number_format($spended_diff_tel_noms_summs[$tn_month]["summ"], 2, ",", " ");
				$result[$i]["month"]=$spended_diff_tel_noms_summs[$tn_month]["month"];
				$result[$i]["year_month"]=$spended_diff_tel_noms_summs[$tn_month]["year_month"];
				$result[$i]["operator"]=$operators[$tel_nom][0]["operator"];

				if (in_array($tel_nom, $tel_noms_list)==false) {
					$tel_noms_list[]=$tel_nom;
				}

				$i++;
			}
		}
	}

	$sort_direction=$_POST["sort_direction"];

	usort($result, "cmp");

	sort($tel_noms_list);

	$results=Array(
		"result" => $result,
		"tel_noms_list" => $tel_noms_list
	);

	echo json_encode($results);

	function cmp($a, $b) {
		global $sort_direction;

		if ($sort_direction=="") {
			if ($a["tel_nom"]>$b["tel_nom"]) {
				return true;
			} else if ($a["tel_nom"]==$b["tel_nom"]) {
				return $a["month"]>$b["month"] ? -1:1;
			} else {
				return false;
			}
		} else if ($sort_direction=="asc") {
			if ($a["spended_diff"]>$b["spended_diff"]) {
				return true;
			} else if ($a["spended_diff"]<$b["spended_diff"]) {
				return false;
			} else if ($a["spended_diff"]==$b["spended_diff"]) {
				if ($a["tel_nom"]>$b["tel_nom"]) {
					return true;
				} else if ($a["tel_nom"]==$b["tel_nom"]) {
					return $a["month"]>$b["month"] ? -1:1;
				} else {
					return false;
				}
			}
		} else if ($sort_direction=="desc") {
			if ($a["spended_diff"]<$b["spended_diff"]) {
				return true;
			} else if ($a["spended_diff"]<$b["spended_diff"]) {
				return false;
			} else if ($a["spended_diff"]==$b["spended_diff"]) {
				if ($a["tel_nom"]>$b["tel_nom"]) {
					return true;
				} else if ($a["tel_nom"]==$b["tel_nom"]) {
					return $a["month"]>$b["month"] ? -1:1;
				} else {
					return false;
				}
			}
		}
	}

    function addZero($num) {
    	if ($num<10) {
    		return "0".$num;
    	} else {
    		return $num;
    	}
    }	
?>