<?php
	require("pdo_db_connect.php");
	require("cron_job_check.php");

	$year=date("Y", time());
	$month=date("m", time());
	$day=date("d", time());

	$prev_year=date("Y", time()-10*24*3600);
	$prev_month=date("m", time()-10*24*3600);

	$services_dict_g1000=Array(7, 36, 51, 66);
	$services_dict_bl=Array(8, 37, 52, 67);
	$services_dict_svoya=Array(5, 34, 49, 64);
	$services_dict_all_gprs=Array(7, 36, 51, 66, 8, 37, 52, 67, 25, 41, 56, 71, 30, 45, 60, 75);

	$s=$db->prepare("SELECT tel_nom, operator FROM clients.clients WHERE tel_nom>0");
	$s->execute();

	$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

	$s_active_services=$db->prepare("SELECT code FROM clients.services WHERE tel_nom=:tel_nom AND status='Активная'");

	$s_spended=$db->prepare("SELECT length FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month AND category=:category");

	foreach ($tel_noms as $tel_nom) {
		if ($tel_nom["operator"]=="meg") {
			$oper="megafon";
		} else {
			$oper=$tel_nom["operator"];
		}

		$s=$db->prepare("SELECT clients_calc FROM ".$oper."_phones WHERE PhoneNumber=:tel_nom");
		$s->bindValue(":tel_nom", $tel_nom["tel_nom"]);
		$s->execute();

		$clients_calc=$s->fetchAll(PDO::FETCH_COLUMN);

		if ($clients_calc==0) {
			continue;
		}

		//Если текущий месяц не посчитан - пропускаем
		$s_is_curr_month=$db->prepare("SELECT id FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month");
		$s_is_curr_month->bindValue(":tel_nom", $tel_nom["tel_nom"]);
		$s_is_curr_month->bindValue(":year", $year);
		$s_is_curr_month->bindValue(":month", $month);
		$s_is_curr_month->execute();

		if ($s_is_curr_month->rowCount()==0) {
			continue;
		}

		$s_active_services->bindValue(":tel_nom", $tel_nom["tel_nom"]);
		$s_active_services->execute();

		if ($s_active_services->rowCount()>0) {		
			$active_services=$s_active_services->fetchAll(PDO::FETCH_COLUMN);
		} else {
			$active_services=Array();
		}

		//Если есть своя сеть
		if (is_service($active_services, $services_dict_svoya)) {
			$s_spended->bindValue(":tel_nom", $tel_nom["tel_nom"]);
			$s_spended->bindValue(":year", $year);
			$s_spended->bindValue(":month", $month);
			$s_spended->bindValue(":category", 'МИНУТ ВСР');
			$s_spended->execute();

			if ($s_spended->rowCount()>0) {
				$spended["МИНУТ ВСР"]=$s_spended->fetch(PDO::FETCH_COLUMN);
			} else {
				$spended["МИНУТ ВСР"]=0;
			}

			if ($day==16) {
				$max_length=100;

				if ($spended["МИНУТ ВСР"]<$max_length) {
					save_attention($tel_nom["tel_nom"], "no_need_svoya", "НЕ нужна СВОЯ СЕТЬ (".$spended["МИНУТ ВСР"]."<".$max_length.")", 0, $tel_nom["operator"]);
				}
			}
		} else {//Если нет своей сети
			$s_spended->bindValue(":tel_nom", $tel_nom["tel_nom"]);
			$s_spended->bindValue(":year", $year);
			$s_spended->bindValue(":month", $month);
			$s_spended->bindValue(":category", 'ИСХ-ВСР');
			$s_spended->execute();

			if ($s_spended->rowCount()>0) {
				$spended["ИСХ-ВСР"]=$s_spended->fetch(PDO::FETCH_COLUMN);
			} else {
				$spended["ИСХ-ВСР"]=0;
			}

			if ($day>=1 && $day<=16) {
				$min_length=200;
			} else {
				$min_length=325;
			}

			if ($spended["ИСХ-ВСР"]>$min_length) {
				save_attention($tel_nom["tel_nom"], "need_svoya", "Нужна СВОЯ СЕТЬ (".$spended["ИСХ-ВСР"].">".$min_length.")", 0, $tel_nom["operator"]);
			}
		}

		$s_spended->bindValue(":tel_nom", $tel_nom["tel_nom"]);
		$s_spended->bindValue(":year", $year);
		$s_spended->bindValue(":month", $month);
		$s_spended->bindValue(":category", 'GPRS');
		$s_spended->execute();

		if ($s_spended->rowCount()>0) {
			$spended["GPRS"]=$s_spended->fetch(PDO::FETCH_COLUMN);
		} else {
			$spended["GPRS"]=0;
		}

		//Если есть G1000
		if (is_service($active_services, $services_dict_g1000)) {
			if ($day>=1 && $day<=16) {
				$min_length=5000;
			} else {
				$min_length=8000;
			}

			if ($spended["GPRS"]>$min_length) {
				save_attention($tel_nom["tel_nom"], "need_gprs_bl", "Нужен GPRS-БЛ (".$spended["GPRS"].">".$min_length.")", 0, $tel_nom["operator"]);
			}
		}

		//Если есть GPRS-BL
		if (is_service($active_services, $services_dict_bl)) {
			if ($day==16) {
				$max_length=3000;

				if ($spended["GPRS"]<$max_length) {
					save_attention($tel_nom["tel_nom"], "no_need_gprs_bl", "НЕ нужен GPRS-БЛ (".$spended["GPRS"]."<".$max_length.")", 0, $tel_nom["operator"]);
				}
			}
		}

		//Если есть любая услуга про Интернет
		//print_r($active_services);

		if ($day==1) {
			//Если предыдущий месяц не посчитан - пропускаем
			$s_is_prev_month=$db->prepare("SELECT id FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month");
			$s_is_prev_month->bindValue(":tel_nom", $tel_nom["tel_nom"]);
			$s_is_prev_month->bindValue(":year", $prev_year);
			$s_is_prev_month->bindValue(":month", $prev_month);
			$s_is_prev_month->execute();

			if ($s_is_prev_month->rowCount()==0) {
				continue;
			}

			//Если есть своя сеть
			if (is_service($active_services, $services_dict_svoya)) {
				$s_spended->bindValue(":tel_nom", $tel_nom["tel_nom"]);
				$s_spended->bindValue(":year", $prev_year);
				$s_spended->bindValue(":month", $prev_month);
				$s_spended->bindValue(":category", 'МИНУТ ВСР');
				$s_spended->execute();

				if ($s_spended->rowCount()>0) {
					$prev_spended["МИНУТ ВСР"]=$s_spended->fetch(PDO::FETCH_COLUMN);
				} else {
					$prev_spended["МИНУТ ВСР"]=0;
				}

				$max_length=275;

				if ($prev_spended["МИНУТ ВСР"]<$max_length) {
					save_attention($tel_nom["tel_nom"], "no_need_svoya", "НЕ нужна СВОЯ СЕТЬ (".$prev_spended["МИНУТ ВСР"]."<".$max_length.")", 0, $tel_nom["operator"]);
				}
			} else {
				$s_spended->bindValue(":tel_nom", $tel_nom["tel_nom"]);
				$s_spended->bindValue(":year", $prev_year);
				$s_spended->bindValue(":month", $prev_month);
				$s_spended->bindValue(":category", 'ИСХ-ВСР');
				$s_spended->execute();

				if ($s_spended->rowCount()>0) {
					$prev_spended["МИНУТ ВСР"]=$s_spended->fetch(PDO::FETCH_COLUMN);
				} else {
					$prev_spended["МИНУТ ВСР"]=0;
				}

				$min_length=325;

				if ($prev_spended["МИНУТ ВСР"]>$min_length) {
					save_attention($tel_nom["tel_nom"], "need_svoya", "Нужна СВОЯ СЕТЬ (".$prev_spended["МИНУТ ВСР"].">".$min_length.")", 0, $tel_nom["operator"]);
				}
			}

			$s_spended->bindValue(":tel_nom", $tel_nom["tel_nom"]);
			$s_spended->bindValue(":year", $prev_year);
			$s_spended->bindValue(":month", $prev_month);
			$s_spended->bindValue(":category", 'GPRS');
			$s_spended->execute();

			if ($s_spended->rowCount()>0) {
				$prev_spended["GPRS"]=$s_spended->fetch(PDO::FETCH_COLUMN);
			} else {
				$prev_spended["GPRS"]=0;
			}

			if (is_service($active_services, $services_dict_all_gprs)) {
				if ($prev_spended["GPRS"]<0.01) {
					save_attention($tel_nom["tel_nom"], "no_use_inet", "НЕ пользуется GPRS (".$prev_spended["GPRS"].")", 1, $tel_nom["operator"]);
				}
			} else {
				if ($prev_spended["GPRS"]>0) {
					save_attention($tel_nom["tel_nom"], "use_inet", "Пользуется GPRS (".$prev_spended["GPRS"].")", 0, $tel_nom["operator"]);
				}
			}

			if (is_service($active_services, $services_dict_bl)) {
				$max_length=7000;

				if ($prev_spended["GPRS"]<$max_length) {
					save_attention($tel_nom["tel_nom"], "no_need_gprs_bl", "НЕ нужен GPRS-БЛ (".$prev_spended["GPRS"]."<".$max_length.")", 0, $tel_nom["operator"]);
				}
			} else {
				$min_length=8000;

				if ($prev_spended["GPRS"]>$min_length) {
					save_attention($tel_nom["tel_nom"], "need_gprs_bl", "Нужен GPRS-БЛ (".$prev_spended["GPRS"].">".$min_length.")", 0, $tel_nom["operator"]);
				}
			}
		}
	}



	function is_service($active_services, $compare_array) {
		/*print_r($active_services);

		if (count(array_intersect($active_services, $compare_array))==0) {
			return false;
		} else {
			return true;
		}*/

		foreach ($active_services as $as) {
			foreach ($compare_array as $ca) {
				//echo $as."==".$ca."\n";
				if ($as == $ca) {
					return true;

					break 2;
				}
			}
		}

		return false;
	}

	function save_attention($tel_nom, $type, $txt, $done, $oper) {
			global $db;

			if (no_attention($tel_nom, $type)) {
				return false;
			}

			$s_select=$db->prepare("SELECT id FROM clients.attentions WHERE tel_nom=:tel_nom AND type=:type AND `date`=DATE(NOW())");

			$s_insert=$db->prepare("INSERT INTO clients.attentions (tel_nom, `date`, txt, done, type, operator, user) VALUES (:tel_nom, DATE(NOW()), :txt, :done, :type, :operator, :user)");

			$s_select->bindValue(":tel_nom", $tel_nom);
			$s_select->bindValue(":type", $type);
			$s_select->execute();

			echo $tel_nom.": ".$txt;

			if ($done==1) {
				echo " (Автоотжатие)";
			}

			if ($s_select->rowCount()==0 || $type=="") {
				if ($done==1) {
					$user="SCRIPT";
				} else {
					$user="";
				}

				try {
					$s_insert->bindValue(":tel_nom", $tel_nom);
					$s_insert->bindValue(":txt", $txt);
					$s_insert->bindValue(":done", $done);
					$s_insert->bindValue(":type", $type);
					$s_insert->bindValue(":operator", $oper);
					$s_insert->bindValue(":user", $user);
					$s_insert->execute();

					echo " added\n";
				} catch (Exception $e) {
					
				}

				return true;
			} else {
				echo "\n";
				return false;
			}
	}

	function no_attention($tel_nom, $type) {
		global $db;

		$s_no_attentions=$db->prepare("SELECT id FROM clients.no_attentions WHERE until_date>=DATE(NOW()) AND tel_nom=:tel_nom AND type=:type");
		$s_no_attentions->bindValue(":tel_nom", $tel_nom);
		$s_no_attentions->bindValue(":type", $type);

		$s_no_attentions->execute();

		if ($s_no_attentions->rowCount()>0) {
			return true;
		} else {
			return false;
		}
	}
?>