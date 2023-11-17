<?php
	ini_set('memory_limit', '3000M');

	date_default_timezone_set("Asia/Vladivostok");

	error_reporting(E_ALL & ~E_NOTICE);

	$dir=realpath(dirname(__FILE__) . '/..');

	if ($_POST["calc_in_background"]==1) {
		ignore_user_abort(true);
	}

	require("pdo_db_connect.php");
	require("cron_job_check.php");

	$spended=Array();
	$g1000=false;

	//G1000
	$codes["g1000"]=Array(
		"mts" => Array(7),
		"bee" => Array(36),
		"meg" => Array(51),
		"tele2" => Array(66)
	);

	//G3000
	$codes["g3000"]=Array(
		"mts" => Array(25),
		"bee" => Array(41),
		"meg" => Array(56),
		"tele2" => Array(71)
	);

	//GPRS БЛ и БЛ Интернет
	$codes["gprs_bl"]=Array(
		"mts" => Array(8,30),
		"bee" => Array(37,45),
		"meg" => Array(52,60),
		"tele2" => Array(67,75)
	);

	//G-роуминг
	$codes["g_roaming"]=Array(
		"mts" => Array(83),
		"bee" => Array(85),
		"meg" => Array(),
		"tele2" => Array()
	);

	//Л-роуминг
	$codes["l_roaming"]=Array(
		"mts" => Array(11),
		"bee" => Array(40),
		"meg" => Array(55),
		"tele2" => Array(70)
	);

	//SMS 300
	$codes["sms300"]=Array(
		"mts" => Array(82),
		"bee" => Array(),
		"meg" => Array(),
		"tele2" => Array()
	);

	//SMS 500
	$codes["sms500"]=Array(
		"mts" => Array(9),
		"bee" => Array(38),
		"meg" => Array(53),
		"tele2" => Array(68)
	);

	//1000 SMS
	$codes["sms1000"]=Array(
		"mts" => Array(31),
		"bee" => Array(46),
		"meg" => Array(61),
		"tele2" => Array(76)
	);

	//Своя сеть
	$codes["svoya_set"]=Array(
		"mts" => Array(5),
		"bee" => Array(34),
		"meg" => Array(49),
		"tele2" => Array(64)
	);

	//БЛ Минут
	$codes["bl_minut"]=Array(
		"mts" => Array(29),
		"bee" => Array(44),
		"meg" => Array(59),
		"tele2" => Array(74)
	);

	//Периодические
	$codes["periodic"]=Array(
		"mts" => Array(3,4,10,80,78),
		"bee" => Array(32,33,39,77,79),
		"meg" => Array(47,48,54),
		"tele2" => Array(62,63,69)
	);

	//Формирование массива расчётных периодов
	if (isset($_POST["tel_nom"])) {//Запуск из браузера
		$parts=explode("-",$_POST["year_month"]);

		$from_year=intval($parts[0]);
		$from_month=intval($parts[1]);

		if ($_POST["calc_in_background"]==1) {//В фоновом режиме от принятого месяца до текущего месяца
			$to_year=date("Y", time());
			$to_month=intval(date("m", time()));
		} else {//От принятого месяца до принятого месяца
			$to_year=intval($parts[0]);
			$to_month=intval($parts[1]);
		}
	} else {//Запуск из консоли/планировщика
		$from_year=date("Y", strtotime("-1 month"));
		$from_month=intval(date("m", strtotime("-1 month")));

		$to_year=date("Y", time());
		$to_month=intval(date("m", time()));
	}

	$current_year=date("Y", time());

	if (isset($_POST["tel_nom"]) && $_POST["calc_in_background"]==0) {
		$periods[0]["year"]=$from_year;
		$periods[0]["month"]=$from_month;

		$manual_update=true;
	} else {
		$manual_update=false;

		$i=0;
		for ($year=$from_year; $year<$to_year+1; $year++) {
			if ($year!=$current_year) {
				for ($month=$from_month; $month<13; $month++) {
					$periods[$i]["year"]=$year;
					$periods[$i]["month"]=$month;

					$i++;
				}
			} else {
				if ($to_month<2) {
					for ($month=1; $month<$to_month+1; $month++) {
						$periods[$i]["year"]=$current_year;
						$periods[$i]["month"]=$month;

						$i++;
					}
				} else {
					for ($month=$from_month; $month<$to_month+1; $month++) {
						$periods[$i]["year"]=$current_year;
						$periods[$i]["month"]=$month;

						$i++;
					}
				}
			}
		}

		/*$periods[0]["year"]=2021;
		$periods[0]["month"]=1;

		$periods[1]["year"]=2021;
		$periods[1]["month"]=2;

		$periods[2]["year"]=2021;
		$periods[2]["month"]=3;

		$periods[3]["year"]=2021;
		$periods[3]["month"]=4;

		$periods[4]["year"]=2021;
		$periods[4]["month"]=5;

		$periods[5]["year"]=2021;
		$periods[5]["month"]=6;

		$periods[6]["year"]=2021;
		$periods[6]["month"]=7;

		$periods[7]["year"]=2021;
		$periods[7]["month"]=8;

		$periods[8]["year"]=2021;
		$periods[8]["month"]=9;

		$periods[9]["year"]=2021;
		$periods[9]["month"]=10;	

		$periods[10]["year"]=2021;
		$periods[10]["month"]=11;	

		$periods[11]["year"]=2021;
		$periods[11]["month"]=12;	

		$periods[12]["year"]=2022;
		$periods[12]["month"]=1;

		$periods[13]["year"]=2022;
		$periods[13]["month"]=2;

		$periods[14]["year"]=2022;
		$periods[14]["month"]=3;

		$periods[15]["year"]=2022;
		$periods[15]["month"]=4;

		$periods[16]["year"]=2022;
		$periods[16]["month"]=5;

		$periods[17]["year"]=2022;
		$periods[17]["month"]=6;

		$periods[18]["year"]=2022;
		$periods[18]["month"]=7;

		$periods[19]["year"]=2022;
		$periods[19]["month"]=8;

		$periods[20]["year"]=2022;
		$periods[20]["month"]=9;

		$periods[21]["year"]=2022;
		$periods[21]["month"]=10;	

		$periods[22]["year"]=2022;
		$periods[22]["month"]=11;

		$periods[0]["year"]=2022;
		$periods[0]["month"]=12;

		$periods[1]["year"]=2023;
		$periods[1]["month"]=1;	

		$periods[2]["year"]=2023;
		$periods[2]["month"]=2;

		$periods[3]["year"]=2023;
		$periods[3]["month"]=3;*/							
	}

	//Запросы, не привязанные к оператору
	$s_spended_delete=$db->prepare("DELETE FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month");
	$s_spended_insert=$db->prepare("INSERT INTO clients.spended (tel_nom, category, service_code, `sum`, length, unit_price, `year`, `month`, update_date, update_time, user_name, operator) VALUES (:tel_nom, :category, :service_code, :sum, :length, :unit_price, :year, :month, :update_date, :update_time, :user_name, :operator)");

	$s_last_balance=$db->prepare("SELECT balance FROM clients.history WHERE tel_nom=:tel_nom AND update_date<:date ORDER BY update_date DESC LIMIT 1");

	$s_total_payments=$db->prepare("SELECT SUM(summ) as summ FROM clients.payments WHERE tel_nom=:tel_nom AND payment_date BETWEEN :date1 AND :date2 AND removed=0");
	$s_today_payments=$db->prepare("SELECT SUM(summ) as summ FROM clients.payments WHERE tel_nom=:tel_nom AND payment_date=:date AND removed=0");

	$s_select=$db->prepare("SELECT id FROM clients.history WHERE tel_nom=:tel_nom AND update_date=:date");
	$s_update=$db->prepare("UPDATE clients.history SET balance=:balance, spended=:spended, payments=:payments WHERE id=:id");
	$s_insert=$db->prepare("INSERT INTO clients.history (tel_nom, update_date, `year`, `month`, balance, spended, payments) VALUES (:tel_nom, :date, :year, :month, :balance, :spended, :payments)");

	$s_main_tbl=$db->prepare("UPDATE clients.clients SET balance=:balance, spended=:spended WHERE tel_nom=:tel_nom");

	$s_exceptions=$db->prepare("SELECT exception FROM clients.exceptions");
	$s_exceptions->execute();

	$exceptions=$s_exceptions->fetchAll(PDO::FETCH_COLUMN);

	$s_call_types_prices=$db->prepare("SELECT call_type, price FROM clients.call_types_prices WHERE operator=:operator AND :date BETWEEN `date` AND finish_date");

	$s_services_prices=$db->prepare("SELECT service_code, price FROM clients.services_prices WHERE operator=:operator AND :date BETWEEN `date` AND finish_date");

	$s_current_services=$db->prepare("SELECT tel_nom, service_code, kef FROM clients.services_history WHERE tel_nom=:tel_nom AND :date BETWEEN start_date AND finish_date");

	$s_edited_calls_prices=$db->prepare("SELECT CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, unit_price FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND call_date=:date");

	$s_blocked_periods=$db->prepare("SELECT blocked FROM clients.spended WHERE tel_nom=:tel_nom AND year=:year AND month=:month AND blocked=1");

	if (isset($_POST["tel_nom"])) {//Запуск из браузера
		$opers[0] = $_POST["operator"];
	} else {
		$options = getopt("o:");

		if ($options["o"]=="all") {
			$opers=Array("mts", "bee", "meg", "tele2");
		} else {
			$opers=Array($options["o"]);
		}
	}

	if (isset($_POST["user_name"])) {
		$user_name=$_POST["user_name"];
	} else {
		$user_name="SCRIPT";
	}

	foreach ($opers as $oper) {
		if ($oper=="meg") {
			$oper1="megafon";
		} else {
			$oper1=$oper;
		}

		if (isset($_POST["tel_nom"])) {//Запуск из браузера
			$tel_noms[0] = $_POST["tel_nom"];
		} else {
			$s_phones=$db->prepare("SELECT PhoneNumber FROM ".$oper1."_phones WHERE SUBSTR(PhoneNumber,1,1)!=2 AND SUBSTR(PhoneNumber,2,1)!=1 AND clients_calc=1 AND status=0");

			$s_phones->execute();
			$tel_noms=$s_phones->fetchAll(PDO::FETCH_COLUMN);

			if (count($tel_noms)==0) {
				save_attention("phones100", 7777777, "non_calc_clients", "Выключен расчёт клиентской части",  0, $oper);

				continue;
			}
		}

		if (isset($_POST["tel_nom"])==false) {
			print_log($db, strtoupper($oper)."-КЧ <B>Начало расчёта</B>");
		}


		if ($oper=="mts") {
			//У кого в офисе подключена Эксклюзивная сеть
			$s_is_service=$db->prepare("SELECT tel_nom FROM mts_services WHERE (service_id=2701 OR service_id=1823) AND status='Активная'");
			$s_is_service->execute();

			if ($s_is_service->rowCount()>0) {
				$is_service=$s_is_service->fetchAll(PDO::FETCH_COLUMN);
			} else {
				$is_service=Array();	
			}
		} else {
			$is_service=Array();
		}

		$nom_pp=0;
		foreach ($tel_noms as $tel_nom) {
			$nom_pp++;

			$s_call_types=$db->prepare("SELECT CONCAT(call_type,'-',service,'-',zone) as txt, type, items_count FROM call_types WHERE operator=:operator");
			$s_call_types->bindValue(":operator", $oper);
			$s_call_types->execute();

			$call_types1=$s_call_types->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			foreach ($call_types1 as $txt => $values) {
				if (stristr($txt, "Любая")) {
					$txt=str_replace("-Любая", "", $txt);
				}

				$call_types[$txt][0]["type"]=$values[0]["type"];
				$call_types[$txt][0]["items_count"]=$values[0]["items_count"];
				$call_types[$txt][0]["price"]=0;
			}

			//Коэффициенты типов вызовов
			$s=$db->prepare("SELECT call_type, kef FROM clients.call_types_kefs WHERE tel_nom=:tel_nom");
			$s->bindValue(":tel_nom", $tel_nom);
			$s->execute();

			$call_types_kefs=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);


			$s_services_dict=$db->prepare("SELECT id, service FROM clients.services_dict WHERE operator=:operator");
			$s_services_dict->bindValue(":operator", $oper);
			$s_services_dict->execute();
			$services_dict=$s_services_dict->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);


			foreach ($periods as $period) {
				echo "OPER: ".$oper." YEAR: ".$period["year"]." MONTH: ".$period["month"]."\n";

				$year=$period["year"];
				$month=$period["month"];

				$s_blocked_periods->bindValue(":tel_nom", $tel_nom);
				$s_blocked_periods->bindValue(":year", $year);
				$s_blocked_periods->bindValue(":month", $month);

				$s_blocked_periods->execute();

				if ($s_blocked_periods->rowCount()>0) {
					echo "BLOCKED PERIOD!!!!!!!!\n";

					continue;
				}

				$payments=0;

				$spended["services"]=Array();
				$spended["tarifs"]=Array();

				$sms=0;
				$gprs=0;
				$min=0;
				$roaming_gprs=0;
				
				if ($oper=="mts") {
					$col="service_type";
				} else {
					$col="call_type";
				}

				//Вызовы за месяц
				if ($oper=="tele2") {//Теле2 сдвигаем на 7 часов вперёд в связи с тем, что они ведут учёт по МСК (т.е. начало месяца - 07:00:00, а конец уже в следующем месяце)
					$max_day=cal_days_in_month(CAL_GREGORIAN, $month, $year);
					$from_time=$year."-".$month."-01 07:00:00";
					$to_time=$year."-".$month."-".$max_day." 23:59:59";//7 часов добавим уже в запросе

					$s_calls=$db->prepare("SELECT call_date, tel_nom, call_time, ".$col." as ct, service as serv, CONCAT(".$col.", '-', service) as call_type, call_length, from_number, to_number, value, unit, price FROM ".$oper."_detal WHERE STR_TO_DATE(CONCAT(call_date,' ',call_time), '%Y-%m-%d %H:%i:%s') >= :from_time AND STR_TO_DATE(CONCAT(call_date,' ',call_time), '%Y-%m-%d %H:%i:%s') < :to_time + INTERVAL 7 HOUR AND tel_nom=:tel_nom AND removed=0 ORDER BY call_date");
					$s_calls->bindValue(":tel_nom", $tel_nom);
					$s_calls->bindValue(":from_time", $from_time);
					$s_calls->bindValue(":to_time", $to_time);
				} else if ($oper=="mts" || $oper=="meg") {
					$s_calls=$db->prepare("SELECT call_date, tel_nom, call_time, ".$col." as ct, service as serv, CONCAT(".$col.", '-', service, '-', service_provider) as call_type, call_length, from_number, to_number, value, unit, price FROM ".$oper."_detal WHERE year=:year AND month=:month AND tel_nom=:tel_nom AND removed=0 ORDER BY call_date");
					$s_calls->bindValue(":tel_nom", $tel_nom);
					$s_calls->bindValue(":year", $year);
					$s_calls->bindValue(":month", $month);
				} else {
					$s_calls=$db->prepare("SELECT call_date, tel_nom, call_time, ".$col." as ct, service as serv, CONCAT(".$col.", '-', service) as call_type, call_length, from_number, to_number, value, unit, price FROM ".$oper."_detal WHERE year=:year AND month=:month AND tel_nom=:tel_nom AND removed=0 ORDER BY call_date");
					$s_calls->bindValue(":tel_nom", $tel_nom);
					$s_calls->bindValue(":year", $year);
					$s_calls->bindValue(":month", $month);
				}

				$s_calls->execute();

				//if ($s_calls->rowCount()>0) {
					$all_calls=$s_calls->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

					if ($oper=="tele2") {//Из-за сдвига на 7 часов нужно перенести вызовы из 1го числа следующего месяца в последний день расчитываемого
						$max_day_of_calc_month=cal_days_in_month(CAL_GREGORIAN, $month, $year);

						$max_date_of_calc_month=$year."-".addZero($month)."-".addZero($max_day_of_calc_month);

						$first_day_of_next_month=date("Y-m-d", strtotime($max_date_of_calc_month)+24*3600);

						if (isset($all_calls[$first_day_of_next_month])) {
							if (isset($all_calls[$max_date_of_calc_month])==false) {
								$all_calls[$max_date_of_calc_month]=Array();
							}

							foreach ($all_calls[$first_day_of_next_month] as $call_append) {
								$all_calls[$max_date_of_calc_month][]=$call_append;
							}
						}
					}

					//Изменённые периоды (кого НЕ нужно считать)
					$s=$db->prepare("SELECT CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type) as txt, year_old, month_old FROM clients.edited_calls_periods WHERE tel_nom=:tel_nom AND year_old=:year AND month_old=:month");
					$s->bindValue(":tel_nom", $tel_nom);
					$s->bindValue(":year", $year);
					$s->bindValue(":month", $month);
					$s->execute();

					$no_need_calc_edited_periods=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

					//Изменённые периоды (кого нужно ДОсчитать)
					$s=$db->prepare("SELECT call_length, call_date, call_time, phone, call_type, service, year_old, month_old FROM clients.edited_calls_periods WHERE tel_nom=:tel_nom AND year_edited=:year AND month_edited=:month");
					$s->bindValue(":tel_nom", $tel_nom);
					$s->bindValue(":year", $year);
					$s->bindValue(":month", $month);
					$s->execute();

					if ($s->rowCount()>0) {
						$need_calc_edited_periods=$s->fetchAll(PDO::FETCH_ASSOC);

						//Извлекаем все необходимые данные вызова из детализации
						if ($oper=="mts") {
							$column="service_type";
						} else {
							$column="call_type";
						}

						if ($oper=="meg" || $oper=="mts") {
							$s_call_append=$db->prepare("SELECT 0, tel_nom, call_time, ".$col." as ct, service as serv, CONCAT(".$col.", '-', service, '-', service_provider) as call_type, call_length, from_number, to_number, value, unit, price FROM ".$oper."_detal WHERE `month`=:month AND `year`=:year AND tel_nom=:tel_nom AND call_length=:call_length AND call_date=:call_date AND call_time=:call_time AND service=:service AND ".$column."=:call_type AND (from_number=:phone OR to_number=:phone)");
						} else {
							$s_call_append=$db->prepare("SELECT 0, tel_nom, call_time, ".$col." as ct, service as serv, CONCAT(".$col.", '-', service) as call_type, call_length, from_number, to_number, value, unit, price FROM ".$oper."_detal WHERE `month`=:month AND `year`=:year AND tel_nom=:tel_nom AND call_length=:call_length AND call_date=:call_date AND call_time=:call_time AND service=:service AND ".$column."=:call_type AND (from_number=:phone OR to_number=:phone)");
						}

						$append_calls[$year."-".addZero($month)."-01"]=Array();

						if (isset($all_calls[$year."-".addZero($month)."-01"])==false) {
							$all_calls[$year."-".addZero($month)."-01"]=Array();
						}


						foreach ($need_calc_edited_periods as $edited_call) {
							$s_call_append->bindValue(":tel_nom", $tel_nom);
							$s_call_append->bindValue(":month", $edited_call["month_old"]);
							$s_call_append->bindValue(":year", $edited_call["year_old"]);
							$s_call_append->bindValue(":call_length", $edited_call["call_length"]);
							$s_call_append->bindValue(":call_date", $edited_call["call_date"]);
							$s_call_append->bindValue(":call_time", $edited_call["call_time"]);
							$s_call_append->bindValue(":call_type", $edited_call["call_type"]);
							$s_call_append->bindValue(":service", $edited_call["service"]);
							$s_call_append->bindValue(":phone", $edited_call["phone"]);
							
							$s_call_append->execute();

							if ($s_call_append->rowCount()>0) {
								$call_append=$s_call_append->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

								$phone=phone_calc($oper, $call_append[0]);

								$index=$call_append[0]["call_length"]."*".$call_append[0]["call_date"]."*".$call_append[0]["call_time"]."*".$phone."*".$call_append[0]["call_type"]."*".$call_append[0]["service"];

								$s_edited_call_prices_append=$db->prepare("SELECT CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, unit_price FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service)=:index");
								$s_edited_call_prices_append->bindValue(":tel_nom", $tel_nom);
								$s_edited_call_prices_append->bindValue(":index", $index);
								$s_edited_call_prices_append->execute();

								if ($s_edited_call_prices_append->rowCount()>0) {
									$edited_calls_prices_append=$s_edited_call_prices_append->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

									$edited_calls_prices=array_merge($edited_calls_prices, $edited_calls_prices_append);
								}

								if (isset($edited_calls_prices[$index]["price"])) {
									$call_append["price"]=$edited_calls_prices[$index][0]["price"];
								}

								$all_calls[$year."-".addZero($month)."-01"]=array_merge($all_calls[$year."-".addZero($month)."-01"], $call_append[0]);
							}
						}
					}
					
					//print_r($all_calls);
					//exit();

					if ($year==date("Y", time()) && $month==date("m", time())) {
						$to_day=date("d", time());
					} else {
						$to_day=cal_days_in_month(CAL_GREGORIAN, $month, $year);
					}

					for ($day=1; $day<$to_day+1; $day++) {
						$date=$year."-".addZero($month)."-".addZero($day);

						if (isset($all_calls[$date])) {
							echo "CALL_DATE: ".$date." (".count($all_calls[$date]).")\n";

							calls_calculate($tel_nom, $all_calls[$date], $date);

							services_calculate($tel_nom, $gprs, $min, $sms);
						} else {
							echo "CALL_DATE: ".$date." (NO_CALLS)\n";

							calls_calculate($tel_nom, Array(), $date);

							services_calculate($tel_nom, 0, 0, 0);
						}		

						to_db($tel_nom, $user_name, $spended, $date, true, $oper);
					}
			}
		}

		if (isset($_POST["tel_nom"])==false) {
			print_log($db, strtoupper($oper)."-КЧ <B>Окончание расчёта</B>");
		}
	}

	function calls_calculate($tel_nom, $calls, $date) {
		global $db;
		global $oper;
		global $spended;	
		global $call_types;
		global $current_services;
		global $services_prices;
		global $sms;
		global $gprs;
		global $min;
		global $svoya_set;
		global $sms500;
		global $bl_minut;
		global $exceptions;
		global $codes;
		global $year;
		global $month;
		global $g1000;
		global $roaming_gprs;
		global $is_service;//У кого подключена Эксклюзивная сеть

		global $s_call_types_prices;
		global $s_call_types;
		global $s_services_prices;
		global $s_current_services;
		global $s_edited_calls_prices;

		//Цены вызовов у Билайна различаются по контрактам, поэтому необходимо узнать на каком контракте номер
		$oper1=$oper;
		if ($oper=="bee") {
			$s_contract=$db->prepare("SELECT Contract FROM bee_phones WHERE PhoneNumber=:tel_nom");
			$s_contract->bindValue(":tel_nom", $tel_nom);
			$s_contract->execute();

			$contr=$s_contract->fetch(PDO::FETCH_COLUMN);

			if ($contr==2) {
				$oper1="bee+";
			}
		}

		//Типы вызовов и их цены
		$s_call_types_prices->bindValue(":operator", $oper1);
		$s_call_types_prices->bindValue(":date", $date);
		$s_call_types_prices->execute();

		$call_types_prices=$s_call_types_prices->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		foreach ($call_types as $txt => $values) {
			$ct=$values[0]["type"];

			$call_types[$txt][0]["price"]=$call_types_prices[$ct][0]["price"];
		}

		foreach ($call_types_prices as $type => $value) {
			$call_types[$type][0]["price"]=$value[0]["price"];
		}

		//Цена услуг на дату
		$s_services_prices->bindValue(":operator", $oper);
		$s_services_prices->bindValue(":date", $date);
		$s_services_prices->execute();
				
		$services_prices=$s_services_prices->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Подключенные услуги на дату
		$s_current_services->bindValue(":tel_nom", $tel_nom);
		$s_current_services->bindValue(":date", $date);
		$s_current_services->execute();

		$current_services=$s_current_services->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Изменённые цены вызов на дату
		$s_edited_calls_prices->bindValue(":tel_nom", $tel_nom);
		$s_edited_calls_prices->bindValue(":date", $date);
		$s_edited_calls_prices->execute();

		$edited_calls_prices=$s_edited_calls_prices->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//$edited_calls_prices=array_merge($edited_calls_prices, $edited_calls_prices_append);

		$parts=explode("-", $date);
		$year1=$parts[0];
		$month1=intval($parts[1]);
		$day1=intval($parts[2]);

		//Первого числа, если есть перенесённые в этот период вызовы, то ищем их в таблице изменённых цен и, если нашлись, то добавляем эти вызовы в массив изменённых вызовов
		if ($day1==1) {
			$s_edited_periods_append=$db->prepare("SELECT call_date, call_time, phone, call_type, service, call_length FROM clients.edited_calls_periods WHERE tel_nom=:tel_nom AND year_edited=:year AND month_edited=:month");
			$s_edited_periods_append->bindValue(":tel_nom", $tel_nom);
			$s_edited_periods_append->bindValue(":year", $year1);
			$s_edited_periods_append->bindValue(":month", $month1);
			$s_edited_periods_append->execute();

			if ($s_edited_periods_append->rowCount()>0) {
				$edited_periods_append=$s_edited_periods_append->fetchAll(PDO::FETCH_ASSOC);

				$call_date=$year1."-".addZero($month1)."-01";

				$s=$db->prepare("SELECT CONCAT(call_length, '*', '".$call_date."', '*', call_time, '*', phone, '*', call_type, '*', service) as txt, unit_price FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND call_date=:call_date AND call_time=:call_time AND phone=:phone AND call_type=:call_type AND service=:service AND call_length=:call_length");

				foreach ($edited_periods_append as $edited_period_call) {
					//print_r($edited_period_call);
					$s->bindValue(":tel_nom", $tel_nom);
					$s->bindValue(":call_date", $edited_period_call["call_date"]);
					$s->bindValue(":call_time", $edited_period_call["call_time"]);
					$s->bindValue(":phone", $edited_period_call["phone"]);
					$s->bindValue(":call_type", $edited_period_call["call_type"]);
					$s->bindValue(":service", $edited_period_call["service"]);
					$s->bindValue(":call_length", $edited_period_call["call_length"]);
					$s->execute();

					if ($s->rowCount()>0) {
						$index=$edited_period_call["call_length"]."*".$year1."-".addZero($month1)."-01*".$edited_period_call["call_time"]."*".$edited_period_call["phone"]."*".$edited_period_call["call_type"]."*".$edited_period_call["service"];

						if (isset($edited_calls_prices[$index])==false) {
							$edited_calls_prices[$index]=Array();
						}

						$edited_call_price_append=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
//echo $index."\n";						
//print_r($edited_call_price_append);
						$edited_calls_prices[$index]=array_merge($edited_calls_prices[$index], $edited_call_price_append[$index]);
					}
				}
			}

			//print_r($edited_calls_prices);		
		}

		$g1000=false;
		$sms500=false;
		$svoya_set=false;
		$bl_minut=false;
		$l_roaming=false;
		$g_roaming=false;

		if (isset($current_services[$tel_nom])) {
			//G1000
			foreach ($codes["g1000"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$g1000=true;
					break;
				}
			}

			//G3000
			foreach ($codes["g3000"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$g1000=true;
					break;
				}
			}

			//БЛ интернет или GPRS БЛ
			foreach ($codes["gprs_bl"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$g1000=true;
					break;
				}
			}

			//G-роуминг
			foreach ($codes["g_roaming"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$g_roaming=true;
					break;
				}
			}

			//SMS 300
			foreach ($codes["sms300"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$sms500=true;
					break;
				}
			}

			//SMS 500
			foreach ($codes["sms500"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$sms500=true;
					break;
				}
			}

			//1000 SMS
			foreach ($codes["sms1000"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$sms500=true;
					break;
				}
			}

			//Своя сеть
			foreach ($codes["svoya_set"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$svoya_set=true;
					break;
				}
			}

			//БЛ Минут
			foreach ($codes["bl_minut"][$oper] as $code) {
				if (array_search($code, array_column($current_services[$tel_nom], 'service_code'))!==false) {
					$bl_minut=true;
					break;
				}
			}
		}

		foreach ($calls as $call) {	
			$call_type=$call["call_type"];

			if (isset($call_types[$call_type])) {
				$call_type_name=$call_types[$call_type][0]["type"];

				$call_type1=$call["ct"];
				$service=$call["serv"];

				//Изменённые стоимости вызовов
				$phone=phone_calc($oper, $call);

				$index=$call["call_length"]."*".$date."*".$call["call_time"]."*".$phone."*".$call_type1."*".$service;//Единичка чтобы не путать с call_type, который совокупность двух колонок

				//Если вызов в изменённых периодах и новый период совпадает с расчитываемым, то считаем, если нет, то пропускаем
				if (no_need_calc($date, $call, $phone, $call_type, $oper, $year, $month)) {
					continue;
				}
				
				//echo "-----------------\n";
				//print_r($edited_calls_prices);
				//echo $index;

				if (isset($edited_calls_prices[$index])) {
					echo "EDITED CALL PRICE\n";
					if (strpos($call_types[$call_type][0]["type"],"GPRS")!==false && $oper=="mts") {
						$l=call_length_calc($call);
					} else {
						$l=eval("return {$call_types[$call['call_type']][0]['items_count']};");
					}

					$price=$l*$edited_calls_prices[$index][0]["unit_price"];

					$spended["tarifs"][$call_type_name]["money"]+=$price;
					$spended["tarifs"][$call_type_name]["length"]+=$l;
					$spended["tarifs"][$call_type_name]["unit_price"]=$edited_calls_prices[$index][0]["unit_price"];

					echo $index.":".$l."*".$edited_calls_prices[$index][0]["unit_price"]."\n";

					loss_detect($price, $call["price"], $date, $call["call_time"], $type, $call_type);

					continue;
				}

				/*if (in_array($call['to_number'], $exceptions)) {
					echo "IN EXCEPIONS\n";
					continue;
				}*/

				foreach ($exceptions as $ex) {
					if (stristr($call['to_number'], $ex)!==false) {
						echo $date." ".$call["call_time"].": IN EXCEPIONS\n";
						continue 2;
					} else if (stristr("7".$call['to_number'], $ex)!==false) {
						echo $date." ".$call["call_time"].": IN EXCEPIONS\n";
						continue 2;
					} else if (stristr(substr($call['to_number'],1), $ex)!==false) {
						echo $date." ".$call["call_time"].": IN EXCEPIONS\n";
						continue 2;
					}
				}

				if (strpos($call_types[$call_type][0]["type"],"ВХ")!==false) {
					minutes_sms($call, $phone, $call_types, $call_type, $date, $oper);

					continue;
				}			

				if (strpos($call_types[$call_type][0]["type"],"РОУМ-GPRS")!==false && $g_roaming==true) {
					if ($oper=="mts") {
						$roaming_gprs+=call_length_calc($call);
					} else {
						$roaming_gprs+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
					}

					loss_detect(0, $call["price"], $date, $call["call_time"], $type, $call_type);
					
					continue;
				}

				if ($call_types[$call_type][0]["type"]=="ИСХ-ВСР") {
					echo $call["call_time"]."\n";
				}

				if (strpos($call_types[$call_type][0]["type"],"РОУМ")!==false && strpos($call_types[$call_type][0]["type"],"GPRS")!==false && $g_roaming==false) {
					gprs_calc($call, $phone, $call_types, $call_type, $date, $oper);
				} else if (strpos($call_types[$call_type][0]["type"],"GPRS")!==false) {
				    if ($g1000==true) {//Если подключены G1000, G3000, БЛ интернет или GPRS БЛ, то не считаем стомость вызова - посчитаем позже по тарифам услуг
						if ($oper=="mts") {
							$gprs+=call_length_calc($call);
						} else {
							$gprs+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
						}

						loss_detect(0, $call["price"], $date, $call["call_time"], $type, $call_type);
					} else {
						gprs_calc($call, $phone, $call_types, $call_type, $date, $oper);
					}
				} else if (strpos($call_types[$call_type][0]["type"],"SMS")!==false) {
					if ($oper=="mts") {
						$sms+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
					} else {
						$sms+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
					}

					if ($sms500==false || $call_types[$call_type][0]["type"]=="SMS-900") {//Если подключено SMS 300, SMS 500 или 1000 SMS, то не считаем стомоть вызова - посчитаем позже по тарифам услуг
						minutes_sms($call, $phone, $call_types, $call_type, $date, $oper);
					} else {
						loss_detect(0, $call["price"], $date, $call["call_time"], $type, $call_type);
					}
				} else if ($svoya_set==true) {//Если подкючена Своя сеть
					//if (strpos($call_types[$call_type][0]["type"],"ИСХ-ВСР")!==false || strpos($call_types[$call_type][0]["type"],"ИСX-ЗГП")!==false || strpos($call_types[$call_type][0]["type"],"ПЕР-ВСР")!==false || strpos($call_types[$call_type][0]["type"],"ПЕР-ЗГП")!==false) {
					if (strpos($call_types[$call_type][0]["type"],"ИСХ-ВСР")!==false || strpos($call_types[$call_type][0]["type"],"ИСX-ЗГП")!==false) {	
						//Посчитаем позже по тарифам услуг
						if ($oper=="mts") {
							$min+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
						} else {
							$min+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
						}

						if (in_array($tel_nom, $is_service)==false) {//Для обладателей Эксклюзивной сети убыток не определяем
							loss_detect(0, $call["price"], $date, $call["call_time"], $type, $call_type);
						}
					} else {
						minutes_sms($call, $phone, $call_types, $call_type, $date, $oper);
					}
				} else if ($bl_minut==true) {//Если подкючены БЛ минуты
					if ($oper=="mts") {
						$min+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
					} else {
						$min+=eval("return {$call_types[$call['call_type']][0]['items_count']};");
					}

					if (strpos($call_types[$call_type][0]["type"],"ИСХ-ВСР")!==false || strpos($call_types[$call_type][0]["type"],"ИСХ-СРДО")!==false || strpos($call_types[$call_type][0]["type"],"ИСX-ЗГП")!==false) {
						//Посчитаем позже по тарифам услуг
						loss_detect(0, $call["price"], $date, $call["call_time"], $type, $call_type);
					} else {
						minutes_sms($call, $phone, $call_types, $call_type, $date, $oper);
					}
				} else {
					minutes_sms($call, $phone, $call_types, $call_type, $date, $oper);
				}
			} else {
				echo "Не подписанный тип вызова: ".$call_type."\n";

				save_attention("clients", $tel_nom, "new_call_type_".$call_type, "Не подписанный тип вызова<BR>за ".$date." ".$call["call_time"], 0, $oper);
			}
		}
	}

	function minutes_sms($call, $phone, $call_types, $call_type, $date, $oper) {
		global $spended;
		global $call_types_kefs;
		global $tel_nom;

		$type=$call_types[$call_type][0]["type"];

		if (isset($call_types_kefs[$type])) {
			$kef=$call_types_kefs[$type][0]["kef"];
		} else {
			$kef=1;
		}

		if (isset($spended["tarifs"][$type]["money"])==false) {
			$spended["tarifs"][$type]["money"]=0;
		}

		if (isset($spended["tarifs"][$type]["call_length"])==false) {
			$spended["tarifs"][$type]["call_length"]=0;
		}

		if ($oper=="mts") {
			$call_length=eval("return {$call_types[$call['call_type']][0]['items_count']};");

			$price=$call_types[$call_type][0]["price"]*$call_length*$kef;

			$spended["tarifs"][$type]["money"]+=$price;	
			$spended["tarifs"][$type]["length"]+=$call_length;
		} else {
			$call_length=eval("return {$call_types[$call['call_type']][0]['items_count']};");

			$price=$call_types[$call_type][0]["price"]*$call_length*$kef;

			$spended["tarifs"][$type]["money"]+=$price;	
			$spended["tarifs"][$type]["length"]+=$call_length;
		}

		//echo "(".$call_types[$call_type][0]["type"].") ".$call["call_date"]." ".$call["call_time"].": ".$call_length."\n";

		$new_unit_price=$call_types[$call_type][0]["price"]*$kef;

		if (isset($spended["tarifs"][$type]["unit_price"])==false || $new_unit_price>$spended["tarifs"][$type]["unit_price"]) {
			$spended["tarifs"][$type]["unit_price"]=$new_unit_price;
		}

		//echo $date." ".$call["call_time"].":".$call["price"]."/".$price."/".$call_type."\n";

		loss_detect($price, $call["price"], $date, $call["call_time"], $type, $call_type);
	}

	function gprs_calc($call, $phone, $call_types, $call_type, $date, $oper) {
		global $spended;
		global $call_types_kefs;
		global $tel_nom;

		$type=$call_types[$call_type][0]["type"];

		if (isset($call_types_kefs[$type])) {
			$kef=$call_types_kefs[$type][0]["kef"];
		} else {
			$kef=1;
		}

		if (isset($spended["tarifs"][$type]["money"])==false) {
			$spended["tarifs"][$type]["money"]=0;
		}

		if (isset($spended["tarifs"][$type]["call_length"])==false) {
			$spended["tarifs"][$type]["call_length"]=0;
		}

		if ($oper=="mts") {
			$call_length=call_length_calc($call);

			$price=$call_types[$call_type][0]["price"]*$call_length*$kef;

			$spended["tarifs"][$type]["money"]+=$price;	
			$spended["tarifs"][$type]["length"]+=$call_length;
		} else {
			$call_length=eval("return {$call_types[$call['call_type']][0]['items_count']};");

			$price=$call_types[$call_type][0]["price"]*$call_length*$kef;

			$spended["tarifs"][$type]["money"]+=$price;	
			$spended["tarifs"][$type]["length"]+=$call_length;
		}

		$new_unit_price=$call_types[$call_type][0]["price"]*$kef;

		if (isset($spended["tarifs"][$type]["unit_price"])==false || $new_unit_price>$spended["tarifs"][$type]["unit_price"]) {
			$spended["tarifs"][$type]["unit_price"]=$new_unit_price;
		}

		loss_detect($price, $call["price"], $date, $call["call_time"], $type, $call_type);
	}

	function loss_detect($client_price, $office_price, $date, $call_time, $type, $call_type) {
		global $oper;
		global $tel_nom;

		if ($oper=="mts") {
			if (strpos(mb_strtoupper($call_type,"UTF-8"), "ПЛАТЕЖ")===false && strpos(mb_strtoupper($call_type,"UTF-8"), "ПЕРЕНОС")===false && strpos(mb_strtoupper($call_type,"UTF-8"), "КОРРЕКТ")===false) {
				$no_loss=false;
			} else {
				$no_loss=true;
			}
		} else {
			$no_loss=false;
		}

		if ($office_price>$client_price+1 && $type!="." && $no_loss==false) {
			$type="payable_call_".$tel_nom."_".$date."_".$call_time;
			$txt="УБЫТОК (".$date." ".$call_time.")";

			echo $txt."\n";

			save_attention("clients", $tel_nom, $type, $txt, 0, $oper);
		}
	}

	function services_calculate($tel_nom) {
		global $oper;
		global $db;
		global $services_dict;
		global $current_services;
		global $services_prices;
		global $spended;
		global $oper;
		global $gprs;
		global $sms;
		global $codes;

		if (isset($current_services[$tel_nom])==false) {
			return 0;
		}

		foreach ($current_services[$tel_nom] as $current_service) {
			$client_service_code=$current_service["service_code"];

			$services_name=$services_dict[$client_service_code][0]["service"];
			$price=$services_prices[$client_service_code][0]["price"];

			if (isset($spended["services"][$services_name]["money"])==false) {
				$spended["services"][$services_name]["money"]=0;
			}

			if (isset($spended["tarifs"][$type]["call_length"])==false) {
				$spended["services"][$services_name]["call_length"]=0;
			}

			$spended["services"][$services_name]["service_code"]=$client_service_code;

			if (array_search($client_service_code, $codes["l_roaming"][$oper])!==false) {
				$spended["services"][$services_name]["money"]+=$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]+=1;
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["g_roaming"][$oper])!==false) {
				$spended["services"][$services_name]["money"]+=$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]+=1;
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["svoya_set"][$oper])!==false) {
				$spended["services"][$services_name]["money"]=$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=1;
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["gprs_bl"][$oper])!==false) {
				$spended["services"][$services_name]["money"]=$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=1;
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["bl_minut"][$oper])!==false) {
				$spended["services"][$services_name]["money"]=$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=1;
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["g1000"][$oper])!==false) {//Для G1000 стоимость возрастает кратно кольчеству потраченных Гб
				if ($gprs==0) {
					$v=1;
				} else {
					$v=$gprs;
				}
				$spended["services"][$services_name]["money"]=ceil($v/1000)*$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=ceil($v/1000);
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];

				if ($spended["services"][$services_name]["length"]==0) {
					$spended["services"][$services_name]["length"]=1;
				}
			} else if (array_search($client_service_code, $codes["g3000"][$oper])!==false) {
				if ($gprs/1000>3) {//Если подключено G3000 и израсходовано > 3Гб, то остальное считектся как G1000 кратно его стоимости
					if ($oper=="mts") {
						$price1000=$services_prices[7][0]["price"];
					} else if ($oper=="bee") {
						$price1000=$services_prices[36][0]["price"];
					} else if ($oper=="meg") {
						$price1000=$services_prices[51][0]["price"];
					} else if ($oper=="tele2") {
						$price1000=$services_prices[66][0]["price"];
					}

					$k=ceil(($gprs-3*1000)/1000);

					$spended["services"]["G1000"]["money"]=$k*$price1000;
					$spended["services"]["G1000"]["length"]=$k;
					$spended["services"]["G1000"]["unit_price"]=$price1000*$current_service["kef"];
					$spended["services"]["G1000"]["service_code"]=$codes["g1000"][$oper][0];

					$spended["services"][$services_name]["money"]=$price;
					$spended["services"][$services_name]["length"]=1;
					$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
				} else {
					$spended["services"][$services_name]["money"]=$price;
					$spended["services"][$services_name]["length"]=1;
					$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
				}
			} else if (array_search($client_service_code, $codes["sms300"][$oper])!==false) {
				if ($sms==0) {
					$value=1;
				} else {
					$value=$sms;
				}

				$spended["services"][$services_name]["money"]=ceil($value/300)*$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=ceil($value/300);
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["sms500"][$oper])!==false) {
				if ($sms==0) {
					$value=1;
				} else {
					$value=$sms;
				}

				$spended["services"][$services_name]["money"]=ceil($value/500)*$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=ceil($value/500);
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["sms1000"][$oper])!==false) {
				if ($sms==0) {
					$value=1;
				} else {
					$value=$sms;
				}

				$spended["services"][$services_name]["money"]=ceil($value/1000)*$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=ceil($value/1000);
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else if (array_search($client_service_code, $codes["periodic"][$oper])!==false) {
				$spended["services"][$services_name]["money"]=$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=1;
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			} else {
				$spended["services"][$services_name]["money"]=$price*$current_service["kef"];
				$spended["services"][$services_name]["length"]=1;
				$spended["services"][$services_name]["unit_price"]=$price*$current_service["kef"];
			}
		}

		//print_r($spended["services"]);
	}

	function phone_calc($oper, $call) {
		$phone="";
	
		if ($oper=="bee") {
			if ($call["from_number"]==$call["tel_nom"] || "7".$call["from_number"]==$call["tel_nom"]) {
				$phone=$call["to_number"];
			} else if ($call["to_number"]==$call["tel_nom"] || "7".$call["to_number"]==$call["tel_nom"]) {
				$phone=$call["from_number"];
			} else if ($call["to_number"]=="") {
				$phone=$call["from_number"];
			} else if ($call["from_number"]=="") {
				$phone=$call["to_number"];
			} else {
				$phone=$call["from_number"];
			}
		} else {
			if ($call["from_number"]==$call["tel_nom"]) {
				$phone=$call["to_number"];
			}
			if ($call["to_number"]==$call["tel_nom"]) {
					$phone=$call["from_number"];
			}
		}

		return $phone;
	}

	function no_need_calc($date, $call, $phone, $ct, $operator, $year, $month) {
		global $no_need_calc_edited_periods;

		$call_type=explode("-", $call["call_type"]);
		$call_type=$call_type[0];
		
		$index=$call["call_length"]."*".$date."*".$call["call_time"]."*".$phone."*".$call_type;

		if (isset($no_need_calc_edited_periods[$index])) {
			if ($no_need_calc_edited_periods[$index][0]["year_old"]==$year && $no_need_calc_edited_periods[$index][0]["month_old"]==$month) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function roaming_calculate($tel_nom, $roaming_gprs, $roaming_sms, $roaming_min_I, $roaming_min_O) {
		global $oper;
		global $db;
		global $services_dict;
		global $client_services;
		global $s_roaming;

		$spended=0;

		$s_roaming->bindValue(":tel_nom", $tel_nom);
		$s_roaming->execute();

		if ($s_roaming->rowCount()>0) {
			$roamings=$s_roaming->fetchAll(PDO::FETCH_ASSOC);

			foreach ($roamings as $r) {
				if ($r["finish_date"]=="2030-01-01") {
					$r["finish_date"]==date("Y-m-d", time());
				}
				
				$roaming_days=daysInterval(strtotime($r["start_date"]), strtotime($r["finish_date"]));
			}
		} else {
			return 0;
		}

		if (isset($client_services[$tel_nom])==false) {
			$client_services[$tel_nom]["code"]=777777777777777;
		}

		if (array_search(12, array_column($client_services[$tel_nom], 'code'))!==false) {//SMS
			$price=$services_prices[12][0]["price"];

			$spended+=$roaming_sms*$price;
		}

		if (array_search(26, array_column($client_services[$tel_nom], 'code'))!==false) {//GPRS по дням
			$price=$services_prices[26][0]["price"];

			$spended+=$days*$price;
		}

		if (array_search(14, array_column($client_services[$tel_nom], 'code'))!==false) {
			$price=$services_prices[14][0]["price"];

			$spended+=$roaming_min_O*$price;
		}

		if (array_search(11, array_column($client_services[$tel_nom], 'code'))!==false) {//Если подключен л/Роуминг, то входящие не считаются
			$price=$services_prices[11][0]["price"];

			$spended+=$days*$price;
		} else if (array_search(13, array_column($client_services[$tel_nom], 'code'))!==false) {
			$price=$services_prices[13][0]["price"];

			$spended+=$roaming_min_I*$price;
		}

		return $spended;
	}

	function to_db($tel_nom, $user_name, $spended, $date, $single_number, $oper) {
		global $db;
		global $oper;
		global $s_last_balance;
		global $s_account;
		global $s_total_payments;
		global $s_today_payments;
		global $s_main_tbl;
		global $s_select;
		global $s_insert;
		global $s_update;
		global $s_spended_delete;
		global $s_spended_insert;
		global $payments;
		global $min;
		global $sms;
		global $gprs;
		global $year;
		global $month;
		global $svoya_set;
		global $sms300;
		global $sms500;
		global $sms1000;
		global $bl_minut;
		global $roaming_gprs;
		global $g1000;

		$s_spended_delete->bindValue(":tel_nom", $tel_nom);
		$s_spended_delete->bindValue(":year", $year);
		$s_spended_delete->bindValue(":month", $month);
		$s_spended_delete->execute();

		foreach ($spended["services"] as $category => $values) {
			echo $tel_nom.": Начисления ".$category." (".$values["money"]."/".$values["length"].")\n";

			$s_spended_insert->bindValue(":tel_nom", $tel_nom);
			$s_spended_insert->bindValue(":category", $category);
			$s_spended_insert->bindValue(":service_code", $values["service_code"]);
			$s_spended_insert->bindValue(":sum", $values["money"]);
			$s_spended_insert->bindValue(":length", $values["length"]);
			$s_spended_insert->bindValue(":unit_price", $values["unit_price"]);
			$s_spended_insert->bindValue(":year", $year);
			$s_spended_insert->bindValue(":month", $month);
			$s_spended_insert->bindValue(":update_date", date("Y-m-d", time()));
			$s_spended_insert->bindValue(":update_time", date("H:i:s", time()));
			$s_spended_insert->bindValue(":user_name", $user_name);
			$s_spended_insert->bindValue(":operator", $oper);
			$s_spended_insert->execute();
		}

		$is_sms_by_tarif=false;

		if (isset($spended["tarifs"]["ИСХ-ВСР"]) && isset($spended["tarifs"]["ИСX-ЗГП"])) {
			$spended["tarifs"]["ИСХ-ВСР"]["money"]=$spended["tarifs"]["ИСХ-ВСР"]["money"]+$spended["tarifs"]["ИСX-ЗГП"]["money"];
			$spended["tarifs"]["ИСХ-ВСР"]["length"]=$spended["tarifs"]["ИСХ-ВСР"]["length"]+$spended["tarifs"]["ИСX-ЗГП"]["length"];
			$spended["tarifs"]["ИСX-ЗГП"]["money"]=0;
		} else if (isset($spended["tarifs"]["ИСX-ЗГП"])) {
			$spended["tarifs"]["ИСХ-ВСР"]["money"]=$spended["tarifs"]["ИСX-ЗГП"]["money"];
			$spended["tarifs"]["ИСХ-ВСР"]["length"]=$spended["tarifs"]["ИСX-ЗГП"]["length"];
			$spended["tarifs"]["ИСX-ЗГП"]["money"]=0;
		}

		if (isset($spended["tarifs"]["ПЕР-ВСР"]) && isset($spended["tarifs"]["ПЕР-СРДО"])) {
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["money"]=$spended["tarifs"]["ПЕР-ВСР"]["money"]+$spended["tarifs"]["ПЕР-СРДО"]["money"];
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["length"]=$spended["tarifs"]["ПЕР-ВСР"]["length"]+$spended["tarifs"]["ПЕР-СРДО"]["length"];
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["unit_price"]=$spended["tarifs"]["ПЕР-ВСР"]["unit_price"];
			$spended["tarifs"]["ПЕР-СРДО"]["money"]=0;
			$spended["tarifs"]["ПЕР-ВСР"]["money"]=0;
		} else if (isset($spended["tarifs"]["ПЕР-СРДО"])) {
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["money"]=$spended["tarifs"]["ПЕР-СРДО"]["money"];
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["length"]=$spended["tarifs"]["ПЕР-СРДО"]["length"];
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["unit_price"]=$spended["tarifs"]["ПЕР-СРДО"]["unit_price"];
			$spended["tarifs"]["ПЕР-СРДО"]["money"]=0;
			$spended["tarifs"]["ПЕР-ВСР"]["money"]=0;
		} else if (isset($spended["tarifs"]["ПЕР-ВСР"])) {
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["money"]=$spended["tarifs"]["ПЕР-ВСР"]["money"];
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["length"]=$spended["tarifs"]["ПЕР-ВСР"]["length"];
			$spended["tarifs"]["ПЕРЕАДРЕСАЦИЯ"]["unit_price"]=$spended["tarifs"]["ПЕР-ВСР"]["unit_price"];
			$spended["tarifs"]["ПЕР-СРДО"]["money"]=0;
			$spended["tarifs"]["ПЕР-ВСР"]["money"]=0;
		}

		foreach ($spended["tarifs"] as $category => $values) {
			echo $tel_nom.": Начисления ".$category." (".$values["money"]."/".$values["length"].")\n";

			if ($values["money"]>0 || stripos($category,"РОУМ-ВХ")!==false) {
				if (strpos($category,"SMS")!==false) {//Например SMS-900
					$is_sms_by_tarif=true;
				}

				$s_spended_insert->bindValue(":tel_nom", $tel_nom);
				$s_spended_insert->bindValue(":category", $category);
				$s_spended_insert->bindValue(":service_code", 0);
				$s_spended_insert->bindValue(":sum", floatval($values["money"]));
				$s_spended_insert->bindValue(":length", floatval($values["length"]));
				$s_spended_insert->bindValue(":unit_price", floatval($values["unit_price"]));
				$s_spended_insert->bindValue(":year", $year);
				$s_spended_insert->bindValue(":month", $month);
				$s_spended_insert->bindValue(":update_date", date("Y-m-d", time()));
				$s_spended_insert->bindValue(":update_time", date("H:i:s", time()));
				$s_spended_insert->bindValue(":user_name", $user_name);				
				$s_spended_insert->bindValue(":operator", $oper);
				$s_spended_insert->execute();
			}
		}

		if ($min>0) {//$min есть только в случае, если подключена Своя сеть или БЛ минут
			if ($svoya_set==true) {
				echo $tel_nom.": Начисления "."МИНУТ ВСР"." (0/".$min.")\n";
				$type="МИНУТ ВСР";
			} else if ($bl_minut==true) {
				echo $tel_nom.": Начисления "."МИНУТ"." (0/".$min.")\n";
				$type="МИНУТ";
			} else {//На случай, если Свою сеть или БЛ отключили посреди месяца. Накопленные минуты-то остались
				echo $tel_nom.": Начисления "."МИНУТ ВСР"." (0/".$min.")\n";
				$type="МИНУТ ВСР";
			}

			$s_spended_insert->bindValue(":tel_nom", $tel_nom);
			$s_spended_insert->bindValue(":category", $type);
			$s_spended_insert->bindValue(":service_code", 0);
			$s_spended_insert->bindValue(":sum", 0);
			$s_spended_insert->bindValue(":length", $min);
			$s_spended_insert->bindValue(":unit_price", 0);
			$s_spended_insert->bindValue(":year", $year);
			$s_spended_insert->bindValue(":month", $month);
			$s_spended_insert->bindValue(":update_date", date("Y-m-d", time()));
			$s_spended_insert->bindValue(":update_time", date("H:i:s", time()));
			$s_spended_insert->bindValue(":user_name", $user_name);			
			$s_spended_insert->bindValue(":operator", $oper);
			$s_spended_insert->execute();
		} 

		if (($sms>0 || $sms300==true || $sms500==true || $sms1000==true) && $is_sms_by_tarif==false) {
			echo $tel_nom.": Начисления "."SMS"." (0/".$sms.")\n";

			$s_spended_insert->bindValue(":tel_nom", $tel_nom);
			$s_spended_insert->bindValue(":category", "SMS");
			$s_spended_insert->bindValue(":service_code", 0);
			$s_spended_insert->bindValue(":sum", 0);
			$s_spended_insert->bindValue(":length", $sms);
			$s_spended_insert->bindValue(":unit_price", 0);
			$s_spended_insert->bindValue(":year", $year);
			$s_spended_insert->bindValue(":month", $month);
			$s_spended_insert->bindValue(":update_date", date("Y-m-d", time()));
			$s_spended_insert->bindValue(":update_time", date("H:i:s", time()));
			$s_spended_insert->bindValue(":user_name", $user_name);			
			$s_spended_insert->bindValue(":operator", $oper);
			$s_spended_insert->execute();
		}

		if ($gprs>0 || $g1000==true) {
			echo "IS_G1000\n";
		}

		if ($gprs>0 || $g1000==true) {
			echo $tel_nom.": Начисления "."GPRS"." (0/".$gprs.")\n";

			$s_spended_insert->bindValue(":tel_nom", $tel_nom);
			$s_spended_insert->bindValue(":category", "GPRS");
			$s_spended_insert->bindValue(":service_code", 0);
			$s_spended_insert->bindValue(":sum", 0);
			$s_spended_insert->bindValue(":length", $gprs);
			$s_spended_insert->bindValue(":unit_price", 0);
			$s_spended_insert->bindValue(":year", $year);
			$s_spended_insert->bindValue(":month", $month);
			$s_spended_insert->bindValue(":update_date", date("Y-m-d", time()));
			$s_spended_insert->bindValue(":update_time", date("H:i:s", time()));
			$s_spended_insert->bindValue(":user_name", $user_name);			
			$s_spended_insert->bindValue(":operator", $oper);
			$s_spended_insert->execute();
		}

		if ($roaming_gprs>0) {
			if ($roaming_gprs==1) {
				$roaming_gprs=0;
			}

			echo $tel_nom.": Начисления "."РОУМ-GPRS"." (0/".$roaming_gprs.")\n";

			$s_spended_insert->bindValue(":tel_nom", $tel_nom);
			$s_spended_insert->bindValue(":category", "РОУМ-GPRS");
			$s_spended_insert->bindValue(":service_code", 0);
			$s_spended_insert->bindValue(":sum", 0);
			$s_spended_insert->bindValue(":length", $roaming_gprs);
			$s_spended_insert->bindValue(":unit_price", 0);
			$s_spended_insert->bindValue(":year", $year);
			$s_spended_insert->bindValue(":month", $month);
			$s_spended_insert->bindValue(":update_date", date("Y-m-d", time()));
			$s_spended_insert->bindValue(":update_time", date("H:i:s", time()));
			$s_spended_insert->bindValue(":user_name", $user_name);			
			$s_spended_insert->bindValue(":operator", $oper);
			$s_spended_insert->execute();
		}

		$spended_sum=0;

		foreach ($spended["services"] as $sp) {
			$spended_sum+=$sp["money"];
		}

		foreach ($spended["tarifs"] as $sp) {
			$spended_sum+=$sp["money"];
		}

		$spended=$spended_sum;

		//Выбираем последний баланс прошлого месяца, а начисление обнуляем
		$s_last_balance->bindValue(":tel_nom", $tel_nom);
		$s_last_balance->bindValue(":date", $year."-".addZero($month)."-01");
		$s_last_balance->execute();

		if ($s_last_balance->rowCount()>0) {
			$last_balance=$s_last_balance->fetch(PDO::FETCH_COLUMN);
		} else {
			$last_balance=0;
		}

		echo "LAST BALANCE: ".$last_balance."\n";

		$s_total_payments->bindValue(":tel_nom", $tel_nom);
		$s_total_payments->bindValue(":date1", $year."-".addZero($month)."-01");
		$s_total_payments->bindValue(":date2", $date);
		$s_total_payments->execute();

		$total_payments=floatval($s_total_payments->fetch(PDO::FETCH_COLUMN));

		$s_today_payments->bindValue(":tel_nom", $tel_nom);
		$s_today_payments->bindValue(":date", $date);
		$s_today_payments->execute();

		$today_payments=floatval($s_today_payments->fetch(PDO::FETCH_COLUMN));

		$balance=$last_balance+$total_payments-$spended;

		echo $date.PHP_EOL;
		echo $tel_nom.": Баланс (".$balance."), Платежи (".$today_payments.")\n";

		$s_main_tbl->bindValue(":balance", $balance);
		$s_main_tbl->bindValue(":spended", $spended);
		$s_main_tbl->bindValue(":tel_nom", $tel_nom);
		$s_main_tbl->execute();

		$s_select->bindValue(":tel_nom", $tel_nom);
		$s_select->bindValue(":date", $date);
		$s_select->execute();

		$parts=explode("-",$date);
		$day=$parts[2];

		if ($s_select->rowCount()>0) {
			$id=$s_select->fetch(PDO::FETCH_COLUMN);

			$s_update->bindValue(":id", $id);
			$s_update->bindValue(":balance", $balance);
			$s_update->bindValue(":spended", $spended);
			$s_update->bindValue(":payments", $today_payments);
			$s_update->execute();
		} else {
			$s_insert->bindValue(":tel_nom", $tel_nom);
			$s_insert->bindValue(":balance", $balance);
			$s_insert->bindValue(":spended", $spended);
			$s_insert->bindValue(":payments", $today_payments);
			$s_insert->bindValue(":date", $date);
			$s_insert->bindValue(":year", $year);
			$s_insert->bindValue(":month", $month);
			$s_insert->execute();
		}

		$max_day=cal_days_in_month(CAL_GREGORIAN, $month, $year);

		if ($max_day==$day || $date==date("Y-m-d", time())) {//Досчитался любой из месяцев
			$s_sel=$db->prepare("SELECT id, summ FROM clients.1s WHERE tel_nom=:tel_nom AND `year`=:year AND `month`=:month");
			$s_sel->bindValue(":tel_nom", $tel_nom);
			$s_sel->bindValue(":year", $year);
			$s_sel->bindValue(":month", $month);
			$s_sel->execute();

			if ($s_sel->rowCount()>0) {
				$ss_spended=$s_sel->fetch(PDO::FETCH_ASSOC);

				$diff=$spended-$ss_spended["summ"];
				
				echo "DIFF: ".$spended."-".$ss_spended["summ"]."=".$diff."\n";
				
				$ss_update=$db->prepare("UPDATE clients.1s SET diff=:diff WHERE id=:id");
				$ss_update->bindValue(":diff", $diff);
				$ss_update->bindValue(":id", $ss_spended["id"]);
				$ss_update->execute();
			}
		}

		if ($date==date("Y-m-d", time())) {//Досчиталась текущая дата
			$s_actual=$db->prepare("UPDATE clients.clients SET balance=:balance, spended=:spended, actual_date=:actual_date, update_date=DATE(NOW()), update_time=TIME(NOW()) WHERE tel_nom=:tel_nom");

			$s_actual->bindValue(":tel_nom", $tel_nom);
			$s_actual->bindValue(":balance", $balance);
			$s_actual->bindValue(":spended", $spended);
			$s_actual->bindValue(":actual_date", $date);
			$s_actual->execute();
		}
	}

	function save_attention($db_name, $tel_nom, $type, $txt, $done, $oper) {
			global $db;

			$s_select=$db->prepare("SELECT id FROM ".$db_name.".attentions WHERE tel_nom=:tel_nom AND type=:type");

			$s_insert=$db->prepare("INSERT INTO ".$db_name.".attentions (tel_nom, `date`, txt, done, type, operator, user) VALUES (:tel_nom, DATE(NOW()), :txt, :done, :type, :operator, :user)");

			$s_select->bindValue(":tel_nom", $tel_nom);
			$s_select->bindValue(":type", $type);
			$s_select->execute();

			echo $tel_nom.": ".$txt;

			if ($done==1) {
				echo " (Автоотжатие)";
			}

			if ($s_select->rowCount()==0) {
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
				//echo "\n";
				return false;
			}
	}

	function daysInterval($low_date, $high_date) {
        $day_step = 60*60*24;
   
   		$roaming=Array();

        for ($d=$low_date; $d<=$high_date; $d=$d+$day_step) {
        	$month=date('Y', $d)."-".date('m', $d);

        	if (isset($roaming[$month])) {
            	$roaming[$month]++;
            } else {
            	$roaming[$month]=1;
            }
        }

        if (isset($roaming[date("Y-m")])) {
       		return $roaming[date("Y-m")];
       	} else {
       		return 0;
       	}
    }

    function call_length_calc($call) {
		if ($call['unit']=='гигабайт') {
			return $call['call_length']*1024;
		} else if ($call['unit']=='мегабайт') {
			return $call['call_length'];
		} else if ($call['unit']=='килобайт' || $call['unit']=='байт') {
			return $call['call_length']/1024;
		} else if ($call['unit']=='факт') {
			return $call['call_length']/1024/1024;
		}
	}

	function time_to_sec($time) {
			$parts=explode(":",$time);
			$res=intval($parts[0])*3600+intval($parts[1])*60+$parts[2];
			if ($res==0) {
				$res=1;
			}
			return $res;
	}

    function addZero($num) {
    	if ($num<10) {
    		return "0".$num;
    	} else {
    		return $num;
    	}
    }

	function print_log($db, $txt) {
		$s=$db->prepare("INSERT INTO logs (txt, script_name) VALUES (:txt, :script_name)");
		$s->bindValue(":txt", $txt);
		$s->bindValue(":script_name", $_SERVER['SCRIPT_NAME']);
		$s->execute();
	}    
?>