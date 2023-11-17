<?php
	date_default_timezone_set("Asia/Vladivostok");

	$dir=realpath(dirname(__FILE__) . '/../../..');

	require($dir."/php/pdo_db_connect.php");
	require($dir."/php/phpQuery.php");

	$result=Array();
	$i=0;

	$operators=Array("bee", "mts", "meg", "tele2");

	$s_no_attentions=$db->prepare("SELECT id FROM no_attentions WHERE until_date>=DATE(NOW()) AND tel_nom=:tel_nom AND type=:type");

	$a=0;

	foreach ($operators as $oper) {
		if ($oper=="meg") {
			$oper1="megafon";
		} else {
			$oper1=$oper;
		}

		$hour=intval(date("H", time()));
		$day=intval(date("d", time()));

		if ($day==1) {
			if ($hour<8) {
				exit();
			}
		} else {
			if ($oper!='mts' && $hour<6) {
				continue;
			}
			if ($oper=='mts' && ($hour<4 || $hour==23)) {
				continue;
			}
		}

		if ($oper=="mts") {
			$s=$db->prepare("SELECT tel_nom FROM requests WHERE type='Смена ТП' AND status='Выполнено' AND date_time BETWEEN NOW()-INTERVAL 2 HOUR AND NOW()-INTERVAL 1 HOUR AND operator='mts'");
			$s->execute();

			if ($s->rowCount()>0) {
				$tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);

				foreach ($tel_noms as $tel_nom) {
					//Проверка наличия службы коротких сообщений у номера
					$s=$db->prepare("SELECT id FROM mts_services WHERE tel_nom=:tel_nom AND service_id=2410 AND status='Активная'");
					$s->bindValue(":tel_nom", $tel_nom);
					$s->execute();

					if ($s->rowCount()==0) {
						$attempts=0;
						do {
							if ($attempts==3) {
								save_attention($req["tel_nom"], "service_on_error", "Не принята заявка: Служба коротких сообщений", 0, "mts");

								$txt=$tel_nom.". Не принята заявка: Служба коротких сообщений";

								$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation) VALUES (NOW(), :name, :log_str)");
								$s->bindValue(":name", "SCRIPT");
								$s->bindValue(":log_str", $txt);
								$s->execute();

								break;
							}

							$attempts++;
									
							$txt=$tel_nom.". Подключение Службы коротких сообщений (".$attempts.")";

							echo $txt."\n";

							save_attention($tel_nom, "", $txt, 0, "mts");

							$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation) VALUES (NOW(), :name, :log_str)");
							$s->bindValue(":name", "SCRIPT");
							$s->bindValue(":log_str", $txt);
							$s->execute();

							$res=mts_service_on($tel_nom, "PE0320");

							sleep(2);
						} while ($res==false);
					} else {
						echo $tel_nom.": УЖЕ ПОДКЛЮЧЕНА служба коротких сообщений\n";
					}
				}
			}
		}

		echo $oper.". Отключение мобильного Интенета\n";
		echo $oper.". Рассмотреть смену ТП\n";
		echo $oper.". Заканчиваются минуты/Инет/СМС\n";

		//Отключение мобильного Интернета

		$s=$db->prepare("SELECT tel_nom, SUM(start) as start, SUM(remain) as remain, tarif, DATEDIFF(refresh_date,NOW()) as time_diff, unit FROM ".$oper."_counters_actual WHERE to_dinamic=1 AND contract>0 AND LENGTH(blocks)<2 AND LOCATE('Командный игрок',tarif)=0 GROUP BY tel_nom, unit");
		$s->execute();
		$res=$s->fetchAll(PDO::FETCH_ASSOC);

		if ($oper=="mts") {
			$s=$db->prepare("SELECT tel_nom FROM ".$oper."_counters_actual WHERE LOCATE('УБ-М+ (МИН', counter) GROUP BY tel_nom");
			$s->execute();
			$tel_noms_with_markers_min=$s->fetchAll(PDO::FETCH_COLUMN);

			$s=$db->prepare("SELECT tel_nom FROM ".$oper."_counters_actual WHERE LOCATE('УБ-М+ (КБ', counter) GROUP BY tel_nom");
			$s->execute();
			$tel_noms_with_markers=$s->fetchAll(PDO::FETCH_COLUMN);

			$s_is_service=$db->prepare("SELECT id FROM mts_services WHERE (service_id=1731 OR service_id=1681) AND status='Активная' AND tel_nom=:tel_nom");

			$s_small_gprs=$db->prepare("SELECT tel_nom FROM monthes_total_rests WHERE gprs<10*1024");
			$s_small_gprs->execute();

			$small_gprs=$s_small_gprs->fetchAll(PDO::FETCH_COLUMN);

			//print_r($small_gprs);
		}

		$found_for_change_tarif=Array();

		foreach ($res as $r) {
			if ($oper=="mts") {
				if ($r["tarif"]=="Бизнес Коннект 1 (КОРП) (SS)" && $r["remain"]/1024<100 && ($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
					$s->bindValue(":tel_nom", $r["tel_nom"]);
					$s->execute();

					$s_is_service->bindValue(":tel_nom", $r["tel_nom"]);
					$s_is_service->execute();

					if ($s->rowCount()>0 && $s_is_service->rowCount()>1) {
						save_attention($r["tel_nom"], "inet_under", "Интернет < 100Мб", 0, $oper);
						mts_service_off($r["tel_nom"]);
					}
				}
			}

			//Рассмотреть смену ТП

			if ($oper=="mts" && $r["time_diff"]==3) {//За 3 дня до обновления
				if (in_array($r["tel_nom"], $tel_noms_with_markers_min)==false) {//Рассматриваем только если нет УБМ+ по минутам
					if ($r["tarif"]=="Умный бизнес M 092018 (КОРП) (SS)") {
						$found_for_change_tarif[$r["tel_nom"]]["tarif"]="UBM";

						if ($r["unit"]=="МИН" && $r["start"]!=0 && ($r["start"]-$r["remain"])<500) {
							if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
								$found_for_change_tarif[$r["tel_nom"]]["count"]++;
							} else {
								$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
							}
						}

						if ($r["unit"]=="КБ" && $r["start"]!=0 && ($r["start"]-$r["remain"])<15*1024*1024) {
							if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
								$found_for_change_tarif[$r["tel_nom"]]["count"]++;
							} else {
								$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
							}
						}

						if ($r["unit"]=="SMS" && $r["start"]!=0 && ($r["start"]-$r["remain"])<70) {
							if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
								$found_for_change_tarif[$r["tel_nom"]]["count"]++;
							} else {
								$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
							}
						}
					}

					if ($r["tarif"]=="Умный бизнес M (КОРП) (SS)") {
						$found_for_change_tarif[$r["tel_nom"]]["tarif"]="UBM";

						if ($r["unit"]=="МИН" && $r["start"]==600 && in_array($r["tel_nom"], $small_gprs)) {//Расход меньше 10Гб за 2 недели
							$found_for_change_tarif[$r["tel_nom"]]["count"]=3;

							continue;
						}

						if ($r["unit"]=="МИН" && $r["start"]!=0 && ($r["start"]-$r["remain"])<500) {
							if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
								$found_for_change_tarif[$r["tel_nom"]]["count"]++;
							} else {
								$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
							}
						}

						if ($r["unit"]=="КБ" && $r["start"]!=0 && ($r["start"]-$r["remain"])<15*1024*1024) {
							if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
								$found_for_change_tarif[$r["tel_nom"]]["count"]++;
							} else {
								$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
							}
						}

						if ($r["unit"]=="SMS" && $r["start"]!=0 && ($r["start"]-$r["remain"])<70) {
							if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
								$found_for_change_tarif[$r["tel_nom"]]["count"]++;
							} else {
								$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
							}
						}
					}
				}

				if ($r["tarif"]=="Умный бизнес Start 092018 (КОРП) (SS)") {
					$found_for_change_tarif[$r["tel_nom"]]["tarif"]="UBS";

					if ($r["unit"]=="МИН" && $r["start"]!=0 && ($r["start"]-$r["remain"])<100) {
						if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
							$found_for_change_tarif[$r["tel_nom"]]["count"]++;
						} else {
							$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
						}
					}

					if ($r["unit"]=="КБ" && $r["start"]!=0 && ($r["start"]-$r["remain"])<10*1024) {
						if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
							$found_for_change_tarif[$r["tel_nom"]]["count"]++;
						} else {
							$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
						}
					}

					if ($r["unit"]=="SMS" && $r["start"]!=0 && ($r["start"]-$r["remain"])<70) {
						if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
							$found_for_change_tarif[$r["tel_nom"]]["count"]++;
						} else {
							$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
						}
					}
				}

				if ($r["tarif"]=="Умный бизнес Start (КОРП) (SS)") {
					$found_for_change_tarif[$r["tel_nom"]]["tarif"]="UBS";

					if (in_array($r["tel_nom"], $tel_noms_with_markers) || in_array($r["tel_nom"], $tel_noms_with_markers_min)) {//Если есть УБ-М+ любой, то сразу смена тарифа
						$found_for_change_tarif[$r["tel_nom"]]["count"]=3;

						continue;
					}

					if ($r["unit"]=="МИН" && $r["start"]>550 && ($r["start"]-$r["remain"])<450) {//Если начальное больше 550, а израсходовано меньше 450
						$found_for_change_tarif[$r["tel_nom"]]["count"]=3;

						continue;
					}

					if ($r["unit"]=="МИН" && $r["start"]!=0 && ($r["start"]-$r["remain"])<100) {
						if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
							$found_for_change_tarif[$r["tel_nom"]]["count"]++;
						} else {
							$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
						}
					}

					if ($r["unit"]=="КБ" && $r["start"]!=0 && ($r["start"]-$r["remain"])<10*1024) {
						if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
							$found_for_change_tarif[$r["tel_nom"]]["count"]++;
						} else {
							$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
						}
					}

					if ($r["unit"]=="SMS" && $r["start"]!=0 && ($r["start"]-$r["remain"])<70) {
						if (isset($found_for_change_tarif[$r["tel_nom"]]["count"])==true) {
							$found_for_change_tarif[$r["tel_nom"]]["count"]++;
						} else {
							$found_for_change_tarif[$r["tel_nom"]]["count"]=1;
						}
					}
				}
			}

			//Заканчиваются минуты/Инет/СМС;
			
			if ($oper=="mts") {
				if ($r["start"]!=0 && $r["remain"]/$r["start"]<0.101) {
					$type="";

					if ($r["tarif"]=="Умный бизнес M (КОРП) (SS)") {
						if ($r["unit"]=="МИН") {

							if ($r["start"]<700) {

								$type="mins";
								$txt="Заканчиваются минуты (".$r["start"]."/".$r["remain"].")";
								$type1="mins_ending";

							}

						} else if ($r["unit"]=="SMS") {

							$type="sms";
							$txt="Заканчиваются SMS (".$r["start"]."/".$r["remain"].")";
							$type1="sms_ending";

						}
					} else if ($r["tarif"]=="Умный бизнес Start 092018 (КОРП) (SS)") {
						if ($r["unit"]=="МИН") {

							$type="mins";
							$txt="Заканчиваются минуты (".$r["start"]."/".$r["remain"].")";
							$type1="mins_ending";

						} else if (($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
							
							$type="inet";
							$txt="Заканчивается Интернет (".ceil($r["start"]/1024)."/".ceil($r["remain"]/1024).")";
							$type1="inet_ending";

						} else if ($r["unit"]=="SMS") {

							$type="sms";
							$txt="Заканчиваются SMS (".$r["start"]."/".$r["remain"].")";
							$type1="sms_ending";

						}
					} else if ($r["tarif"]=="Умный бизнес Start (КОРП) (SS)") {
						if ($r["unit"]=="МИН") {

							if ($r["start"]<550) {

								$type="mins";
								$txt="Заканчиваются минуты (".$r["start"]."/".$r["remain"].")";
								$type1="mins_ending";

							}

						} else if (($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
							
							if ($r["start"]<14*1024*1024) {

								$type="inet";
								$txt="Заканчивается Интернет (".ceil($r["start"]/1024)."/".ceil($r["remain"]/1024).")";
								$type1="inet_ending";
							
							}

						} else if ($r["unit"]=="SMS") {

							$type="sms";
							$txt="Заканчиваются SMS (".$r["start"]."/".$r["remain"].")";
							$type1="sms_ending";

						}
					}

					if ($type!="") {
						$s_no_attentions->bindValue(":tel_nom", $r["tel_nom"]);
						$s_no_attentions->bindValue(":type", $type);
						$s_no_attentions->execute();

						if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
							save_attention($r["tel_nom"], $type1, $txt, 0, $oper);
						}
					}
				}
			} else if ($oper=="bee") {
				if ($r["start"]!=0 && $r["remain"]/$r["start"]<0.101) {
					echo $r["tel_nom"].":".$r["start"]."/".$r["remain"]."/".$r["unit"]."\n";

					if ($r["unit"]=="МИН") {
						$type="mins";
						$txt="Заканчиваются минуты (".$r["start"]."/".$r["remain"].")";
						$type1="mins_ending";
					} else if ($r["unit"]=="SMS") {
						$type="sms";
						$txt="Заканчиваются SMS (".$r["start"]."/".$r["remain"].")";
						$type1="sms_ending";
					} else if (($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
						$type="inet";
						$txt="Заканчивается Интернет (".ceil($r["start"]/1024)."/".ceil($r["remain"]/1024).")";
						$type1="inet_ending";
					} else {
						$txt="";
					}

					$s_no_attentions->bindValue(":tel_nom", $r["tel_nom"]);
					$s_no_attentions->bindValue(":type", $type);
					$s_no_attentions->execute();

					if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
						save_attention($r["tel_nom"], $type1, $txt, 0, $oper);
					}
				} else if ($r["start"]!=0 && $r["remain"]/$r["start"]<0.5) {
					echo $r["tel_nom"].":".$r["start"]."/".$r["remain"]."/".$r["unit"]."\n";

					if ($r["unit"]=="МИН") {
						$type="mins50";
						$txt="Заканчиваются минуты 50% (".$r["start"]."/".$r["remain"].")";
						$type1="mins_ending50";
					} else if ($r["unit"]=="SMS") {
						$type="sms50";
						$txt="Заканчиваются SMS 50% (".$r["start"]."/".$r["remain"].")";
						$type1="sms_ending50";
					} else if (($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
						$type="inet50";
						$txt="Заканчивается Интернет 50% (".ceil($r["start"]/1024)."/".ceil($r["remain"]/1024).")";
						$type1="inet_ending50";
					} else {
						$txt="";
					}

					$s_no_attentions->bindValue(":tel_nom", $r["tel_nom"]);
					$s_no_attentions->bindValue(":type", $type);
					$s_no_attentions->execute();

					if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
						save_attention($r["tel_nom"], $type1, $txt, 0, $oper);
					}
				}
			} else if ($oper=="meg") {
				if ($r["start"]!=0 && $r["remain"]/$r["start"]<0.101) {
					echo $r["tel_nom"].":".$r["start"]."/".$r["remain"]."/".$r["unit"]."\n";

					if ($r["unit"]=="МИН") {
						$type="mins";
						$txt="Заканчиваются минуты (".$r["start"]."/".$r["remain"].")";
						$type1="mins_ending";
					} else if ($r["unit"]=="SMS") {
						$type="sms";
						$txt="Заканчиваются SMS (".$r["start"]."/".$r["remain"].")";
						$type1="sms_ending";
					} else if (($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
						$type="inet";
						$txt="Заканчивается Интернет (".ceil($r["start"]/1024)."/".ceil($r["remain"]/1024).")";
						$type1="inet_ending";
					} else {
						$txt="";
					}

					$s_no_attentions->bindValue(":tel_nom", $r["tel_nom"]);
					$s_no_attentions->bindValue(":type", $type);
					$s_no_attentions->execute();

					if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
						save_attention($r["tel_nom"], $type1, $txt, 0, $oper);
					}
				} else if ($r["start"]!=0 && $r["remain"]/$r["start"]<0.5) {
					echo $r["tel_nom"].":".$r["start"]."/".$r["remain"]."/".$r["unit"]."\n";

					if ($r["unit"]=="МИН") {
						$type="mins50";
						$txt="Заканчиваются минуты 50% (".$r["start"]."/".$r["remain"].")";
						$type1="mins_ending50";
					} else if ($r["unit"]=="SMS") {
						$type="sms50";
						$txt="Заканчиваются SMS 50% (".$r["start"]."/".$r["remain"].")";
						$type1="sms_ending50";
					} else if (($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
						$type="inet50";
						$txt="Заканчивается Интернет 50% (".ceil($r["start"]/1024)."/".ceil($r["remain"]/1024).")";
						$type1="inet_ending50";
					} else {
						$txt="";
					}

					$s_no_attentions->bindValue(":tel_nom", $r["tel_nom"]);
					$s_no_attentions->bindValue(":type", $type);
					$s_no_attentions->execute();

					if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
						save_attention($r["tel_nom"], $type1, $txt, 0, $oper);
					}
				}
			} else {
				if ($r["start"]!=0 && $r["remain"]/$r["start"]<0.101) {
					echo $r["tel_nom"].":".$r["start"]."/".$r["remain"]."/".$r["unit"]."\n";

					if ($r["unit"]=="МИН") {
						$type="mins";
						$txt="Заканчиваются минуты (".$r["start"]."/".$r["remain"].")";
						$type1="mins_ending";
					} else if ($r["unit"]=="SMS") {
						$type="sms";
						$txt="Заканчиваются SMS (".$r["start"]."/".$r["remain"].")";
						$type1="sms_ending";
					} else if (($r["unit"]=="КБ" || $r["unit"]=="МБ" || $r["unit"]=="ГБ")) {
						$type="inet";
						$txt="Заканчивается Интернет (".ceil($r["start"]/1024)."/".ceil($r["remain"]/1024).")";
						$type1="inet_ending";
					} else {
						$txt="";
					}

					$s_no_attentions->bindValue(":tel_nom", $r["tel_nom"]);
					$s_no_attentions->bindValue(":type", $type);
					$s_no_attentions->execute();

					if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
						save_attention($r["tel_nom"], $type1, $txt, 0, $oper);
					}
				}
			}
		}

		print_r($found_for_change_tarif);

		//Маленький расход - рассмотреть смену тарифа
		foreach ($found_for_change_tarif as $tel_nom => $value) {
			if ($value["count"]==3) {
				echo $tel_nom.": Рассмотреть смену тарифа (".$value["count"].")\n";

				//Для Умный бизнес Start (КОРП) (SS) при наличии Ежемесячной скидки 70 смену тарифа не рассматриваем 
				$s_serv_sel=$db->prepare("SELECT id FROM mts_services WHERE tel_nom=:tel_nom AND service_id=2084 AND param=70 AND status='Активная'");
				$s_serv_sel->bindValue(":tel_nom", $tel_nom);
				$s_serv_sel->execute();

				if ($value["tarif"]=="UBS" && $s_serv_sel->rowCount()>0) {
					echo "UBS и Есть скидка 70 - пропускаем\n";
					continue;
				}

				$s_select=$db->prepare("SELECT id FROM attentions WHERE tel_nom=:tel_nom AND type=:type AND `date` BETWEEN DATE(NOW()-24*3600) AND DATE(NOW())");
				$s_select->bindValue(":tel_nom", $tel_nom);
				$s_select->bindValue(":type", "change_tarif");
				$s_select->execute();

				if ($s_select->rowCount()==0) {
					save_attention($tel_nom, "change_tarif", "Рассмотреть смену тарифа", 0, $oper);
				}
			}
		}

		//Завтра обновление тарифного плана
		if ($oper=="mts") {

			echo $oper.". Определение Завтра обновление тарифного плана 'Умный бизнес M 092018 (КОРП) (SS)'\n";

			$s=$db->prepare("SELECT tel_nom, SUM(start) as start, SUM(remain) as remain FROM mts_counters_actual WHERE tarif='Умный бизнес M 092018 (КОРП) (SS)' AND LENGTH(blocks)<5 AND refresh_date=:date AND unit='МИН' GROUP BY tel_nom");
			$s->bindValue(":date", date("Y-m-d", time()+24*3600));
			$s->execute();

			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

			foreach ($tel_noms as $tn) {
				echo $tn["tel_nom"].": Завтра обновление тарифного плана (".($tn["start"]-$tn["remain"]).")\n";

				if ($tn["start"]-$tn["remain"]>1000) {
					save_attention($tn["tel_nom"], "tommorow_refresh", "Завтра обновление тарифного плана", 1, "mts");
				} else {
					save_attention($tn["tel_nom"], "tommorow_refresh", "Завтра обновление тарифного плана", 0, "mts");
				}
			}

			echo $oper.". Определение Завтра обновление тарифного плана 'Умный бизнес Start 092018 (КОРП) (SS)'\n";

			$s=$db->prepare("SELECT tel_nom  FROM mts_counters_actual WHERE tarif='Умный бизнес Start 092018 (КОРП) (SS)' AND LENGTH(blocks)<5 AND refresh_date=:date GROUP BY tel_nom");
			$s->bindValue(":date", date("Y-m-d", time()+24*3600));
			$s->execute();

			$tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);

			foreach ($tel_noms as $tn) {
				echo $tn.": Завтра обновление тарифного плана\n";

				save_attention($tn, "tommorow_refresh", "Завтра обновление тарифного плана", 0, "mts");
			}


			echo $oper.". Отсутствие Ограничение Инета в межд роуминге \n";

			$s=$db->prepare("SELECT id FROM services_dict WHERE name='Международный и национальный роуминг' AND operator='mts'");
			$s->execute();

			$services1=$s->fetchAll(PDO::FETCH_COLUMN);

			$services1=implode(",", $services1);

			$s=$db->prepare("SELECT id FROM services_dict WHERE name='Мобильный Интернет' AND operator='mts'");
			$s->execute();

			$services2=$s->fetchAll(PDO::FETCH_COLUMN);

			$services2=implode(",", $services2);

			$s=$db->prepare("SELECT id FROM services_dict WHERE name='Ограничение интернета в международном роуминге' AND operator='mts'");
			$s->execute();

			$services3=$s->fetchAll(PDO::FETCH_COLUMN);

			$services3=implode(",", $services3);

			$s=$db->prepare("SELECT tel_nom FROM mts_services WHERE service_id IN (".$services1.") AND status='Активная' GROUP BY tel_nom");
			$s->execute();

			$tel_noms1=$s->fetchAll(PDO::FETCH_COLUMN);

			$tel_noms1=implode(",", $tel_noms1);

			//У кого есть "Мобильный Интернет" и "Международный и национальный роуминг"
			$s=$db->prepare("SELECT tel_nom FROM mts_services WHERE tel_nom IN (".$tel_noms1.") AND service_id IN (".$services2.")  AND status='Активная' GROUP BY tel_nom");
			$s->execute();

			$tel_noms2=$s->fetchAll(PDO::FETCH_COLUMN);

			//У кого есть "Ограничение интернета в международном роуминге"
			$s=$db->prepare("SELECT tel_nom FROM mts_services WHERE service_id IN (".$services3.") AND status='Активная' GROUP BY tel_nom");
			$s->execute();

			$tel_noms3=$s->fetchAll(PDO::FETCH_COLUMN);

			//У кого есть первые два и нет третьего
			$tel_noms=array_diff($tel_noms2, $tel_noms3);

			$tel_noms=implode(",", $tel_noms);

			//Из них не удалённые
			$s=$db->prepare("SELECT PhoneNumber FROM mts_phones WHERE PhoneNumber IN (".$tel_noms.") AND status=0");
			$s->execute();

			$tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);

			foreach ($tel_noms as $tn) {
				echo $tn.": Без ограничения GPRS в роуминге\n";

				save_attention($tn, "without_inet_limit", "Без ограничения GPRS в роуминге", 0, "mts");
			}
		}


		$s_migrations_out=$db->prepare("SELECT PhoneNumber FROM phones_changes WHERE operator='beeline' AND DATE(date_time)=CURRENT_DATE()");
		$s_migrations_out->execute();

		$migrations_out=$s_migrations_out->fetchAll(PDO::FETCH_COLUMN);

		//Большие начисления

		echo $oper.". Определение Большого расхода\n";

		$s=$db->prepare("SELECT tel_nom, spended, refresh_date, account, contract FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()) GROUP BY tel_nom");
		$s->execute();
		$spended1=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, spended, refresh_date FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 24 HOUR) GROUP BY tel_nom");
		$s->execute();
		$spended2=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, spended, refresh_date FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 48 HOUR) GROUP BY tel_nom");
		$s->execute();
		$spended3=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, spended, refresh_date FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 72 HOUR) GROUP BY tel_nom");
		$s->execute();
		$spended4=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, spended, refresh_date FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 96 HOUR) GROUP BY tel_nom");
		$s->execute();
		$spended5=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s_last_month1=$db->prepare("SELECT spended FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 1 MONTH) AND tel_nom=:tel_nom");

		$s_last_month2=$db->prepare("SELECT spended FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 1 MONTH-INTERVAL 1 DAY) AND tel_nom=:tel_nom");

		foreach ($spended1 as $tel_nom => $spended) {
			if (isset($spended1[$tel_nom][0]["spended"])==false || isset($spended2[$tel_nom][0]["spended"])==false || isset($spended3[$tel_nom][0]["spended"])==false || isset($spended4[$tel_nom][0]["spended"])==false || isset($spended5[$tel_nom][0]["spended"])==false) {
				//echo "++++".$tel_nom."\n";
				continue;
			}

			$today_spended=round($spended1[$tel_nom][0]["spended"]-$spended2[$tel_nom][0]["spended"],2);

			$two_days_spended=round($spended1[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"],2);

			$day=intval(date("d", time()));

			if ($oper=="bee" && $spended1[$tel_nom][0]["contract"]==1 && $day>4 && $day<6 && $today_spended<0) {
				continue;
			}
			if ($oper=="bee" && $spended1[$tel_nom][0]["contract"]==2 && $day>0 && $day<2 && $today_spended<0) {
				continue;
			}
			if ($oper!="bee" && $day>0 && $day<2 && $today_spended<0) {
				continue;
			}
			
			if ($today_spended<-0.1 && (in_array($tel_nom, $migrations_out)==false || $oper!="bee")) {
				$s_no_attentions->bindValue(":tel_nom", $tel_nom);
				$s_no_attentions->bindValue(":type", "money");
				$s_no_attentions->execute();

				if ($s_no_attentions->rowCount()==0) {
					if ($oper=='meg' && $spended1[$tel_nom][0]["spended"]==0) {//Для Мегафона если (возможно) не получены начисления, то пытаемся переполучить
						print_log($db, "MEG. ".$tel_nom.". Не получены начисления. Повторный запрос");

						$meg_select=$db->prepare("SELECT id FROM megafon_phones WHERE PhoneNumber=:tel_nom");
						$meg_select->bindValue(":tel_nom", $tel_nom);
						$meg_select->execute();

						$phone=$meg_select->fetch(PDO::FETCH_ASSOC);

						$result=json_decode(curl_spended("b2blk.megafon.ru", "/subscriber/finances/", $phone["id"], "megafon_9241017799"));

						$other_spended=Array();
						
						if (isset($result->financeProfile->subscriberCostsEntity->periodAmount)) {
							$periodAmount=$result->financeProfile->subscriberCostsEntity->periodAmount;
						} else {
							$periodAmount=0;
						}

						foreach ($result->financeProfile->subscriberCostsEntity->feeChargesList as $chargeType) {
							$other_spended[]=$chargeType->amount;
						}

						foreach ($result->financeProfile->charges as $charge) {
							foreach ($charge as $ch) {
								if (count($charge)>0) {
									foreach ($ch as $c) {
										$other_spended[]=$c->value;
									}
								}
							}
						}

						foreach ($result->financeProfile->subscriberCostsEntity->otherChargesList as $charge) {
							$other_spended[]=$charge->amount;
						}

						if (count($other_spended)==0) {
							$spended[$i]=$periodAmount;
						} else {
							$other_spended=array_sum($other_spended);

							$spended[$i]=$other_spended;
						}
		
						if ($spended[$i]>100000) {
							$spended[$i]=0;
						}

						$today_spended=$spended[$i]-$spended2[$tel_nom][0]["spended"];
					}

					save_attention($tel_nom, "low_spended", "Отрицательный расход: ".$today_spended, 0, $oper);
				}
			} else if ($oper=="bee" && ($today_spended==-0.01 || $today_spended==-0.02)) {
				$s_edit_prev_spended=$db->prepare("UPDATE ".$oper."_counters_history SET spended=spended".$today_spended." WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 24 HOUR)");
				$s_edit_prev_spended->bindValue(":tel_nom", $tel_nom);
				$s_edit_prev_spended->execute();

				if ($s_edit_prev_spended->rowCount()>0) {
					$s_edit_spended_tbl=$db->prepare("UPDATE spended SET spended=spended".$today_spended." WHERE tel_nom=:tel_nom AND `date`=DATE(NOW()-INTERVAL 24 HOUR)");

					$s_edit_spended_tbl->bindValue(":tel_nom", $tel_nom);
					$s_edit_spended_tbl->execute();

					if ($s_edit_spended_tbl->rowCount()>0) {
						echo $tel_nom.": Исправлено начисление за вчера\n";

						save_attention($tel_nom, "edit_spended", "Исправлено начисление за вчера ".abs($today_spended), 1, $oper);

						total_spended($spended1[$tel_nom][0]["account"], date("Y-m-d", time()-24*3600), $oper);
					}
				}
			}

			if ($oper=="bee") {
				if ($two_days_spended==-0.01) {
					$s_edit_prev_spended=$db->prepare("UPDATE ".$oper."_counters_history SET spended=spended".$two_days_spended." WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 48 HOUR)");
					$s_edit_prev_spended->bindValue(":tel_nom", $tel_nom);
					$s_edit_prev_spended->execute();

					if ($s_edit_prev_spended->rowCount()>0) {
						$s_edit_spended_tbl=$db->prepare("UPDATE spended SET spended=spended".$two_days_spended." WHERE tel_nom=:tel_nom AND `date`=DATE(NOW()-INTERVAL 48 HOUR)");

						$s_edit_spended_tbl->bindValue(":tel_nom", $tel_nom);
						$s_edit_spended_tbl->execute();

						if ($s_edit_spended_tbl->rowCount()>0) {
							save_attention($tel_nom, "edit_spended_tow_days_later", "Исправлено начисление за позавчера ".abs($two_days_spended), 1, $oper);

							total_spended($spended1[$tel_nom][0]["account"], date("Y-m-d", time()-48*3600), $oper);
						}
					}
				}

				if ($spended1[$tel_nom][0]["spended"]==0 && $spended3[$tel_nom][0]["spended"]==0 && $spended2[$tel_nom][0]["spended"]!=0) {
					$s_edit_prev_spended=$db->prepare("UPDATE ".$oper."_counters_history SET spended=0 WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 24 HOUR)");
					$s_edit_prev_spended->bindValue(":tel_nom", $tel_nom);
					$s_edit_prev_spended->execute();

					if ($s_edit_prev_spended->rowCount()>0) {
						$s_edit_spended_tbl=$db->prepare("UPDATE spended SET spended=0 WHERE tel_nom=:tel_nom AND `date`=DATE(NOW()-INTERVAL 24 HOUR)");

						$s_edit_spended_tbl->bindValue(":tel_nom", $tel_nom);
						$s_edit_spended_tbl->execute();

						if ($s_edit_spended_tbl->rowCount()>0) {
							save_attention($tel_nom, "edit_spended_null_tomorrow", "Исправлено начисление за вчера ".$spended2[$tel_nom][0]["spended"], 1, $oper);

							total_spended($spended1[$tel_nom][0]["account"], date("Y-m-d", time()-24*3600), $oper);
						}
					}
				}
			}

			$s_no_attentions->bindValue(":tel_nom", $tel_nom);
			$s_no_attentions->bindValue(":type", "money");
			$s_no_attentions->execute();

			if ($s_no_attentions->rowCount()==0) {
				$simple_bee=false;
				if ($spended1[$tel_nom][0]["contract"]==1) {
					$simple_bee=true;
				}

				if ($day==1 && $simple_bee==false) {//Первого числа сравниваем накопительное начисление с перым числом прошлого месяца
					if ($spended1[$tel_nom][0]["spended"]>45) {
						$s_last_month1->bindValue(":tel_nom", $tel_nom);
						$s_last_month1->execute();

						if ($s_last_month1->rowCount()>0) {
							$spendeds_last_month1=$s_last_month1->fetch(PDO::FETCH_COLUMN);//За 1e число прошлого месяца

							if (round($spended1[$tel_nom][0]["spended"],2)!=round($spendeds_last_month1,2)) {
								echo $tel_nom.": 1-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", THIS MONTH: ".$spended1[$tel_nom][0]["spended"].", LAST MONTH:".$spendeds_last_month1."\n";

								save_attention($tel_nom, "big_spended", "Расход: ".$spended1[$tel_nom][0]["spended"], 0, $oper);
							} else {
								echo $tel_nom.": 1-THE SAME TOTAL SPENDED THAT IN LAST MONTH (".$spended1[$tel_nom][0]["spended"].")\n";
							}
						}
					}
				} else if ($day>1 && $simple_bee==false) {//Если не первое число, то в случае равенства с таким же днём прошлого месяца ничего не записываем
					if ($today_spended>45) {
						$s_last_month1->bindValue(":tel_nom", $tel_nom);
						$s_last_month1->execute();

						$s_last_month2->bindValue(":tel_nom", $tel_nom);
						$s_last_month2->execute();

						if ($s_last_month1->rowCount()>0 && $s_last_month2->rowCount()>0) {
							$spendeds_last_month1=$s_last_month1->fetch(PDO::FETCH_COLUMN);//За сегодня прошлого месяца
							$spendeds_last_month2=$s_last_month2->fetch(PDO::FETCH_COLUMN);//За вчера прошлого месяца

							$spendeds_last_month=$spendeds_last_month1-$spendeds_last_month2;
						} else {
							$spendeds_last_month=null;
						}

						if (round($today_spended,2)>=round($spendeds_last_month+10,2)) {//Если сегодняшнее не совпадает с прошлым месяцем
							echo $tel_nom.": 2-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", TODAY: ".$today_spended.", LAST MONTH:".$spendeds_last_month."\n";

							save_attention($tel_nom, "big_spended", "Расход: ".$today_spended, 0, $oper);
						} else {
							echo $tel_nom.": 2-THE SAME SPENDED THAT IN LAST MONTH (".$today_spended.")\n";
						}
					}
				} else if ($day==1 && $simple_bee==true) {
					if ($spended1[$tel_nom][0]["spended"]>=1) {
						$s_last_month1->bindValue(":tel_nom", $tel_nom);
						$s_last_month1->execute();

						if ($s_last_month1->rowCount()>0) {
							$spendeds_last_month1=$s_last_month1->fetch(PDO::FETCH_COLUMN);//За 1e число прошлого месяца

							if (round($spended1[$tel_nom][0]["spended"],2)>=round($spendeds_last_month1+10,2)) {//Цифра за 1-е число больше цифры за 1-е число прошлого месяца хотябы на 10р							
								//$avg_spended=(($spended1[$tel_nom][0]["spended"]-$spended2[$tel_nom][0]["spended"])+($spended2[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"])+($spended3[$tel_nom][0]["spended"]-$spended4[$tel_nom][0]["spended"])+($spended4[$tel_nom][0]["spended"]-$spended5[$tel_nom][0]["spended"]))/4;

								//if ($today_spended>=$avg_spended+45) {//Начисление между 1 и последним чилом больше хотя бы на 45 среднего начисления за 4 дня
									echo $tel_nom.": 3-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", THIS MONTH".$spended1[$tel_nom][0]["spended"].", LAST MONTH:".$spendeds_last_month1.", TODAY SPENDED (".$today_spended."), AVG SPENDED (".$avg_spended.")\n";

									save_attention($tel_nom, "big_spended", "Расход: ".$spended1[$tel_nom][0]["spended"], 0, $oper);
								/*} else {
									echo $tel_nom.": 3-TODAY SPENDED (".$today_spended."), AVG SPENDED (".$avg_spended.")\n";
								}*/
							}
						} else {
							echo $tel_nom.": THE SAME TOTAL SPENDED THAT IN LAST MONTH (".$spended1[$tel_nom][0]["spended"].")\n";
						}
					}
				} else if (($day==2 || $day==6) && $simple_bee==true) {
					if ($today_spended>45) {
						echo $tel_nom.": 4-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", TODAY SPENDED (".$today_spended.")\n";

						save_attention($tel_nom, "big_spended", "Расход: ".$today_spended, 0, $oper);
					}
				} else if ($day==3 && $simple_bee==true) {
					$avg_spended=$spended2[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"];

					if ($today_spended>45 && $today_spended>=$avg_spended+45) {
						echo $tel_nom.": 5-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", TODAY SPENDED (".$today_spended."), AVG SPENDED (".$avg_spended.")\n";

						save_attention($tel_nom, "big_spended", "Расход: ".$today_spended, 0, $oper);
					}
				} else if ($day==4 && $simple_bee==true) {
					$avg_spended=(($spended2[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"])+($spended2[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"]))/2;

					if ($today_spended>45 && $today_spended>=$avg_spended+45) {
						echo $tel_nom.": 6-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", TODAY SPENDED (".$today_spended."), AVG SPENDED: (".$avg_spended.")\n";

						save_attention($tel_nom, "big_spended", "Расход: ".$today_spended, 0, $oper);
					}
				} else if ($day==5 && $simple_bee==true) {
					$s_last_month1->bindValue(":tel_nom", $tel_nom);
					$s_last_month1->execute();

					if ($s_last_month1->rowCount()>0) {
						if (ceil($spended1[$tel_nom][0]["spended"])!=ceil($spendeds_last_month1)) {
							echo $tel_nom.": 7-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", TODAY SPENDED (".$today_spended."), AVG SPENDED (".$avg_spended.")\n";

							save_attention($tel_nom, "big_spended", "Расход: ".$spended1[$tel_nom][0]["spended"], 0, $oper);
						} else {
							echo $tel_nom.": 7-THE SAME SPENDED THAT IN LAST MONTH (".$spended1[$tel_nom][0]["spended"].")\n";
						}
					}
				} else if ($day>6 && $simple_bee==true) {
					if ($day==7) {
						$avg_spended=$spended1[$tel_nom][0]["spended"]-$spended2[$tel_nom][0]["spended"];
					} else if ($day==8) {
						$avg_spended=(($spended1[$tel_nom][0]["spended"]-$spended2[$tel_nom][0]["spended"])+($spended2[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"]))/2;
					} else if ($day==9) {
						$avg_spended=(($spended1[$tel_nom][0]["spended"]-$spended2[$tel_nom][0]["spended"])+($spended2[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"])+($spended3[$tel_nom][0]["spended"]-$spended4[$tel_nom][0]["spended"]))/3;
					} else {
						$avg_spended=(($spended1[$tel_nom][0]["spended"]-$spended2[$tel_nom][0]["spended"])+($spended2[$tel_nom][0]["spended"]-$spended3[$tel_nom][0]["spended"])+($spended3[$tel_nom][0]["spended"]-$spended4[$tel_nom][0]["spended"])+($spended4[$tel_nom][0]["spended"]-$spended5[$tel_nom][0]["spended"]))/4;
					}

					if ($today_spended>45 && $avg_spended>45) {
						$s_last_month1->bindValue(":tel_nom", $tel_nom);
						$s_last_month1->execute();

						$s_last_month2->bindValue(":tel_nom", $tel_nom);
						$s_last_month2->execute();

						if ($s_last_month1->rowCount()>0 && $s_last_month2->rowCount()>0) {
							$spendeds_last_month1=$s_last_month1->fetch(PDO::FETCH_COLUMN);//За сегодня прошлого месяца
							$spendeds_last_month2=$s_last_month2->fetch(PDO::FETCH_COLUMN);//За вчера прошлого месяца

							$spendeds_last_month=$spendeds_last_month1-$spendeds_last_month2;
						} else {
							$spendeds_last_month=null;
						}

						if (round($today_spended,2)!=round($spendeds_last_month,2)) {//Если сегодняшнее не совпадает с прошлым месяцем
							echo $tel_nom.": 8-CONTRACT: ".$spended1[$tel_nom][0]["contract"].", TODAY SPENDED (".$today_spended."), LAST MONTH (".$spendeds_last_month.")\n";

							save_attention($tel_nom, "big_spended", "Расход: ".$today_spended, 0, $oper);
						} else {
							echo $tel_nom.": THE SAME SPENDED THAT IN LAST MONTH (".$today_spended.")\n";
						}
					}
				}
			}
		}

		//Определение неподписанных типов вызовов

		echo $oper.". Определение неподписанных типов вызовов\n";

		$s=$db->prepare("SELECT CONCAT(call_type,'-',service) as txt, type, type_nom, items_count, items_summa, unit_price FROM call_types WHERE operator=:operator OR operator=:operator");
		$s->bindValue(":operator", $oper);
		$s->execute();
		$call_types=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		if ($oper1=="mts") {
			$cols="service_type, service";
		} else {
			$cols="call_type, service";
		}

		if ($oper=="bee") {
			$s=$db->prepare("SELECT ".$cols.", tel_nom, price, call_length, value, call_date, call_time FROM ".$oper."_detal WHERE call_date>NOW()-INTERVAL 14 DAY AND contract!=2");
		} else {
			$s=$db->prepare("SELECT ".$cols.", tel_nom, price, call_length, value, call_date, call_time FROM ".$oper."_detal WHERE call_date>NOW()-INTERVAL 14 DAY");
		}

		//$s->bindValue(":first_day", date('Y-m-01'));
		$s->execute();

		$calls=$s->fetchAll(PDO::FETCH_ASSOC);

		$no_subscrible_type=Array();

		foreach ($calls as $call) {
			if ($oper=="mts") {
				$ct=$call['service_type']."-".$call['service'];
				if (isset($call_types[$ct])==false) {
					if (in_array($ct, $no_subscrible_type)==false) {
						$no_subscrible_type[]=$ct;
						echo $call['tel_nom'].": ".$ct." (".$call["call_date"].")\n";

						save_attention($call['tel_nom'], "new_call_type_".$ct, "Не подписанный тип вызова (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
					}
				}
			} else if ($oper=="tele2") {
				if (strpos($call['call_type'], "Переадр")==false) {
					$ct=$call['call_type']."-".$call['service'];
					if (isset($call_types[$ct])==false) {
						if (in_array($ct, $no_subscrible_type)==false) {
							$no_subscrible_type[]=$ct;
							echo $call['tel_nom'].": ".$ct." (".$call["call_date"].")\n";

							save_attention($call['tel_nom'], "new_call_type_".$ct, "Не подписанный тип вызова (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
						}
					}
				}
			} else {
				$ct=$call['call_type']."-".$call['service'];
				if (isset($call_types[$ct])==false) {
					if (in_array($ct, $no_subscrible_type)==false) {
						$no_subscrible_type[]=$ct;
						echo $call['tel_nom'].": ".$ct." (".$call["call_date"].")\n";

						save_attention($call['tel_nom'], "new_call_type_".$ct, "Не подписанный тип вызова (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
					}
				}
			}
		}

		if ($oper=="mts") {
			//Интернет стал платным для Бизнес Коннекта

			echo $oper.". Определение Интернет стал платным для Бизнес Коннекта\n";

			$s=$db->prepare("SELECT tel_nom, client FROM ".$oper."_counters_actual WHERE LOCATE('Бизнес Коннект', tarif) GROUP BY tel_nom");
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			$s=$db->prepare("SELECT price FROM ".$oper."_detal WHERE tel_nom=:tel_nom AND CONCAT(call_date,' ',call_time)>NOW()-INTERVAL 3 HOUR AND LOCATE('Мобильный интернет',service) ORDER BY `call_date` DESC, `call_time` DESC LIMIT 2");
			
			$s_is_service=$db->prepare("SELECT id FROM mts_services WHERE (service_id=1731 OR service_id=1681) AND status='Активная' AND tel_nom=:tel_nom");

			foreach ($tel_noms as $tn => $client) {
				$s->bindValue(":tel_nom", $tn);
				$s->execute();

				if ($s->rowCount()==0) {
					continue;
				} 

				$prices=$s->fetchAll(PDO::FETCH_COLUMN);

				if ($prices[0]>0 && $prices[1]>0) {
					$s_is_service->bindValue(":tel_nom", $tn);
					$s_is_service->execute();

					if ($s_is_service->rowCount()==2) {
						save_attention($tn, "inet_payable", "Интернет стал платным", 0, $oper);
						//mts_service_off($tn);
					}
				}
			}

			//Интернет стал платным для Других

			echo $oper.". Определение Интернет стал платным для Других\n";

			$s=$db->prepare("SELECT tel_nom, client FROM ".$oper."_counters_actual WHERE LOCATE('Бизнес Коннект', tarif)=0 GROUP BY tel_nom");
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			$s=$db->prepare("SELECT price FROM ".$oper."_detal WHERE tel_nom=:tel_nom AND CONCAT(call_date,' ',call_time)>NOW()-INTERVAL 3 HOUR AND LOCATE('Мобильный интернет',service) ORDER BY `call_date` DESC, `call_time` DESC LIMIT 3");

			$s_is_service=$db->prepare("SELECT id FROM mts_services WHERE service_id=1681 AND status='Активная' AND tel_nom=:tel_nom");

			foreach ($tel_noms as $tn => $client) {
				$s->bindValue(":tel_nom", $tn);
				$s->execute();

				if ($s->rowCount()==0) {
					continue;
				} 

				$prices=$s->fetchAll(PDO::FETCH_COLUMN);

				if (isset($prices[1])==false) {
					$prices[1]=0;
				}

				if (isset($prices[2])==false) {
					$prices[2]=0;
				}

				if ($prices[0]>10 || $prices[1]>10 || $prices[2]>10) {
					$s_is_service->bindValue(":tel_nom", $tn);
					$s_is_service->execute();

					if ($s_is_service->rowCount()>0) {
						save_attention($tn, "inet_payable", "Интернет стал платным", 0, $oper);
						mts_service_off($tn);
					}
				}
			}
		}

		$wrong_dinamic=Array();

		//Определение не полученной динамики МТС

		echo $oper.". Определение не полученной динамики МТС\n";

		$s=$db->prepare("select tel_nom from 
		         (select mts_counters_history.tel_nom, mts_counters_history.tarif, sum(mts_counters_history.start) as test_value 
		             from mts_counters_history WHERE mts_counters_history.update_date=DATE(NOW()-INTERVAL 1 DAY)
		           group by mts_counters_history.tel_nom ) as t2 
					where t2.test_value >0 AND t2.tarif!='Удалён'");
		$s->execute();

		if ($s->rowCount()>0) {
			$res=$s->fetchAll(PDO::FETCH_COLUMN);
				
			$tel_noms=implode(",",$res);

			$s=$db->prepare("select tel_nom, tarif from 
			         (select mts_counters_history.tel_nom, mts_counters_history.tarif, sum(mts_counters_history.start) as test_value 
			             from mts_counters_history WHERE mts_counters_history.update_date=DATE(NOW())
			           group by mts_counters_history.tel_nom ) as t2 
						where t2.test_value =0 AND t2.tarif!='Удалён' AND t2.tel_nom IN (".$tel_noms.")");
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

			if (count($tel_noms)>0) {
				$s_tarif=$db->prepare("SELECT tarif FROM mts_counters_history WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 1 DAY) LIMIT 1");

				foreach ($tel_noms as $tn) {
					$s_tarif->bindValue(":tel_nom", $tn["tel_nom"]);
					$s_tarif->execute();
					$yesterday_tarif=$s_tarif->fetch(PDO::FETCH_COLUMN);

					if ($yesterday_tarif==$tn["tarif"]) {
						$s=$db->prepare("UPDATE mts_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();

						$s=$db->prepare("SELECT id FROM attentions WHERE tel_nom=:tel_nom AND type='no_dinamic_data0' AND date_time+INTERVAL 1 HOUR>NOW()");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();

						if ($s->rowCount()==0) {
							echo $tn["tel_nom"].": не удалось получить динамику\n";
							$txt="Не удалось получить динамику";
							save_attention($tn["tel_nom"], "no_dinamic_data0", $txt, 1, 'mts');
						}

						$wrong_dinamic[]=$tn["tel_nom"];
					}
				}
			}
		}

		//Определение не полученной динамики Билайн

		echo $oper.". Определение не полученной динамики Билайн\n";

		$s=$db->prepare("select tel_nom from 
		         (select bee_counters_history.tel_nom, bee_counters_history.tarif, sum(bee_counters_history.start) as test_value 
		             from bee_counters_history WHERE bee_counters_history.update_date=DATE(NOW()-INTERVAL 1 DAY)
		           group by bee_counters_history.tel_nom ) as t2 
					where t2.test_value >0 AND t2.tarif!='Удалён'");
		$s->execute();

		if ($s->rowCount()>0) {
			$res=$s->fetchAll(PDO::FETCH_COLUMN);
				
			$tel_noms=implode(",",$res);

			$s=$db->prepare("select tel_nom, tarif from 
			         (select bee_counters_history.tel_nom, bee_counters_history.tarif, sum(bee_counters_history.start) as test_value 
			             from bee_counters_history WHERE bee_counters_history.update_date=DATE(NOW())
			           group by bee_counters_history.tel_nom ) as t2 
						where t2.test_value =0 AND t2.tarif!='Удалён' AND t2.tel_nom IN (".$tel_noms.")");
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

			if (count($tel_noms)>0) {
				$s_tarif=$db->prepare("SELECT tarif FROM bee_counters_history WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 1 DAY) LIMIT 1");

				foreach ($tel_noms as $tn) {
					$s_tarif->bindValue(":tel_nom", $tn["tel_nom"]);
					$s_tarif->execute();
					$yesterday_tarif=$s_tarif->fetch(PDO::FETCH_COLUMN);

					if ($yesterday_tarif==$tn["tarif"]) {
						$s=$db->prepare("UPDATE ".$oper."_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();

						if ($s->rowCount()==0) {
							echo $tn["tel_nom"].": не удалось получить динамику\n";
							$txt="Не удалось получить динамику";
							save_attention($tn["tel_nom"], "no_dinamic_data0", $txt, 1, 'bee');
						} else {
							echo $tn["tel_nom"].": не удалось получить динамику повторно\n";
							$txt="Не удалось получить динамику повторно";
							save_attention($tn["tel_nom"], "no_dinamic_data1", $txt, 0, 'bee');
						}

						$wrong_dinamic[]=$tn["tel_nom"];
					}
				}
			}
		}

		//Определение не полученной динамики Мегафон

		echo $oper.". Определение не полученной динамики Мегафон\n";

		$s=$db->prepare("select tel_nom from 
		         (select meg_counters_history.tel_nom, meg_counters_history.tarif, sum(meg_counters_history.start) as test_value 
		             from meg_counters_history WHERE meg_counters_history.update_date=DATE(NOW()-INTERVAL 1 DAY)
		           group by meg_counters_history.tel_nom ) as t2 
					where t2.test_value >0 AND t2.tarif!='Удалён'");
		$s->execute();

		if ($s->rowCount()>0) {
			$res=$s->fetchAll(PDO::FETCH_COLUMN);
				
			$tel_noms=implode(",",$res);

			$s=$db->prepare("select tel_nom, tarif from 
			         (select meg_counters_history.tel_nom, meg_counters_history.tarif, sum(meg_counters_history.start) as test_value 
			             from meg_counters_history WHERE meg_counters_history.update_date=DATE(NOW())
			           group by meg_counters_history.tel_nom ) as t2 
						where t2.test_value =0 AND t2.tarif!='Удалён' AND t2.tel_nom IN (".$tel_noms.")");
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

			if (count($tel_noms)>0) {
				$s_tarif=$db->prepare("SELECT tarif FROM meg_counters_history WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 1 DAY) LIMIT 1");

				foreach ($tel_noms as $tn) {
					$s_tarif->bindValue(":tel_nom", $tn["tel_nom"]);
					$s_tarif->execute();
					$yesterday_tarif=$s_tarif->fetch(PDO::FETCH_COLUMN);

					if ($yesterday_tarif==$tn["tarif"]) {
						$s=$db->prepare("UPDATE meg_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();

						$s=$db->prepare("SELECT id FROM attentions WHERE tel_nom=:tel_nom AND type='no_dinamic_data0' AND date_time+INTERVAL 1 HOUR>NOW()");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();

						if ($s->rowCount()==0) {
							echo $tn["tel_nom"].": не удалось получить динамику\n";
							$txt="Не удалось получить динамику";
							save_attention($tn["tel_nom"], "no_dinamic_data0", $txt, 1, 'meg');
						} else {
							echo $tn["tel_nom"].": не удалось получить динамику повторно\n";
							$txt="Не удалось получить динамику повторно";
							save_attention($tn["tel_nom"], "no_dinamic_data1", $txt, 0, 'meg');
						}

						$wrong_dinamic[]=$tn["tel_nom"];
					}
				}
			}
		}

		//Определение не полученной динамики TELE2

		echo $oper.". Определение не полученной динамики Теле2\n";

		$s=$db->prepare("select tel_nom from 
		         (select tele2_counters_history.tel_nom, tele2_counters_history.tarif, sum(tele2_counters_history.start) as test_value 
		             from tele2_counters_history WHERE tele2_counters_history.update_date=DATE(NOW()-INTERVAL 1 DAY)
		           group by tele2_counters_history.tel_nom ) as t2 
					where t2.test_value >0 AND t2.tarif!='Удалён'");
		$s->execute();

		if ($s->rowCount()>0) {
			$res=$s->fetchAll(PDO::FETCH_COLUMN);
				
			$tel_noms=implode(",",$res);

			$s=$db->prepare("select tel_nom, tarif from 
			         (select tele2_counters_history.tel_nom, tele2_counters_history.tarif, sum(tele2_counters_history.start) as test_value 
			             from tele2_counters_history WHERE tele2_counters_history.update_date=DATE(NOW())
			           group by tele2_counters_history.tel_nom ) as t2 
						where t2.test_value =0 AND t2.tarif!='Удалён' AND t2.tel_nom IN (".$tel_noms.")");
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

			if (count($tel_noms)>0) {
				$s_tarif=$db->prepare("SELECT tarif FROM tele2_counters_history WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 1 DAY) LIMIT 1");

				foreach ($tel_noms as $tn) {
					$s_tarif->bindValue(":tel_nom", $tn["tel_nom"]);
					$s_tarif->execute();
					$yesterday_tarif=$s_tarif->fetch(PDO::FETCH_COLUMN);

					if ($yesterday_tarif==$tn["tarif"]) {
						$s=$db->prepare("UPDATE tele2_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();

						$s=$db->prepare("SELECT id FROM attentions WHERE tel_nom=:tel_nom AND type='no_dinamic_data0' AND date_time+INTERVAL 1 HOUR>NOW()");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();

						if ($s->rowCount()==0) {
							echo $tn["tel_nom"].": не удалось получить динамику\n";
							$txt="Не удалось получить динамику";
							save_attention($tn["tel_nom"], "no_dinamic_data0", $txt, 1, 'tele2');
						} else {
							echo $tn["tel_nom"].": не удалось получить динамику повторно\n";
							$txt="Не удалось получить динамику повторно";
							save_attention($tn["tel_nom"], "no_dinamic_data1", $txt, 0, 'tele2');
						}

						$wrong_dinamic[]=$tn["tel_nom"];
					}
				}
			}
		}

		//Определение неполученного GPRS МТС

		echo $oper.". Определение неполученного GPRS МТС\n";

		$s=$db->prepare("select tel_nom from 
		         (select mts_counters_history.tel_nom, mts_counters_history.tarif, sum(mts_counters_history.start) as test_value, unit 
		             from mts_counters_history WHERE mts_counters_history.update_date=DATE(NOW()-INTERVAL 1 DAY) AND (unit='КБ' OR unit='МБ' OR unit='ГБ')
		           group by mts_counters_history.tel_nom ) as t2 
					where t2.test_value >0 AND t2.tarif!='Удалён' AND (unit='КБ' OR unit='МБ' OR unit='ГБ')");
		$s->execute();
		$res=$s->fetchAll(PDO::FETCH_COLUMN);
		
		if ($s->rowCount()>0) {	
			$tel_noms=implode(",",$res);

			$s=$db->prepare("select tel_nom, tarif from 
			         (select mts_counters_history.tel_nom, mts_counters_history.tarif, sum(mts_counters_history.start) as test_value, unit 
			             from mts_counters_history WHERE mts_counters_history.update_date=DATE(NOW()) AND (unit='КБ' OR unit='МБ' OR unit='ГБ')
			           group by mts_counters_history.tel_nom ) as t2 
						where t2.test_value =0 AND t2.tarif!='Удалён' AND t2.tel_nom IN (".$tel_noms.") AND (unit='КБ' OR unit='МБ' OR unit='ГБ')");
			$s->execute();
			$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

			if (count($tel_noms)>0) {
				$s_tarif=$db->prepare("SELECT tarif FROM mts_counters_history WHERE tel_nom=:tel_nom AND update_date=DATE(NOW()-INTERVAL 1 DAY) LIMIT 1");

				foreach ($tel_noms as $tn) {
					$s_tarif->bindValue(":tel_nom", $tn["tel_nom"]);
					$s_tarif->execute();
					$yesterday_tarif=$s_tarif->fetch(PDO::FETCH_COLUMN);

					if ($yesterday_tarif==$tn["tarif"]) {
						$s=$db->prepare("UPDATE mts_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
						$s->bindValue(":tel_nom", $tn["tel_nom"]);
						$s->execute();
						
						$txt="Не удалось получить GPRS";
						save_attention($tn["tel_nom"], "no_dinamic_data2", $txt, 0, 'mts');

						$wrong_dinamic[]=$tn["tel_nom"];
					}
				}
			}
		}


		if ($oper1=="bee") {
			$oper2="beeline";
		} else {
			$oper2=$oper1;
		}

		$s_dead_accounts=$db->prepare("SELECT account FROM accounts_groups WHERE (LOCATE('Золото', group_name) OR LOCATE('Платина', group_name)) AND operator=:operator");
		$s_dead_accounts->bindValue(":operator", $oper2);
		$s_dead_accounts->execute();

		if ($s_dead_accounts->rowCount()>0) {
			$dead_accounts=$s_dead_accounts->fetchAll(PDO::FETCH_COLUMN);

			$dead_accounts[]=533309688;

			$dead_accounts=implode(",",$dead_accounts);
		} else {
			$dead_accounts=0;
		}

		$s_active_phones=$db->prepare("SELECT PhoneNumber FROM ".$oper1."_phones WHERE status=0 AND AccountNumber NOT IN (".$dead_accounts.")");
		$s_active_phones->execute();
		$active_phones=$s_active_phones->fetchAll(PDO::FETCH_COLUMN);
		$active_phones=implode(",",$active_phones);

		//Определение неактивных

		echo $oper.". Определение неактивных\n";

		$exceptions=Array(79020547279, 79037030428, 9037030428, 9020547279);

		if ($oper=="mts") {
			$exception_col="service";
		} else {
			$exception_col="call_type";
		}

		$s=$db->prepare("SELECT tel_nom, call_date, to_number, ".$exception_col." as call_type FROM ".$oper."_detal WHERE (from_number!='' OR to_number!='') AND call_date>NOW()-INTERVAL 11 DAY AND tel_nom IN (".$active_phones.") GROUP BY tel_nom, call_date, ".$exception_col.", to_number ORDER BY call_date DESC");
		$s->execute();
		$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		foreach ($tel_noms as $tel_nom => $calls) {
			if ($calls[0]["call_date"]!=date("Y-m-d", time()-24*10*60*60)) {
				continue;
			}

			$s_no_attentions->bindValue(":tel_nom", $tel_nom);
			$s_no_attentions->bindValue(":type", 'no_active');
			$s_no_attentions->execute();

			if ($s_no_attentions->rowCount()==0) {
				$s_no_attentions->bindValue(":tel_nom", $tel_nom);
				$s_no_attentions->bindValue(":type", 'no_active_ls');
				$s_no_attentions->execute();

				if ($s_no_attentions->rowCount()==0) {
					$txt="Неактивен с ".$calls[0]["call_date"];

					foreach ($calls as $call) {
						if (strpos($call["call_type"],'сервисный')===false && strpos($call["call_type"],'спам')===false && strpos($call["call_type"],'детализ')===false && strpos($call["call_type"],'на e-mail')===false && strpos($call["call_type"],'ереадр')===false && in_array($call["to_number"],$exceptions)===false && substr($call["to_number"],1,3)!="800") {
							save_attention($tel_nom, "no_active", $txt, 0, $oper);
						} else {
							save_attention($tel_nom, "no_active", $txt, 1, $oper);
						}
					}
				}
			}
		}

		$s=$db->prepare("SELECT tel_nom, MAX(call_date) as max_call_date FROM ".$oper."_detal WHERE service!='Пополнение баланса' AND (from_number!='' OR to_number!='') GROUP BY tel_nom");
		$s->execute();
		$tel_noms=$s->fetchAll(PDO::FETCH_ASSOC);

		$txt="";
		foreach ($tel_noms as $tn) {
			if ($tn["max_call_date"]==date("Y-m-d", time()-61*24*60*60)) {
				$txt="На снятие (вызов: ".$tn["max_call_date"].")";
				save_attention($tn["tel_nom"], "for_delete", $txt, 0, $oper);
			}
		}

		//Подключение мобильного Интернета, если подключен Бизнес коннект 3350 (день первого подключения должен быть равен сегодняшнему) и Мобильный Интернет отключен
		
		echo $oper.". Подключение мобильного Интернета\n";

		if ($oper=="mts") {
			$s=$db->prepare("SELECT tel_nom FROM mts_services WHERE DAY(append_time)=DAY(NOW()) AND service_id=1731 AND status='Активная'");
			$s->execute();

			$tel_noms=$s->fetchAll(PDO::FETCH_COLUMN);

			$s=$db->prepare("SELECT id FROM mts_services WHERE tel_nom=:tel_nom AND service_id=1681 AND status='Не активная'");

			foreach ($tel_noms as $tn) {
				$s->bindValue(":tel_nom", $tn);
				$s->execute();

				if ($s->rowCount()>0) {
					mts_service_on($tn, "PE0005");	
				}
			}
		}

		//Определение sms_Info

		if ($oper=="mts") {
			echo $oper.". Определение sms_Info\n";

			$s=$db->prepare("SELECT `tel_nom`, COUNT(id) as c, call_date, call_time FROM ".$oper."_detal WHERE (from_number='sms_Info' OR from_number='sms_MTS') AND `call_date` = DATE(NOW()) ORDER BY tel_nom ASC, call_date DESC, call_time DESC");
			$s->execute();

			if ($s->rowCount()>0) {
				$calls=$s->fetchAll(PDO::FETCH_ASSOC);

				foreach ($calls as $call) {
					if ($call["c"]>1) {
						save_attention($call["tel_nom"], "sms_Info", "SMS-INFO (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
					}
				}
			}
		}

		//Определение типов у которых стоит галочка Вниманий в справочнике
		echo $oper.". Определение типов у которых стоит галочка Вниманий в справочнике\n";

		$s=$db->prepare("SELECT CONCAT(CHAR(39),call_type,'-',service,CHAR(39)) as txt FROM call_types WHERE (operator=:operator OR operator=CONCAT(:operator,'+')) AND attention=1");
		$s->bindValue(":operator", $oper);
		$s->execute();

		if ($s->rowCount()==0) {
			continue;
		}

		$call_types=$s->fetchAll(PDO::FETCH_COLUMN);

		$call_types=implode(",",$call_types);

		$filter="1=1";
		if ($oper=="mts") {
			$filter.=" AND CONCAT(`service_type`,'-',`service`) IN (".$call_types.")";
			$field="service_type";
		} else {
			$filter.=" AND CONCAT(`call_type`,'-',`service`) IN (".$call_types.")";
			$field="call_type";
		}

		$s=$db->prepare("SELECT `tel_nom`, `service`, call_date, call_time, ".$field." FROM ".$oper."_detal WHERE `call_date` BETWEEN DATE(NOW()-INTERVAL 23 HOUR) AND DATE(NOW()) AND ".$filter);
		$s->execute();

		if ($s->rowCount()>0) {
			$tel_noms = $s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

			$s_no_attention_by_number=$db->prepare("SELECT id FROM no_attentions WHERE tel_nom=:tel_nom AND type=:type");

			foreach ($tel_noms as $tel_nom => $calls) {
				foreach ($calls as $call) {
					$s_no_attention_by_number->bindValue(":tel_nom", $tel_nom);

					if ($oper=="mts") {
						$service=$call["service"];
						$service_type=$call["service_type"];

						$type=$service."-".$service_type;

						$s_no_attention_by_number->bindValue(":type", $type);

						$s_no_attention_by_number->execute();

						if ($s_no_attention_by_number->rowCount()==0) {
							save_attention($tel_nom, $type, $service." (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
						}
					} else if ($oper=="bee") {
						$service=$call["service"];
						$call_type=$call["call_type"];

						$type=$service."-".$call_type;

						$s_no_attention_by_number->bindValue(":type", $type);

						$s_no_attention_by_number->execute();

						if ($s_no_attention_by_number->rowCount()==0) {
							save_attention($tel_nom, $type, $service." (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
						}						
					} else if ($oper=="meg") {
						$service=$call["service"];
						$call_type=$call["call_type"];

						$type=$call_type."-".$service;

						$s_no_attention_by_number->bindValue(":type", $type);

						$s_no_attention_by_number->execute();

						if ($s_no_attention_by_number->rowCount()==0) {
							save_attention($tel_nom, $type, $service." (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
						}						
					} else {
						$service=$call["service"];
						$call_type=$call["call_type"];

						$type=$service."-".$call_type;

						$s_no_attention_by_number->bindValue(":type", $type);

						$s_no_attention_by_number->execute();

						if ($s_no_attention_by_number->rowCount()==0) {
							save_attention($tel_nom, $type, $call_type." (".$call["call_date"]." ".$call["call_time"].")", 0, $oper);
						}						
					}
				}
			}
		}

		//Расход Минут

		echo $oper.". Определение Большого расхода минут\n";

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit, contract FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()) AND to_dinamic=1 AND unit='МИН' GROUP BY tel_nom, unit");
		$s->execute();
		$rests1=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 24 HOUR) AND to_dinamic=1 AND unit='МИН' GROUP BY tel_nom, unit");
		$s->execute();
		$rests2=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 48 HOUR) AND to_dinamic=1 AND unit='МИН' GROUP BY tel_nom, unit");
		$s->execute();
		$rests3=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 72 HOUR) AND to_dinamic=1 AND unit='МИН' GROUP BY tel_nom, unit");
		$s->execute();
		$rests4=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 96 HOUR) AND to_dinamic=1 AND unit='МИН' GROUP BY tel_nom, unit");
		$s->execute();
		$rests5=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		foreach ($rests1 as $tel_nom => $rests) {
			if (isset($rests1[$tel_nom][0]["remain"])==false || isset($rests2[$tel_nom][0]["remain"])==false || isset($rests3[$tel_nom][0]["remain"])==false || isset($rests4[$tel_nom][0]["remain"])==false || isset($rests5[$tel_nom][0]["remain"])==false) {
				continue;
			}

			$avg_rests=(($rests3[$tel_nom][0]["remain"]-$rests2[$tel_nom][0]["remain"])+($rests4[$tel_nom][0]["remain"]-$rests3[$tel_nom][0]["remain"])+($rests5[$tel_nom][0]["remain"]-$rests4[$tel_nom][0]["remain"]))/3;

			$today_rests=intval($rests2[$tel_nom][0]["remain"]-$rests1[$tel_nom][0]["remain"]);

			$day=date("d", time());

			if ($avg_rests<0) {
				continue;
			}

			if ($today_rests>$avg_rests+50) {
				$s_no_attentions->bindValue(":tel_nom", $tel_nom);
				$s_no_attentions->bindValue(":type", "min");
				$s_no_attentions->execute();

				if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
					$txt="Большой расход минут: ".$today_rests;
					save_attention($tel_nom, "big_mins", $txt, 1, $oper);
				}
			}
		}

		//Расход SMS

		echo $oper.". Определение Большого расхода СМС\n";

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit, contract FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()) AND to_dinamic=1 AND unit='SMS' GROUP BY tel_nom, unit");
		$s->execute();
		$rests1=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 24 HOUR) AND to_dinamic=1 AND unit='SMS' GROUP BY tel_nom, unit");
		$s->execute();
		$rests2=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 48 HOUR) AND to_dinamic=1 AND unit='SMS' GROUP BY tel_nom, unit");
		$s->execute();
		$rests3=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 72 HOUR) AND to_dinamic=1 AND unit='SMS' GROUP BY tel_nom, unit");
		$s->execute();
		$rests4=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 96 HOUR) AND to_dinamic=1 AND unit='SMS' GROUP BY tel_nom, unit");
		$s->execute();
		$rests5=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		foreach ($rests1 as $tel_nom => $rests) {
			if (isset($rests1[$tel_nom][0]["remain"])==false || isset($rests2[$tel_nom][0]["remain"])==false || isset($rests3[$tel_nom][0]["remain"])==false || isset($rests4[$tel_nom][0]["remain"])==false || isset($rests5[$tel_nom][0]["remain"])==false) {
				continue;
			}

			$avg_rests=(($rests3[$tel_nom][0]["remain"]-$rests2[$tel_nom][0]["remain"])+($rests4[$tel_nom][0]["remain"]-$rests3[$tel_nom][0]["remain"])+($rests5[$tel_nom][0]["remain"]-$rests4[$tel_nom][0]["remain"]))/3;

			$today_rests=intval($rests2[$tel_nom][0]["remain"]-$rests1[$tel_nom][0]["remain"]);

			$day=date("d", time());

			if ($avg_rests<0) {
				continue;
			}

			if ($today_rests>$avg_rests+50) {
				$s_no_attentions->bindValue(":tel_nom", $tel_nom);
				$s_no_attentions->bindValue(":type", "sms");
				$s_no_attentions->execute();

				if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
					echo $tel_nom.": ".$today_rests."/".$avg_rests."\n";
					$txt="Большой расход SMS: ".$today_rests;
					save_attention($tel_nom, "big_sms", $txt, 1, $oper);
				}
			}
		}



		//Расход GPRS

		echo $oper.". Определение Большого расхода Интернета\n";

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit, contract FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()) AND to_dinamic=1 AND (unit='КБ' OR unit='КБ' OR unit='ГБ') GROUP BY tel_nom, unit");
		$s->execute();
		$rests1=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 24 HOUR) AND to_dinamic=1 AND (unit='КБ' OR unit='КБ' OR unit='ГБ') GROUP BY tel_nom, unit");
		$s->execute();
		$rests2=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 48 HOUR) AND to_dinamic=1 AND (unit='КБ' OR unit='КБ' OR unit='ГБ') GROUP BY tel_nom, unit");
		$s->execute();
		$rests3=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 72 HOUR) AND to_dinamic=1 AND (unit='КБ' OR unit='КБ' OR unit='ГБ') GROUP BY tel_nom, unit");
		$s->execute();
		$rests4=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		$s=$db->prepare("SELECT tel_nom, SUM(remain) as remain, unit FROM ".$oper."_counters_history WHERE `update_date`=DATE(NOW()-INTERVAL 96 HOUR) AND to_dinamic=1 AND (unit='КБ' OR unit='КБ' OR unit='ГБ') GROUP BY tel_nom, unit");
		$s->execute();
		$rests5=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

		foreach ($rests1 as $tel_nom => $rests) {
			if (isset($rests1[$tel_nom][0]["remain"])==false || isset($rests2[$tel_nom][0]["remain"])==false || isset($rests3[$tel_nom][0]["remain"])==false || isset($rests4[$tel_nom][0]["remain"])==false || isset($rests5[$tel_nom][0]["remain"])==false) {
				continue;
			}

			$avg_rests=(($rests3[$tel_nom][0]["remain"]-$rests2[$tel_nom][0]["remain"])+($rests4[$tel_nom][0]["remain"]-$rests3[$tel_nom][0]["remain"])+($rests5[$tel_nom][0]["remain"]-$rests4[$tel_nom][0]["remain"]))/3;

			$today_rests=$rests2[$tel_nom][0]["remain"]-$rests1[$tel_nom][0]["remain"];

			$day=date("d", time());

			if ($avg_rests<0) {
				continue;
			}

			if ($today_rests>$avg_rests+1024*1024) {
				$s_no_attentions->bindValue(":tel_nom", $tel_nom);
				$s_no_attentions->bindValue(":type", "inet");
				$s_no_attentions->execute();

				if ($s_no_attentions->rowCount()==0 && in_array($r["tel_nom"], $wrong_dinamic)==false) {
					$txt="Большой расход GPRS: ".intval($today_rests/1024);
					save_attention($tel_nom, "big_i", $txt, 1, $oper, "");
				}
			}
		}
	}




	//Проверка заявок Билайн

	echo $oper.". Проверка заявок Билайн\n";

	$operator="beeline";
	require($dir."/php/get_logins.php");

	$token=Array();

	$s=$db->prepare("SELECT tel_nom, request_id, type FROM requests WHERE status='' AND operator='bee'");
	$s->execute();
	$requests=$s->fetchAll(PDO::FETCH_ASSOC);

	$s=$db->prepare("SELECT Contract FROM bee_phones WHERE PhoneNumber=:tel_nom");

	$req_status=$db->prepare("UPDATE requests SET status='Выполнено' WHERE request_id=:request_id");

	$attentions=$db->prepare("INSERT IGNORE INTO attentions (tel_nom, `date`, txt, operator) VALUES (:tel_nom, DATE(NOW()), :txt, 'bee')");

	foreach ($requests as $req) {
		$s->bindValue(":tel_nom", $req["tel_nom"]);
		$s->execute();
		$contract=$s->fetch(PDO::FETCH_COLUMN);

		if (isset($token[$contract])==false) {
			auth_bee($login[$contract], $password[$contract], $contract);
		}

		$result=get_request($req["request_id"], $contract, $login[$contract-1]);

		$document = phpQuery::newDocumentXML($result);

		$status=pq($document)->find("requestStatus")->text();

		if ($status=="COMPLETE") {
			echo $req["tel_nom"].": ".$status;

			$req_status->bindValue(":request_id", $req["request_id"]);
			$req_status->execute();

			$attentions->bindValue(":tel_nom", $req["tel_nom"]);
			$attentions->bindValue(":txt", $req["type"].": Выполено");
			$attentions->execute();
		}
	}

	function total_spended($spended_account, $spended_update_date, $oper) {
		global $db;

		$s_del=$db->prepare("DELETE FROM spended_totals WHERE update_date=:update_date AND account=:account");
		$s_del->bindValue(":update_date", $spended_update_date);
		$s_del->bindValue(":account", $spended_account);

		$s_del->execute();

		$s_spended=$db->prepare("SELECT SUM(spended) as spended FROM (SELECT account, update_date, spended FROM ".$oper."_counters_history WHERE `update_date` = :update_date GROUP BY tel_nom) t WHERE account=:account GROUP BY account");
		$s_spended->bindValue(":account", $spended_account);
		$s_spended->bindValue(":update_date", $spended_update_date);

		$s_spended->execute();
		$spended=$s_spended->fetch(PDO::FETCH_COLUMN);

		$sql="(".$spended_account.",'".$spended_update_date."',".$spended.", '".$oper."')";

		$s_insert=$db->prepare("INSERT INTO spended_totals (account, update_date, spended, operator) VALUES ".$sql);
		$s_insert->execute();
	}

	function auth_bee($login, $pass, $contract) {
		global $token;

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "https://my.beeline.ru/api/1.0/auth?login=".$login."&password=".$pass);
		curl_setopt($curl, CURLOPT_POST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		
		$result=curl_exec($curl);

		$info=curl_getinfo($curl);

		curl_close($curl);

		$result=json_decode($result, true);

		$token[$contract]=$result["token"];
	}

	function get_request($request_id, $contract, $login) {
		global $token;

		$data='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:uss-wsapi:Subscriber">';
		$data.='<soapenv:Header/>';
		$data.='<soapenv:Body>';
		$data.='<urn:getRequestList>';
		$data.='<token>'.$token[$contract].'</token>';
		$data.='<hash>?</hash>';
		$data.='<login>'.$login.'</login>';
		$data.='<requestId>'.$request_id.'</requestId>';
		$data.='<page>1</page>';
		$data.='</urn:getRequestList>';
		$data.='</soapenv:Body>';
		$data.='</soapenv:Envelope>';

		$headers[]='Content-Type: text/xml; charset=utf-8';
		$headers[]='Content-Length: '.strlen($data);

		$curl = curl_init();

		curl_setopt($curl, CURLOPT_URL, "https://my.beeline.ru/api/SubscriberService?WSDL");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);

		$result=curl_exec($curl);

		return $result;
	}



	//Проверка заявок МТС

	echo $oper.". Проверка заявок МТС\n";

	$token=Array();

	$s=$db->prepare("SELECT tel_nom, request_id, type FROM requests WHERE status='' AND date_time+INTERVAL 3 HOUR>NOW() AND operator='mts'");
	$s->execute();
	$requests=$s->fetchAll(PDO::FETCH_ASSOC);

	$s1=$db->prepare("SELECT client FROM mts_phones WHERE PhoneNumber=:tel_nom");

	$req_status=$db->prepare("UPDATE requests SET status=:status WHERE request_id=:request_id");

	$attentions=$db->prepare("INSERT IGNORE INTO attentions (tel_nom, `date`, txt, operator, done, user) VALUES (:tel_nom, DATE(NOW()), :txt, 'mts', :done, :user)");

	print_r($requests);

	foreach ($requests as $req) {
		$s1->bindValue(":tel_nom", $req["tel_nom"]);
		$s1->execute();
		$client=$s1->fetch(PDO::FETCH_COLUMN);

		if (isset($token[$client])==false) {
			$s=$db->prepare("SELECT name, access_token FROM logins WHERE operator='mts' AND type='Динамика'");
			$s->execute();
			$token=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
		}

		if ($req["type"]=="Замена SIM") {
			$status=get_request_mts52($req["request_id"], $client);
		} else {
			$status=get_request_mts51($req["request_id"], $client);
		}

		echo $req["type"].": ".$status."\n";

		if ($status!="Заявка ещё не обработана") {
			$req_status->bindValue(":request_id", $req["request_id"]);
			$req_status->bindValue(":status", $status);
			$req_status->execute();

			$attentions->bindValue(":tel_nom", $req["tel_nom"]);
			$attentions->bindValue(":txt", $req["type"].": ".$status);

			if (mb_strpos($req["type"],"M1",0,'UTF-8')!==false || mb_strpos($req["type"],"M2",0,'UTF-8')!==false || mb_strpos($req["type"],"M3",0,'UTF-8')!==false) {
				echo $req["tel_nom"].": ".$req["type"]."\n";
				$attentions->bindValue(":done", 1);
				$attentions->bindValue(":user", 'SCRIPT');
			} else {
				echo $req["tel_nom"].": ".$req["type"]."\n";
				$attentions->bindValue(":done", 0);
				$attentions->bindValue(":user", '');
			}
			$attentions->execute();

			if ($req["type"].": ".$status=="Смена ТП: Выполнено") {	
				$attempts=0;
				do {
					if ($attempts==3) {
						save_attention($req["tel_nom"], "service_on_error", "Не принята заявка: Служба коротких сообщений", 0, "mts");

						$txt=$req["tel_nom"].". Не принята заявка: Служба коротких сообщений";

						$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation) VALUES (NOW(), :name, :log_str)");
						$s->bindValue(":name", "SCRIPT");
						$s->bindValue(":log_str", $txt);
						$s->execute();

						break;
					}

					$attempts++;
							
					$txt=$req["tel_nom"].". Подключение Службы коротких сообщений (".$attempts.")";

					echo $txt."\n";

					save_attention($req["tel_nom"], "", $txt, 0, "mts");

					$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation) VALUES (NOW(), :name, :log_str)");
					$s->bindValue(":name", "SCRIPT");
					$s->bindValue(":log_str", $txt);
					$s->execute();

					$res=mts_service_on($req["tel_nom"], "PE0320");

					sleep(2);
				} while ($res==false);

				sleep(30);

				$log_str=$req["tel_nom"].". Отключение услуги (Видеозвонок)";
		
				echo $log_str."\n";

				$result=service_off($req["tel_nom"], "PE0092", $token[$client][0]["access_token"]);

				$document = phpQuery::newDocumentXML($result);
				$externalID=pq($document)->find("result")->attr("externalID");

				$s=$db->prepare("INSERT INTO user_log (date_time, user_name, operation) VALUES (NOW(), :name, :log_str)");
				$s->bindValue(":name", "script");
				$s->bindValue(":log_str", $log_str);
				$s->execute();

				$s=$db->prepare("INSERT INTO requests (request_id, tel_nom, type, operator) VALUES (:request_id, :tel_nom, :type, 'mts')");
				$s->bindValue(":request_id", $externalID);
				$s->bindValue(":type", $log_str);
				$s->bindValue(":tel_nom", $req["tel_nom"]);
				$s->execute();

				sleep(15);

				$s=$db->prepare("UPDATE mts_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
				$s->bindValue(":tel_nom", $req["tel_nom"]);
				$s->execute();
			}
		} else {
			$attentions->bindValue(":tel_nom", $req["tel_nom"]);
			$attentions->bindValue(":txt", $req["type"].": ".$status);
			$attentions->bindValue(":user", 'SCRIPT');
			$attentions->bindValue(":done", 1);
			$attentions->execute();
		}

		sleep(1);
	}

	function get_request_mts51($ExternalId, $client) {
		global $token;

		$req=Array(
			"ExternalId" => $ExternalId
		);

		$json=json_encode($req);

		$curl=curl_init();

		$headers[]="Accept-Encoding: deflate";
		$headers[]="Content-Type: application/json";
		$headers[]="Authorization: Bearer ".$token[$client][0]["access_token"];
		$headers[]="Content-Length: ".strlen($json);
		$headers[]="Host: login.mts.ru:443";
		$headers[]="Connection: Keep-Alive";
		$headers[]="User-Agent: Apache-HttpClient/4.1.1 (java 1.5)";

		$url="https://login.mts.ru:443/wss/api-manager/PublicApi/Sandbox/Callback/GetOperationStatus";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);

		$res=curl_exec($curl);

		$res=json_decode($res);

		if (isset($res->state) && $res->state=="success") {
			return "Выполнено";
		} else if (isset($res->state) && $res->state=="error") {
			return $res->terminationMessage;
		} else if (isset($res->ExternalId)==false || $res->ExternalId==$ExternalId) {
			return "Заявка ещё не обработана";
		}
	}

	function get_request_mts52($ExternalId, $client) {
		global $token;

		$req=Array(
			"ExternalId" => $ExternalId
		);

		$json=json_encode($req);

		$curl=curl_init();

		$headers[]="Accept-Encoding: deflate";
		$headers[]="Content-Type: application/json";
		$headers[]="Authorization: Bearer ".$token[$client][0]["access_token"];
		$headers[]="Content-Length: ".strlen($json);
		$headers[]="Host: login.mts.ru:443";
		$headers[]="Connection: Keep-Alive";
		$headers[]="User-Agent: Apache-HttpClient/4.1.1 (java 1.5)";

		$url="https://login.mts.ru:443/wss/api-manager/PublicApi/Sandbox/Callback/GetOperationStatus/raw";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);

		$res=curl_exec($curl);

		$document = phpQuery::newDocumentXML($res);

		$error=pq($document)->find("ErrorMessage")->length;
		if ($error>0) {
			$status=pq($document)->find("ErrorMessage")->text();
		} else {
			$error=pq($document)->find("Exception")->length;
			if ($error>0) {
				$status=pq($document)->find("Exception")->text();
			} else {
				$status=pq($document)->find("State")->text();
			}
		}

		if ($status=="InProgress") {
			$status="";
		}

		return $status;
	}

		function service_on($tel_nom, $code, $access_token) {
			$curl = curl_init();

			$request='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
	xmlns:sieb="http://mts.ru/siebel" xmlns:pag="http://mts.ru/siebel/pagination"
	xmlns:sec="http://schemas.xmlsoap.org/ws/2002/07/secext"
	xmlns:Product="http://www.mts.ru/schema/api/Product" 
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';

			$request.='<soapenv:Header>';
			$request.='<msisdn>'.$tel_nom.'</msisdn>';
			$request.='<sieb:Login>'.$tel_nom.'</sieb:Login>';
			$request.='<sieb:ReplyTo>http://apimsandbox02.msk.mts.ru:8282/services/Callback_Harvester</sieb:ReplyTo>';
			$request.='</soapenv:Header>';
			$request.='<soapenv:Body>';
			$request.='<sieb:CreateOrder>';
			$request.='<!--Optional:-->';
			$request.='<order fetchServicesAndBlocks="true">';
			$request.='<channel id="msisdn"/>';
			$request.='<items>';
			$request.='<item id="'.$code.'" xsi:type="Product:ProductOrderItem">';
			$request.='<productOffering>';
			$request.='<availableFor>create</availableFor>';
			$request.='</productOffering>';
			$request.='</item>';
			$request.='</items>';
			$request.='</order>';
			$request.='</sieb:CreateOrder>';
			$request.='</soapenv:Body>';
			$request.='</soapenv:Envelope>';

			$url='https://login.mts.ru:443/wss/api-manager/PublicApi/Sandbox/IProductOrderingService/v1';
			$headers[]='SOAPAction: ""';

			$curl=curl_init();

			$headers[]="Accept-Encoding: deflate";
			$headers[]="Content-Type: text/xml;charset=UTF-8";
			$headers[]="Authorization: Bearer ".$access_token;
			$headers[]="Content-Length: ".strlen($request);
			$headers[]="Host: login.mts.ru:443";
			$headers[]="Connection: Keep-Alive";
			$headers[]="User-Agent: Apache-HttpClient/4.1.1 (java 1.5)";

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_TIMEOUT, 20);

			return curl_exec($curl);
		}

		function mts_service_on($tel_nom, $code) {
			global $dir;
			global $db;
			global $token;

			$s=$db->prepare("SELECT client FROM mts_phones WHERE PhoneNumber=:tel_nom");
			$s->bindValue(":tel_nom", $tel_nom);
			$s->execute();

			$client=$s->fetch(PDO::FETCH_COLUMN);

			if (isset($token[$client])==false) {
				$s=$db->prepare("SELECT name, access_token FROM logins WHERE operator='mts' AND type='Динамика'");
				$s->execute();
				$token=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
			}

			//print_r($token);
			$access_token=$token[$client][0]["access_token"];
			
			//echo "access_token: ".$access_token." (".$client.")";

			$result=service_on($tel_nom, $code, $access_token);

			echo htmlspecialchars($result);

			if (strpos($result, "Выполнено без ошибок")!==false) {
				if ($code=="PE0320") {
					$log_str=$tel_nom.". Подключение услуги (Служба коротких сообщений)";
				} else {
					$log_str=$tel_nom.". Подключение услуги (Мобильный Интернет)";
				}
				
				$document = phpQuery::newDocumentXML($result);
				$externalID=pq($document)->find("result")->attr("externalID");
				
				$s=$db->prepare("INSERT INTO requests (request_id, tel_nom, type, operator) VALUES (:request_id, :tel_nom, :type, 'mts')");
				$s->bindValue(":request_id", $externalID);
				$s->bindValue(":type", $log_str);
				$s->bindValue(":tel_nom", $tel_nom);
				$s->execute();

				$s=$db->prepare("INSERT INTO logs (txt, script_name) VALUES (:txt, :script_name)");
				$s->bindValue(":script_name", $_SERVER['SCRIPT_NAME']."_mts");
				$s->bindValue(":txt", $log_str);
				$s->execute();

				if ($code!="PE0320") {
					$s=$db->prepare("UPDATE mts_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
					$s->bindValue(":tel_nom", $tel_nom);
					$s->execute();
				}

				return true;
			} else {
				$document = phpQuery::newDocumentXML($result);
				echo pq($document)->find("result")->attr("terminationMessage");

				return false;
			}
		}

		function service_off($tel_nom, $code, $access_token) {
			$curl = curl_init();

			$request='<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" 
	xmlns:sieb="http://mts.ru/siebel" xmlns:pag="http://mts.ru/siebel/pagination"
	xmlns:sec="http://schemas.xmlsoap.org/ws/2002/07/secext"
	xmlns:Product="http://www.mts.ru/schema/api/Product" 
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';

			$request.='<soapenv:Header>';
			$request.='<msisdn>'.$tel_nom.'</msisdn>';
			$request.='<sieb:Login>'.$tel_nom.'</sieb:Login>';
			$request.='<sieb:ReplyTo>http://apimsandbox02.msk.mts.ru:8282/services/Callback_Harvester</sieb:ReplyTo>';
			$request.='</soapenv:Header>';
			$request.='<soapenv:Body>';
			$request.='<sieb:CreateOrder>';
			$request.='<!--Optional:-->';
			$request.='<order fetchServicesAndBlocks="true">';
			$request.='<channel id="msisdn"/>';
			$request.='<items>';
			$request.='<item id="'.$code.'" xsi:type="Product:ProductOrderItem">';
			$request.='<productOffering>';
			$request.='<availableFor>delete</availableFor>';
			$request.='</productOffering>';
			$request.='</item>';
			$request.='</items>';
			$request.='</order>';
			$request.='</sieb:CreateOrder>';
			$request.='</soapenv:Body>';
			$request.='</soapenv:Envelope>';

			$url='https://login.mts.ru:443/wss/api-manager/PublicApi/Sandbox/IProductOrderingService/v1';
			$headers[]='SOAPAction: ""';

			$curl=curl_init();

			$headers[]="Accept-Encoding: deflate";
			$headers[]="Content-Type: text/xml;charset=UTF-8";
			$headers[]="Authorization: Bearer ".$access_token;
			$headers[]="Content-Length: ".strlen($request);
			$headers[]="Host: login.mts.ru:443";
			$headers[]="Connection: Keep-Alive";
			$headers[]="User-Agent: Apache-HttpClient/4.1.1 (java 1.5)";

			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			curl_setopt($curl, CURLOPT_TIMEOUT, 20);

			return curl_exec($curl);
		}

		function mts_service_off($tel_nom) {
			global $dir;
			global $db;
			global $token;

			$s=$db->prepare("SELECT client FROM mts_phones WHERE PhoneNumber=:tel_nom");
			$s->bindValue(":tel_nom", $tel_nom);
			$s->execute();

			$client=$s->fetch(PDO::FETCH_COLUMN);

			if (isset($token[$client])==false) {
				$s=$db->prepare("SELECT name, access_token FROM logins WHERE operator='mts' AND type='Динамика'");
				$s->execute();
				$token=$s->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
			}

			//print_r($token);
			$access_token=$token[$client][0]["access_token"];
			
			//echo "access_token: ".$access_token." (".$client.")";

			$result=service_off($tel_nom, "PE0005", $access_token);

			echo htmlspecialchars($result);

			if (strpos($result, "Выполнено без ошибок")!==false) {
				$log_str="MTS ".$tel_nom.". Отключение услуги (Мобильный Интернет)";
				
				$document = phpQuery::newDocumentXML($result);
				$externalID=pq($document)->find("result")->attr("externalID");
				
				$s=$db->prepare("INSERT INTO requests (request_id, tel_nom, type, operator) VALUES (:request_id, :tel_nom, :type, 'mts')");
				$s->bindValue(":request_id", $externalID);
				$s->bindValue(":type", $log_str);
				$s->bindValue(":tel_nom", $tel_nom);
				$s->execute();

				$s=$db->prepare("INSERT INTO logs (txt, script_name) VALUES (:txt, :script_name)");
				$s->bindValue(":script_name", $_SERVER['SCRIPT_NAME']."_mts");
				$s->bindValue(":txt", $log_str);
				$s->execute();

				$s=$db->prepare("UPDATE mts_counters_actual SET update_time='00:00:00' WHERE tel_nom=:tel_nom");
				$s->bindValue(":tel_nom", $tel_nom);
				$s->execute();
			} else {
				$document = phpQuery::newDocumentXML($result);
				echo pq($document)->find("result")->attr("terminationMessage");
			}
		}

		function curl_spended($host, $uri, $phone_id, $cookie) {
			global $dir;
			global $cookie_postfix;
			
			$headers[]=":authority: dv.b2blk.megafon.ru";
			$headers[]=":method: GET";
			$headers[]=":path: ".$uri.$phone_id;
			$headers[]=":scheme: https";
			$headers[]="accept: application/json, text/javascript, */*; q=0.01";
			$headers[]="accept-encoding: deflate, br";
			$headers[]="accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7";
			$headers[]="Content-Type: application/json";
			$headers[]="cache-control: max-age=0";
			$headers[]="upgrade-insecure-requests: 1";
			$headers[]="referer: https://".$host."/b2b/subscriber/info/".$phone_id;
			$headers[]="user-agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.170 Safari/537.36 OPR/53.0.2907.99";
			$headers[]="x-requested-with: XMLHttpRequest";

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, "https://".$host.$uri.$phone_id);
			curl_setopt($curl, CURLOPT_POST, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_COOKIEJAR, $dir.'/cookie/'.$cookie.'.txt');
	    	curl_setopt($curl, CURLOPT_COOKIEFILE, $dir.'/cookie/'.$cookie.'.txt');
	    	curl_setopt($curl, CURLOPT_TIMEOUT, 10);

	    	$result=curl_exec($curl);

			return $result;
		}

		function save_attention($tel_nom, $type, $txt, $done, $oper) {
			global $db;

			$s_select=$db->prepare("SELECT id FROM attentions WHERE tel_nom=:tel_nom AND type=:type AND `date`=DATE(NOW())");

			$s_insert=$db->prepare("INSERT INTO attentions (tel_nom, `date`, txt, done, type, operator, user) VALUES (:tel_nom, DATE(NOW()), :txt, :done, :type, :operator, :user)");

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
					echo strtoupper($oper).". Ошибка записи ВНИМАНИЯ: ".$txt."\n";

					print_log($db, strtoupper($oper).". Ошибка записи ВНИМАНИЯ: ".$txt);
				}

				return true;
			} else {
				echo "\n";
				return false;
			}
		}

	function print_log($db, $txt) {
		$s=$db->prepare("INSERT INTO logs (txt, script_name) VALUES (:txt, :script_name)");
		$s->bindValue(":txt", $txt);
		$s->bindValue(":script_name", $_SERVER['SCRIPT_NAME']);
		$s->execute();
	}
?>