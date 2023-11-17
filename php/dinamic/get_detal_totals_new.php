<?php
		ini_set("display_errors", 1);
		error_reporting(E_ALL);

		require("../pdo_db_connect.php");

		if (isset($_POST["tel_nom"])!=false && $_POST["operator"]=="bee") {
			$s=$db->prepare("SELECT Contract FROM bee_phones WHERE PhoneNumber=:tel_nom");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->execute();

			$contract=$s->fetch(PDO::FETCH_COLUMN);

			if ($contract==2) {
				$_POST["operator"]="bee+";
			}
		}

		$in_exceptions=0;

		$s_exceptions=$db->prepare("SELECT exception FROM clients.exceptions");
		$s_exceptions->execute();
		$exceptions=$s_exceptions->fetchAll(PDO::FETCH_COLUMN);

		if ($_POST["operator"]=="mts") {
			$s=$db->prepare("SELECT CONCAT(call_type,'-',service,'-',zone) as txt, type, type_nom, items_count, items_summa, unit_price FROM call_types WHERE operator=:operator");
			
			$cols="service_type as call_type, service, service_provider";
		} else if ($_POST["operator"]=="mts" || $_POST["operator"]=="meg") {
			$s=$db->prepare("SELECT CONCAT(call_type,'-',service,'-',zone) as txt, type, type_nom, items_count, items_summa, unit_price FROM call_types WHERE operator=:operator");
			
			$cols="call_type, service, service_provider";
		} else {
			$s=$db->prepare("SELECT CONCAT(call_type,'-',service) as txt, type, type_nom, items_count, items_summa, unit_price FROM call_types WHERE operator=:operator");

			$cols="call_type, service";
		}

		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$call_types=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		if ($_POST["operator"]=="tele2") {//Теле2 сдвигаем на 7 часов вперёд в связи с тем, что они ведут учёт по МСК (т.е. начало месяца - 07:00:00, а конец уже в следующем месяце)
			$max_day=cal_days_in_month(CAL_GREGORIAN, $_POST["month"], $_POST["year"]);
			$from_time=$_POST["year"]."-".$_POST["month"]."-01 07:00:00";
			$to_time=$_POST["year"]."-".$_POST["month"]."-".$max_day." 23:59:59";//7 часов добавим уже в запросе

			$s=$db->prepare("SELECT ".$cols.", tel_nom, price, call_length, value, call_date, call_time, from_number, to_number, unit FROM tele2_detal WHERE removed=0 AND STR_TO_DATE(CONCAT(call_date,' ',call_time), '%Y-%m-%d %H:%i:%s') >= :from_time AND STR_TO_DATE(CONCAT(call_date,' ',call_time), '%Y-%m-%d %H:%i:%s') < :to_time + INTERVAL 7 HOUR AND tel_nom=:tel_nom ORDER BY call_date");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":from_time", $from_time);
			$s->bindValue(":to_time", $to_time);
		} else {
			$s=$db->prepare("SELECT ".$cols.", tel_nom, price, call_length, value, call_date, call_time, from_number, to_number, unit FROM ".str_replace("+","",$_POST["operator"])."_detal WHERE removed=0 AND `month`=:month AND `year`=:year AND tel_nom=:tel_nom");
			$s->bindValue(":tel_nom", $_POST["tel_nom"]);
			$s->bindValue(":month", $_POST["month"]);
			$s->bindValue(":year", $_POST["year"]);
		}

		$s->execute();

		$calls=$s->fetchAll(PDO::FETCH_ASSOC);

		$s=$db->prepare("SELECT call_type, `date`, price FROM clients.call_types_prices WHERE operator=:operator GROUP BY call_type, `date` ORDER BY call_type ASC, `date` ASC");
		$s->bindValue(":operator", $_POST["operator"]);
		$s->execute();

		$call_types_prices=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Коэффициенты типов вызовов
		$s=$db->prepare("SELECT call_type, kef FROM clients.call_types_kefs WHERE tel_nom=:tel_nom");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->execute();

		$call_types_kefs=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Изменённые цены вызовов
		$from_date=$_POST["year"]."-".addZero($_POST["month"])."-01";
		$to_date=$_POST["year"]."-".$_POST["month"]."-".cal_days_in_month(CAL_GREGORIAN, $_POST["month"], $_POST["year"]);

		$s=$db->prepare("SELECT CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, unit_price FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND call_date BETWEEN :from_date AND :to_date");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":from_date", $from_date);
		$s->bindValue(":to_date", $to_date);
		$s->execute();

		$edited_calls_prices=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Изменённые периоды (кого НЕ нужно считать)
		$s=$db->prepare("SELECT CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, year_old, month_old FROM clients.edited_calls_periods WHERE tel_nom=:tel_nom AND year_old=:year AND month_old=:month");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":year", $_POST["year"]);
		$s->bindValue(":month", $_POST["month"]);
		$s->execute();

		$no_need_calc_edited_periods=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		//Изменённые периоды (кого нужно ДОсчитать)
		$s=$db->prepare("SELECT call_length, call_date, call_time, phone, call_type, service, year_old, month_old FROM clients.edited_calls_periods WHERE tel_nom=:tel_nom AND year_edited=:year AND month_edited=:month");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->bindValue(":year", $_POST["year"]);
		$s->bindValue(":month", $_POST["month"]);
		$s->execute();

		if ($s->rowCount()>0) {
			$need_calc_edited_periods=$s->fetchAll(PDO::FETCH_ASSOC);

			if ($_POST["operator"]=="mts") {
				$column="service_type";
			} else {
				$column="call_type";
			}

			$s_call_append=$db->prepare("SELECT ".$cols.", tel_nom, price, call_length, value, call_date, call_time, from_number, to_number, unit FROM ".str_replace("+","",$_POST["operator"])."_detal WHERE removed=0 AND `month`=:month AND `year`=:year AND tel_nom=:tel_nom AND call_length=:call_length AND call_date=:call_date AND call_time=:call_time AND service=:service AND ".$column."=:call_type AND (from_number=:phone OR to_number=:phone)");

			foreach ($need_calc_edited_periods as $edited_call) {
				$s_call_append->bindValue(":tel_nom", $_POST["tel_nom"]);
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
					$call_append=$s_call_append->fetchAll(PDO::FETCH_ASSOC);

					$phone=phone_calc($_POST["operator"], $call_append[0]);

					$index=$call_append[0]["call_length"]."*".$call_append[0]["call_date"]."*".$call_append[0]["call_time"]."*".$phone."*".$call_append[0]["call_type"]."*".$call_append[0]["service"];

					//Если у перенесённого вызова изменена цена, то добавляем этот вызов в массив изменённых цен 
					$s_edited_call_prices_append=$db->prepare("SELECT CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service) as txt, unit_price FROM clients.edited_calls_prices WHERE tel_nom=:tel_nom AND CONCAT(call_length, '*', call_date, '*', call_time, '*', phone, '*', call_type, '*', service)=:index");
					$s_edited_call_prices_append->bindValue(":tel_nom", $_POST["tel_nom"]);
					$s_edited_call_prices_append->bindValue(":index", $index);
					$s_edited_call_prices_append->execute();

					if ($s_edited_call_prices_append->rowCount()>0) {
						$edited_calls_prices_append=$s_edited_call_prices_append->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

						$edited_calls_prices=array_merge($edited_calls_prices, $edited_calls_prices_append);
					}

					$calls=array_merge($calls, $call_append);
				}
			}
		}

		$s=$db->prepare("SELECT service_code, start_date, finish_date FROM clients.services_history WHERE tel_nom=:tel_nom GROUP BY service_code, `start_date`");
		$s->bindValue(":tel_nom", $_POST["tel_nom"]);
		$s->execute();

		$number_services_settings["services_history"]=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$call_length=0;
		$items_summa=0;
		$no_subscrible=0;
		$paying_count=0;
		$edited_calls_prices_count=0;

		foreach ($calls as $call) {
			$phone=phone_calc($_POST["operator"], $call);

			$index=$call["call_length"]."*".$call["call_date"]."*".$call["call_time"]."*".$phone."*".$call["call_type"]."*".$call["service"];

			if ($_POST["operator"]=="mts" || $_POST["operator"]=="meg") {
				if (isset($call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']])==false) {
					$no_subscrible++;
					continue;
				}

				$ct=$call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']][0]['type'];

				if (is_payable($call, $phone, $ct, $_POST["operator"])==true || $ct=="SMS-900" || isset($edited_calls_prices[$index])) {
					$unit_price=get_unit_price($index, $call, $phone, $ct, $_POST["operator"]);
				} else {
					$unit_price["value"]=0;
					$unit_price["type"]="nominal";
				}

				if (stripos($ct,"GPRS")!==false) {
					$call_length=call_length_calc($call);
				} else {
					$call_length=eval("return {$call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']][0]['items_count']};");
				}
			} else {
				if (isset($call_types[$call['call_type']."-".$call['service']])==false) {
					$no_subscrible++;
					continue;
				}

				$ct=$call_types[$call['call_type']."-".$call['service']][0]['type'];

				if (is_payable($call, $phone, $ct, $_POST["operator"])==true || $ct=="SMS-900" || isset($edited_calls_prices[$index])) {
					$unit_price=get_unit_price($index, $call, $phone, $ct, $_POST["operator"]);
				} else {
					$unit_price["value"]=0;
					$unit_price["type"]="nominal";
				}

				$call_length=eval("return {$call_types[$call['call_type']."-".$call['service']][0]['items_count']};");
			}	

			//Если вызов в изменённых периодах и новый период совпадает с расчитываемым, то считаем, если нет, то пропускаем
			if (no_need_calc($call, $phone, $ct, $_POST["operator"], $_POST["year"], $_POST["month"])) {
				continue;
			}

			if ($_POST["operator"]=="mts" || $_POST["operator"]=="meg") {
				if (isset($result[$ct])==false) {
					$result[$ct]["items_count"]=0;
					$result[$ct]["total_price"]=0;
					$result[$ct]["type_nom"]=$call_types[$call['call_type']."-".$call['service']."-".$call['service_provider']][0]['type_nom'];
					$result[$ct]["type"]=$ct;
				}
			} else {
				if (isset($result[$ct])==false) {
					$result[$ct]["items_count"]=0;
					$result[$ct]["total_price"]=0;
					$result[$ct]["type_nom"]=$call_types[$call['call_type']."-".$call['service']][0]['type_nom'];
					$result[$ct]["type"]=$ct;
				}
			}

			//echo $ct.":".$unit_price."*".$call_length."\n";

			$item_price=$unit_price["value"]*$call_length;		

			$result[$ct]["items_count"]+=$call_length;
			$result[$ct]["total_price"]+=$item_price;

			if ($item_price>0) {
				$paying_count++;
			}

			if ($unit_price["type"]=="edited") {
				$edited_calls_prices_count++;
			}
		}

		if (isset($result)==true) {
			ksort($result);
		}

		$res[0]=Array();

		$total_sum=0;

		$i=1;
		if (isset($result)==true) {
			foreach ($result as $key => $value) {
				if ($value["items_count"]==0 || $key=="-") {
					continue;
				}

				$res[$i]["type"]=$key;
				$res[$i]["type_nom"]=$value["type_nom"];
				$total_sum+=$value["total_price"];
				$res[$i]["total_price"]=number_format($value["total_price"], 2, ",", " ");
				$res[$i]["value"]=number_format(round($value["items_count"]*100)/100, 2, ",", " ");

				$i++;
			}
		}

		$res[0]["type"] = "ПЛАТНЫЕ";
		$res[0]["type_nom"] = "PAYING";
		$res[0]["total_price"] = number_format($total_sum, 2, ",", " ");;
		$res[0]["value"] = $paying_count;

		if ($no_subscrible>0) {
			$res[$i]["type"]="Не подписанные";
			$res[$i]["type_nom"]="no_subscribered";
			$res[$i]["total_price"]="-";
			$res[$i]["value"]=$no_subscrible;

			$i++;
		}

		if ($in_exceptions>0) {
			$res[$i]["type"]="Исключения";
			$res[$i]["type_nom"]="IN_EXCEPTIONS";
			$res[$i]["total_price"]="-";
			$res[$i]["value"]=$in_exceptions;

			$i++;
		}

		if ($edited_calls_prices_count>0) {
			$res[$i]["type"]="Изменённые";
			$res[$i]["type_nom"]="EDITED_CALL_PRICES";
			$res[$i]["total_price"]="-";
			$res[$i]["value"]=$edited_calls_prices_count;
		}


		$result=Array(
			"result" => $res,
			"kefs" => $call_types_kefs
		);

		echo json_encode($result); 

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

		function is_payable($call, $phone, $call_type, $operator) {
			global $number_services_settings;
			global $exceptions;
			global $in_exceptions;
			global $edited_calls_prices;

			$from_number=$call["from_number"];
			$to_number=$call["to_number"];

			/*if ($from_number!=="") {
				if (in_array($from_number, $exceptions)) {
					$in_exceptions++;

					return false;
				} else if (in_array("7".$from_number, $exceptions)) {
					$in_exceptions++;

					return false;
				} else if (in_array(substr($from_number,1), $exceptions)) {
					$in_exceptions++;

					return false;
				}
			}*/

			if ($to_number!=="") {
				foreach ($exceptions as $ex) {
					if (stristr($to_number, $ex)!==false) {
						$in_exceptions++;

						return false;
					} else if (stristr("7".$to_number, $ex)!==false) {
						$in_exceptions++;

						return false;
					} else if (stristr(substr($to_number,1), $ex)!==false) {
						$in_exceptions++;
						
						return false;
					}
				}

				/*if (in_array($to_number, $exceptions)) {
					$in_exceptions++;

					return false;
				} else if (in_array("7".$to_number, $exceptions)) {
					$in_exceptions++;

					return false;
				} else if (in_array(substr($to_number,1), $exceptions)) {
					$in_exceptions++;

					return false;
				}*/
			}

			$call_date=strtotime($call["call_date"]);
			
			$available_services=Array();

			foreach ($number_services_settings["services_history"] as $service_code => $service_date_items) {
				foreach ($service_date_items as $key => $service_date_item) {
					$start_date=strtotime($service_date_item["start_date"]);
					$finish_date=strtotime($service_date_item["finish_date"]);

					if ($start_date<=$call_date && $finish_date>=$call_date) {
						$available_services[]=$service_code;
					}
				}
			}

			$g1000=false;
			$sms500=false;
			$svoya_set=false;
			$bl_minut=false;
			$l_roaming=false;
			$g_roaming=false;

			if (count($available_services)) {
				//G1000, G3000, БЛ интернет или GPRS БЛ
				$codes=Array(
					"mts" => Array(7, 8, 25, 30),
					"bee" => Array(36, 41, 37, 45),
					"meg" => Array(51, 56, 59, 60),
					"tele2" => Array(66, 71, 67, 75)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$g1000=true;
						break;
					}
				}

				//G-роуминг
				$codes=Array(
					"mts" => Array(83),
					"bee" => Array(),
					"meg" => Array(),
					"tele2" => Array()
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$g_roaming=true;
						break;
					}
				}

				//SMS 300, SMS 500 или 1000 SMS
				$codes=Array(
					"mts" => Array(9, 31, 82),
					"bee" => Array(38, 46),
					"meg" => Array(53, 61),
					"tele2" => Array(68, 76)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$sms500=true;
						break;
					}
				}

				//Своя сеть
				$codes=Array(
					"mts" => Array(5),
					"bee" => Array(34),
					"meg" => Array(49),
					"tele2" => Array(64)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$svoya_set=true;
						break;
					}
				}

				//БЛ Минут
				$codes=Array(
					"mts" => Array(29),
					"bee" => Array(44),
					"meg" => Array(59),
					"tele2" => Array(74)
				);

				foreach ($codes[$operator] as $code) {
					if (in_array($code, $available_services)!==false) {
						$bl_minut=true;
						break;
					}
				}
			}


			$result=true;

			$index=$call["call_date"]."*".$call["call_time"]."*".$phone."*".$call["call_type"]."*".$call["service"];

			if (isset($edited_calls_prices[$index]) && $edited_calls_prices[$index]>0) {
				$result=true;
			} else if (strpos($call_type,"ВХ")!==false) {
				$result=false;
			} else if (strpos($call_type,"РОУМ-GPRS")!==false) {
				if ($g_roaming==true) {
					return false;
				} else {
					return true;
				}
			} else if (strpos($call_type,"GPRS")!==false) {
				if ($g1000==true) {//Если подключены G1000, G3000, БЛ интернет или GPRS БЛ, то не бесплатный
					$result=false;
				}
			} else if (strpos($call_type,"SMS")!==false) {
				if ($sms500==true) {//Если подключено SMS 500 или 1000 SMS, то бесплатный
					$result=false;
				}
			} else if ($svoya_set==true) {//Если подкючена Своя сеть
				//if (stripos($call_type,"ИСХ-ВСР")!==false || stripos($call_type,"ИСX-ЗГП")!==false || strpos($call_type,"ПЕР-ВСР")!==false || strpos($call_type,"ПЕР-ЗГП")!==false) {
				if (stripos($call_type,"ИСХ-ВСР")!==false || stripos($call_type,"ИСX-ЗГП")!==false) {
					$result=false;
				} else {
					$result=true;
				}
			} else if ($bl_minut==true) {//Если подкючены БЛ минуты
				if (strpos($call_type,"ИСХ-ВСР")!==false || strpos($call_type,"ИСX-ЗГП")!==false || strpos($call_type,"ИСХ-СРДО")!==false) {
					$result=false;
				} else {
					$result=true;
				}
			} else {
				$result=true;
			}

			//echo "RES:".$result."(".$call_type."):".date("Y-m-d",$call_date).":".implode(",",$available_services)."\n";

			return $result;
		}

		function no_need_calc($call, $phone, $ct, $operator, $year, $month) {
			global $no_need_calc_edited_periods;

			$index=$call["call_length"]."*".$call["call_date"]."*".$call["call_time"]."*".$phone."*".$call["call_type"]."*".$call["service"];
;
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

		function get_unit_price($index, $call, $phone, $ct, $operator) {
			global $call_types_prices;
			global $edited_calls_prices;
			global $call_types_kefs;

//echo $index."\n";			
//print_r($edited_calls_prices);

			$unit_price["type"]="nominal";
			$unit_price["value"]=0;
			$unit_price["old_value"]="";

			if (isset($edited_calls_prices[$index])) {
				$unit_price["type"]="edited";
				$unit_price["value"]=$edited_calls_prices[$index][0]["unit_price"];
			}

			if (isset($call_types_prices[$ct])) {
				for ($ii=0;$ii<count($call_types_prices[$ct]);$ii++) {
					if (strtotime($call["call_date"])>=strtotime($call_types_prices[$ct][$ii]["date"])) {
						if ($unit_price["type"]=="nominal") {
							$unit_price["value"]=round($call_types_prices[$ct][$ii]["price"],2);
						} else {
							$unit_price["old_value"]=round($call_types_prices[$ct][$ii]["price"],2);
						}
					}
				}
			} else {
				if ($unit_price["type"]=="nominal") {
					$unit_price["value"]=0;
				} else {
					$unit_price["old_value"]=0;
				}
			}

			if (isset($call_types_kefs[$ct])) {
				$unit_price["value"]=$unit_price["value"]*$call_types_kefs[$ct][0]["kef"];
			}

			return $unit_price;
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

		function value($v) {
			if ($v==0) {
				return 1;
			} else {
				return $v;
			}
		}
		
		function addZero($n) {
			if ($n<10) {
				return "0".$n;
			} else {
				return $n;
			}
		}
?>