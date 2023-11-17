	call_types="";
	call_types_prices="";

	function get_call_types(distinct) {
		$.ajax({
			url: server+"/php/dinamic/get_call_types.php",
			data:{tel_nom:tel_nom, operator:operator, distinct:distinct},
			type:"POST",
			async:false,
			success: function(data) {
				call_types=JSON.parse(data);
			}
		});
	}

	bee_plus=false;

	function get_call_types_prices(type_nom, for_calls_list) {
		if (typeof type_nom == 'undefined' || type_nom=='') {
			type_nom=0;
		}

		if (typeof for_calls_list == 'undefined' || for_calls_list=='') {
			for_calls_list=0;
		}

		if (bee_plus == true) {
			operator1="bee+";
		} else {
			operator1=operator;
		}

		$.ajax({
			url:"/php/get_call_types_prices.php",
			data:{operator:operator1, type_nom:type_nom, for_calls_list:for_calls_list},
			type:"POST",
			async:false,
			success: function(data) {
				call_types_prices=JSON.parse(data);

				if (for_calls_list==1) {
					return false;
				}

				if (type_nom==0) {
					$("#call_types_div table tbody").empty();

					txt="";
					for (i=0;i<call_types_prices.length;i++) {
						txt+="<tr id='dinamic_legend_call_type_tr_"+call_types_prices[i]["sort_id"]+"' type_nom="+call_types_prices[i]["type_nom"]+">";
						txt+="<td class='legend_sort' call_type_sort_id='"+call_types_prices[i]["sort_id"]+"'>"+call_types_prices[i]["sort"]+"</td>";
						txt+="<td style='text-align:center'>"+call_types_prices[i]["type_nom"]+"</td>";
						txt+="<td>"+call_types_prices[i]["call_type"]+"</td>";
						txt+="<td><input class='price_"+call_types_prices[i]["id"]+"' value='"+call_types_prices[i]["price"]+"' style='width:120px; text-align:center'/></td>";
						txt+="<td><button onclick='get_call_types_prices("+call_types_prices[i]["type_nom"]+")'>История</button></td>";
						txt+="<td><button call_type_id='"+call_types_prices[i]["id"]+"' operator='"+operator1+"' class='save_call_type_price'>Сохранить</button></td>";
						txt+="<td><button>Удалить</button></td></tr>";
					}

					$("#call_types_div table tbody").append(txt);

					$("#call_types_div .save_call_type_price").on("click", function(){
						call_type_id=$(this).attr("call_type_id");
						oper=$(this).attr("operator");

						save_call_type_price(1, call_type_id, oper)
					});

					bind_click_call_types_legend();
				} else {
					$("#call_type_history_form").remove();

					type_name=$("#call_types_div").find("tr[type_nom='"+type_nom+"']").find("td:eq(2)").text();

					txt="<div id='call_type_history_form' style='width:530px; height:600px; position: absolute; z-index: 1000; top: 25%; margin-left:30%; background: grey; border-radius:10px; border:2px solid black'>";
					txt+="<table style='width:95%; margin-left:5%'>";
					txt+="<tr>";
					txt+="<td style='text-align:center; font-weight:bold; color:white; border:0'>"+type_name+"</td>";
					txt+="<td style='text-align:right; width:55px; border:0'>";
					txt+="<img src='img/cancel.png' style='width:30px' title='Закрыть'>";
					txt+="</td>";
					txt+="</tr>";
					txt+="</table>";
					txt+="<table id='call_type_history'><th>Начало</th><th>Конец</th><th>Цена</th><th class='msg' colspan=2></th>";

					for (i=0;i<call_types_prices.length;i++) {
						if (call_types_prices[i]["finish_date"]=="2030-01-01") {
							finish_date="";
						} else {
							finish_date=call_types_prices[i]["finish_date"];
						}

						txt+="<tr history_id="+call_types_prices[i]["id"]+">";
						txt+="<td class='start_date'>"+call_types_prices[i]["date"]+"</td>";
						txt+="<td class='finish_date'>"+finish_date+"</td>";
						txt+="<td><input class='price_"+call_types_prices[i]["id"]+"' value='"+call_types_prices[i]["price"]+"'/></td>";
						txt+="<td><button onclick='save_call_type_price(2, "+call_types_prices[i]["id"]+")'>Сохранить</button></td>";
						txt+="<td><button onclick='remove_call_types_prices_history("+call_types_prices[i]["id"]+","+type_nom+")'>Удалить</button></td>";
						txt+="</tr>";
					}
					
					txt+="</table>";

					txt+="</div>";

					$("body").append(txt);

					$("#call_type_history_form").draggable();

					$("#call_type_history_form img").click(function(){
						$("#call_type_history_form").remove();
					});

					$("#call_type_history td.start_date, #call_type_history td.finish_date").click(function(){
						history_id=$(this).parent().attr("history_id");
						date_type=$(this).attr("class");
						date=$(this).text();

						txt="<div id='date_form' history_id="+history_id+" date_type='"+date_type+"' style='width:175px; height:245px; position: absolute; z-index: 1000; top:360px; left:32%; background: gainsboro; border-radius:10px; border:2px solid black'>";
						txt+="<table>";
						txt+="<tr><td style='text-align:right; border:0'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></td></tr>";
						txt+="<tr><td style='text-align:center; border:0'><div class='new_date' style='width:170px; margin-top:5px;'></div></td></tr>";
						if (date_type=="finish_date") {
							txt+="<tr><td style='text-align:center; border:0'><button class='btn_remove' style='margin-top:5px; font-size:10px; border-radius:4px'>Удалить</button></td></tr>";
						}
						txt+="</table>";
						txt+="</div>";

						$("body").append(txt);

						$("#date_form .btn_close").click(function(){
							$("#date_form").remove();
						});

						$("#date_form .btn_remove").click(function(){
							save_call_types_prices_history_date("");
						});

						$("#date_form .new_date").datepicker({
							dateFormat: 'yy-mm-dd', 
							currentText: 'Сейчас',
							closeText: 'Закрыть',
							timeText: 'Время',
							monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
							monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
							dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
							dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
							dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
							firstDay: 1,
							prevText: '<Пред',
							nextText: 'След>',
							showButtonPanel: true,
							onSelect: function(dateText, inst) {
								new_date=$(this).val();

								save_call_types_prices_history_date(new_date);
							}
						});

						$("#date_form .new_date").datepicker("setDate", date);
					});
				}
			}
		});
	}

	function save_call_types_prices_history_date(new_date) {
		history_id=$("#date_form").attr("history_id");
		date_type=$("#date_form").attr("date_type");

		$("#call_type_history tr[history_id="+history_id+"]").find("."+date_type).html(new_date);

		start_date=$("#call_type_history tr[history_id="+history_id+"]").find(".start_date").html();
		finish_date=$("#call_type_history tr[history_id="+history_id+"]").find(".finish_date").html();

		if (finish_date=="") {
			finish_date="2030-01-01";
		}

		$("#date_form").remove();

		$.ajax({
			url:"/php/save_call_types_prices_history_date.php",
			data:{id:history_id, start_date:start_date, finish_date:finish_date},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#call_type_history .msg").html("СОХРАНЕНО");
					get_call_types_prices(0,0);
				} else {
					$("#call_type_history .msg").html("НЕ СОХРАНЕНО");
				}

				setTimeout(function(){
					$("#call_type_history .msg").html("");
				},3000)
			}
		});
	}

	function remove_call_types_prices_history(id, type_nom) {
		if (confirm("Точно удалить???")==false) {
			return false;
		}

		$.ajax({
			url:"/php/remove_call_types_prices_history.php",
			data:{id:id},
			type:"POST",
			success: function(data) {
				get_call_types_prices(type_nom,0);
				get_call_types_prices(0,0);
			}
		});
	}

	function save_call_type_price(action, id, oper) {
		console.log(oper);
		if (action==1) {
			action="new_history_record";
			price=$("#call_types_div").find(".price_"+id).val();
		} else {
			action="old_record_correct";
			price=$("#call_type_history_form").find(".price_"+id).val();
		}

		$.ajax({
			url:"/php/save_call_type_price.php",
			data:{id:id, price:price, action:action},
			type:"POST",
			success: function(data) {
				if (action=="new_history_record") {
					tbl="#call_types_tbl";
				} else {
					tbl="#call_type_history";
				}

				if (data=="OK") {
					
					get_call_types_prices(0,0);

					$(tbl).find(".msg").html("СОХРАНЕНО");

					setTimeout(function(){
						$(tbl).find(".msg").html("");
					},3000);
				} else {
					$(tbl).find(".msg").html("НЕ СОХРАНЕНО");

					setTimeout(function(){
						$(tbl).find(".msg").html("");
					},3000);
				}
			}
		});
	}

	function get_services_settings(service_id) {
		if (typeof service_id == 'undefined') {
			service_id=0;
		}

		$("#dinamic_details").hide();

		$.ajax({
			url:"/php/get_services_settings.php",
			data:{operator:operator, service_id:service_id},
			type:"POST",
			success: function(data) {
				services=JSON.parse(data);

				if (service_id==0) {
					$("#services_settings_div table tbody").empty();

					txt="";
					for (i=0;i<services.length;i++) {
						if (services[i]["periodic"]==1) {
							periodic="checked";
						} else {
							periodic="";
						}

						txt+="<tr id='dinamic_legend_services_tr_"+services[i]["service_code"]+"' row_id='"+services[i]["sort"]+"' service_code='"+services[i]["service_code"]+"' class='service_"+services[i]["service_code"]+"'>";
						txt+="<td class='legend_sort' service_id='"+services[i]["service_code"]+"' style='width:50px; text-align:center'>"+services[i]["sort"]+"</td>";
						txt+="<td>"+services[i]["service_code"]+"</td>";
						if (services[i]["count"]>0) {
							txt+="<td class='phones_count'>"+services[i]["count"]+"</td>";
						} else {
							txt+="<td>"+services[i]["count"]+"</td>";
						}
						txt+="<td class='service_name' style='cursor:pointer'>"+services[i]["service"]+"</td>";
						txt+="<td><input type='checkbox' class='periodic' "+periodic+"></td>";
						txt+="<td><input class='price' value='"+services[i]["price"]+"' style='width:120px; text-align:center'/></td>";

						txt+="<td><button onclick='get_services_settings("+services[i]["service_code"]+")'>История</button></td>";
						txt+="<td><button onclick='set_services_settings("+services[i]["service_code"]+")'>Сохранить</button></td>";
						txt+="<td><button onclick=''>Удалить</button></td>";
						txt+="</tr>";
					}

					txt+="<tr><td></td><td></td><td></td><td><input id='new_service' style='width:220px; text-align:center'/></td><td></td><td><input id='new_price' value='0.00'/></td><td><button onclick='set_services_settings()'>Создать</button></td></tr>";

					$("#services_settings_div table tbody").append(txt);

					bind_click_service_legend();

					form_sort_services_hide();

					tbl=$("#services_settings_div table");

					$(tbl).find(".service_name").off("click");
					$(tbl).find(".service_name").on("click", function(){
						if ($("#service_rename_div").is(":visible")) {
							return false;
						}

						old_name=$(this).text();
						service_id=$(this).parent().attr("service_code");

						txt="<div id='service_rename_div' style='width:200px; height:70px; position: absolute; z-index: 1000; top: 25%; left: 6%; background: grey; border-radius:10px; border:2px solid black'>";
						txt+="<input id='new_service_name' value='"+old_name+"' style='text-align:center'/><BR>";
						txt+="<button style='margin-top:10px' onclick='save_service_name("+service_id+")'>Сохранить</button>";
						txt+="<button class='hide_btn' style='margin-top:10px; margin-left:10px'>Закрыть</button>";
						txt+="</div>";

						$("body").append(txt);

						$("#service_rename_div").draggable();

						$("#service_rename_div .hide_btn").click(function(){
							$("#service_rename_div").remove();
						});
					});

					$(tbl).find(".phones_count").bind("click", function(){
						service_id=$(this).parent().attr("service_code");
						service_name=$(this).parent().find(".service_name").text();

						get_service_numbers(service_id, service_name);
					});

					$(tbl).find(".periodic").bind("click", function(){
						service_id=$(this).parent().parent().attr("service_code");

						periodic=0;
						if ($(this).is(":checked")) {
							periodic=1;
						}

						save_service_periodic(service_id, periodic);
					});
				} else {
					$("#service_history_form").remove();

					service_name=$("#services_settings_div").find("tr[service_code='"+service_id+"']").find("td:eq(3)").text();

					txt="<div id='service_history_form' style='width:530px; height:600px; position: absolute; z-index: 1000; top: 25%; margin-left:30%; background: grey; border-radius:10px; border:2px solid black'>";
					txt+="<table style='width:97%; margin-left:3%'>";
					txt+="<tr>";
					txt+="<td style='text-align:center; font-weight:bold; color:white; border:0'>"+service_name+"</td>";
					txt+="<td style='text-align:right; width:55px; border:0'>";
					txt+="<img src='img/cancel.png' style='width:30px' title='Закрыть'>";
					txt+="</td>";
					txt+="</tr>";
					txt+="</table>";
					txt+="<table id='service_history'><th>Начало</th><th>Конец</th><th>Цена</th><th class='msg' style='width:200px' colspan=2></th>";

					for (i=0;i<services.length;i++) {
						if (services[i]["finish_date"]=="2030-01-01") {
							finish_date="";
						} else {
							finish_date=services[i]["finish_date"];
						}

						txt+="<tr history_id="+services[i]["id"]+">";
						txt+="<td class='start_date'>"+services[i]["date"]+"</td>";
						txt+="<td class='finish_date'>"+finish_date+"</td>";
						txt+="<td><input class='price' value='"+services[i]["price"]+"'/></td>";
						txt+="<td><button class='save_btn' history_id="+services[i]["id"]+">Сохранить</button></td>";
						txt+="<td><button onclick='remove_service_prices_history("+services[i]["id"]+","+service_id+")'>Удалить</button></td>";
						txt+="</tr>";
					}
					
					txt+="</table></div>";

					$("body").append(txt);

					$("#service_history .save_btn").click(function(){
						id=$(this).attr("history_id");
						tr=$("#service_history").find("tr[history_id="+id+"]");
						price=$(tr).find(".price").val();
						start_date=$(tr).find(".start_date").text();
						finish_date=$(tr).find(".finish_date").text();

						save_service_price_history(id, price, start_date, finish_date);
					});

					$("#service_history_form").draggable();

					$("#service_history_form img").click(function(){
						$("#service_history_form").remove();
					});

					$("#service_history td.start_date, #service_history td.finish_date").click(function(){
						history_id=$(this).parent().attr("history_id");
						date_type=$(this).attr("class");
						date=$(this).text();

						txt="<div id='date_form' history_id="+history_id+" date_type='"+date_type+"' style='width:175px; height:245px; position: absolute; z-index: 1000; top:360px; left:32%; background: gainsboro; border-radius:10px; border:2px solid black'>";
						txt+="<table>";
						txt+="<tr><td style='text-align:right; border:0'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></td></tr>";
						txt+="<tr><td style='text-align:center; border:0'><div class='new_date' style='width:170px; margin-top:5px;'></div></td></tr>";
						if (date_type=="finish_date") {
							txt+="<tr><td style='text-align:center; border:0'><button class='btn_remove' style='margin-top:5px; font-size:10px; border-radius:4px'>Удалить</button></td></tr>";
						}
						txt+="</table>";
						txt+="</div>";

						$("body").append(txt);

						$("#date_form .btn_close").click(function(){
							$("#date_form").remove();
						});

						$("#date_form .btn_remove").click(function(){
							save_service_prices_history_date("");
						});

						$("#date_form .new_date").datepicker({
							dateFormat: 'yy-mm-dd', 
							currentText: 'Сейчас',
							closeText: 'Закрыть',
							timeText: 'Время',
							monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
							monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
							dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
							dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
							dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
							firstDay: 1,
							prevText: '<Пред',
							nextText: 'След>',
							showButtonPanel: true,
							onSelect: function(dateText, inst) {
								new_date=$(this).val();

								save_service_prices_history_date(new_date);
							}
						});

						$("#date_form .new_date").datepicker("setDate", date);
					});
				}
			}
		});
	}

	function save_service_periodic(service_id, periodic) {
		$.ajax({
			url:"/php/save_service_periodic.php",
			data:{service_id:service_id, periodic:periodic},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#services_settings_div .msg").text("Сохранено");
				} else {
					$("#services_settings_div .msg").text("НЕ сохранено");
				}

				setTimeout(function(){
					$("#services_settings_div .msg").text("");
				},3000);
			}
		});
	}

	function get_service_numbers(service_id, service_name) {
		$(".ui-dialog-buttonset button:contains('Ok')").show();
		$(".ui-dialog-buttonset button:contains('Сохранить')").hide();
		$(".ui-dialog-buttonset button:contains('Изменить')").hide();

		have_no_service=0;//$("#have_no_service:checked").length;

		$.ajax({
			url:"/php/get_service_numbers.php",
			data:{operator:operator, service_id:service_id, have_no_service:have_no_service},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				txt="<select class='account_select'><option value='all'>Все</option></select>";
				txt+="<select class='contract_select' style='margin-left:10px'><option value='all'>Все</option></select>";
				txt+="<select class='tarif_select' style='margin-left:10px'><option value='all'>Все</option></select>";
				//txt+="<span style='margin-left:10px'>Без услуги</span><input type='checkbox' id='have_no_service'/>";

				txt+="<table style='width:100%; margin-top:5px'><thead style='font-size:12px'><th>п/п</th><th>ПО</th><th>ЛС</th><th>КТ</th><th>ТАРИФ</th></thead><tbody>";
				
				accounts="";
				contracts="";
				tarifs="";
				accounts_done=Array();
				contract_done=Array();
				tarifs_done=Array();
				i=0;

				for (tel_nom in data["tel_noms"]) {
					console.log(tel_nom);
					i++;

					account=data["tel_noms"][tel_nom][0]["account"];
					contract=data["tel_noms"][tel_nom][0]["contract"];
					tarif=data["tarifs"][tel_nom][0]["tarif"];

					txt+="<tr style='font-size:13px' account='"+account+"' contract='"+contract+"' tarif='"+tarif+"'>";
					txt+="<td>"+i+"</td>";
					txt+="<td class='tel_nom'>"+tel_nom+"</td>";
					txt+="<td>"+account+"</td>";
					txt+="<td>"+contract+"</td>";
					txt+="<td>"+tarif+"</td>";
					txt+="<tr>";

					if (accounts_done.indexOf(account)==-1) {
						accounts_done.push(account);
						accounts+="<option value='"+account+"'>"+account+"</option>";
					}
					if (contract_done.indexOf(contract)==-1) {
						contract_done.push(contract);
						contracts+="<option value='"+contract+"'>"+contract+"</option>";
					}
					if (tarifs_done.indexOf(tarif)==-1) {
						tarifs_done.push(tarif);
						tarifs+="<option value='"+tarif+"' title='"+tarif+"'>"+short_text(tarif,30)+"</option>";
					}
				}

				txt+="</tbody></table>";

				$("#alert_dialog").html(txt);
				$("#alert_dialog .account_select").append(accounts);
				$("#alert_dialog .contract_select").append(contracts);
				$("#alert_dialog .tarif_select").append(tarifs);

				$("#alert_dialog").dialog("option","title",service_name);
				$("#alert_dialog").dialog("option","width",700);
				$("#alert_dialog").dialog("option","height",500);

				$("#alert_dialog").dialog("open");

				$("#alert_dialog select").bind("change", function(){
					account=$("#alert_dialog .account_select").find("option:selected").val();
					contract=$("#alert_dialog .contract_select").find("option:selected").val();
					tarif=$("#alert_dialog .tarif_select").find("option:selected").val();
					i=0;

					$("#alert_dialog table").find("tr").each(function(){
						tr_account=$(this).attr("account");
						tr_contract=$(this).attr("contract");
						tr_tarif=$(this).attr("tarif");

						if ((tr_account==account || account=="all") && (tr_contract==contract || contract=="all") && (tr_tarif==tarif || tarif=="all")) {
							i++;
							$(this).find("td:eq(0)").html(i);
							$(this).show();
						} else {
							$(this).hide();
						}
					});
				});

				$("#alert_dialog .tel_nom").bind("click", function(){
					tel_nom=$(this).html();
					window.open("client_details.php?operator="+operator+"&tel_nom="+tel_nom, "_blank");
				});
			}
		});
	}

	function save_service_prices_history_date(new_date) {
		history_id=$("#date_form").attr("history_id");
		date_type=$("#date_form").attr("date_type");

		$("#service_history tr[history_id="+history_id+"]").find("."+date_type).html(new_date);

		start_date=$("#service_history tr[history_id="+history_id+"]").find(".start_date").html();
		finish_date=$("#service_history tr[history_id="+history_id+"]").find(".finish_date").html();

		if (finish_date=="") {
			finish_date="2030-01-01";
		}

		$("#date_form").remove();

		$.ajax({
			url:"/php/save_service_prices_history_date.php",
			data:{id:history_id, start_date:start_date, finish_date:finish_date},
			type:"POST",
			success: function(data) {
				get_services_settings();
			}
		});
	}

	function remove_service_prices_history(id, service_id) {
		$.ajax({
			url:"/php/remove_service_prices_history.php",
			data:{id:id},
			type:"POST",
			success: function(data) {
				get_services_settings(service_id);
				get_services_settings();
			}
		});
	}

	function save_service_price_history(id, price, start_date, finish_date) {
		$.ajax({
			url:"/php/save_service_price_history.php?"+Math.random(),
			data:{id:id, price:price, start_date:start_date, finish_date:finish_date},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#service_history").find(".msg").html("СОХРАНЕНО");

					get_services_settings();
				} else {
					$("#service_history").find(".msg").html("НЕ СОХРАНЕНО");
				}

				setTimeout(function(){
					$("#service_history").find(".msg").html("");
				},3000);
			}
		});
	}

	function save_service_name(service_id) {
		service_name=$("#new_service_name").val();

		$.ajax({
			url:"/php/set_services_settings.php",
			data:{service_id:service_id, service_name:service_name},
			type:"POST",
			success: function(data) {
				$("#service_rename_div").remove();

				get_services_settings();
			}
		});
	}

	var from_service_id=0;

	//Прикрепление клика для изменения сортировки услуг
	function bind_click_service_legend(){
		$("#services_settings_div table .legend_sort").bind("click", function(){
			if (from_service_id==0) {
				from_service_id=$(this).attr("service_id");

				service_id=$(this).attr("service_id");

				$("#dinamic_legend_services_tr_"+service_id).css("font-weight", "bold");

				$("#sort_form_service").css("display","block");
				$("#sort_form_service").animate({opacity:0.8},1000);
			} else {
				$("#services_settings_div table .legend_sort").unbind("click");

				to_service_id=$(this).attr("service_id");

				change_dinamic_services_legend_sort(from_service_id, to_service_id);

				$("#sort_form_service").animate({opacity:0},1000, function(){
					$("#sort_form_service").css("display","none");
				});
			}
		});
	}

	//Скрыть форму сортировки услуг
	function form_sort_services_hide(){
		from_service_id=0;

		$("#sort_form_service").animate({opacity:0},1000, function(){
			$("#sort_form_service").css("display","none");
		});
	}

	//Изменение сортировки услуг
	function change_dinamic_services_legend_sort(from_service_id, to_service_id) {
		console.log(from_service_id+"; "+to_service_id);

		$.ajax({
			url:"/php/dinamic/change_dinamic_services_legend_sort.php",
			data:{operator:operator, from_service_id:from_service_id, to_service_id:to_service_id},
			type:"POST",
			//async:false,
			success: function(data) {
				get_services_settings(0);

				form_sort_services_hide();

				$(".loading2").hide();
			}
		});
	}

	function remove_service(service_id) {
		if (confirm("Точно удалить услугу???")==false) {
			return false;
		}

		$.ajax({
			url:"/php/remove_service.php",
			data:{service_id:service_id},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					get_services_settings();
				} else {
					$("#services_settings_div table th.msg").html("Ошибка!!!");

					setTimeout(function(){
						$("#services_settings_div table th.msg").html("");
					},3000);
				}
			}
		});
	}

	function set_services_settings(service_id) {
		if (confirm("Точно изменить настройки услуги???")==false) {
			return false;
		}

		if (typeof service_id == 'undefined') {
			action="add";

			service=$("#new_service").val();
			price=$("#new_price").val();
		} else {
			action="update";

			service="";
			price=$("#services_settings_div tr[service_code='"+service_id+"']").find(".price").val();
		}

		$.ajax({
			url:"/php/set_services_settings.php",
			data:{service_id:service_id, service:service, price:price, operator:operator, action:action},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#services_settings_div table th.msg").html("Сохранено");

					if (action=="add") {
						$("#new_service").val("");
						$("#new_service_price").val("0.00");

						get_services_settings();
					}
				} else {
					$("#services_settings_div table th.msg").html("НЕ сохранено");
				}

				setTimeout(function(){
					$("#services_settings_div table th.msg").html("");
				},3000);
			}
		});
	}

	/*function set_call_type_price(type_nom) {
		unit_price=$("#call_types_div input.unit_price_"+type_nom).val().replace(",",".");

		$.ajax({
			url:"/php/set_call_type_price.php",
			data:{type_nom:type_nom, unit_price:unit_price},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#call_types_div table th.msg").html("Сохранено");
				} else {
					$("#call_types_div table th.msg").html("НЕ сохранено");
				}

				setTimeout(function(){
					$("#call_types_div table th.msg").html("");
				},3000);
			}
		});
	}*/

	from_call_type_sort_id=0;

	//Прикрепление клика для изменения сортировки типов
	function bind_click_call_types_legend(){
		$("#call_types_tbl .legend_sort").bind("click", function(){
			if (from_call_type_sort_id==0) {
				from_call_type_sort_id=$(this).attr("call_type_sort_id");

				sort_id=$(this).attr("call_type_sort_id");

				$("#dinamic_legend_call_type_tr_"+sort_id).css("font-weight", "bold");

				$("#sort_form_service").css("display","block");
				$("#sort_form_service").animate({opacity:0.8},1000);
			} else {
				$("#call_types_tbl .legend_sort").unbind("click");

				to_call_type_sort_id=$(this).attr("call_type_sort_id");

				change_dinamic_call_types_legend_sort(from_call_type_sort_id, to_call_type_sort_id);

				$("#sort_form_service").animate({opacity:0},1000, function(){
					$("#sort_form_service").css("display","none");
				});
			}
		});
	}

	//Скрыть форму сортировки типов
	function form_sort_call_types_hide(){
		from_call_type_sort_id=0;

		$("#sort_form_service").animate({opacity:0},1000, function(){
			$("#sort_form_service").css("display","none");
		});
	}

	//Изменение сортировки типов
	function change_dinamic_call_types_legend_sort(from_call_type_sort_id, to_call_type_sort_id) {
		if (bee_plus==true) {
			operator1="bee+";
		} else {
			operator1=operator;
		}

		$.ajax({
			url:"/php/dinamic/change_dinamic_call_types_legend_sort.php",
			data:{operator:operator1, from_call_type_sort_id:from_call_type_sort_id, to_call_type_sort_id:to_call_type_sort_id},
			type:"POST",
			//async:false,
			success: function(data) {
				get_call_types_prices(0,0);

				form_sort_call_types_hide();

				$(".loading2").hide();
			}
		});
	}

	function get_roaming_settings() {
		$('#dinamic_details').hide();

		$.ajax({
			url:"/php/get_roaming_settings.php",
			data:{},
			type:"POST",
			success: function(data) {
				roaming=JSON.parse(data);

				$("#roaming_settings_tbl tbody").empty();

				txt="";
				for (i=0;i<roaming.length;i++) {
					txt+="<tr class='roaming_"+roaming[i]["id"]+"'><td>"+roaming[i]["country"]+"</td>";
					txt+="<td><input class='min_price' value='"+roaming[i]["min_price"]+"' style='width:120px; text-align:center'/></td>";
					txt+="<td><button onclick='set_roaming_settings("+roaming[i]["id"]+")'>Сохранить</button></td>";
				}

				$("#roaming_settings_tbl tbody").append(txt);
			}
		});
	}

	function set_roaming_settings(id) {
		min_price=$("#roaming_settings_tbl").find(".roaming_"+id).find(".min_price").val();

		$.ajax({
			url:"/php/set_roaming_settings.php",
			data:{id:id, min_price:min_price},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#roaming_settings_header th.msg").html("Сохранено");
				} else {
					$("#roaming_settings_header th.msg").html("НЕ Сохранено");
				}

				setTimeout(function(){
					$("#roaming_settings_header th.msg").html("");
				},3000);
			}
		});
	}

	function get_attentions(attention_id, tel_nom1) {	
		if (attention_id==-1) {
			search_attentions_by_phone=tel_nom;
		} else {
			search_attentions_by_phone=$("#search_attentions_by_phone").val();
		}

		$.ajax({
			url:"/php/dinamic/attentions/get_attentions.php",
			data:{attention_id:attention_id, tel_nom:search_attentions_by_phone},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				$("#roaming_td table tbody").find(".attention_tr").remove();

				for (i=0;i<data.length;i++) {
					color="yellow";
					if (data[i]["type"]=='no_dinamic_data1' || data[i]["type"]=='no_dinamic_data2') {
						color="#f1c2fc";
					} else if (data[i]["type"]=='UB-M') {
						color="#fc9e0f";
					} else if (data[i]["type"]=='sms_Info') {
						color="#e3d5ba";
					} else if (data[i]["type"]=='mins_ending50' || data[i]["type"]=='inet_ending50' || data[i]["type"]=='sms_ending50') {
						color="#15dceb";
					}  else if (data[i]["type"]=='news') {
						color="magenta";
					}

					if (data[i]["type"]=="news") {
						txt="НОВОСТЬ "+data[i]["operator"].toUpperCase();
						class_name="to_url";
					} else if (data[i]["tel_nom"]=="7777777") {
						txt="ОШИБКА "+data[i]["operator"].toUpperCase()+"!!!";
						class_name="to_number";
					}	else {
						txt=data[i]["tel_nom"];
						class_name="to_number";
					}

					$("#roaming_td table tbody").append("<tr class='attention_tr'><td style='text-align:center'><button class='"+class_name+"' style='border:1px solid gray; width:130px; margin-top:5px; font-size:12px; background:"+color+"' row_id='"+data[i]["id"]+"' operator='"+data[i]["operator"]+"' tel_nom="+data[i]["tel_nom"]+" url='"+data[i]["url"]+"' title='"+data[i]["txt"]+" ("+data[i]["date_time"]+")'>"+txt+"</button></td></tr>");

					if (data[i]["tel_nom"]==tel_nom1) {
						$("#attention_btn").attr("src", "img/attention_on.png");
						$("#attention_btn").attr("title", "Внимание!!! Активно");
						
						$("#attention_reason").html("<span style='font-size:15px' title='История ВНИМАНИЙ'>&#9888;</span> "+
							data[i]["txt"]+" ("+data[i]["date_time"]+")");
						$("#attention_reason").attr("title", data[i]["txt"]+" ("+data[i]["date_time"]+")");
						$("#attention_reason").attr("type", data[i]["type"]);
					}
				}

				$("#roaming_tbl button.to_number, #roaming_tbl button.to_url").bind("click", function() {
					row_id=$(this).attr("row_id");
					operator1=$(this).attr("operator");
					tel_nom1=$(this).attr("tel_nom");

					window.open("client_details.php?operator="+operator1+"&tel_nom="+tel_nom1+"&attention_id="+row_id, "_blank");
				});
			}
		});
	}

	function get_attentions_by_phone() {
			$(".loading2").show();

			if ($("#alert_dialog").find(".no_done").is(":visible")) {
				if ($("#alert_dialog").find(".no_done").is(":checked")) {
					done=0;
					show_all=0;
				} else {
					done=1;
					show_all=1;
				}
			} else {
				show_all=1;
				done=0;
			}

			$.ajax({
				url:"php/dinamic/attentions/get_attentions_report.php",
				data:{tel_nom:tel_nom, date:'', done:done, show_all:show_all, from_dinamic:1},
				type:"POST",
				success: function(data) {
					$("#alert_dialog").dialog("option", "width", 700);
					$("#alert_dialog").dialog("option", "height", 500);
					$(".ui-dialog-buttonset button:contains('Ok')").hide();
					$(".ui-dialog-buttonset button:contains('Сохранить')").hide();
					$(".ui-dialog-buttonset button:contains('Изменить')").hide();
					$("#alert_dialog").dialog("option", "title", "!!!Внимание!!! "+tel_nom);

					txt="<input id='attention_search' style='width:320px; background:yellow; margin-bottom:5px' placeholder='Поиск'></input>";
					txt+="<span>&nbsp; Не отжатые &nbsp;</span><input class='no_done' type='checkbox'/>";
					txt+="<div style='padding-left:5px; width:630px; height:310px; overflow-y:auto'>";
					txt+="<table id='attentions_by_phone_tbl' style='font-size:12px'>";
					txt+="<thead><th style='width:160px'>Время</th>";
					txt+="<th style='width:330px'>Текст</th>";
					txt+="<th style='width:60px'>Отжат</th>";
					txt+="<th style='width:60px'>Кто</th>";
					txt+="</thead><tbody></tbody></table></div>";

					$("#alert_dialog").html(txt);

					if (show_all==1) {
						$("#alert_dialog .no_done").prop("checked", false);
					} else {
						if (done==0) {
							$("#alert_dialog .no_done").prop("checked", true);
						} else {
							$("#alert_dialog .no_done").prop("checked", false);
						}
					}

					data=JSON.parse(data);

					for (i=0;i<data.length;i++) {
						if (data[i]["done"]==1) {
							done="Да";
							title="Включить";
						} else {
							done="Нет";
							title="Выключить";
						}

						if (data[i]["tel_nom"]=="1111111") {//Для новостей
							$("#alert_dialog table").append("<tr><td>"+data[i]["date_time"]+"</td><td class='news' url='"+data[i]["url"]+"' title='"+data[i]["txt"]+"' style='cursor:pointer; text-decoration:underline'>"+short_text(data[i]["txt"].replace("<BR>"," "),46)+"</td><td style='cursor:pointer; text-decoration:underline' onclick='attention_on_off(0,"+data[i]["id"]+"); get_attentions_by_phone()' title='"+title+"'>"+done+"</td><td>"+data[i]["user"]+"</td></tr>");
						} else {
							$("#alert_dialog table").append("<tr><td>"+data[i]["date_time"]+"</td><td title='"+data[i]["txt"]+"'>"+short_text(data[i]["txt"].replace("<BR>"," "),46)+"</td><td style='cursor:pointer; text-decoration:underline' onclick='attention_on_off(0,"+data[i]["id"]+"); get_attentions_by_phone()' title='"+title+"'>"+done+"</td><td>"+data[i]["user"]+"</td></tr>");
						}
					}

					$("#alert_dialog").dialog("open");

					$("#alert_dialog table").find(".news").on("click", function(){
						url=$(this).attr("url");

						window.open(url, "_blank");
					});

					$("#alert_dialog .no_done").on("click", function(){
						get_attentions_by_phone();
					});

					$("#attention_search").keyup(function(){
						$search_text=$(this).val();

						i=0;

						$("#attentions_by_phone_tbl").find("tr").each(function(){
							i++;

							if (i>1) {
								txt=$(this).find("td:eq(1)").text().toLowerCase();

								if (txt.indexOf($search_text.toLowerCase())>-1) {
									$(this).show();
								} else {
									$(this).hide();
								}
							}
						});
					});

					$(".loading2").hide();
				}
			});
		}

	function attention_on_off(only_off, attention_id) {
		$.ajax({
			url:"/php/dinamic/attentions/attention_on_off.php",
			data:{tel_nom:tel_nom, attention_id:attention_id, only_off:only_off, db_name:"clients", hash:localStorage["session_hash"]},
			type:"POST",
			async:false,
			success: function(data) {
				if (data=="session_expaired") {
					location.reload();
					return false;
				}

				if (data=="on") {
					$("#attention_btn").attr("src", "img/attention_on.png");
					$("#attention_btn").attr("title", "Внимание!!! Активно");
				} else if (data=="off") {
					$("#attention_btn").attr("src", "img/attention_off.png");
					$("#attention_btn").attr("title", "Внимание!!! Активировать");
				} else if (data.length>0) {
					$("#alert_dialog").html("Какая-то ошибка");
					$("#alert_dialog").dialog("open");
					$(".ui-dialog-buttonset button:contains('Ok')").show();
					$(".ui-dialog-buttonset button:contains('Сохранить')").hide();
					$(".ui-dialog-buttonset button:contains('Изменить')").hide();
				}
			}
		});
	}

	function no_attentions_show() {
		if ($("#no_attentions_div").is(":visible")) {
			return false;
		}

		txt="<div id='no_attentions_div' style='border-radius:10px; border:3px solid; width:250px; min-height:330px; padding-bottom:10px; position:absolute; z-index:100; left:1111px; top:15%; background:grey; text-align:center'>";
		txt+="<span><B>Не беспокоить до:</B></span>";
		txt+="<div class='date_picker' style='margin-left:19px'></div><BR>";
		txt+="<span style='font-weight:bold'>по поводу</span>";

		txt+="<select id='no_attentions_type' style='margin-left:10px'>";
		txt+="<option value='need_svoya'>Нужна СС</option>";
		txt+="<option value='need_gprs_bl'>Нужен GPRS-БЛ</option>";
		txt+="<option value='no_need_svoya'>Не нужна СС</option>";
		txt+="<option value='no_need_gprs_bl'>Не нужен GPRS-БЛ</option>";
		txt+="<option value='use_inet'>Пользуется GPRS</option>";
		txt+="<option value='no_use_inet'>Не пользуется GPRS</option>";

		txt+="</select>";
		txt+="<select id='other_attention_reason' style='display:none; width:200px; font-size:10px; margin-top:5px' size=10></select>";
		txt+="<button class='no_attentions_btn' to=2 style='margin-top:10px; width:93'>На 10 дней</button>";
		txt+="<button class='no_attentions_btn' to=1 style='margin-left:10px; width:93; margin-top:10px'>До кон мес</button>";
		txt+="<button class='no_attentions_btn' to=0 style='margin-top:10px; width:93'>Вручную</button>";
		txt+="<button id='no_attentions_close_btn' style='margin-left:10px; margin-top:10px; width:93'>Закрыть</button>";
		txt+="<BR><div style='font-weight:bold; margin-top:10px'>Уже добавлено:";
		txt+="<table id='no_attentions_exists' style='width:100%'>";
		txt+="<thead style='font-size:12px'><th>Тип</th><th>До даты</th></thead>";
		txt+="<tbody style='font-size:13px; background:white'></tbody>";
		txt+="</table>";
		txt+="</div>";

		txt+="</div>";	
		$("body").append(txt);

		$("#no_attentions_div").draggable();

		$("#no_attentions_div .date_picker").datepicker({
			dateFormat: 'yy-mm-dd', 
			currentText: 'Сейчас',
			closeText: 'Закрыть',
			timeText: 'Время',
			monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
			monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
			dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
			dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
			dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
			firstDay: 1,
			prevText: '<Пред',
			nextText: 'След>',
			showButtonPanel: true
		});

		var d = new Date();
  		d.setDate(d.getDate() + 1);

		$("#no_attentions_div .date_picker").datepicker("setDate", d);

		$(".no_attentions_btn").click(function(){
			to=$(this).attr("to");

			if (to==1) {
				var d = new Date();

				d = new Date(d.getFullYear(), d.getMonth()+1, 0);

  				$("#no_attentions_div .date_picker").datepicker("setDate", d);
  			} else if (to==2) {
  				var d = new Date();

  				d.setDate(d.getDate() + 10);

  				$("#no_attentions_div .date_picker").datepicker("setDate", d);
  			}

			date=$("#no_attentions_div .date_picker").val();
			type=$("#no_attentions_type option:selected").val();
			
			if (type=="other_attention_reason") {
				type=$("#other_attention_reason option:selected").text();
			}

			no_attentions(date, type);

			attention_on_off(1, attention_id);
		});

		$("#no_attentions_close_btn").click(function(){
			$("#no_attentions_div").remove();
		});

		$.ajax({
			url:"/php/dinamic/attentions/no_attentions_exists.php",
			data:{tel_nom:tel_nom},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				if (data.length==0) {
					$("#no_attentions_exists").parent().hide();
					$("#no_attentions_remove_all_btn").hide();
				} else {
					$("#no_attentions_remove_all_btn").show();
				}

				$("#no_attentions_exists tbody").empty();

				for (i=0;i<data.length;i++) {
					$("#no_attentions_type option[value='"+data[i]["type"]+"']").remove();

					title="";
					if (data[i]["type"]=="need_svoya") {
						data[i]["type"]="Нужна СС";
					} else if (data[i]["type"]=="no_need_svoya") {
						data[i]["type"]="Не нужна СС";
					} else if (data[i]["type"]=="need_gprs_bl") {
						data[i]["type"]="Нужен GPRS-БЛ";
					} else if (data[i]["type"]=="no_need_gprs_bl") {
						data[i]["type"]="Не нужен GPRS-БЛ";
					}  else if (data[i]["type"]=="no_use_inet") {
						data[i]["type"]="Не польз И-нетом";
					}

					$("#no_attentions_exists tbody").append("<tr><td style='text-align:center' title='"+title+"'>"+data[i]["type"]+"</td><td style='text-align:center'>"+data[i]["until_date"]+"</td><td><img src='img/cancel.png' style='cursor:pointer; width:20px' no_attention_id='"+data[i]["id"]+"'></td></tr>");
				}

				$("#no_attentions_exists tbody img").bind("click", function(){
					no_attention_id=$(this).attr("no_attention_id");

					no_attentions(0,0,no_attention_id,"delete");
				});

				try {
					active_attention_type=$("#attention_reason").attr("type").replace("_ending", "");

					$("#no_attentions_type option[value='"+active_attention_type+"']").attr("selected", "selected");
				} catch (e) {

				}
			}

		});
	}

	function no_attentions(date, type, no_attention_id, delete1) {
		if (typeof no_attention_id == 'undefined') {
			no_attention_id=0;
			delete1="";
		}

		$.ajax({
			url:"/php/dinamic/attentions/no_attentions.php",
			data:{tel_nom:tel_nom, date:date, type:type, operator:operator, delete:delete1, no_attention_id:no_attention_id},
			type:"POST",
			success: function(data) {
				$("#no_attentions_div").remove();

				if (delete1=="delete") {
					if (type=="g_append") {
						no_attentions_exists();
					} else {
						no_attentions_show();

						return false;
					}
				} else {
					no_attentions_exists();
				}

				if (data=="OK") {
					$("#alert_dialog").html("Успешно");
				} else {
					$("#alert_dialog").html("Какая-то ошибка");
				}
				$("#alert_dialog").dialog("option","title","Результат");
				$("#alert_dialog").dialog("open");
				$(".ui-dialog-buttonset button:contains('Ok')").show();
				$(".ui-dialog-buttonset button:contains('Сохранить')").hide();
				$(".ui-dialog-buttonset button:contains('Изменить')").hide();
			}
		});
	}

	function get_exceptions() {
		$("#dinamic_details").hide();
		
		$.ajax({
			url:"/php/exceptions.php",
			data:{action:3},
			type:"POST",
			success: function(data) {
				$("#exceptions_tbl tbody").empty();

				data=JSON.parse(data);

				txt="";
				for (i=0;i<data.length;i++) {
					txt+="<tr>";
					txt+="<td>"+data[i]["exception"]+"</td>";
					txt+="<td>"+data[i]["date"]+"</td>";
					txt+="<td onclick='edit_exception("+data[i]["id"]+")' style='cursor:pointer'>"+data[i]["comment"]+"</td>";
					txt+="<td><img src='img/edit.png' style='width:18px;  cursor:pointer' onclick='edit_exception("+data[i]["id"]+")' title='Изменить'></td>";
					txt+="<td><img src='img/cancel.png' style='width:18px;  cursor:pointer' onclick='remove_exception("+data[i]["id"]+")' title='Удалить'></td>";
					txt+="</tr>";
				}

				txt+="<tr>";
				txt+="<td><input id='new_exception'/></td>";
				txt+="<td><button onclick='add_exception()'>Добавить</button></td>";
				txt+="<td><input id='new_exception_comment' style='width:350px'/></td>";
				txt+="</tr>";

				$("#exceptions_tbl tbody").html(txt);
			}
		});
	}

	function add_exception() {
		new_exception=$("#new_exception").val();
		new_exception_comment=$("#new_exception_comment").val();

		$.ajax({
			url:"/php/exceptions.php",
			data:{new_exception:new_exception, new_exception_comment:new_exception_comment, action:1},
			type:"POST",
			success: function(data) {
				get_exceptions();
			}
		});
	}

	function edit_exception(id) {
		txt="<div id='edit_exception_div' style='width:400px; height:100px; position: absolute; z-index: 1000; left:222px; top:215px; padding-top:5px; background: grey; border-radius:10px; border:2px solid black'>";
		txt+="<input class='comment' style='width:350px'>";
		txt+="<div style='margin-top:5px'>";
		txt+="<button class='save'>Сохранить</button>";
		txt+="<button class='close_form' style='margin-left:10px'>Закрыть</button>";
		txt+="</div>";
		txt+="<div class='msg' style='width:100%; font-wight:bold; color:red'>";
		txt+="</div>";
		txt+="</div>";

		$("body").append(txt);

		$("#edit_exception_div .close_form").click(function(){
			$("#edit_exception_div").remove();
		});

		$("#edit_exception_div .save").click(function(){
			comment=$("#edit_exception_div .comment").val();

			$.ajax({
				url:"/php/exceptions.php",
				data:{id:id, action:2, comment:comment},
				type:"POST",
				success: function(data) {
					if (data=="OK") {
						$("#edit_exception_div").remove();
						get_exceptions();
					} else {
						$("#edit_exception_div .msg").text("Не сохранено");
					}
				}
			});
		});
	}

	function remove_exception(id) {
		$.ajax({
			url:"/php/exceptions.php",
			data:{id:id, action:0},
			type:"POST",
			success: function(data) {
				get_exceptions();
			}
		});
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
	    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	    var n = !isFinite(+number) ? 0 : +number,
	        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
	        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
	        s = '',
	        toFixedFix = function (n, prec) {
	            var k = Math.pow(10, prec);
	            return '' + Math.round(n * k) / k;
	        };
	    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	    if (s[0].length > 3) {
	        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	    }
	    if ((s[1] || '').length < prec) {
	        s[1] = s[1] || '';
	        s[1] += new Array(prec - s[1].length + 1).join('0');
	    }
	    return s.join(dec);
	}