	var from_row_id=0;
	var move_id=0;

	//Прикрепление клика для изменения сортировки услуг
	function bind_click_service_legend(){
		console.log("***");
		$("#dinamic_legend_services_tbl .legend_sort").bind("click", function(){
			if (from_row_id==0) {
				from_row_id=parseInt($(this).attr("sort"));
				tr_nom=$(this).html();
				move_id=$(this).attr("service_id");

				$("#dinamic_legend_services_tr_"+tr_nom).css("font-weight", "bold");

				$("#sort_form_service").css("display","block");
				$("#sort_form_service").animate({opacity:0.8},1000);
			} else {
				$("#dinamic_legend_services_tbl .legend_sort").unbind("click");

				to_row_id=parseInt($(this).attr("sort"));
				tr_nom=$(this).html();
				change_dinamic_services_legend_sort(from_row_id, to_row_id, move_id);

				$("#sort_form_service").animate({opacity:0},1000, function(){
					$("#sort_form_service").css("display","none");
				});
			}
		});
	}

	//Скрыть форму сортировки услуг
	function form_sort_services_hide(){
		from_row_id=0;
		move_id=0;

		$("#sort_form_service").animate({opacity:0},1000, function(){
			$("#sort_form_service").css("display","none");
		});
	}

	//Изменение сортировки услуг
	function change_dinamic_services_legend_sort(from_row_id, to_row_id, move_id) {
		console.log(move_id+": "+from_row_id+"; "+to_row_id);

		$.ajax({
			url:"/php/dinamic/change_dinamic_services_legend_sort.php",
			data:{operator:operator, id:move_id, from_row_id:from_row_id, to_row_id:to_row_id},
			type:"POST",
			//async:false,
			success: function(data) {
				if (data=="OK") {
					$("#legend_services_msg").html("СОХРАНЕНО");

					get_services_settings(0);
				} else {
					$("#legend_services_msg").html("НЕ сохранено");

					form_sort_services_hide();
				}

				$(".loading2").hide();

				setTimeout(function(){
					$("#legend_services_msg").html("");
					$("#legend_services_save_btn").show();
				},3000);
			}
		});
	}

	//Измение данных по услугам
	function change_dinamic_services_legend(row_id) {
		$("#legend_services_save_btn").hide();

		$(".loading2").show();

		data=Array();

		id=$("#dinamic_legend_services_tr_"+row_id).attr("service_id");

		$("#dinamic_legend_services_tr_"+row_id).css("font-weight","normal");
		
		if (typeof $("#dinamic_legend_services_tr_"+row_id).attr("style")!='undefined') {
			color=$("#dinamic_legend_services_tr_"+row_id).attr("style").replace("background:", "").split(";")[0];
			if (color.indexOf("font-weight")>-1) {
				color=$("#dinamic_legend_services_tr_"+row_id).attr("style").replace("background:", "").split(";")[1];
			}
		} else {
			color="";
		}

		if ($("#dinamic_legend_services_tr_"+row_id).find(".show_service").is(":checked")==true) {
			show=1;
		} else {
			show=0;
		}

		$.ajax({
			url:"/php/dinamic/legend/change_dinamic_services_legend.php",
			data:{operator:operator, id:id, color:color, show:show},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#legend_services_msg").html("СОХРАНЕНО");
				} else {
					$("#legend_services_msg").html("НЕ сохранено");
				}
				$(".loading2").hide();

				setTimeout(function(){
					$("#legend_services_msg").html("");
					$("#legend_services_save_btn").show();
				},3000);
			}
		});
	}

	//Получение списка номеров с услугами или остатками
	function get_service_or_packege_numbers(id, param, name, target, type) {
		$(".ui-dialog-buttonset button:contains('Ok')").show();
		$(".ui-dialog-buttonset button:contains('Сохранить')").hide();
		$(".ui-dialog-buttonset button:contains('Изменить')").hide();

		have_no_service=$("#have_no_service:checked").length;

		$.ajax({
			url:"/php/dinamic/legend/get_service_numbers.php",
			data:{operator:operator, id:id, param:param, name:name, target:target, type:type, to_excel:0, have_no_service:have_no_service},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				console.log(target+": "+data["tel_noms"]);

				if (target=="numbers") {
					txt="<select class='account_select'><option value='all'>Все</option></select>";
					txt+="<select class='contract_select' style='margin-left:10px'><option value='all'>Все</option></select>";
					txt+="<select class='tarif_select' style='margin-left:10px'><option value='all'>Все</option></select>";
					txt+="<span style='margin-left:10px'>Без услуги</span><input type='checkbox' param='"+param+"' id='have_no_service'/>";

					txt+="<table style='width:100%; margin-top:5px'><thead style='font-size:12px'><th>п/п</th><th>ПО</th><th>ЛС</th><th>КТ</th><th>ТАРИФ</th></thead><tbody>";
				
					accounts="";
					contracts="";
					tarifs="";
					accounts_done=Array();
					contract_done=Array();
					tarifs_done=Array();
					i=0;

					if (type=="service") {
						for (tel_nom in data["tel_noms"]) {
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
					} else {
						for (id in data) {
							i++;

							account=data[id]["account"];
							contract=data[id]["contract"];
							tarif=data[id]["tarif"];

							txt+="<tr style='font-size:13px' account='"+account+"' contract='"+contract+"' tarif='"+tarif+"'>";
							txt+="<td>"+i+"</td>";
							txt+="<td class='tel_nom'>"+data[id]["tel_nom"]+"</td>";
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
					}

					txt+="</tbody></table>";

					$("#alert_dialog").html(txt);
					$("#alert_dialog .account_select").append(accounts);
					$("#alert_dialog .contract_select").append(contracts);
					$("#alert_dialog .tarif_select").append(tarifs);

					if (have_no_service==1) {
						$("#have_no_service").prop("checked","checked");
					} 

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

					$("#have_no_service").click(function(){
						param=$(this).attr("param");
						get_service_or_packege_numbers(id, param, name, target, type);
					});
				} else {
					$("#alert_dialog").html("<table style='width:80%; margin-left:10%'><thead style='font-size:12px'><th>ЛС</th><th>КТ</th></thead></table>");
					for (i=0;i<data.length;i++) {
						$("#alert_dialog table").append("<tr style='font-size:13px'><td>"+data[i]["account"]+"</td><td>"+data[i]["contract"]+"</td>");
					}
				}

				$("#alert_dialog").dialog("option", "height", 500);
				$("#alert_dialog").dialog("option", "width", 800);
				$("#alert_dialog").dialog("option", "title", name);
				$("#to_excel_btn").unbind("click");
				$("#to_excel_btn").remove();
				$(".ui-dialog-buttonset").append("<img id='to_excel_btn' src='img/to_excel.png' title='Экспорт в Excel' name='"+name+"' type='"+type+"' style='width:64px; cursor:pointer'>");
				$("#alert_dialog").dialog("open");

				$("#alert_dialog .tel_nom").bind("click", function(){
					tel_nom=$(this).html();
					window.open("dinamic1.php?operator="+operator+"&tel_nom="+tel_nom+"&api="+api, "_blank");
				});

				$("#to_excel_btn").bind("click", function(){
					to_excel("alert_dialog table", "Услуги");
				});
			}
		});
	}