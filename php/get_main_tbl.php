<?php
	require("pdo_db_connect.php");

	$tarifs_filter=json_decode($_POST["tarif_filter"], true);
	$accounts_filter=json_decode($_POST["account_filter"], true);
	$contracts_filter=json_decode($_POST["contract_filter"], true);

	if (count($tarifs_filter)>0) {
		foreach ($tarifs_filter as $tf) {
			$t[]="'".$tf."'";
		}
		$tarifs_filter=$_POST["operator"]."_counters_actual.tarif IN (".implode(",", $t).")";
	} else {
		$tarifs_filter="1=1";
	}

	if (count($accounts_filter)>0) {
		$accounts_filter="clients.account IN (".implode(",", $accounts_filter).")";
	} else {
		$accounts_filter="1=1";
	}

	if (count($contracts_filter)>0) {
		$contracts_filter="clients.contract IN (".implode(",", $contracts_filter).")";
	} else {
		$contracts_filter="1=1";
	}

	//Блокированные
	/*if ($_POST["status"]==2) {
		$blocked_numbers_filter="LENGTH(".$_POST["operator"]."_counters_actual.blocks)>1";
	} else {
		$blocked_numbers_filter="1=1";
	}*/

	if ($_POST["status"]==1) {//All
		$removed_numbers_filter="clients.clients.account>0";
		$blocked_numbers_filter="1=1";
	} else if ($_POST["status"]==2) {//Only Blocked
		$blocked_numbers_filter="blocks!='' AND blocks!='-'";
		$removed_numbers_filter="clients.clients.account>0";
	} else if ($_POST["status"]==3) {//Only Unblocked
		$blocked_numbers_filter="(blocks='' OR blocks='-')";
		$removed_numbers_filter="clients.clients.account>0";
	} else if ($_POST["status"]==4) {//Only Removed
		$removed_numbers_filter="clients.clients.account=0";
		$blocked_numbers_filter="1=1";
	}

	//Группы пользователей
	if (isset($_POST["client_groups_filter"])==false || $_POST["client_groups_filter"]=="Все группы") {
		$client_groups_filter="1=1";
	} else if ($_POST["client_groups_filter"]=="Не в группе") {
		$client_groups_filter="client_group=''";
	} else {
		$client_groups_filter="client_group='".$_POST["client_groups_filter"]."'";
	}

	$sort_field=$_POST["sort_field"];
	$sort_direction=$_POST["sort_direction"];

	if ($_POST["tel_nom"]!='') {
		$s_users=$db->prepare("SELECT tel_nom, fio FROM users_profiles WHERE operator=:operator AND (LOCATE(:tel_nom,fio) OR LOCATE(:tel_nom,tel_nom))");
		$s_users->bindValue(":operator", $_POST["operator"]);
		$s_users->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s_users->execute();
		$users=$s_users->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		if (count($users)>0) {
			foreach ($users as $tel_nom => $fio) {
				$users_noms[]=$tel_nom;
			}
			$users_noms=implode(",",$users_noms);
		} else {
			$users_noms=0;
		}

		$s=$db->prepare("SELECT clients.tel_nom, clients.account, clients.contract, clients.balance, clients.spended, clients.update_date, clients.comment, clients.update_date, clients.update_time, clients.client_group, UNIX_TIMESTAMP(STR_TO_DATE(CONCAT(clients.update_date,' ',clients.update_time), '%Y-%m-%d %H-%i-%s')) as actual, DATE_FORMAT(".$_POST["operator"]."_counters_actual.refresh_date, '%y-%m-%d') as refresh_date, ".$_POST["operator"]."_counters_actual.tarif, ".$_POST["operator"]."_counters_actual.blocks FROM clients.clients INNER JOIN ".$_POST["operator"]."_counters_actual ON clients.clients.tel_nom=".$_POST["operator"]."_counters_actual.tel_nom WHERE clients.operator=:operator AND (LOCATE(:tel_nom, clients.tel_nom)>0 OR clients.tel_nom IN (".$users_noms.")) GROUP BY tel_nom");
		
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
	} else {
		$s_users=$db->prepare("SELECT tel_nom, fio FROM users_profiles WHERE operator=:operator");
		$s_users->bindValue(":operator", $_POST["operator"]);
		$s_users->execute();
		$users=$s_users->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT clients.tel_nom, clients.account, clients.contract, clients.balance, clients.spended, clients.comment, clients.update_date, clients.update_time, clients.client_group, UNIX_TIMESTAMP(STR_TO_DATE(CONCAT(clients.update_date,' ',clients.update_time), '%Y-%m-%d %H-%i-%s')) as actual, DATE_FORMAT(".$_POST["operator"]."_counters_actual.refresh_date, '%y-%m-%d') as refresh_date, ".$_POST["operator"]."_counters_actual.tarif, ".$_POST["operator"]."_counters_actual.blocks FROM clients.clients INNER JOIN ".$_POST["operator"]."_counters_actual ON clients.clients.tel_nom=".$_POST["operator"]."_counters_actual.tel_nom WHERE clients.operator=:operator AND (".$tarifs_filter." AND ".$accounts_filter." AND ".$contracts_filter." AND ".$blocked_numbers_filter." AND ".$removed_numbers_filter." AND ".$client_groups_filter.") GROUP BY tel_nom ORDER BY ".$sort_field." ".$sort_direction);
	}

	$s->bindValue(":operator", $_POST["operator"]);
	$s->execute();

	$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

	if ($_POST["loss_filter"]==1) {
		$max_day=cal_days_in_month(CAL_GREGORIAN, $_POST["month"],  $_POST["year"]);

		$date=$_POST["year"]."-".addZero($_POST["month"])."-".addZero($max_day);

		$s=$db->prepare("SELECT tel_nom, spended FROM clients.history WHERE update_date=:update_date");
		$s->bindValue(":update_date", $date);
		$s->execute();

		$spended_clients=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
		$s=$db->prepare("SELECT tel_nom, spended FROM ".$_POST["operator"]."_counters_history WHERE update_date=:update_date GROUP BY tel_nom");
		$s->bindValue(":update_date", $date);
		$s->execute();

		$spended_from_dinamic=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
	}

	if ($_POST["ss_spended_diff_filter"]==1) {
		$s=$db->prepare("SELECT tel_nom FROM clients.1s WHERE year=:year AND month=:month AND diff!=0");
		$s->bindValue(":year", $_POST["year"]);
		$s->bindValue(":month", $_POST["month"]);
		$s->execute();

		$spended_diff=$s->fetchAll(PDO::FETCH_COLUMN);
	}

	$i=0;
	$total_balance=0;
	$total_spended=0;
	$calc_total_stats=0;

	$result=Array();

	foreach ($tel_noms as $tn) {
		if (($_POST["status"]==4 && $tn["tarif"]=="Удалён") || ($_POST["status"]!=4 && $tn["tarif"]!="Удалён") || $_POST["status"]==1) {
			$phone=$tn["tel_nom"];

			if (isset($_POST["client_groups_filter"]) && $_POST["client_groups_filter"]!="Все группы" && $_POST["client_groups_filter"]!="Не в группе") {
				$calc_total_stats=1;
				$total_balance+=$tn["balance"];
				$total_spended+=$tn["spended"];
			}

			if ($_POST["loss_filter"]==1 && isset($spended_clients[$phone]) && isset($spended_from_dinamic[$phone]) && $spended_clients[$phone][0]["spended"]-$spended_from_dinamic[$phone][0]["spended"]<0)  {
			
			
				$result[$i]["account"]=$tn["account"];
				$result[$i]["contract"]=$tn["contract"];
				$result[$i]["client_group"]=$tn["client_group"];
				$result[$i]["balance"]=$tn["balance"];
				$result[$i]["comment"]=$tn["comment"];
				$result[$i]["spended"]=$tn["spended"];
				$result[$i]["refresh_date"]=$tn["refresh_date"];
				$result[$i]["tarif"]=$tn["tarif"];
				$result[$i]["blocks"]=$tn["blocks"];
				$result[$i]["tel_nom"]=$phone;
				$result[$i]["name"] = $users[$tn["tel_nom"]][0]["fio"];

				if ($tn["update_date"]=="1970-01-01") {
					$result[$i]["actual"]=0;
				} else {
					$result[$i]["actual"]=strtotime($tn["update_date"]." ".$tn["update_time"]);
				}

				$i++;
			}

			if ($_POST["ss_spended_diff_filter"]==1 && in_array($phone, $spended_diff)) {
				$result[$i]["account"]=$tn["account"];
				$result[$i]["contract"]=$tn["contract"];
				$result[$i]["client_group"]=$tn["client_group"];
				$result[$i]["balance"]=$tn["balance"];
				$result[$i]["comment"]=$tn["comment"];
				$result[$i]["spended"]=$tn["spended"];
				$result[$i]["refresh_date"]=$tn["refresh_date"];
				$result[$i]["tarif"]=$tn["tarif"];
				$result[$i]["blocks"]=$tn["blocks"];
				$result[$i]["tel_nom"]=$phone;
				$result[$i]["name"] = $users[$tn["tel_nom"]][0]["fio"];

				if ($tn["update_date"]=="1970-01-01") {
					$result[$i]["actual"]=0;
				} else {
					$result[$i]["actual"]=strtotime($tn["update_date"]." ".$tn["update_time"]);
				}

				$i++;
			}

			if ($_POST["loss_filter"]==0 && $_POST["ss_spended_diff_filter"]==0) {
				$result[$i]["account"]=$tn["account"];
				$result[$i]["contract"]=$tn["contract"];
				$result[$i]["client_group"]=$tn["client_group"];
				$result[$i]["balance"]=$tn["balance"];
				$result[$i]["comment"]=$tn["comment"];
				$result[$i]["spended"]=$tn["spended"];
				$result[$i]["refresh_date"]=$tn["refresh_date"];
				$result[$i]["tarif"]=$tn["tarif"];
				$result[$i]["blocks"]=$tn["blocks"];
				$result[$i]["tel_nom"]=$phone;
				$result[$i]["name"] = $users[$tn["tel_nom"]][0]["fio"];

				if ($tn["update_date"]=="1970-01-01") {
					$result[$i]["actual"]=0;
				} else {
					$result[$i]["actual"]=strtotime($tn["update_date"]." ".$tn["update_time"]);
				}

				$i++;
			}
		}
	}

	if ($_POST["sort_field"]=='actual') {
		usort($result, 'cmp');
	}

	$result=Array(
		"result" => $result,
		"total_balance" => $total_balance,
		"total_spended" => $total_spended,
		"calc_total_stats" => $calc_total_stats
	);

	echo json_encode($result);

	function cmp($a, $b) {
		global $sort_field;
		global $sort_direction;
		
		if (floatval($a[$sort_field])==floatval($b[$sort_field])) {
			return floatval($a["tel_nom"])<floatval($b["tel_nom"]) ? -1:1;
		}

		if ($sort_direction=="desc") {
			return floatval($a[$sort_field])<floatval($b[$sort_field]) ? 1:-1;
		} else {
			return floatval($a[$sort_field])<floatval($b[$sort_field]) ? -1:1;
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