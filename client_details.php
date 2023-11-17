<?php
	ob_start();
?>

<HTML>
<HEAD>
<meta charset="utf8">
<meta name="robots" content="noindex, nofollow, noarchive"/>
<meta name="google-site-verification" content="pLej8ME0DMBdjOeMBJlrWSSeNXT6B-0GIZ9XKWpHuCs" />

<title>Клиенты</title>
	<?
		Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //Дата в прошлом
		Header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		Header("Pragma: no-cache"); // HTTP/1.1
		Header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
		ob_end_flush();
	?>
	<link  href="https://офис.димс.рф/css/jquery-ui.css" rel="stylesheet">
	<link  href="https://офис.димс.рф/css/timepicker.css" rel="stylesheet">
	<link  href="/css/dinamic.css" rel="stylesheet">
	<link  href="/css/loader.css" rel="stylesheet">
	<link  href="/css/loader1.css" rel="stylesheet">
	<link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-3.3.2.min.css" type="text/css">
    <link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-example.min.css" type="text/css">
    <link rel="stylesheet" href="https://офис.димс.рф/css/prettify.min.css" type="text/css">
    <link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-multiselect.css" type="text/css">
	<link  href="https://офис.димс.рф/css/colResizable.css" rel="stylesheet" type="text/css">
	<link  href="https://офис.димс.рф/js/contextMenu/jquery.contextMenu.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-multiselect.css" type="text/css">
	<link  href="https://офис.димс.рф/css/navigation.css" rel="stylesheet" type="text/css">

	<style>
		.ui-datepicker-current-day {background:red!important;}
		.ui-widget-overlay {display:none!important}
	</style>

	<link id="operator_logo" rel="icon" type="vnd.microsoft.icon" href="img/icon.png">

	<script type="text/javascript" src="https://офис.димс.рф/js/jquery-2.1.3.min.js"></script>
	<script src="https://офис.димс.рф/js/jquery-ui.js" type="text/javascript"></script>
	<script src="https://офис.димс.рф/js/timepicker.js" type="text/javascript"></script> 
	<script src="https://офис.димс.рф/js/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/bootstrap-3.3.2.min.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="https://клиент.димс.рф/js/navigation.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/jquery.table2excel.min.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/contextMenu/jquery.contextMenu.js" defer></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/dinamic/blocks_and_forwardings.js" defer></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/dinamic/comments.js" defer></script>
    <script type="text/javascript" src="js/shared_scripts.js?r=<?echo time();?>"></script>
    
    <script src="https://офис.димс.рф/js/check_auth.js" type="text/javascript"></script>

    <style>
    	.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {background: palegreen;}
    	.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active {background:  white;}
	</style>
</HEAD>
</HEAD>
<body>

<script>
	var server="https://офис.димс.рф";
	var api=0;
	
	operator=<?php echo "'".$_GET["operator"]."'";?>;

	user_name="";

	<?php
		if (isset($_GET["tel_nom"])) {
			echo "tel_nom=".$_GET["tel_nom"].";";
		} else {
			echo "tel_nom=0;";
		}

		if (isset($_GET["attention_id"])) {
			echo "attention_id=".$_GET["attention_id"].";";
		} else {
			echo "attention_id=-1";
		}
	?>

	var dinamic_details_date=0;

	$(document).ready(function(){
		check_auth();

		$(document).find("title").html(tel_nom+" (Клиенты)");

		$("#main_tabs").tabs({
			fx: { opacity: "toggle", duration: "slow" },
			spinner: 'Загрузка...'
		});

		if (operator=="meg") {
			$("#operator_logo").attr("href", "img/megafon_icon.png");
			$("#main_tabs .ui-tabs-nav").before("<img src='img/megafon_logo.png' style='width:68px; position:absolute; left:7px; top:6px'>");
		} else if (operator=="tele2") {
			$("#operator_logo").attr("href", "img/"+operator+"_icon.ico");
			$("#main_tabs .ui-tabs-nav").before("<img src='img/"+operator+"_logo.png' style='width:68px; position:absolute; left:7px; top:6px'>");
		} else {
			$("#operator_logo").attr("href", "img/"+operator+"_icon.png");
			$("#main_tabs .ui-tabs-nav").before("<img src='img/"+operator+"_logo.png' style='width:68px; position:absolute; left:7px; top:6px'>");
		}

		if (operator!="bee") {
			$("#call_types_bee_plus").hide();
		}

		make_navigation();

		/*$.contextMenu({
	        selector: '#calls_list td.phone', 
	        callback: function(key, options) {
	            if (key=="set_phone_exception") {
	            	set_phone_exception();
	            }
	        },
	        items: {
	            "set_phone_exception": {name: "Записать в исключения", icon: ""}
	        }
	    });*/

	    $('#attention_btn').bind('contextmenu', function(e) {
		    return false;
		});

		$("#attention_btn").mousedown(function(e){
			if (e.which==3) {
				no_attentions_show();
			} else {
				attention_on_off(0, attention_id);
			}
		});

		/*$("#calls_list").parent().on("scroll", function(){
			if ($(this).scrollTop() + $(this).height() >= $(this).find("table").height()-100 && !data_loading) {
		       	$(this).unbind("scroll");
		       	eval($(this).find("table").attr("function"));
		    }
		});*/

		$("#alert_dialog").dialog({
			modal: true,
			resizable: false,
			autoOpen: false,
			buttons: {
				Ok: function() {
					$(this).dialog("close");
					$("#alert_dialog").dialog("option", "height", 200);
					$("#alert_dialog").dialog("option", "title", "");
					$("#alert_dialog").dialog("option", "width", 200);
					$("#to_excel_btn").remove();
				}
			},
			resizable: true,
			maxHeight: 500,
			width:300
		});

		$("#date_picker, #calls_list_from_date, #calls_list_to_date").datepicker({
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
				if ($(this).attr("id")=="calls_list_to_date") {
					$("#calls_list").attr("page_nom",0);
					get_calls_list();
				} else if ($(this).attr("id")!="calls_list_from_date") {
					date_picker_is_active=true;
					get_comments_by_date();
				}
			}
		});
						
		$("body").append("<script type='text/javascript' src='js/colorPicker.js'/>");

		body_height=document.body.clientHeight;
		$("#main_tabs").css("height", body_height);
		$("#dinamic_main_div").css("height", body_height-157);
		$("#scroll_down_btn").css("top", body_height-64);

		$("#dinamic_details_tbl").parent().css("height", body_height*0.4);
		$("#monthes_history_tbl").parent().css("height", body_height*0.4);
		$("#client_services_tbl").parent().css("height", body_height*0.4);
		$("#calls_list").parent().css("height", body_height*0.31);
		$("#detal_totals").parent().css("height", body_height*0.31);

		$("#comments_td div").css("height", body_height-390);
		
		$("#legend_services, #legend").css("height", body_height-64);

		body_width=document.body.clientWidth;

		$("#main_tabs").css("width", body_width*1);

		if (operator=="bee") {
			str="<tr><th style='width:107px'>Время</th>";
			str+="<th style='width:61px'>Длит.</th>";
			str+="<th style='width:61px'>Объём</th>";
			str+="<th style='width:116px'>Номер</th>";
			str+="<th style='width:31px'>I/O</th>";
			str+="<th style='width:201px'>Тип</th>";
			str+="<th style='width:306px'>Услуга</th>";
			str+="<th style='width:60px'>Цена</th>";
			str+="<th style='width:60px'>За ед.</th>";
			str+="<th style='width:60px'>№ БС</th>";
			str+="<th style='width:60px'></th>";
			str+="<th style='width:80px'>Коммент.</th></tr>";
		} else if (operator=="mts") {
			str="<tr><th style='width:108px'>Время</th>";
			str+="<th style='width:75px'>Факт</th>";
			str+="<th style='width:75px'>Билл</th>";
			str+="<th style='width:130px'>Номер</th>";
			str+="<th style='width:30px'>I/O</th>";
			str+="<th style='width:180px'>Тип</th>";
			str+="<th style='width:285px'>Услуга</th>";
			str+="<th style='width:60px'>Цена</th>";
			str+="<th style='width:60px'>За ед.</th>";
			str+="<th style='width:60px'>Ед. изм.</th>";
			str+="<th style='width:100px'>Зона</th>";
			str+="<th style='width:20px'></th>";
			str+="<th style='width:20px'></th>";
			str+="<th style='width:40px'>GMT</th>";
			str+="<th style='width:81px'>Коммент</th>";
		} else if (operator=="meg") {
			str="<tr><th style='width:107px'>Время</th>";
			str+="<th style='width:66px'>Факт</th>";
			str+="<th style='width:66px'>Билл</th>";
			str+="<th style='width:96px'>Номер</th>";
			str+="<th style='width:31px'>I/O</th>";
			str+="<th style='width:191px'>Услуга</th>";
			str+="<th style='width:190px'>Тип</th>";
			str+="<th style='width:60px'>Цена</th>";
			str+="<th style='width:60px'>За ед.</th>";
			str+="<th style='width:65px'>Ед. изм.</th>";
			str+="<th style='width:191px'>Зона</th>";
			str+="<th style='width:60px'></th>";
			str+="<th style='width:60px'></th>";
			str+="<th style='width:80px'>Коммент.</th></tr>";
		} else if (operator=="tele2") {
			str="<tr><th style='width:106px'>Время</th>";
			str+="<th style='width:60px'>Длит.</th>";
			str+="<th style='width:60px'>Объём</th>";
			str+="<th style='width:100px'>Номер</th>";
			str+="<th style='width:30px'>I/O</th>";
			str+="<th style='width:285px'>Тип</th>";
			str+="<th style='width:305px'>Услуга</th>";
			str+="<th style='width:60px'>Цена</th>";
			str+="<th style='width:60px'>За ед.</th>";
			str+="<th style='width:60px'></th>";
			str+="<th style='width:60px'></th>";
			str+="<th style='width:60px'></th>";
			str+="<th style='width:77px'>Коммент.</th></tr>";
		}
		$("#calls_list_header thead").html(str);

		$(".month_down").click(function(){
			selected_month=$("#detal_totals_month option:selected").val();
			selected_year=$("#detal_totals_year option:selected").val();

			if (selected_month==1) {
				if (selected_year>2018) {
					$("#detal_totals_month option").prop('selected', false);
					$("#detal_totals_year option").prop('selected', false);

					$("#detal_totals_year option[value='"+(selected_year-1)+"']").prop('selected', true);
					$("#detal_totals_month option[value='12']").prop('selected', true);
				}
			} else {
				$("#detal_totals_month option").prop('selected', false);
				$("#detal_totals_month option[value='"+(selected_month-1)+"']").prop('selected', true);
			}
		});

		$(".month_up").click(function(){
			selected_month=parseInt($("#detal_totals_month option:selected").val());
			selected_year=parseInt($("#detal_totals_year option:selected").val());
			date=new Date();

			current_year=date.getFullYear();

			if (selected_month==12) {
				if (selected_year!=current_year) {
					$("#detal_totals_month option").prop('selected', false);
					$("#detal_totals_year option").prop('selected', false);

					$("#detal_totals_year option[value='"+(selected_year+1)+"']").prop('selected', true);
					$("#detal_totals_month option[value='1']").prop('selected', true);
				}
			} else {
				$("#detal_totals_month option").prop('selected', false);
				$("#detal_totals_month option[value='"+(selected_month+1)+"']").prop('selected', true);
			}
		});

		get_dinamic_details();

		$("#calls_list_adresat").keyup(function(e) {
			if (e.keyCode==13) {
				$("#calls_list").attr("page_nom",0);
				get_calls_list();
			}
		});

		$("#dinamic_details_tbl").parent().on("scroll", function(){
			if ($(this).scrollTop() + $(this).height() >= $(this).find("table").height()-100 && !data_loading) {
			    get_client_history(1);
			}
		});
	});

	
	var tables_loaded_count=0;
	var timer=0;

	function get_dinamic_details() {
		$("#dinamic_details").show();

		get_attentions(attention_id, tel_nom);
		get_detal_totals();
		get_phone_data();
		get_client_history();
		get_client_history_by_monthes();
		get_client_services();
		get_contracts_names();

		/*tables_loaded_count_interv=setInterval(function(){
			timer++;

			if (timer>150) {
				clearInterval(tables_loaded_count_interv);
				window.location.reload();
			}

			$(".loader-percents").html("загружено "+parseInt(tables_loaded_count/5*100)+"%");

			if (tables_loaded_count>3) {
				clearInterval(tables_loaded_count_interv);

				$(".loader-background").animate({opacity:0}, 800, function(){
					$(".loader-background").remove();
				});
			}
		},100);*/
	}

	function get_contracts_names() {
		$.ajax({
			url: server+"/php/changes/get_contracts_names.php",
			data: {operator:operator},
			type: "POST",
			async:false,
			success: function (data) {
				contracts_names=JSON.parse(data);
			}
		});
	}

	function short_left(str) {
		str=String(str);
		if (str.length>5) {
			start_pos=str.length-6;
			return str.substring(start_pos);
		} else {
			return str;
		}
	}

	function show_user_profile() {
		$.ajax({
			url:"/php/dinamic/users/get_user_profile.php",
			data:{operator:operator, tel_nom:tel_nom},
			type:"POST",
			success: function(data) {
				if (data.indexOf("no_user_profile")>-1) {
					fio="";
					passport="";
					address="";
					birth="";
				} else {
					data=JSON.parse(data);
					fio=data["fio"];
					passport=data["passport"];
					address=data["address"],
					birth=data["birth"]
				}

				$("#alert_dialog_profile").html("<table id='user_profile_tbl'><tr><td>ФИО:<BR><textarea id='user_profile_fio'>"+fio+"</textarea></td></tr><tr><td>Паспорт:<BR><textarea id='user_profile_passport'>"+passport+"</textarea></td></tr><tr><td>Адрес:<BR><textarea id='user_profile_address'>"+address+"</textarea></td><tr><td>Дата рождения:<BR><textarea id='user_profile_birth'>"+birth+"</textarea></td></tr></table>");
				$("#alert_dialog_profile").dialog("open");
			}
		});
	}

	var number_services_settings="";

	function get_number_services_settings() {
		$.ajax({
			url: "/php/get_number_services_settings.php",
			data:{tel_nom:tel_nom, operator:operator},
			type:"POST",
			async:false,
			success: function(data) {
				number_services_settings=JSON.parse(data);
			}
		});
	}

	var data_loading=false;

	function get_unit_price(call_date, call_type) {
		unit_price=0;
				
		parts=call_date.split("-");
		call_date=new Date(parts[0], parts[1] - 1, parts[2]);

		for (ii=0;ii<call_types_prices[call_type].length;ii++) {
			parts=call_types_prices[call_type][ii]["date"].split("-");
			call_types_prices_date=new Date(parts[0], parts[1] - 1, parts[2]);

			if (call_date>=call_types_prices_date) {
				unit_price=call_types_prices[call_type][ii]["price"];
			}
		}

		return unit_price;
	}

	function is_payable(call_date, from_number, to_number, call_type) {
		if (number_services_settings["numbers_exceptions"].indexOf(from_number)>-1) {
			return false;
		} else if (number_services_settings["numbers_exceptions"].indexOf(to_number)>-1) {
			return false;
		} else if (number_services_settings["numbers_exceptions"].indexOf("7"+from_number)>-1) {
			return false;
		} else if (number_services_settings["numbers_exceptions"].indexOf("7"+from_number)>-1) {
			return false;
		} else if (number_services_settings["numbers_exceptions"].indexOf(from_number.substring(1))>-1) {
			return false;
		} else if (number_services_settings["numbers_exceptions"].indexOf(to_number.substring(1))>-1) {
			return false;
		}

		parts=call_date.split("-");
		call_date=new Date(parts[0], parts[1] - 1, parts[2]);

		available_services=Array();

		for (service_code in number_services_settings["services_history"]) {
			for (j=0;j<number_services_settings["services_history"][service_code].length;j++) {
				parts=number_services_settings["services_history"][service_code][j]["start_date"].split("-");
				start_date=new Date(parts[0], parts[1] - 1, parts[2]);

				parts=number_services_settings["services_history"][service_code][j]["finish_date"].split("-");
				finish_date=new Date(parts[0], parts[1] - 1, parts[2]);

				if (start_date<=call_date && finish_date>=call_date) {
					available_services.push(parseInt(service_code));
				}
			}
		}

		result=true;

		if (call_type.indexOf("ВХ")>-1) {
			result=false;
		} else if (call_type.indexOf("РОУМ")>-1) {
			result=false;
		} else if (call_type.indexOf("GPRS")>-1) {
			if (available_services.indexOf(7)>-1 || available_services.indexOf(8)>-1 || available_services.indexOf(25)>-1 || available_services.indexOf(30)>-1) {//Если подключены G1000, G3000, БЛ интернет или GPRS БЛ, то не бесплатный
				result=false;
			}
		} else if (call_type.indexOf("SMS")>-1) {
			if (available_services.indexOf(9)>-1 || available_services.indexOf(31)>-1) {//Если подключено SMS 500 или 1000 SMS, то бесплатный
				result=false;
			}
		} else if (available_services.indexOf(5)>-1) {//Если подкючена Своя сеть
			if (call_type.indexOf("ИСХ-ВСР")>-1 || call_type.indexOf("ИСX-ЗГП")>-1) {
				result=false;
			} else {
				result=true;
			}
		} else if (available_services.indexOf(29)>-1) {//Если подкючены БЛ минуты
			if (call_type.indexOf("ИСХ-ВСР")>-1 || call_type.indexOf("ИСX-ЗГП")>-1 || call_type.indexOf("ИСХ-СРДО")>-1) {
				result=false;
			} else {
				result=true;
			}
		} else {
			result=true;
		}

		return result;
	}

	function get_calls_list(append, to_excel_option) {
		data_loading=true;
		$(".loading2").show();

		if (typeof to_excel_option == 'undefined') {
			to_excel_option=0;
		}

		filter=$('#detal_totals').attr('filter');

		if (append==0) {
			$("#calls_list").attr("page_nom",0);
			$("#calls_list tbody").empty();
		}

		page_nom=parseInt($("#calls_list").attr("page_nom"));

		/*if (operator=="bee" && $("#dinamic_details_header").html().indexOf("Бандиты")>-1) {
			operator1="bee+";
		} else {
			operator1=operator;
		}*/

		from_date=$("#calls_list_from_date").val();
		to_date=$("#calls_list_to_date").val();

		if (from_date=="") {
			if (to_excel_option==1) {
				now=new Date();

				now.setMonth(now.getMonth() - 1);

				from_date=now.getFullYear()+"-"+addZero(now.getMonth())+"-01";
			} else {
				from_date="2000-01-01";
			}
		}

		if (to_date=="") {
			to_date="2050-01-01";
		}

		adresat=$("#calls_list_adresat").val();

		if ($("#edited_calls_checkbox").is(":checked")) {
			edited_calls=1;
		} else {
			edited_calls=0;
		}

		if ($("#loss_calls_checkbox").is(":checked")) {
			loss=1;
		} else {
			loss=0;
		}

		totals_year=$("#detal_totals_year option:selected").val();
		totals_month=$("#detal_totals_month option:selected").val();

		$.ajax({
			url: "/php/dinamic/get_calls_list.php",
			data:{operator:operator, tel_nom:tel_nom, page_nom:page_nom, filter:filter, from_date:from_date, to_date:to_date, totals_year:totals_year, totals_month:totals_month, adresat:adresat, loss:loss, our_numbers:0, only_our_numbers:0, edited_calls:edited_calls, for_autoblock:0, to_excel:to_excel_option, from_client:1},
			type:"POST",
			//async: false,
			success: function(data) {
				data=JSON.parse(data);

				if (data["calls"].length==0 && append==0) {
					$("#calls_list tbody").html("<tr><td colspan=9>Данные отсутствуют</td></tr>");
					data_loading=false;
					$(".loading2").hide();
					return false;
				} else {
					if (append==1) {
						$("#calls_list").attr("page_nom", page_nom+1);
					} else {
						$("#calls_list tbody").empty();
						$("#calls_list").animate({scrollTop:0},0);
						$("#calls_list").attr("page_nom", 1);
						detal_last_time=data["calls"][0]["update_date"]+" "+data["calls"][0]["update_time"];
						$("#detal_last_time").html("Данные получены: "+detal_last_time);
					}
				}

				for (i=0; i<data["calls"].length; i++) {
					if (data["calls"][i]["edited_status"]=="edited") {
						old_unit_price="Исходное значение: "+number_format(data["calls"][i]["old_unit_price"],2,'.','');
						price_color="#ea39fa";
					} else if (data["calls"][i]["unit_price"]>0) {
						price_color='rgb(252,155,28)';
						old_unit_price="";
					} else {
						old_unit_price="";
						price_color='white';
					}

					if (data["calls"][i]["year_edited"]!=0) {
						date_time_color="#ea39fa";
					} else {
						date_time_color="white";
					}

					//console.log(parseFloat(data["calls"][i]["office_price"].replace(".",","))+">"+(parseFloat(data["calls"][i]["price"].replace(".",","))+1));

					if (parseFloat(data["calls"][i]["office_price"].replace(",","."))>parseFloat(data["calls"][i]["price"].replace(",","."))+1) {
						price_color="yellow";
					}

					if (parseInt(data["calls"][i]["removed"])==1) {
						removed_position_style="font-style: italic; font-weight:bold";
					} else {
						removed_position_style="";
					}

					if (operator=="bee") {
						str="<tr style='"+removed_position_style+"'>";
						str+="<td style='display:none'>"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"</td>";
						str+="<td class='date_time noExcel' style='width:107px; background:"+date_time_color+"' title='"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"' call_id="+data["calls"][i]["id"]+" year_edited='"+data["calls"][i]["year_edited"]+"' month_edited='"+data["calls"][i]["month_edited"]+"'>"+(data["calls"][i]["call_date_form"]+" "+data["calls"][i]["call_time"])+"</td>";
						str+="<td style='width:61px' class='fakt'>"+data["calls"][i]["call_length"]+"</td>";
						str+="<td style='width:61px' class='call_length'>"+data["calls"][i]["value"]+"</td>";
						str+="<td style='width:116px' class='phone' title='"+data["calls"][i]["phone"]+"'><div class='short_text'>"+data["calls"][i]["phone"]+"</div></td>";
						str+="<td style='width:31px; background:#96f28f'>"+data["calls"][i]["direction"]+"</td>";
						str+="<td style='width:201px' class='call_type' title='"+data["calls"][i]["call_type"]+"'><div class='short_text'>"+data["calls"][i]["call_type"]+"</div></td>";
						str+="<td style='width:306px' class='service' title='"+data["calls"][i]["service"]+"'><div class='short_text'>"+data["calls"][i]["service"]+"</div></td>";
						str+="<td style='width:60px; background:"+price_color+"' class='price' title="+data["calls"][i]["office_price"]+">"+data["calls"][i]["price"].replace(".",",")+"</td>";
						str+="<td style='width:60px; background:"+price_color+"' class='unit_price' title='"+old_unit_price+"'>"+data["calls"][i]["unit_price"].replace(".",",")+"</td>";
						str+="<td style='width:60px;'>"+data["calls"][i]["bs_number"]+"</td>";
						str+="<td style='width:60px;'></td>";
						str+="<td style='width:80px;' class='type'>"+data["calls"][i]["type"]+"</td></tr>";
					} else if (operator=="mts") {
						str="<tr style='"+removed_position_style+"'>";
						str+="<td style='display:none'>"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"</td>";
						str+="<td class='date_time noExcel' style='width:108px; background:"+date_time_color+"; background:"+date_time_color+"' title='"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"' call_id="+data["calls"][i]["id"]+" year_edited='"+data["calls"][i]["year_edited"]+"' month_edited='"+data["calls"][i]["month_edited"]+"'>"+(data["calls"][i]["call_date_form"]+" "+data["calls"][i]["call_time"])+"</td>";
						str+="<td style='width:75px'>"+parseInt(data["calls"][i]["value"])+"</td>";
						str+="<td style='width:75px' class='call_length fakt'>"+number_format(data["calls"][i]["call_length"],2,'.',' ')+"</td>";
						str+="<td style='width:130px' class='phone' title='"+data["calls"][i]["phone"]+"'><div class='short_text'>"+data["calls"][i]["phone"]+"</div></td>";
						str+="<td style='width:30px; background:#96f28f'>"+data["calls"][i]["direction"]+"</td>";
						str+="<td style='width:180px' class='call_type' title='"+data["calls"][i]["call_type"]+"'><div class='short_text'>"+data["calls"][i]["call_type"]+"</div></td>";
						str+="<td style='width:285px' class='service' title='"+data["calls"][i]["service"]+"'><div class='short_text'>"+data["calls"][i]["service"]+"</div></td>";
						str+="<td style='width:60px; background:"+price_color+"' class='price' title="+data["calls"][i]["office_price"]+">"+data["calls"][i]["price"]+"</td>";
						str+="<td style='width:60px; background:"+price_color+"' class='unit_price' title='"+old_unit_price+"'>"+data["calls"][i]["unit_price"]+"</td>";
						str+="<td style='width:60px;'><div class='short_text'>"+data["calls"][i]["unit"]+"</div></td>";
						str+="<td style='width:100px;'><div class='short_text' title='"+data["calls"][i]["service_provider"]+"'>"+data["calls"][i]["service_provider"]+"</div></td>";
						str+="<td style='width:20px;'></td>";
						str+="<td style='width:20px;'></td>";
						str+="<td style='width:40px;'>"+data["calls"][i]["gmt"]+"</td>";
						str+="<td style='width:81px;' class='type'>"+data["calls"][i]["type"]+"</td></tr>";
					} else if (operator=="meg") {
						str="<tr style='"+removed_position_style+"'>";
						str+="<td style='display:none'>"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"</td>";
						str+="<td class='date_time noExcel' style='width:107px; background:"+date_time_color+"' title='"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"' call_id="+data["calls"][i]["id"]+" year_edited='"+data["calls"][i]["year_edited"]+"' month_edited='"+data["calls"][i]["month_edited"]+"'>"+(data["calls"][i]["call_date_form"]+" "+data["calls"][i]["call_time"])+"</td>";
						str+="<td style='width:66px' class='fakt'>"+data["calls"][i]["call_length"]+"</td>";
						str+="<td style='width:66px' class='call_length'>"+number_format(data["calls"][i]["value"], 2, '.', ' ')+"</td>";
						str+="<td style='width:96px' class='phone' title='"+data["calls"][i]["phone"]+"'><div class='short_text'>"+data["calls"][i]["phone"]+"</div></td>";
						str+="<td style='width:31px; background:#96f28f'>"+data["calls"][i]["direction"]+"</td>";
						str+="<td style='width:191px' class='call_type' title='"+data["calls"][i]["call_type"]+"'><div class='short_text'>"+data["calls"][i]["call_type"]+"</div></td>";
						str+="<td style='width:190px' class='service' title='"+data["calls"][i]["service"]+"'><div class='short_text'>"+data["calls"][i]["service"]+"</div></td>";
						str+="<td style='width:60px; background:"+price_color+"' class='price' title="+data["calls"][i]["office_price"]+">"+data["calls"][i]["price"]+"</td>";
						str+="<td style='width:60px; background:"+price_color+"' class='unit_price' title='"+old_unit_price+"'>"+data["calls"][i]["unit_price"]+"</td>";
						str+="<td style='width:65px;'>"+data["calls"][i]["unit"]+"</td>";
						str+="<td style='width:191px;'>"+data["calls"][i]["service_provider"]+"</td>";
						str+="<td style='width:60px;'></td>";
						str+="<td style='width:60px;'></td>";
						str+="<td style='width:80px;' class='type'>"+data["calls"][i]["type"]+"</td></tr>";
					} else if (operator=="tele2") {
						str="<tr style='"+removed_position_style+"'>";
						str+="<td style='display:none'>"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"</td>";
						str+="<td class='date_time noExcel' style='width:105px; background:"+date_time_color+"' title='"+data["calls"][i]["call_date"]+" "+data["calls"][i]["call_time"]+"' call_id="+data["calls"][i]["id"]+" year_edited='"+data["calls"][i]["year_edited"]+"' month_edited='"+data["calls"][i]["month_edited"]+"'>"+(data["calls"][i]["call_date_form"]+" "+data["calls"][i]["call_time"])+"</td>";
						str+="<td style='width:60px' class='fakt'>"+data["calls"][i]["call_length"]+"</td>";
						str+="<td style='width:60px' class='call_length'>"+data["calls"][i]["value"]+"</td>";
						str+="<td style='width:100px' class='phone' title='"+data["calls"][i]["phone"]+"'><div class='short_text'>"+data["calls"][i]["phone"]+"</div></td>";
						str+="<td style='width:30px; background:#96f28f'>"+data["calls"][i]["direction"]+"</td>";
						str+="<td style='width:285px' class='call_type' title='"+data["calls"][i]["call_type"]+"'><div class='short_text'>"+data["calls"][i]["call_type"]+"</div></td>";
						str+="<td style='width:305px' class='service' title='"+data["calls"][i]["service"]+"'><div class='short_text'>"+data["calls"][i]["service"]+"</div></td>";
						str+="<td style='width:60px; background:"+price_color+"' class='price' title="+data["calls"][i]["office_price"]+">"+data["calls"][i]["price"]+"</td>";
						str+="<td style='width:60px; background:"+price_color+"' class='unit_price' title='"+old_unit_price+"'>"+data["calls"][i]["unit_price"]+"</td>";
						str+="<td style='width:60px;'></td>";
						str+="<td style='width:60px;'></td>";
						str+="<td style='width:60px;'></td>";
						str+="<td style='width:77px;' class='type'>"+data["calls"][i]["type"]+"</td></tr>";
					}

					$("#calls_list tbody").append(str);
				}

				if (to_excel_option==1) {
					to_excel('calls_list', 'Детализация КЧ'+tel_nom);
				}

				$("#calls_list").parent().on("scroll", function(){
					if ($(this).scrollTop() + $(this).height() >= $(this).find("table").height()-100 && !data_loading) {
			        	$(this).unbind("scroll");
			        	eval($(this).find("table").attr("function"));
			        }
				});

				$("#calls_list .date_time").on("click", function(){
					call_id=$(this).attr("call_id");

					parts=$(this).attr("title").split(" ");
					call_date=parts[0];
					call_time=parts[1];
					call_type=$(this).parent().find(".call_type").attr("title");
					service=$(this).parent().find(".service").attr("title");
					phone=$(this).parent().find(".phone").attr("title");


					year_edited=$(this).attr("year_edited");
					month_edited=$(this).attr("month_edited");

					if ($("#call_period_form").length>0) {
	 					if ($("#call_period_form .return_call_period_btn").is(":visible") && year_edited.length==1) {//Если редактируем уже изменённый период
							return false;
						} else if ($("#call_period_form .return_call_period_btn").is(":visible")==false && year_edited.length>1) {//Если редактируем не изменённый период
							return false;
						}
					}

					$(this).addClass("red");

					change_call_period_form_show(call_id, call_date, call_time, call_type, service, phone, year_edited, month_edited);
				});

				$("#calls_list .unit_price").mousedown(function(event){
					if (event.which==1) {
						if ($("#price_form").length>0) {
							return false;
						}

						$(this).addClass("edit");

						call_id=$(this).parent().find(".date_time").attr("call_id");
						date_time=$(this).parent().find(".date_time").attr("title");

						parts=date_time.split(" ");
						date=parts[0];
						time=parts[1];
						call_length=$(this).parent().find(".fakt").text().replace(" ","");
						phone=$(this).parent().find(".phone").text();
						call_type=$(this).parent().find(".call_type").attr("title");
						service=$(this).parent().find(".service").attr("title");
						current_value=$(this).text();
						if ($(this).attr("title")=="") {
							old_unit_price=$(this).text();
						} else {
							old_unit_price=$(this).attr("title").replace("Исходное значение: ","");
						}

						show_call_price_form(call_length, date, time, phone, call_type, service, old_unit_price, current_value, call_id);
					}
				});

				data_loading=false;
				$(".loading2").hide();
			}
		});
	}

	function to_excel(tbl_name, name) {
		$("#"+tbl_name).table2excel({
			exclude: ".noExcel",
			name: name+" "+operator,
			filename: operator+"_dinamic"
		});
	}

	function change_call_period_form_show(call_id, call_date, call_time, call_type, service, phone, year_edited, month_edited) {
		if ($("#call_period_form").length>0) {
			append_call_for_change_period(call_id, call_date, call_time, call_type, service, phone);
		} else {
			monthes=Array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');

			txt="<div id='call_period_form' style='padding-top:10px; width:550px; height:auto; padding-bottom: 10; position: absolute; z-index: 1000; top:400px; left:500px; background:grey; border-radius:10px; border:2px solid black'>";

			if (year_edited!=0) {
				txt+="<table style='margin:0 auto; width:90%'>";
				txt+="<tr>";
				txt+="<td style='color:white'>Учтён в</td>";
				txt+="<td style='color:white'>"+year_edited+"</td>";
				txt+="<td style='color:white'>"+monthes[month_edited-1]+"</td>";
				txt+="</tr>";
				txt+="</table>";
			}

			txt+="<table>";
			txt+="<tr>";
			txt+="<td style='text-align:center; border:0'>";
			txt+="<span class='period_month_down'>&#9668;</span>";

			txt+="<select class='period_year' style='margin-top:5px'>";
			txt+="</select>";

			txt+="<select class='period_month'>";
			txt+="</select>";

			txt+="<span class='period_month_up'>&#9658;</span><BR>";

			txt+="<div style='margin-top:10px'>";

			txt+="<table class='phones_for_change_period'>";
			txt+="</table>";

			txt+="<BR><button onclick='change_call_period(1)'>Поменять</button>";

			if (year_edited!=0) {
				txt+="<button onclick='change_call_period(2)' class='return_call_period_btn'>Вернуть</button>";
			}

			txt+="<button class='btn_close'>Закрыть</button>";
			txt+="</div>";
			txt+="</td>";

			txt+="<td style='text-align:center; vertical-align: top; border:0'>";
			txt+='<div style="width:100%; font-weight:bold">Из файла</div>';
			txt+='<div style="margin-left: 5px">';
			txt+='<form name="uploader" enctype="multipart/form-data" method="POST">';
	        txt+='<input name="uploadImage" id="upload_file" type="file" accept=".csv"/>'
	        txt+='<input type="hidden" id="action" name="action" value="change_call_period"/>';
			txt+='<input type="hidden" id="hash" name="hash" value=""/>';
	        txt+='</form>';
	    	txt+="</div>";
			txt+="<button onclick='file_upload()'>Поменять всё</button><BR>";
			txt+='<span class="msg" style="font-weight:bold; color:white"></span>';
			txt+="</td>";
			txt+="</tr>";
			txt+="</table>";

			txt+="</div>";

			$("body").append(txt);

			append_call_for_change_period(call_id, call_date, call_time, call_type, service, phone);

			parts=call_date.split("-");
			current_year=parseInt(parts[0]);
			current_month=parseInt(parts[1])-1;

			for (month=0; month<12; month++) {
				if (month==current_month) {
					$("#call_period_form").find("select.period_month").append("<option selected value='"+(month+1)+"'>"+monthes[month]+"</option>");
				} else {
					$("#call_period_form").find("select.period_month").append("<option value='"+(month+1)+"'>"+monthes[month]+"</option>");
				}
			}

			for (year=current_year+1; year>2017; year--) {
				if (year==current_year) {
					$("#call_period_form").find("select.period_year").append("<option selected value='"+year+"'>"+year+"</option>");
				} else {
					$("#call_period_form").find("select.period_year").append("<option value='"+year+"'>"+year+"</option>");
				}
			}

			$("#call_period_form").draggable();

			$("#call_period_form .btn_close").on("click", function() {
				$("#call_period_form .phones_for_change_period td.call").each(function(){
					call_id=$(this).attr("call_id");

					$("#calls_list td[call_id='"+call_id+"']").removeClass("red");
				});

				$("#call_period_form").remove();
			});

			$(".period_month_down").click(function(){
				form=$("#call_period_form");

				selected_month=form.find(".period_month option:selected").val();
				selected_year=form.find(".period_year option:selected").val();

				if (selected_month==1) {
					if (selected_year>2018) {
						console.log(selected_year);
						form.find(".period_month option").prop('selected', false);
						form.find(".period_year option").prop('selected', false);

						form.find(".period_year option[value='"+(selected_year-1)+"']").prop('selected', true);
						form.find(".period_month option[value='12']").prop('selected', true);
					}
				} else {
					form.find(".period_month option").prop('selected', false);
					form.find(".period_month option[value='"+(selected_month-1)+"']").prop('selected', true);
				}
			});

			$(".period_month_up").click(function(){
				form=$("#call_period_form");

				selected_month=parseInt(form.find(".period_month option:selected").val());
				selected_year=parseInt(form.find(".period_year option:selected").val());
				date=new Date();

				current_year=date.getFullYear();

				if (selected_month==12) {
					if (selected_year!=current_year) {
						form.find(".period_month option").prop('selected', false);
						form.find(".period_year option").prop('selected', false);

						form.find(".period_year option[value='"+(selected_year+1)+"']").prop('selected', true);
						form.find(".period_month option[value='1']").prop('selected', true);
					}
				} else {
					form.find(".period_month option").prop('selected', false);
					form.find(".period_month option[value='"+(selected_month+1)+"']").prop('selected', true);
				}
			});
		}
	}

	function append_call_for_change_period(call_id, call_date, call_time, call_type, service, phone) {
		already_exists=false;
		$("#call_period_form .phones_for_change_period td.call").each(function(){
			if ($(this).attr("call_id")==call_id) {
				already_exists=true;
			}
		});

		if (already_exists==true) {
			return false;
		}

		txt="<tr>";
		txt+="<td class='call' call_id="+call_id+" call_date='"+call_date+"' call_time='"+call_time+"' call_type='"+call_type+"' service='"+service+"' phone='"+phone+"'>"+call_date+" "+call_time+"</td>";
		txt+="<td><img src='/img/cancel.png' onclick='remove_call_for_change_period("+call_id+")' style='width:20px; cursor:pointer'></td>";
		txt+="</tr>";

		$("#call_period_form .phones_for_change_period").append(txt);
	}

	function remove_call_for_change_period(call_id) {
		$("#call_period_form .phones_for_change_period").find("td[call_id='"+call_id+"']").parent().remove();

		$("#calls_list td[call_id='"+call_id+"']").removeClass("red");
	}

	function file_upload() {
		if (!$("#upload_file").val()) {
			$("#call_period_form .msg").html("Файл не выбран");

			return false;
		}

		hash=localStorage["session_hash"];
		$("#hash").val(hash);
		
		var formData = new FormData($("form[name='uploader']")[0]);

		$.ajax({
		    url: '/php/upload_file.php',
		    type: "POST",
		    data: formData,
		    async: false,
		    success: function (data) {
		    	data=JSON.parse(data);

		    	if (data["status"]=="OK") {
		    		$("#call_period_form .msg").text("Файл загружен. Обработка");

		        	change_call_period_from_file();
		        } else if (data["status"]=="SESSION_ERROR") {
					window.location.href=window.location.origin+"/auth.php";
				} else {
					$("#call_period_form .msg").html(data["desc"]);
				}
		    },
		    error: function(data) {
		        $("#call_period_form .msg").html("Какая-то ошибка");
		    },
		    cache: false,
		    contentType: false,
		    processData: false
		});
	}

	function change_call_period_from_file() {
		year_edited=$("#call_period_form").find("select.period_year").find("option:selected").val();
		month_edited=$("#call_period_form").find("select.period_month").find("option:selected").val();

		$.ajax({
			url:"php/dinamic/change_call_period.php",
			data:{operator:operator, year_edited:year_edited, month_edited:month_edited, action:"save_from_file"},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				$("#call_period_form .msg").html("Изменено вызовов: "+data["desc"]);

				get_calls_list(0);
			},
			error: function(data) {
				$("#call_period_form .msg").html("Какая-то ошибка");
			}
		});
	}

	function change_call_period(action) {
		if (action==1) {
			action="save";
		} else if (action==2) {
			action="remove";
		}

		year_edited=$("#call_period_form").find("select.period_year").find("option:selected").val();
		month_edited=$("#call_period_form").find("select.period_month").find("option:selected").val();

		same_period=false;
		calls=Array();
		$("#call_period_form .phones_for_change_period td.call").each(function(){
			call_id=$(this).attr("call_id");
			call_type=$(this).attr("call_type");
			service=$(this).attr("service");
			phone=$(this).attr("phone");
			call_date=$(this).attr("call_date");
			call_time=$(this).attr("call_time");

			parts=call_date.split("-");

			if (action=="save") {
				if (year_edited==parts[0] && month_edited==parseInt(parts[1])) {
					$("#call_period_form .msg").html("Выбран тот же период.<BR>Дата вызова: "+call_date);
					same_period=true;
				}
			}

			calls.push({
				"call_id":call_id,
				"call_type":call_type,
				"service":service,
				"phone":phone,
				"call_date":call_date,
				"call_time":call_time
			});
		});

		if (same_period==true || calls.length==0) {
			return false;
		}

		$.ajax({
			url:"php/dinamic/change_call_period.php",
			data:{tel_nom:tel_nom, operator:operator, year_edited:year_edited, month_edited:month_edited, calls:JSON.stringify(calls), action:action},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				$("#call_period_form .msg").html("Изменено вызовов: "+data["desc"]);

				$("#call_period_form .phones_for_change_period").empty();

				get_calls_list(0);
			},
			error: function(data) {
				$("#call_period_form .msg").html("Какая-то ошибка");
			}
		});
	}

	function show_call_price_form(call_length, date, time, phone, call_type, service, old_unit_price, current_value, call_id) {
		txt="<div id='price_form' style='padding-top:10px; width:280px; height:100px; position: absolute; z-index: 1000; top:500px; left:500px; background:grey; border-radius:10px; border:2px solid black' call_id="+call_id+" call_length='"+call_length+"' date='"+date+"' time='"+time+"' phone='"+phone+"' call_type='"+call_type+"' service='"+service+"' old_unit_price='"+old_unit_price+"'>";
		txt+="<input class='unit_price' style='width:100px; text-align:center' value='"+current_value+"'>";
		txt+="<div style='margin-top:5px'>";
		txt+="<button class='modify' action='save'>Сохранить</button>";
		txt+="<button class='modify' action='remove' style='margin-left:10px; margin-left:10px'>Удалить</button>";
		txt+="<button class='close_btn' style='margin-left:10px; margin-left:10px'>Закрыть</button>";
		txt+="</div>";
		txt+="<span class='msg' style='font-weight:bold; color:red'></span>";
		txt+="</div>";

		$("body").append(txt);

		$("#price_form").draggable();

		$("#price_form input").click(function(){
			$(this).val("");
		});

		$("#price_form button.modify").click(function(){
			$(this).prop('disabled', true);

			action=$(this).attr("action");

			unit_price=$("#price_form").find(".unit_price").val().replace(",",".");

			call_id=$("#price_form").attr("call_id");
			call_length=$("#price_form").attr("call_length");
			date=$("#price_form").attr("date");
			time=$("#price_form").attr("time");
			phone=$("#price_form").attr("phone");
			call_type=$("#price_form").attr("call_type");
			service=$("#price_form").attr("service");
			old_unit_price=$("#price_form").attr("old_unit_price");

			$.ajax({
				url:"php/dinamic/set_call_price.php",
				data:{tel_nom:tel_nom, operator:operator, call_length:call_length, date:date, time:time, phone:phone, call_type:call_type, service:service, unit_price:unit_price, old_unit_price:old_unit_price, action:action},
				type:"POST",
				success: function(data) {
					if (data=="OK") {
						if (action=="save") {
							call_id=$("#price_form").attr("call_id");
							unit_price=parseFloat($("#price_form").find(".unit_price").val());
							call_length=parseFloat($("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".call_length").text());
							unit=$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".unit").text();
							type=$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".type").text();

							if (operator=="mts") {
								if (type.indexOf("GPRS")>-1) {
									price=Math.ceil(unit_price*call_length/1024);
								} else if (unit=="секунда") {
									price=unit_price*Math.ceil(call_length/60);
								} else {
									price=unit_price*call_length;
								}
							} else {
								price=unit_price*call_length;
							}

							$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".unit_price").removeClass("edit");
							$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".unit_price").text(number_format(unit_price,2,'.',''));
							$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".unit_price").attr("title","Исходное значение: "+old_unit_price);
							$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".unit_price").css("background", "#ea39fa");
							$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".price").text(number_format(price,2,'.',''));

							if (unit_price>0) {
								price_color='#ea39fa';
							} else {
								price_color='white';
							}

							$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".price").css("background", price_color);
						} else {
							get_calls_list(0);
						}

						$("#price_form").remove();
					} else {
						$("#price_form").find(".msg").text("НЕ исправлено");
					}
				}
			});
		});

		$("#price_form button.close_btn").click(function(){
			call_id=$("#price_form").attr("call_id");
			$("#calls_list").find("td[call_id='"+call_id+"']").parent().find(".unit_price").removeClass("edit");
			$("#price_form").remove();
		});
	}

	function set_phone_exception() {
		exception=$("#calls_list .edit").attr("title");

		$.ajax({
			url:"php/set_phone_exception.php",
			data:{exception:exception},
			type:"POST",
			success: function(data) {
				$("#calls_list .edit").removeClass("edit");

				txt="<div id='set_exception_form' style='width:280px; height:60px; position: absolute; z-index: 1000; top:300px; left:500px; background:grey; color:white; border-radius:10px; border:2px solid black'>";
				txt+=data;
				txt+="<BR><button>OK</button>";
				txt+="</div>";

				$("body").append(txt);

				$("#set_exception_form button").bind("click", function(){
					$("#set_exception_form").remove();
				});
			}
		});
	}

	function calls_list_resize(direction) {
		if (direction==1) {
			old_details_height=$("#dinamic_details_tbl").parent().height();
			$("#dinamic_details_tbl").parent().animate({height:0}, 700);
			$("#monthes_history_tbl").parent().animate({height:0}, 700);
			old_height=$("#calls_list").parent().height();
			$("#calls_list").parent().animate({height:old_height+old_details_height}, 700);
		} else if (direction==2) {
			old_calls_height=$("#calls_list").parent().height();
			$("#calls_list").parent().animate({height:0}, 700);
			old_height=$("#dinamic_details_tbl").parent().height();
			$("#dinamic_details_tbl").parent().animate({height:old_height+old_calls_height}, 700);
			$("#monthes_history_tbl").parent().animate({height:old_height+old_calls_height}, 700);		
		} else {
			body_height=document.body.clientHeight;
			$("#dinamic_details_tbl").parent().animate({height:body_height*0.4},700);
			$("#monthes_history_tbl").parent().animate({height:body_height*0.4},700);
			//if (document.body.clientHeight>880) {
				$("#calls_list").parent().animate({height:body_height*0.31},700);
			/*} else {
				$("#calls_list").parent().animate({height:body_height-430},700);
			}*/
		}
	}

	function get_detal_totals(re_calculate) {
		$(".loading2").show();

		year=$("#detal_totals_year option:selected").val();
		month=$("#detal_totals_month option:selected").val();

		var monthStart = new Date(year, month-1, 1);
		var monthEnd = new Date(year, month, 1);
		var day = addZero((monthEnd - monthStart) / (1000 * 60 * 60 * 24));

		//$("#calls_list_from_date").val("");
		$("#calls_list_to_date").val(year+"-"+addZero(month)+"-"+day);

		get_calls_list(0);

		if (typeof re_calculate != 'undefined') {
			re_calculate=1;
		} else {
			re_calculate=0;
		}

		$.ajax({
			url: "/php/dinamic/get_detal_totals_new.php",
			data:{tel_nom:tel_nom, operator:operator, year:year, month:month, re_calculate:re_calculate},
			type:"POST",
			success: function(data) {
				if (data.length==0) {
					return false;

					tables_loaded_count++;
				}

				data=JSON.parse(data);

				$("#detal_totals tbody .filter").unbind("click");
				$("#detal_totals tbody").empty();

				console.log(data);

				for (i=0;i<data["result"].length;i++) {
					if (typeof data["kefs"][data["result"][i]["type"]] !=='undefined' && data["kefs"][data["result"][i]["type"]][0]["kef"]!=1) {
						color="#ea39fa";
						kef=data["kefs"][data["result"][i]["type"]][0]["kef"];
					} else {
						color='';
						kef=1;
					}

					txt="<tr>";
					txt+="<td><input type='checkbox' type_nom='"+data["result"][i]["type_nom"]+"'/></td>";
					txt+="<td class='filter' type_nom='"+data["result"][i]["type_nom"]+"'>"+data["result"][i]["type"]+"</td>";
					txt+="<td>"+data["result"][i]["value"]+"</td>";
					txt+="<td class='change_kef' title='КЭФ: "+kef+"' style='cursor:pointer; background:"+color+"'>"+data["result"][i]["total_price"]+"</td>";
					txt+="</tr>";

					$("#detal_totals tbody").append(txt);
				}

				$("#detal_totals tbody .change_kef").bind("click", function(){
					call_type=$(this).parent().find(".filter").text();

					save_call_type_kef_form_show(call_type);
				});

				$("#detal_totals tbody input").bind("click", function(){
					types=Array();
					$("#detal_totals tbody input:checked").each(function(){
						types.push($(this).attr("type_nom"));
					});
					filter=types.join(",");

					$('#detal_totals').attr('filter',filter); 
					$('#calls_list').attr('page_nom',0); 

					get_calls_list(0)
				});

				$(".loading2").hide();

				tables_loaded_count++;
			}
		});
	}

	function save_call_type_kef_form_show(call_type) {
		$("#save_call_type_kef_form").remove();

		txt="<div id='save_call_type_kef_form' style='width:150px; height:100px; position: absolute; z-index: 2000; top:650px; left:1700px; background: grey; border-radius:10px; border:2px solid black'>";
		txt+="<div style='width:100%; text-align:right'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></div>";
		txt+="<input id='call_type_kef' style='width:100px; margin-top:5px; text-align:center' placeholder='Коэффициент' value=''>";
		txt+="<button call_type='"+call_type+"' style='margin-top:5px'>Сохранить</button>";
		txt+="</div>";

		$("body").append(txt);

		$("#save_call_type_kef_form").draggable();

		$("#call_type_kef").focus();

		$("#save_call_type_kef_form .btn_close").click(function(){
			$("#save_call_type_kef_form").remove();
		});

		$("#save_call_type_kef_form button").click(function(){
			call_type=$(this).attr("call_type");

			save_call_type_kef(call_type);
		});
	}

	function save_call_type_kef(call_type) {
		kef=$("#call_type_kef").val().replace(",",".");

		$.ajax({
			url: "/php/dinamic/save_call_type_kef.php",
			data:{tel_nom:tel_nom, call_type:call_type, kef:kef},
			type:"POST",
			success: function(data) {
				$("#save_call_type_kef_form").remove();

				get_detal_totals(1);
			}
		});
	}

	function addZero(d) {
		if (d<10) {
			return "0"+d;
		} else {
			return d;
		}
	}

	function clear_detal_filters() {
		if ($("#clear_detal_filters").is(":checked")) {
			$("#detal_totals tbody input").prop("checked", true);
		} else {
			$("#detal_totals tbody input").prop("checked", false);
		}
		types=Array();
		$("#detal_totals tbody input:checked").each(function(){
			types.push($(this).attr("type_nom"));
		});
		filter=types.join(",");

		$('#detal_totals').attr('filter',filter); 
		$('#calls_list').attr('page_nom',0); 

		get_calls_list(0);
	}

	function get_client_services() {
		$.ajax({
			url:"/php/get_client_services.php",
			data:{operator:operator, tel_nom:tel_nom},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				$("#client_services_tbl tbody").empty();
				old_status="";
				for (i=0; i<data.length; i++) {				
					txt="<tr style='background:"+data[i]["color"]+"' code='"+data[i]["code"]+"'>";
					
					if (old_status!=data[i]["status"] && i>0) {
						txt+="<td class='service_name' style='border-top:2px solid black; cursor:pointer'><div class='short_text' title='"+data[i]["service"]+"'>"+data[i]["service"]+"</div></td>";
						txt+="<td style='border-top:2px solid black'>"+data[i]["status"]+"</td>";
						txt+="<td style='border-top:2px solid black'>"+data[i]["price"]+"</td>";
						txt+="<td style='border-top:2px solid black'>"+data[i]["kef"]+"</td>";
						txt+="<td style='border-top:2px solid black'>"+data[i]["date"]+"</td></tr>";
					} else {
						if (data[i]["status"]=="Активная") {
							class_name="class='kef'";
						} else {
							class_name="";
						}

						txt+="<td class='service_name' style='cursor:pointer'><div class='short_text' title='"+data[i]["service"]+"'>"+data[i]["service"]+"</div></td>";
						txt+="<td>"+data[i]["status"]+"</td>";
						txt+="<td>"+data[i]["price"]+"</td>";
						txt+="<td "+class_name+">"+data[i]["kef"]+"</td>";
						txt+="<td>"+data[i]["date"]+"</td></tr>";
					}

					old_status=data[i]["status"];

					$("#client_services_tbl tbody").append(txt);
				}

				$("#client_services_tbl .service_name").bind("click", function(){
					code=$(this).parent().attr("code");

					get_services_history(code);
				});

				$("#client_services_tbl .kef").bind("click", function(){
					$("#save_service_kef_form").remove();

					code=$(this).parent().attr("code");
					kef=$(this).text();

					txt="<div id='save_service_kef_form' style='width:150px; height:100px; position: absolute; z-index: 1000; top:300px; left:1500px; background: grey; border-radius:10px; border:2px solid black'>";
					txt+="<div style='width:100%; text-align:right'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></div>";
					txt+="<input id='service_kef' style='width:100px; margin-top:5px; text-align:center' placeholder='Коэффициент' value=''>";
					txt+="<button onclick='save_service_kef("+code+", 0)' style='margin-top:5px'>Сохранить</button>";
					txt+="</div>";

					$("body").append(txt);

					$("#save_service_kef_form").draggable();

					$("#service_kef").focus();

					$("#save_service_kef_form .btn_close").click(function(){
						$("#save_service_kef_form").remove();
					});
				});

				tables_loaded_count++;
			}
		});
	}

	function get_services_history(service_code) {
		$.ajax({
			url:"/php/get_services_history.php",
			data:{tel_nom:tel_nom, service_code:service_code},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				//$("#service_history_form").remove();

				txt="<div id='service_history_form' service_code="+service_code+" style='width:400px; height:430px; position: absolute; z-index: 1000; top:300px; left:1420px; background: grey; border-radius:10px; border:2px solid black'>";
				txt+="<div style='width:100%; text-align:right'><img src='/img/cancel.png' style='width:24px; cursor:pointer'></div>";
				txt+="<table style='margin: 0 auto; width: 90%;'>";
				txt+="<thead style='font-size:12px'><th>УСЛУГА</th><th>ПОДКЛЮЧЕНИЕ</th><th>ОТКЛЮЧЕНИЕ</th><th>КЭФ</th></thead>";

				for (i=0;i<data.length;i++) {
					if (data[i]["finish_date"]=="2030-01-01") {
						finish_date="";
					} else {
						finish_date=data[i]["finish_date"];
					}

					txt+="<tr row_nom="+i+" code='"+service_code+"' history_id="+data[i]["history_id"]+">";
					txt+="<td style='width:140px; font-size:13px; color:white'>"+data[i]["service"]+"</td>";
					txt+="<td class='start_date' style='font-size:13px; text-align:center; cursor:pointer; color:white'>"+data[i]["start_date"]+"</td>";
					txt+="<td class='finish_date' style='font-size:13px; text-align:center; cursor:pointer; color:white'>"+finish_date+"</td>";
					txt+="<td class='kef' style='font-size:13px; text-align:center; cursor:pointer; color:white'>"+data[i]["kef"]+"</td>";
					txt+="<td style='width:20px; border:0'><img src='/img/cancel.png' style='width:18px; cursor:pointer' onclick='remove_service_history("+data[i]["history_id"]+","+service_code+")' title='Удалить'></td>";
					txt+="</tr>"
				}

				txt+="</table>";
				txt+="</div>";

				$("body").append(txt);

				$("#service_history_form").draggable();

				$("#service_history_form td.start_date, #service_history_form td.finish_date").bind("click", function(){
					history_id=$(this).parent().attr("history_id");
					date_type=$(this).attr("class");
					current_date=$(this).text();

					txt="<div id='service_date_form' history_id="+history_id+" date_type='"+date_type+"' style='width:176px; height:245px; position: absolute; z-index: 1000; top:380px; left:1600px; background: gainsboro; border-radius:10px; border:2px solid black'>";
					txt+="<table>";
					txt+="<tr><td style='text-align:right; border:0'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></td></tr>";
					txt+="<tr><td style='text-align:center; border:0'><div class='service_date' style='width:170px; margin-top:5px;'></div></td></tr>";
					if (date_type=="finish_date") {
						txt+="<tr><td style='text-align:center; border:0'><button class='btn_remove' style='margin-top:5px; font-size:10px; border-radius:4px'>Удалить</button></td></tr>";
					}
					txt+="</table>";
					txt+="</div>";

					$("body").append(txt);

					$("#service_date_form .btn_close").click(function(){
						$("#service_date_form").remove();
					});

					$("#service_date_form .btn_remove").click(function(){
						save_service_date("");
					});

					$("#service_date_form .service_date").datepicker({
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

							save_service_date(new_date);
						}
					});

					if (current_date=="") {
						date=new Date();
						date=date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();
						$("#service_date_form .service_date").datepicker("setDate", date);
					} else {
						$("#service_date_form .service_date").datepicker("setDate", current_date);
					}
				});

				$("#service_history_form td.kef").bind("click", function(){
					$("#save_service_kef_form").remove();

					code=$(this).parent().attr("code");
					row_nom=$(this).parent().attr("row_nom");
					kef=$(this).text();
					history_id=$(this).parent().attr("history_id");

					txt="<div id='save_service_kef_form' style='width:150px; height:100px; position: absolute; z-index: 2000; top:300px; left:1500px; background: grey; border-radius:10px; border:2px solid black'>";
					txt+="<div style='width:100%; text-align:right'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></div>";
					txt+="<input id='service_kef' style='width:100px; margin-top:5px; text-align:center' placeholder='Коэффициент' value=''>";
					txt+="<button onclick='save_service_kef("+code+","+history_id+","+row_nom+")' style='margin-top:5px'>Сохранить</button>";
					txt+="</div>";

					$("body").append(txt);

					$("#save_service_kef_form").draggable();

					$("#service_kef").focus();

					$("#save_service_kef_form .btn_close").click(function(){
						$("#save_service_kef_form").remove();
					});
				});

				$("#service_history_form img").bind("click", function(){
					$("#service_history_form").remove();
				});
			}
		});
	}

	function remove_service_history(history_id, service_code) {
		$.ajax({
			url:"/php/remove_service_history.php",
			data:{tel_nom:tel_nom, id:history_id, service_code:service_code},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					$("#service_history_form tr[history_id="+history_id+"]").remove();
					get_client_services();
				}
			}
		});
	}

	function save_service_date(new_date) {
		service_code=$("#service_history_form").attr("service_code");
		history_id=$("#service_date_form").attr("history_id");
		date_type=$("#service_date_form").attr("date_type");

		$("#service_history_form tr[history_id="+history_id+"]").find("."+date_type).html(new_date);

		start_date=$("#service_history_form tr[history_id="+history_id+"]").find(".start_date").html();
		finish_date=$("#service_history_form tr[history_id="+history_id+"]").find(".finish_date").html();

		if (finish_date=="") {
			finish_date="2030-01-01";
		}

		$.ajax({
			url:"/php/save_service_date.php",
			data:{tel_nom:tel_nom, history_id:history_id, service_code:service_code, start_date:start_date, finish_date:finish_date},
			type:"POST",
			success: function(data) {
				//get_services_history(service_code);
				get_client_services();

				month_calculate_form_show();

				date_type=$("#service_date_form").attr("date_type");
				date=$("#service_history_form tr[history_id="+history_id+"]").find("."+date_type).html();

				parts=date.split("-");
				$("#month_calculate_form select.calc_year option[value='"+parts[0]+"']").attr("selected", "selected");
				$("#month_calculate_form select.calc_month option[value='"+parseInt(parts[1])+"']").attr("selected", "selected");

				$("#service_date_form").remove();
			}
		});
	}

	function save_service_kef(code, history_id, row_nom) {
		kef=$("#service_kef").val().replace(",",".");

		$("#save_service_kef_form").remove();

		$.ajax({
			url:"/php/save_service_kef.php",
			data:{tel_nom:tel_nom, code:code, kef:kef, history_id:history_id, row_nom:row_nom},
			type:"POST",
			success: function(data) {
				$("#service_history_form tr[history_id="+history_id+"]").find(".kef").html(kef);
				get_client_services();
			}
		});
	}

	function service_on_off_form_show(on) {
		if ($(".loading2").is(":visible")) {
			return false;
		}

		$(".loading2").show();

		if (on==1) {
			$.ajax({
				url:"/php/available_services.php",
				data:{operator:operator, tel_nom:tel_nom},
				type:"POST",
				success: function(data) {
					$(".loading2").hide();

					data=JSON.parse(data);

					$("#service_on_off_form").remove();

					txt="<div id='service_on_off_form' style='width:800px; height:600px; position: absolute; z-index: 1000; top: 25%; left: 30%; background: grey; border-radius:10px; border:2px solid black'>";
					txt+="<table style='width:795px; text-align:right'>";
					txt+="<tr><td style='border:0'>Поиск:</td><td style='width: 210px; border:0'><input style='background:yellow; border-radius:5px' value=''/></td>";
					txt+="<td style='width: 40px; border:0; text-align:center'>С:</td>";
					txt+="<td style='width: 50px; border:0; text-align:center'><img style='width:25px; cursor:pointer' src='img/calendar.png' class='calendar'></td>";
					txt+="<td style='border:0px; width:115px;'><input id='service_start_date' class='calendar' style='width:110px; cursor:pointer; text-align:center'/></td>";
					txt+="<td style='text-align:left; border:0'><button>Подключить</button></td>";
					txt+="<td style='border:0'><img class='btn_close_form' src='img/cancel.png' style='width:30px; cursor:pointer' title='Закрыть'></td></tr></table>";

					txt+="<div style='margin-left:15px'>";
					txt+="<table style='width:650px; font-size:12px'><thead><th style='width:100px;'>Код</th><th style='width:580px;'>Наименование</th><th style='width:153px;'>Цена</th></thead></table>";
					txt+="<div style='width:790px; height:510px; overflow-y:auto'>";
					txt+="<table style='width:770px; font-size:13px'>";
					txt+="<tbody>";

					for (i=0;i<data.length;i++) {
						txt+="<tr title='"+data[i]["code"]+"'><td style='width:80px; color:white'>"+data[i]["code"]+"</td><td style='width:450px; color:white'>"+data[i]["service"]+"</td><td style='width:120px; color:white'>"+data[i]["price"]+"</td><td style='width:120px;'><input type='checkbox'/></td></tr>";
					}

					txt+="</tbody>";
					txt+="</table>";
					txt+="</div>";
					txt+="</div>";

					$("body").append(txt);

					$("#service_on_off_form").draggable();

					$(".calendar").bind("click", function(){
						date_type=$(this).attr("date_type");

						txt="<div id='service_date_form' style='width:175px; height:230px; position: absolute; z-index: 1000; top:270px; left:65%; background: lightgray; border-radius:10px; border:2px solid black'>";
						txt+="<table>";
						txt+="<tr><td style='text-align:right; border:0'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></td></tr>";
						txt+="<tr><td style='text-align:center; border:0'><div class='service_date' style='width:170px; margin-top:5px;'></div></td></tr>";
						txt+="</table>";
						txt+="</div>";

						$("body").append(txt);

						date=new Date();
						date=date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();

						$("#service_date_form .service_date").datepicker({
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
							defaultDate: null,
							maxDate: date,
							prevText: '<Пред',
							nextText: 'След>',
							showButtonPanel: true,
							onSelect: function(dateText, inst) {
								date=$("#service_date_form .service_date").val();

								$("#service_start_date").val(date);

								$("#service_date_form").remove();
							}
						});

						$("#service_date_form .btn_close").click(function(){
							$("#service_date_form").remove();
						});
					});
					
					date=new Date();
					date=date.getFullYear()+"-"+addZero(date.getMonth()+1)+"-"+addZero(date.getDate());
					$("#service_start_date").val(date);

					$("#service_on_off_form input").keyup(function(e){
						find_service_by_name($(this).val());
					});

					$("#service_on_off_form button").bind("click", function(){
						form_nom=0;
						$("#service_on_off_form input:checkbox:checked").each(function(){
							form_nom++;

							code=$(this).parent().parent().find("td:eq(0)").html();
							service_name=$(this).parent().parent().find("td:eq(1)").html();
							service_start_date=$("#service_start_date").val();

							msg="";
							services_for_disable=Array();

							codes=Array();

							codes["mts"]=Array("7", "8", "25", "30");
							codes["bee"]=Array("45", "37", "36", "41");
							codes["meg"]=Array("60", "52", "51", "56");
							codes["tele2"]=Array("75", "67", "66", "71");

							if (codes[operator].indexOf(code)>-1) {
								$("#client_services_tbl").find("tr").each(function(){
									old_service_code=$(this).attr("code");
									old_service_name=$(this).find("td:eq(0)").text();
									old_service_status=$(this).find("td:eq(1)").text();

									if (old_service_status=="Активная") {
										if (codes[operator].indexOf(old_service_code)>-1) {
											msg+="Уже подключена услуга: "+old_service_name+"<BR>";

											services_for_disable.push(old_service_code);
										}
									}
								});
							}

							txt="<div id='service_on_off_accept_"+form_nom+"' service_start_date='"+service_start_date+"' code='"+code+"' service_name='"+service_name+"' style='text-align:center; width:305px; height:110px; position: absolute; z-index: 1001; top: 35%; left: 50%; background: grey; border-radius:10px; border:2px solid black'>";
							if (msg.length>0) {
								txt+="<div class='msg' style='width:96%; margin-left: 2%; color:red; font-weight:bold; background: ghostwhite;'>"+msg+"</div>";
							}
							txt+="<div style='color:white;'>Точно добавить услугу - "+service_name+"?</div>";
							txt+="<button style='margin-top:15px'>ДА!!!</button>";
							txt+="<button style='margin-top:15px; margin-left:25px''>НЕТ</button>";
							txt+="</div>";
							$("body").append(txt);

							$("#service_on_off_accept_"+form_nom).draggable();

							$("#service_on_off_accept_"+form_nom).css("top", (form_nom*15+10)+"%");

							$("#service_on_off_accept_"+form_nom).find("button:eq(1)").bind("click", function(){
								$(this).parent().remove();
							});

							$("#service_on_off_accept_"+form_nom).find("button:eq(0)").bind("click", function(){
								if ($(".loading2").is(":visible")) {
									return false;
								}

								$(".loading2").show();

								code=$(this).parent().attr("code");
								service_name=$(this).parent().attr("service_name");
								service_start_date=$(this).parent().attr("service_start_date");

								$(this).parent().remove();

								$.ajax({
									url:"/php/service_on_off.php",
									data:{tel_nom:tel_nom, operator:operator, on:on, service_start_date:service_start_date, service_name:service_name, code:code, user_name:user_name, services_for_disable:services_for_disable.join(","), hash:localStorage["session_hash"]},
									type:"POST",
									success: function(data) {
										if (data=="session_expaired") {
											location.reload();
											return false;
										}

										$("#form_ok").remove();

										if (data.indexOf("OK")>-1) {
											month_calculate_form_show();

											data=JSON.parse(data);

											parts=data["date"].split("-");
											$("#month_calculate_form select.calc_year option[value='"+parts[0]+"']").attr("selected", "selected");
											$("#month_calculate_form select.calc_month option[value='"+parseInt(parts[1])+"']").attr("selected", "selected");

											get_client_services();
										} else {
											txt="<div id='form_ok' style='text-align:center; width:305px; height:235px; position: absolute; z-index: 1000; top: 25%; left: 50%; background: grey; border-radius:10px; border:2px solid black'>";
											txt+="<div style='color:white; margin-top:15px'>"+data+"</div>";
											txt+="<button style='margin-top:15px'>ОК</button>";
											txt+="</div>";
										}

										$("#form_ok").draggable();

										$("#form_ok button").bind("click", function(){
											$("#form_ok").remove();
										});

										$(".loading2").hide();
									}
								});
							});
						});

						$("#service_on_off_form").remove();
					});

					$("#service_on_off_form .btn_close_form").bind("click", function(){
						$("#service_on_off_form").remove();
					});
				}
			});
		} else {
			$.ajax({
				url:"/php/get_client_services.php",
				data:{tel_nom:tel_nom, operator:operator},
				type:"POST",
				success: function(data) {
					$(".loading2").hide();

					data=JSON.parse(data);

					txt="<div id='service_on_off_form' style='width:800px; height:600px; position: absolute; z-index: 1000; top: 25%; left: 30%; background: grey; border-radius:10px; border:2px solid black'>";
					txt+="<table style='width:795px; text-align:right'>";
					txt+="<tr><td style='border:0'>Поиск:</td><td style='width: 210px; border:0'><input style='background:yellow; border-radius:5px' value=''/></td>";
					txt+="<td style='width: 40px; border:0; text-align:center'>С:</td>";
					txt+="<td style='width: 50px; border:0; text-align:center'><img style='width:25px; cursor:pointer' src='img/calendar.png' class='calendar'></td>";
					txt+="<td style='border:0px; width:115px;'><input id='service_finish_date' class='calendar' style='width:110px; cursor:pointer; text-align:center'/></td>";
					txt+="<td style='text-align:left; border:0'><button>Отключить</button></td>";
					txt+="<td style='border:0'><img class='btn_close_form' src='img/cancel.png' style='width:30px; cursor:pointer' title='Закрыть'></td></tr></table>";

					txt+="<table style='font-size:12px'><thead><th style='width:79px;'>Код</th><th style='width:500px;'>Наименование</th><th style='width:130px;'>Цена</th></thead></table>";
					txt+="<div style='width:790px; height:510px; overflow-y:auto'>";
					txt+="<table style='width:770px; font-size:13px'>";
					txt+="<tbody>";

					for (i=0;i<data.length;i++) {
						if (data[i]["status"]=="Активная") {
							txt+="<tr><td style='width:80px; color:white'>"+data[i]["code"]+"</td><td style='width:500px; color:white'>"+data[i]["service"]+"</td><td style='width:130px; color:white'>"+data[i]["price"]+"</td><td style='width:60px;'><input type='checkbox'/></td></tr>";
						}
					}

					txt+="</tbody>";
					txt+="</table>"
					txt+="</div>";

					$("body").append(txt);

					$("#service_on_off_form").draggable();

					$(".calendar").bind("click", function(){
						txt="<div id='service_date_form' style='width:175px; height:230px; position: absolute; z-index: 1000; top:270px; left:65%; background: lightgray; border-radius:10px; border:2px solid black'>";
						txt+="<table>";
						txt+="<tr><td style='text-align:right; border:0'><img class='btn_close' src='/img/cancel.png' style='width:24px; cursor:pointer'></td></tr>";
						txt+="<tr><td style='text-align:center; border:0'><div class='service_date' style='width:170px; margin-top:5px;'></div></td></tr>";
						txt+="</table>";
						txt+="</div>";

						$("body").append(txt);

						date=new Date();
						date=date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();

						$("#service_date_form .service_date").datepicker({
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
							defaultDate: null,
							maxDate: date,
							prevText: '<Пред',
							nextText: 'След>',
							showButtonPanel: true,
							onSelect: function(dateText, inst) {
								date=$("#service_date_form .service_date").val();

								$("#service_finish_date").val(date);

								$("#service_date_form").remove();
							}
						});

						$("#service_date_form .btn_close").click(function(){
							$("#service_date_form").remove();
						});
					});
					
					date=new Date();
					date=date.getFullYear()+"-"+addZero(date.getMonth()+1)+"-"+addZero(date.getDate());
					$("#service_finish_date").val(date);

					$(".loading2").hide();

					$("#service_on_off_form input").bind("keyup", function(){
						find_service_by_name($(this).val());
					});

					$("#service_on_off_form button").bind("click", function(){
						form_nom=0;
						$("#service_on_off_form input:checkbox:checked").each(function(){
							form_nom++;

							code=$(this).parent().parent().find("td:eq(0)").html();
							service_name=$(this).parent().parent().find("td:eq(1)").html();
							service_finish_date=$("#service_finish_date").val();

							txt="<div id='service_on_off_accept_"+form_nom+"' code='"+code+"' service_name='"+service_name+"' service_finish_date='"+service_finish_date+"' style='text-align:center; width:305px; height:110px; position: absolute; z-index: 1001; top: 35%; left: 50%; background: grey; border-radius:10px; border:2px solid black'>";
							txt+="<div style='color:white;'>Точно удалить услугу - "+service_name+"?</div>";
							txt+="<button style='margin-top:15px'>ДА!!!</button>";
							txt+="<button style='margin-top:15px; margin-left:25px''>НЕТ</button>";
							txt+="</div>";
							$("body").append(txt);

							$("#service_on_off_accept_"+form_nom).draggable();

							$("#service_on_off_accept_"+form_nom).css("top", (form_nom*15+10)+"%");

							$("#service_on_off_accept_"+form_nom).find("button:eq(1)").bind("click", function(){
								$(this).parent().remove();
							});

							$("#service_on_off_accept_"+form_nom).find("button:eq(0)").bind("click", function(){
								if ($(".loading2").is(":visible")) {
									return false;
								}

								$(".loading2").show();

								code=$(this).parent().attr("code");
								service_name=$(this).parent().attr("service_name");
								service_finish_date=$(this).parent().attr("service_finish_date");

								$(this).parent().remove();

								$.ajax({
									url:"/php/service_on_off.php",
									data:{tel_nom:tel_nom, operator:operator, on:on, code:code, service_name:service_name, service_finish_date:service_finish_date, user_name:user_name, hash:localStorage["session_hash"]},
									type:"POST",
									success: function(data) {
										if (data=="session_expaired") {
											location.reload();
											return false;
										}

										$("#form_ok").remove();

										if (data.indexOf("OK")>-1) {
											month_calculate_form_show();

											data=JSON.parse(data);

											parts=data["date"].split("-");
											$("#month_calculate_form select.calc_year option[value='"+parts[0]+"']").attr("selected", "selected");
											$("#month_calculate_form select.calc_month option[value='"+parseInt(parts[1])+"']").attr("selected", "selected");

											get_client_services();
										} else {
											txt="<div id='form_ok' style='text-align:center; width:305px; height:235px; position: absolute; z-index: 1000; top: 25%; left: 50%; background: grey; border-radius:10px; border:2px solid black'>";
											txt+="<div style='color:white; margin-top:15px'>"+data+"</div>";
											txt+="<button style='margin-top:15px'>ОК</button>";
											txt+="</div>";
										}

										$("#form_ok").draggable();

										$("#form_ok button").bind("click", function(){
											$("#form_ok").remove();
										});

										$(".loading2").hide();
									}
								});
							});
						});

						$("#service_on_off_form").remove();
					});

					$("#service_on_off_form .btn_close_form").bind("click", function(){
						$("#service_on_off_form").remove();
					});
				}
			});
		}
	}

	function get_phone_data() {
		$.ajax({
			url:"/php/get_phone_data.php",
			data:{operator:operator, tel_nom:tel_nom},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				$("#dinamic_details_header").html("ПО: <B>"+tel_nom+"</B>, к-т: <B>"+data["contract"]+"</B>, л/с: <span id='account' style='font-weight:bold'>"+data["account"]+"</span>, блокировки: <B>"+data["blocks"]+"</B>"+" <U>("+data["actual"]+")</U>");

				$("#tel_user").html(data["info"]["fio"]);

				$("#tel_clients_group").html(data["clients_group"]);
				$("#tel_clients_group").attr("title", data["clients_groups"]);

				info=JSON.parse(data["info"]["info"]);

				$("#user_info").html(info[0]["txt"]);

				tables_loaded_count++;
			}
		});
	}

	function get_client_history(append) {
		data_loading=true;

		$(".loading2").show();

		if (typeof append == 'undefined') {
			append=0;
		}

		if (append==0) {
			$("#dinamic_details_tbl tbody").empty();

			current_date=new Date();
			current_date.setDate(current_date.getDate()+1);
			to_date=current_date.getFullYear()+"-"+(current_date.getMonth()+1)+"-"+current_date.getDate();
		} else {
			to_date=$("#dinamic_details_tbl").attr("to_date");
		}

		$.ajax({
			url:"/php/get_history.php",
			data:{operator:operator, tel_nom:tel_nom, to_date:to_date},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				lastDays=Array();

				for (y=2015;y<2050;y++) {
					for (m=0;m<12;m++) {
						lastDay = new Date(y, m, 0);

						lastDays.push(lastDay.getFullYear()+"-"+addZero(lastDay.getMonth()+1)+"-"+lastDay.getDate());
					}
				}

				txt="";
				for (i=0;i<data["data"].length;i++) {
					txt+="<tr><td class='history_date' title='Добавить комментарий'>"+data["data"][i]["update_date"]+"</td>";

					if (lastDays.indexOf(data["data"][i]["update_date"])>-1) {
						txt+="<td class='calc_from_balance'>"+data["data"][i]["balance"]+"</td>";
					} else {
						txt+="<td>"+data["data"][i]["balance"]+"</td>";
					}
					
					txt+="<td class='spended_td'>"+data["data"][i]["spended"]+"</td>";

					if (data["removed_dates"].indexOf(data["data"][i]["update_date"])>-1) {
						txt+="<td class='payments_td' style='background:#ea39fa'>"+data["data"][i]["payments"]+"</td>";
					} else {
						txt+="<td class='payments_td'>"+data["data"][i]["payments"]+"</td>";
					}
					
					txt+="</tr>";

					to_date=data["data"][i]["update_date"];
				}

				$("#dinamic_details_tbl tbody").append(txt);

				$("#dinamic_details_tbl").attr("to_date", to_date);

				$(".calc_from_balance").on("click", function(){
					date=$(this).parent().find(".history_date").text();

					calc_from_balance(date);
				});

				$("#dinamic_details_tbl tbody .payments_td").on("click", function(){
					date=$(this).parent().find(".history_date").html();

					new_payment_form(date);
				});

				$("#dinamic_details_tbl tbody .history_date").on("click", function(){
					date=$(this).text();

					edit_comment_show(tel_nom, 0, date, "", "", 0, "", 0);
				});

				if (append==0) {
					tables_loaded_count++;
				}

				data_loading=false;

				$(".loading2").hide();
			}
		});
	}

	function calc_from_balance(history_date) {
		if ($("#calc_from_balance_form").is(":visible")) {
			return false;
		}
		
		balance=$("#dinamic_details_tbl .history_date:contains('"+history_date+"')").parent().find("td:eq(1)").text();

		txt="<div id='calc_from_balance_form' style='padding-top:10; text-align:center; width:300px; height:150px; position: absolute; z-index: 1000; top: 25%; left: 30%; background: grey; border-radius:10px; border:2px solid black'>";
		txt+="<input class='balance' value='"+balance+"' style='text-align:center' history_date='"+history_date+"'/><BR>";
		txt+="<button style='margin-top:15px'>Сохранить</button>";
		txt+="<button style='margin-top:15px; margin-left:10px'>Закрыть</button>";
		txt+="</div>";

		$("body").append(txt);

		$("#calc_from_balance_form").draggable();

		$("#calc_from_balance_form button:eq(0)").click(function(){
			balance=$("#calc_from_balance_form .balance").val().replace(",",".");
			history_date=$("#calc_from_balance_form .balance").attr("history_date");

			$.ajax({
				url:"/php/dinamic/calc_from_balance.php",
				data:{tel_nom:tel_nom, balance:balance, history_date:history_date},
				type:"POST",
				success: function(data) {
					if (data=="OK") {
						$("#calc_from_balance_form").remove();

						$("#dinamic_details_tbl .history_date:contains('"+history_date+"')").parent().find("td:eq(1)").text(balance);

						get_client_history_by_monthes();
					}
				}
			});
		});

		$("#calc_from_balance_form button:eq(1)").click(function(){
			$("#calc_from_balance_form").remove();
		});

		$("#calc_from_balance_form input").click(function(){
			$(this).val("");
		});
	}

	var monthes_done=Array();

	function get_client_history_by_monthes() {
		$(".loading2").show();

		$.ajax({
			url:"/php/get_history_by_monthes.php",
			data:{operator:operator, tel_nom:tel_nom},
			type:"POST",
			async: false,
			success: function(data) {
				data=JSON.parse(data);

				$("#total_benefit").text(data["total_benefit"]);

				txt="";
				for (i=0; i<data["periods"].length; i++) {
					if (typeof data["periods"][i+1]!='undefined') {
						start_balance=data["periods"][i+1]["finish_balance"];
					} else {
						start_balance="-";
					}

					year_month=data["periods"][i]["year"]+"-"+addZero(data["periods"][i]["month"]);

					if (data["periods"][i]["diff"]<0) {
						class_name="style='background:rgb(252,155,28)'";
					} else if (data["periods"][i]["diff"]>0) {
						class_name="style='background:#96f28f'";
					} else {
						class_name="style='background:#e3d5ba'";
					}

					ss_diff_class='';
					ss_diff='-';

					if (data["periods"][i]["ss"]!="-") {
						ss_diff=(data["periods"][i]["spended"]-data["periods"][i]["ss"]);

						if (ss_diff!=0) {
							ss_diff_class='red';
						} else {
							ss_diff_class='green';
						}

						ss_diff=ss_diff.toFixed(2);
					}

					update_data=data["periods"][i]["user_name"]+" ("+data["periods"][i]["update_date"]+" "+data["periods"][i]["update_time"]+")";

					txt+="<tr year_month='"+data["periods"][i]["year"]+"-"+addZero(data["periods"][i]["month"])+"' title='"+update_data+"'>";
					if (monthes_done.indexOf(year_month)>-1) {
						txt+="<td class='red'>"+year_month+"</td>";
					} else {
						txt+="<td>"+year_month+"</td>";
					}

					if (i==0 && start_balance!=0) {
						if (start_balance>0) {
							txt+="<td style='background:#96f28f'>"+start_balance+"</td>";	
						} else {
							txt+="<td style='background:rgb(252,155,28)'>"+start_balance+"</td>";	
						}
					} else {
						txt+="<td class='start_balance'>"+start_balance+"</td>";
					}

					payment_color="";
					if (data["periods"][i]["removed_payments"].indexOf(year_month)>-1) {
						payment_color="#ea39fa";
						/*for (j=0; j<data["periods"][i]["removed_payments"].length; j++) {
							console.log(data["periods"][i]["payments"]+"/"+data["periods"][i]["removed_payments"][j]);
							data["periods"][i]["payments"]=data["periods"][i]["payments"]-data["periods"][i]["removed_payments"][j];
						}*/
					}

					blocked_period="";
					if (data["blocked_periods"].indexOf(data["periods"][i]["year"]+"-"+data["periods"][i]["month"])>-1) {
						blocked_period="<img src='img/lock.png' style='width:10px'>";
					}

					txt+="<td class='ss' year='"+data["periods"][i]["year"]+"' month='"+data["periods"][i]["month"]+"'>"+data["periods"][i]["ss"]+"</td>";
					txt+="<td class='spended' year='"+data["periods"][i]["year"]+"' month='"+data["periods"][i]["month"]+"'>"+data["periods"][i]["spended"]+"</td>";
					txt+="<td class='payments' year='"+data["periods"][i]["year"]+"' month='"+data["periods"][i]["month"]+"' style='background:"+payment_color+"'>"+data["periods"][i]["payments"]+"</td>";
					txt+="<td>"+data["periods"][i]["finish_balance"]+"</td>";
					txt+="<td>"+data["periods"][i]["spended_from_dinamic"]+"</td>";
					txt+="<td "+class_name+">"+data["periods"][i]["diff"]+"</td>";
					txt+="<td class='"+ss_diff_class+"''>"+ss_diff+"</td>";
					txt+="<td class='block_period' year_month='"+data["periods"][i]["year"]+"-"+data["periods"][i]["month"]+"' style='border-color: white;'>"+blocked_period+"</td>";
					txt+="</tr>";
				}

				$("#monthes_history_tbl").html(txt);

				$("#monthes_history_tbl .ss").bind("click", function(){
					year=$(this).attr("year");
					month=$(this).attr("month");
					summ=$(this).text();

					show_1s_form(year, month, summ);
				});

				$("#monthes_history_tbl .spended").bind("click", function(){
					year=$(this).attr("year");
					month=$(this).attr("month");

					get_spended_details(year, month);
				});

				/*$("#monthes_history_tbl .start_balance").bind("click", function(){
					year=$(this).attr("year");
					month=$(this).attr("month");

					edit_balance(year, month);
				});*/

				$("#monthes_history_tbl .payments").bind("click", function(){
					year=$(this).attr("year");
					month=$(this).attr("month");

					get_payments_by_month(year, month);
				});

				$(".loading2").hide();
			}
		});
	}

	function show_1s_form(year, month, summ) {
		if ($("#ss_form").is(":visible")) {
			return false;
		}

		txt="<div id='ss_form' style='padding-top:10; text-align:center; width:300px; height:150px; position: absolute; z-index: 1000; top: 25%; left: 30%; background: grey; border-radius:10px; border:2px solid black' year="+year+" month="+month+">";
		txt+="<input class='summ' value='"+summ+"' style='text-align:center'/><BR>";
		txt+="<button style='margin-top:15px'>Сохранить</button>";
		txt+="<button style='margin-top:15px; margin-left:10px'>Закрыть</button>";
		txt+="</div>";

		$("body").append(txt);

		$("#ss_form").draggable();

		$("#ss_form button:eq(0)").click(function(){
			summ=$("#ss_form .summ").val().replace(",",".");
			year=$("#ss_form").attr("year");
			month=$("#ss_form").attr("month");

			$.ajax({
				url:"/php/save_1s_form.php",
				data:{tel_nom:tel_nom, year:year, month:month, summ:summ},
				type:"POST",
				success: function(data) {
					if (data=="OK") {
						$("#ss_form").remove();

						get_client_history_by_monthes();
					}
				}
			});
		});

		$("#ss_form button:eq(1)").click(function(){
			$("#ss_form").remove();
		});

		$("#ss_form input").click(function(){
			$(this).val("");
		});
	}

	function get_spended_details(year, month) {
		$.ajax({
			url:"/php/get_spended_details.php",
			data:{operator:operator, tel_nom:tel_nom, year:year, month:month},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				$("#form_spended_details .month_down").off();
				$("#form_spended_details .month_up").off();
				$("#form_spended_details img").off();

				$("#form_spended_details").remove();

				txt="<div id='form_spended_details' style='text-align:center; width:550px; height:415px; position: absolute; z-index: 1000; top: 25%; left: 30%; background: grey; border-radius:10px; border:2px solid black' year='"+year+"' month='"+month+"'>";
				txt+="<div style='display:inline-block; width:500px; padding-left:30px; color:white; font-weight:bold'>";
				txt+="<span class='month_down'>&#9668;</span>&nbsp;";
				txt+="Период: "+year+"-"+addZero(month)+"&nbsp;";
				txt+="<span class='month_up'>&#9658;</span>";
				txt+="</div>";
				txt+="<div style='display:inline-block; width:0px'><img src='/img/cancel.png' style='width:24px; cursor:pointer'></div>";
				txt+='<div style="height:350px">';
				txt+='<table id="spended_details_tbl">';
				txt+='<thead><th>КАТЕГОРИЯ</th><th>ОБЪЁМ</th><th>ЦЕНА</th><th>СУММА</th></thead>';
				txt+='<tbody>';

				if (data.length==0) {
					txt+='<tr><td colspan=4 style="text-align:center">За этот период данных нет</td></tr>';
					txt+="</tbody></table>";
					txt+='</div>';
				} else {
					total_sum=0;
					old_group=1;
					for (i=0;i<data.length;i++) {
						total_sum+=parseFloat(data[i]["sum"]);

						if (old_group!=data[i]["group"]) {
							old_group=data[i]["group"];

							txt+='<tr><td colspan=4 style="background:yellow">&nbsp;</td></tr>';
						}

						txt+='<tr>';
						txt+='<td>'+data[i]["category"]+'</td><td>'+number_format(data[i]["length"],2,',',' ')+'</td>';
						txt+='<td>'+number_format(data[i]["unit_price"],2,',',' ')+'</td>';
						txt+='<td>'+number_format(data[i]["sum"],2,',',' ')+'</td>';
						txt+='</tr>';
					}

					txt+="<tr style='font-weight:bold'><td colspan='3' style='text-align:right'>ИТОГО:&nbsp;</td><td>"+number_format(total_sum,2,',',' ')+"</td></tr>";
					txt+='</tbody>';
					txt+='</table>';
					txt+='</div>';

					if (data[0]["blocked"]==0) {
						txt+='<button class="block_period_btn" onclick="block_unblock_period('+tel_nom+', '+year+', '+month+', 1)" style="margin-top:5px" title="Заблокируются также все предыдущие периоды">Блокировать период</button>';
						txt+='<button class="unblock_period_btn"onclick="block_unblock_period('+tel_nom+', '+year+', '+month+', 0)" style="margin-top:5px; display:none" title="Разблокируются также все последующие периоды">Разблокировать период</button>';
					} else {
						txt+='<button class="block_period_btn" onclick="block_unblock_period('+tel_nom+', '+year+', '+month+', 1)" style="margin-top:5px; display:none" title="Заблокируются также все предыдущие периоды">Блокировать период</button>';
						txt+='<button class="unblock_period_btn" onclick="block_unblock_period('+tel_nom+', '+year+', '+month+', 0)" style="margin-top:5px" title="Разблокируются также все последующие периоды">Разблокировать период</button>';
					}
				}

				txt+='</div>';

				$("body").append(txt);

				$("#form_spended_details").draggable();

				$("#form_spended_details .month_down").on("click", function() {
					year=$("#form_spended_details").attr("year");
					month=$("#form_spended_details").attr("month");

					newDate=deltaDate(new Date(year, month-1, 1), 0, -1, 0);

					get_spended_details(newDate.getFullYear(), newDate.getMonth()+1);
				});

				$("#form_spended_details .month_up").on("click", function() {
					year=$("#form_spended_details").attr("year");
					month=$("#form_spended_details").attr("month");

					newDate=deltaDate(new Date(year, month-1, 1), 0, 1, 0);

					get_spended_details(newDate.getFullYear(), newDate.getMonth()+1);
				});

				$("#form_spended_details img").on("click", function(){
					$("#form_spended_details").remove();
				});
			}
		});
	}

	function deltaDate(input, days, months, years) {
		var date = new Date(input);
		date.setDate(date.getDate() + days);
		date.setMonth(date.getMonth() + months);
		date.setFullYear(date.getFullYear() + years);
		return date;
	}

	function block_unblock_period(tel_nom, year, month, action) {
		$.ajax({
			url:"/php/dinamic/block_unblock_period.php",
			data:{tel_nom:tel_nom, year:year, month:month, action:action},
			type:"POST",
			success: function(data) {
				if (action==1) {
					$("#form_spended_details .block_period_btn").hide();
					$("#form_spended_details .unblock_period_btn").show();
				} else {
					$("#form_spended_details .block_period_btn").show();
					$("#form_spended_details .unblock_period_btn").hide();
				}

				get_client_history_by_monthes();
			}
		});
	}

	function get_payments_by_month(year, month) {
		$("#payment_form").remove();

		$.ajax({
			url:"/php/get_payments.php",
			data:{tel_nom:tel_nom, year:year, month:month},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				txt="<div id='payment_form' style='padding-top: 10; text-align:center; width:400px; padding-bottom:10px; position: absolute; z-index: 1000; top: 25%; left: 50%; background: grey; border-radius:10px; border:2px solid black' year='"+year+"' month='"+month+"'>";
				if (data.length>0) {
					payments="";
					for (i=0;i<data.length;i++) {
						if (data[i]["removed"]==1) {
							color="red";
							action_time="Удалён: "+data[i]["remove_time"];
						} else {
							color="white";
							action_time="Получен: "+data[i]["append_time"];
						}

						if (data[i]["source"]==0) {
							source="АВТО";
						} else if (data[i]["source"]==1) {
							source="1С";
						} else {
							source="Руки";
						}

						payments+="<tr title='"+action_time+"'>";
						payments+="<td style='color:"+color+"'>"+data[i]["payment_date"]+"</td>";
						payments+="<td style='color:"+color+"'>"+data[i]["payment_time"]+"</td>";
						payments+="<td style='color:"+color+"'>"+data[i]["summ"]+"</td>";
						payments+="<td style='color:"+color+"'>"+source+"</td>";
						if (data[i]["removed"]==0) {
							payments+="<td><img src='/img/cancel1.png' style='width:20px; cursor:pointer' onclick='remove_payment("+data[i]["id"]+",2,0,0)' title='Отменить'/></td>";
							payments+="<td><img src='/img/cancel.png' style='width:20px; cursor:pointer' onclick='remove_payment("+data[i]["id"]+",2,1,0)' title='Удалить совсем'/></td>";
						} else {
							payments+="<td><img src='/img/OK.png' style='width:17px; cursor:pointer' onclick='recover_payment("+data[i]["id"]+",2)' title='Вернуть'/></td>";
							payments+="<td><img src='/img/cancel.png' style='width:20px; cursor:pointer' onclick='remove_payment("+data[i]["id"]+",2,1,1)' title='Удалить совсем'/></td>";														
						}

						payments+="</tr>";
					}

					txt+="<table style='margin-left:3%'><th style='width:80px'>Дата</th><th style='width:80px'>Время</th><th style='width:80px'>Сумма</th><th style='width:80px'>Источник</th>";
					txt+="<tbody>"+payments+"</tbody>";
					txt+="</table>";
				} else {
					txt+="<div style='color:white'>В этом месяце платежей не было<BR></div>";
				}

				txt+="<button style='margin-top:5px; margin-left:10px'>Закрыть</button><BR>";
				txt+="</div>";

				if ($("#payment_form").is(":visible")) {
					return false;
				}

				$("body").append(txt);

				$("#payment_form").draggable();

				$("#payment_form button").click(function(){
					$("#payment_form").remove();
				});
			}
		});
	}

	function new_payment_form(date) {
		$("#payment_form").remove();

		$.ajax({
			url:"/php/get_payments.php",
			data:{tel_nom:tel_nom, payment_date:date},
			type:"POST",
			success: function(data) {
				data=JSON.parse(data);

				txt="<div id='payment_form' style='padding-top: 10; text-align:center; width:400px; padding-bottom:10px; position: absolute; z-index: 1000; top: 25%; left: 50%; background: grey; border-radius:10px; border:2px solid black'>";

				if (data.length>0) {
					payments="";
					for (i=0;i<data.length;i++) {
						if (data[i]["removed"]==1) {
							color="red";
							action_time="Удалён: "+data[i]["remove_time"];
						} else {
							color="white";
							action_time="Получен: "+data[i]["append_time"];
						}

						if (data[i]["source"]==0) {
							source="АВТО";
						} else if (data[i]["source"]==1) {
							source="1С";
						} else {
							source="Руки";
						}

						payments+="<tr title='"+action_time+"'>";
						payments+="<td style='color:"+color+"'>"+data[i]["payment_date"]+"</td>";
						payments+="<td style='color:"+color+"'>"+data[i]["payment_time"]+"</td>";
						payments+="<td style='color:"+color+"'>"+data[i]["summ"]+"</td>";
						payments+="<td style='color:"+color+"'>"+source+"</td>";
						if (data[i]["removed"]==0) {
							payments+="<td><img src='/img/cancel1.png' style='width:20px; cursor:pointer' onclick='remove_payment("+data[i]["id"]+",1,0,0)' title='Отменить'/></td>";
							payments+="<td><img src='/img/cancel.png' style='width:20px; cursor:pointer' onclick='remove_payment("+data[i]["id"]+",1,1,0)' title='Удалить совсем'/></td>";
						} else {
							payments+="<td><img src='/img/OK.png' style='width:17px; cursor:pointer' onclick='recover_payment("+data[i]["id"]+",1)' title='Вернуть'/></td>";
							payments+="<td><img src='/img/cancel.png' style='width:20px; cursor:pointer' onclick='remove_payment("+data[i]["id"]+",1,1,1)' title='Удалить совсем'/></td>";
						}
						

						payments+="</tr>";
					}

					txt+="<table style='margin-left:3%'><th style='width:80px'>Дата</th><th style='width:80px'>Время</th><th style='width:80px'>Сумма</th><th style='width:80px'>Источник</th>";
					txt+="<tbody>"+payments+"</tbody>";
					txt+="</table>";
				}

				txt+="<B>Сумма платежа:</B><BR>";
				txt+="<input id='new_payment_summ' date='"+date+"' val='' style='text-align:center'/><BR>";	
				txt+="<button onclick='new_payment()' style='margin-top:5px'>Сохранить</button>";
				txt+="<button style='margin-top:5px; margin-left:10px'>Закрыть</button><BR>";
				txt+="<span class='msg' style='display:none'>Какая-то ошибка</span>";
				txt+="</div>";

				$("body").append(txt);

				$("#payment_form").draggable();

				$("#new_payment_summ").focus();

				$("#payment_form button:eq(1)").click(function(){
					$("#payment_form").remove();
				});
			}
		});
	}

	function remove_payment(id, from_form, remove_from_db, already_canceled) {
		//from_form 1 - из даты
		//from_form 2 - из месяца

		txt="<div id='confirm_remove_form' style='padding-top: 10; text-align:center; width:300px; padding-bottom:10px; position: absolute; z-index: 1000; top: 40%; left: 54%; background: grey; border-radius:10px; border:2px solid black' tel_nom='"+tel_nom+"' remove_from_db='"+remove_from_db+"' already_canceled='"+already_canceled+"'>";
		
		if (remove_from_db==1) {
			txt+="<span style='color:white'>Точно удалить?</span><BR>";
		} else {
			txt+="<span style='color:white'>Точно отменить?</span><BR>";
		}

		txt+="<button>ДА!!!</button>";
		txt+="<button style='margin-left:10px'>Нет</button>";
		txt+="</div>";

		$("body").append(txt);

		$("#confirm_remove_form").find("button:eq(0)").on("click", function(){
			tel_nom=$("#confirm_remove_form").attr("tel_nom");
			remove_from_db=$("#confirm_remove_form").attr("remove_from_db");
			already_canceled=$("#confirm_remove_form").attr("already_canceled");

			$("#confirm_remove_form").remove();

			$.ajax({
				url:"/php/remove_payment.php",
				data:{id:id, tel_nom:tel_nom, remove_from_db:remove_from_db, already_canceled:already_canceled},
				type:"POST",
				success: function(data) {
					if (data=="OK") {
						if (from_form==1) {
							payment_date=$("#new_payment_summ").attr("date");

							new_payment_form(payment_date);
						} else {
							year=$("#payment_form").attr("year");
							month=$("#payment_form").attr("month");

							get_payments_by_month(year, month)
						}
						get_client_history();
						get_client_history_by_monthes();
					} else {
						$("#payment_form .msg").show();
					}
				}
			});
		});

		$("#confirm_remove_form").find("button:eq(1)").on("click", function(){
			$("#confirm_remove_form").remove();
		});
	}

	function recover_payment(id, from_form) {
		//from_form 1 - из даты
		//from_form 2 - из месяца

		$.ajax({
			url:"/php/recover_payment.php",
			data:{id:id, tel_nom:tel_nom},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					if (from_form==1) {
						payment_date=$("#new_payment_summ").attr("date");

						new_payment_form(payment_date);
					} else {
						year=$("#payment_form").attr("year");
						month=$("#payment_form").attr("month");

						get_payments_by_month(year, month)
					}
					get_client_history();
					get_client_history_by_monthes();
				} else {
					$("#payment_form .msg").show();
				}
			}
		});
	}

	function new_payment(date) {
		summ=$("#new_payment_summ").val().replace(",",".");

		if (summ.length==0) {
			return false;
		}

		payment_date=$("#new_payment_summ").attr("date");

		$.ajax({
			url:"/php/new_payment.php",
			data:{operator:operator, tel_nom:tel_nom, payment_date:payment_date, summ:summ},
			type:"POST",
			success: function(data) {
				if (data=="OK") {
					payment_date=$("#new_payment_summ").attr("date");

					$("#payment_form").remove();

					new_payment_form(payment_date);

					get_client_history();
					get_client_history_by_monthes();
				} else {
					$("#payment_form .msg").show();
				}					
			}
		});
	}

	function to_office() {
		window.open("https://xn--h1alkk.xn--d1aimu.xn--p1ai/dinamic1.php?operator="+operator+"&tel_nom="+tel_nom+"&api=0", "_blank");

		return false;
	}

	function to_start_page() {
		window.location.href="/";
	}

	function month_calculate_form_show() {
		$("#month_calculate_form").remove();

		txt="<div id='month_calculate_form' style='text-align:center; width:305px; height:120px; position: absolute; z-index: 1000; top: 25%; left: 50%; background: grey; border-radius:10px; border:2px solid black'>";
		txt+="<div style='width:100%; text-align:right'><img src='/img/cancel.png' style='width:24px; cursor:pointer'></div>";
		txt+="<span style='color:white; font-weight:bold'>Первый месяц расчёта:</span><BR>";

		txt+="<span class='calc_month_down'>&#9668;</span>";

		txt+="<select class='calc_year' style='margin-top:5px'>";
		txt+="</select>";

		txt+="<select class='calc_month'>";
		txt+="</select>";

		txt+="<span class='calc_month_up'>&#9658;</span><BR>";

		txt+="<button style='margin-top:10px'>Считать</button>";
		txt+="<button style='margin-left:10px; margin-top:10px'>Считать в фоне</button>";
		txt+="</div>";

		$("body").append(txt);

		$("#month_calculate_form").draggable();

		$("#month_calculate_form img").click(function(){
			$("#month_calculate_form").remove();
		});

		$("#month_calculate_form button:eq(0)").click(function(){
			$("#refresh_img").addClass("rotate");

			from_year=$("#month_calculate_form select.calc_year").find("option:selected").val();
			from_month=$("#month_calculate_form select.calc_month").find("option:selected").val();

			d=new Date();
			current_year=d.getFullYear();
			current_month=d.getMonth()+1;

			year_month=Array();

			for (year=from_year; year<current_year+1; year++) {
				if (year==current_year) {
					to_month=current_month;
				} else {
					to_month=12;
				}

				if (year==from_year) {
					from_month1=from_month;
				} else {
					from_month1=1;
				}

				for (month=from_month1; month<to_month+1; month++) {
					y_m=year+"-"+month;

					if ($("#monthes_history_tbl .block_period[year_month='"+y_m+"']").html()=="" || $("#monthes_history_tbl .block_period[year_month='"+y_m+"']").length==0) {
						year_month.push(year+"-"+addZero(month));
					}
				}
			}

			$("#month_calculate_form").remove();

			month_calculate(0, year_month, 0);
		});

		$("#month_calculate_form button:eq(1)").click(function(){
			$("#refresh_img").addClass("rotate");

			from_year=$("#month_calculate_form select.calc_year").find("option:selected").val();
			from_month=$("#month_calculate_form select.calc_month").find("option:selected").val();

			year_month=from_year+"-"+from_month;

			$("#month_calculate_form").remove();

			month_calculate(0, year_month, 1);
		});

		date=new Date();

		current_year=date.getFullYear();
		current_month=date.getMonth()+1;

		monthes=Array("Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь");

		for (month=0; month<12; month++) {
			$("#month_calculate_form").find("select.calc_month").append("<option value='"+(month+1)+"'>"+monthes[month]+"</option>");
		}

		for (year=current_year; year>2014; year--) {
			$("#month_calculate_form").find("select.calc_year").append("<option value='"+year+"'>"+year+"</option>");
		}

		$("#month_calculate_form select.calc_year option[value='"+current_year+"']").attr("selected", "selected");
		$("#month_calculate_form select.calc_month option[value='"+current_month+"']").attr("selected", "selected");

		$(".calc_month_down").click(function(){
			form=$("#month_calculate_form");

			selected_month=form.find(".calc_month option:selected").val();
			selected_year=form.find(".calc_year option:selected").val();

			if (selected_month==1) {
				if (selected_year>2021) {
					form.find(".calc_month option").prop('selected', false);
					form.find(".calc_year option").prop('selected', false);

					form.find(".calc_year option[value='"+(selected_year-1)+"']").prop('selected', true);
					form.find(".calc_month option[value='12']").prop('selected', true);
				}
			} else {
				form.find(".calc_month option").prop('selected', false);
				form.find(".calc_month option[value='"+(selected_month-1)+"']").prop('selected', true);
			}
		});

		$(".calc_month_up").click(function(){
			form=$("#month_calculate_form");

			selected_month=parseInt(form.find(".calc_month option:selected").val());
			selected_year=parseInt(form.find(".calc_year option:selected").val());
			date=new Date();

			current_year=date.getFullYear();

			if (selected_month==12) {
				if (selected_year!=current_year) {
					form.find(".calc_month option").prop('selected', false);
					form.find(".calc_year option").prop('selected', false);

					form.find(".calc_year option[value='"+(selected_year+1)+"']").prop('selected', true);
					form.find(".calc_month option[value='1']").prop('selected', true);
				}
			} else {
				form.find(".calc_month option").prop('selected', false);
				form.find(".calc_month option[value='"+(selected_month+1)+"']").prop('selected', true);
			}
		});			
	}

	function month_calculate(month_nom, year_month, calc_in_background) {
		//calc_in_background - считать в фоновом режиме

		if (calc_in_background==0) {
			$("#monthes_history_tbl").find("tr[year_month='"+year_month[month_nom]+"']").find("td:eq(0)").addClass("red");
			from_month=year_month[month_nom];
		} else {
			from_month=year_month;

			setTimeout(function(){
				window.close();
			},2000);
		}

		$.ajax({
			url:"/php/month_calculate_new_fast.php",
			data:{tel_nom:tel_nom, user_name:user_name, year_month:from_month, calc_in_background:calc_in_background, operator:operator},
			type:"POST",
			success: function(data) {
				monthes_done.push(year_month[month_nom]);

				get_client_history();
				get_client_history_by_monthes();
				
				current_date=new Date();

				current_year=current_date.getFullYear();
				current_month=current_date.getMonth()+1;

				if (from_month==current_year+"-"+addZero(current_month)) {//Если посчитали текущий месяц, то загружаем время обновления
					get_phone_data();
				}

				if (calc_in_background==0) {
					if (month_nom+1<year_month.length) {
						//return false;
						month_calculate(month_nom+1, year_month, 0);
					} else {
						$("#monthes_history_tbl").find("tr").find("td:eq(0)").removeClass("red");
						$("#refresh_img").removeClass("rotate");
						monthes_done=Array();
					}
				} else {
					$("#refresh_img").removeClass("rotate");
					monthes_done=Array();
				}
			},
			error: function(data) {
				$("#refresh_img").removeClass("rotate");
				$("#monthes_history_tbl").find("td.red").removeClass("red");
				monthes_done=Array();
			}
		});
	}

	function short_text(txt, length) {
		if (typeof txt=='undefined') {
			return txt;
		}

		if (txt.length>length) {
			return txt.substr(0,length)+"...";
		} else {
			return txt;
		}
	}

	function monthes_total_rests() {
		if ($(".loading2").is(":visible")) {
			return false;
		}

		$(".loading2").show();

		$.ajax({
			url: server+"/php/dinamic/get_monthes_total_rests.php",
			data:{tel_nom:tel_nom, operator:operator},
			type:"POST",
			success: function(data) {
				try {
					data=JSON.parse(data);
				} catch (e) {
					$(".loading2").hide();
					return false;
				}

				txt="<div id='monthes_totals_rests_div' style='text-align:center; width:955px; height:470px; position: absolute; z-index: 1001; top: 20%; left: 30%; background: rgb(80,80,80); border-radius:10px; border:2px solid black'>";
				txt+="<div style='text-align:right'><img src='/img/cancel.png' style='width:25px; cursor:pointer'/></div>";
				txt+="<table class='tbl_header'>";
				txt+="<th>ДАТА</th><th>ТП</th><th>НАЧ</th><th>РОУМ.</th><th>МН</th><th>ВСР<BR>ЗГП</th><th>СРДО</th><th>МИН</th><th>GPRS ОБЩ</th><th>GPRS</th><th>GPRS+</th><th>SMS/MMS</th>";
				txt+="</table>";
				txt+="<div style='height:370px; overflow-y:auto'>";
				txt+="<table class='tbl_body'>";

				for (var month in data["rests"]) {
					txt+="<tr>";
					txt+="<td>"+month+"</td>";
					txt+="<td><div style='font-size:11px' title='"+data["rests"][month]["ТП"]+"'>"+short_text(data["rests"][month]["ТП"],33)+"</div></td>";
					txt+="<td class='clickable'>"+data["rests"][month]["НАЧ"]+"</td>";

					if (typeof data["roaming"][month] == 'undefined') {
						data["roaming"][month]=0;
					}

					txt+="<td class='clickable'>"+data["roaming"][month]+"</td>";

					txt+="<td class='clickable'>"+divideNumberByPieces(data["rests"][month]["МН"]["value"]," ")+"</td>";
					txt+="<td class='clickable'>"+divideNumberByPieces(data["rests"][month]["ВСР+ЗГП"]["value"]," ")+"</td>";
					txt+="<td class='clickable'>"+divideNumberByPieces(data["rests"][month]["СРДО"]["value"]," ")+"</td>";

					total_min=data["rests"][month]["ВСР+ЗГП"]["value"]+data["rests"][month]["СРДО"]["value"]+data["rests"][month]["МН"]["value"]

					txt+="<td class='bold clickable'>"+Math.ceil(total_min)+"</td>";

					total_gprs=data["rests"][month]["GPRS+"]["value"]+data["rests"][month]["GPRS"]["value"];

					txt+="<td class='bold clickable'>"+divideNumberByPieces(total_gprs.toFixed(2)," ")+"</td>";
					txt+="<td class='clickable'>"+divideNumberByPieces(data["rests"][month]["GPRS"]["value"].toFixed(2)," ")+"</td>";
					txt+="<td class='clickable'>"+divideNumberByPieces(data["rests"][month]["GPRS+"]["value"].toFixed(2)," ")+"</td>";

					txt+="<td class='bold clickable'>"+divideNumberByPieces(data["rests"][month]["SMS/MMS"]["value"]," ")+"</td>";

					txt+="</tr>";
				}

				txt+="</div>";
				txt+="</table>";
				txt+="</div>";

				$("body").append(txt);

				$("#monthes_totals_rests_div").draggable();

				$("#monthes_totals_rests_div .clickable").click(function(){
					if ($(this).hasClass("checked")) {
						$(this).removeClass("checked");
					} else {
						$(this).addClass("checked");
					}
				});

				$("#monthes_totals_rests_div img").click(function(){
					$("#monthes_totals_rests_div").remove();
				});

				$(".loading2").hide();
			}
		});
	}

	function clients_groups(action) {
		//action 1 - Получить все группы
		//action 2 - Отправить в группу

		if ($(".loading2").is(":visible")) {
			return false;
		}

		if (action==2) {
			client_group=$("#new_clients_group").val();

			if (client_group.length==0) {
				client_group=$("#clients_group_div select option:selected").val();
			}
		} else {
			client_group="";
		}

		$("#clients_group_div").remove();


		$(".loading2").show();

		$.ajax({
			url: "/php/dinamic/clients_groups.php",
			data:{tel_nom:tel_nom, client_group:client_group, action:action},
			type:"POST",
			success: function(data) {
				if (action==1) {
					data=JSON.parse(data);

					txt="<div id='clients_group_div' style='text-align:center; width:300px; height:355px; position: absolute; z-index: 1001; top: 20%; left: 35%; background: rgb(80,80,80); border-radius:10px; border:2px solid black'>";
					txt+="<div style='text-align:right'><img src='/img/cancel.png' style='width:25px; cursor:pointer'/></div>";
					txt+="<div style='width:100%; text-align:center; color:white'>";
					txt+="Новая группа<BR>";
					txt+="<input id='new_clients_group' style='color:black'/>";
					txt+="</div><BR>";
					txt+="<select size=10 style='width:176px'>";

					for (i=0; i<data.length; i++) {
						txt+="<option value='"+data[i]+"'>"+data[i]+"</option>";
					}

					txt+="</select><BR>";
					txt+="<button onclick='clients_groups(2)' style='margin-top:10px'>Сохранить</button>";
					txt+="<div class='msg' style='font-weight:bold; color:red; width:100%; text-align:center; display:none;'>НЕ изменено</div>";
					txt+="</div>";

					$("body").append(txt);

					$("#clients_group_div img").click(function(){
						$("#clients_group_div").remove();
					});
				} else {
					if (data=="OK") {
						$("#clients_group_div").remove();

						get_phone_data();
					} else {
						$("#clients_group_div .msg").show();
					}
				}

				$(".loading2").hide();
			}
		});
	}

	function divideNumberByPieces(x, delimiter) {
  		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, delimiter || " ");
	}

	function number_format(number, decimals, dec_point, separator ) {
		  number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		  var n = !isFinite(+number) ? 0 : +number,
		    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		    sep = (typeof separator === 'undefined') ? ',' : separator ,
		    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		    s = '',
		    toFixedFix = function(n, prec) {
		      var k = Math.pow(10, prec);
		      return '' + (Math.round(n * k) / k)
		        .toFixed(prec);
		    };
		  // Фиксим баг в IE parseFloat(0.55).toFixed(0) = 0;
		  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
		    .split('.');
		  if (s[0].length > 3) {
		    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		  }
		  if ((s[1] || '')
		    .length < prec) {
		    s[1] = s[1] || '';
		    s[1] += new Array(prec - s[1].length + 1)
		      .join('0');
		  }
		  return s.join(dec);
	}	
</script>

<div class="loading2">
	<div class="circle circle-1"></div>
	<div class="circle circle-1a"></div>
	<div class="circle circle-2"></div>
	<div class="circle circle-3"></div>
</div>

<div id="alert_dialog"></div>

<div id="sort_form_service" row_id="" onclick='form_sort_hide()' title='Закрыть'><table><tr><td><img id='move_up_arrow' src='img/up.png' style='width:45px'/></td><td>Укажите, куда переместить позицию,<BR>кликнув по номеру строки</td><td><img id='move_down_arrow' src='img/down.png' style='width:45px'/></td></tr></table></div>

<!--<div class="loader-background">
	<div class='loader-txt'>
		Пожалуйта, подождите...
	</div>

	<div class="wrapper">
	  <span class="ball"></span>
	  <span class="ball"></span>
	  <span class="ball"></span>
	  <span class="ball"></span>
	</div>
	<div class="drop-shadow"></div>

	<div class='loader-percents'></div>
</div>-->

<div id="main_tabs">
   <ul>
   	     <li><a href="#dinamic_main" onclick="selected_tab=1; get_dinamic_details()">Динамика</a></li>
   	     <li><a href="#call_types_div" onclick="selected_tab=2; $('#dinamic_details').hide();  bee_plus=false; get_call_types_prices()">Типы</a></li>
   	     <li id='call_types_bee_plus'><a href="#call_types_div" onclick="selected_tab=6; $('#dinamic_details').hide(); bee_plus=true; get_call_types_prices('', '', 'bee+')">Типы BEE+</a></li>
   	     <li><a href="#services_settings_div" onclick="selected_tab=3; get_services_settings()">Услуги</a></li>
   	     <!--<li><a href="#roaming_settings_div" onclick="selected_tab=4; get_roaming_settings()">В стране</a></li>-->
   	     <li><a href="#exceptions_div" onclick="selected_tab=5; get_exceptions()">Исключения</a></li>
   	     <li id="to_office"><div href="javascript:" onclick="to_office()">в ОФ</div></li>
   	     <li><a href="" onclick="window.location.reload(true)">Обновить</a></li>
   </ul>

	<div id="dinamic_details">
		<table style="border:0px">
			<tr>
				<td style="border:0px;">
					<div id='dinamic_details_header'></div>
				</td>
				<td colspan=3 style="border:0px; border:1px solid grey; cursor:pointer" onclick="get_attentions_by_phone()">
					<div id='attention_reason'><span style='font-size:17px' title='История ВНИМАНИЙ'>&#9888;</span></div>
				</td>
			</tr>
			<tr>
				<td style='border:0px'>
					<table style='width:100%'>
						<tr style='height:42px'>
							<td style="border:0px; width:1275px">
								ПОЛЬЗОВАТЕЛЬ: <span id='tel_user' style='font-size:14px; font-weight:bold'></span>
								(ГРУППА: <span id='tel_clients_group' style='font-size:14px; font-weight:bold'></span>)
							</td>
							<td style="text-align:center; border:0px; width:54px">
								<img src='img/face.png' class='clients_groups_btn img_btn' onclick='clients_groups(1)' style='width:34px; margin-right:0px; cursor:pointer' title='Группы'/>
							</td>
							<td style="text-align:center; border:0px; width:54px">
								<img src='img/diagram.png' class='monthes_total_rests img_btn' onclick='monthes_total_rests()' style='width:34px; margin-right:0px; cursor:pointer' title='Итоги по месяцам'/>
							</td>
							<td style="text-align:center; border:0px; width:47px">
								<img src='img/list.png' class='img_btn' onclick='search_comments_by_phone(tel_nom)' style='width:31px; margin-right:0px; cursor:pointer' title='Комментарии'/>
							</td>
							<td style="text-align:center; border:0px; width:54px">
								<img src='img/forwarding.png' class='forwarding_btn img_btn' onclick='show_forwarding_form()' style='width:36px; margin-right:0px; cursor:pointer' title='Переадесация'/>
							</td>
							<td style="text-align:center; border:0px; width:47px">
								<img src='img/attention_off.png' id='attention_btn' class='img_btn' style='width:33px; margin-right:0px; cursor:pointer' title='Внимание!!! Активировать'/>
							</td>
							<td style="text-align:center; border:0px; width:47px">
								<img src="img/block.png" class='block_btn img_btn' style='width:33px; margin-right:0px; cursor:pointer' onclick="show_block_number_form()" title='Блокировать'>
							</td>
							<td style="text-align:center; border:0px; width:47px; display:none">
								<img src="img/unblock.png" class='unblock_btn img_btn' style='width:35px; margin-right:0px; cursor:pointer' onclick="show_unblock_form()" title='Разблокировать'>
							</td>
							<td style="text-align:center; border:0px; width:45px">
								<img src="img/refresh.png" id='refresh_img' onclick='month_calculate_form_show()' class='img_btn' style='width:35px; margin-right:0px; cursor:pointer' title='Пересчитать период'>
							</td>
						</tr>
					</table>
				</td>
				<td style='border:0px; text-align:center; width:30%'>
					<img src="img/service_on.png" id='refresh_img' class='img_btn' style='width:38px; cursor:pointer' title='Подключить услугу' onclick='service_on_off_form_show(1)'>
				</td>

				<td style='border:0px; text-align:center'>
					<div id='total_benefit' title='Общий +/- за всю историю'></div>
				</td>

				<td style='border:0px; text-align:center; width:30%'>
					<img src="img/service_off.png" id='refresh_img' class='img_btn' style='width:38px; cursor:pointer' title='Отключить услугу' onclick='service_on_off_form_show(2)'>
				</td>
			</tr>
			<tr>
				<td style='background:lightgray; border:0px'>
					<span id='user_info' style='font-size:12px; font-weight:bold;'></span>
				</td>
			</tr>
			<tr>
				<td style="width:1400px; vertical-align:top; border:0px;">
					<table>
						<tr>
							<td style="border:0px; vertical-align:top">
								<table id="dinamic_details_tbl_header">
								   	<thead><th>ДАТА</th><th>БАЛ.</th><th>НАЧ.</th><th>ОПЛ.</th></thead>
								</table>
								<div style="width:420px; overflow-y:auto; border: 1px solid #aaaaaa;">
								   	<!--<table id="dinamic_details_tbl" function='get_dinamic_details(1)!-->
								   	<table id="dinamic_details_tbl" to_date="<?echo date('Y-m-d', time()+24*3600)?>">
								   		<tbody></tbody>
								   	</table>
								</div>
							</td>
							<td style="border:0px; padding-left: 20px; vertical-align: top;">
								<table id="monthes_history_tbl_header">
								   	<thead><th>МЕСЯЦ</th><th>БАЛАНС НАЧАЛО</th><th>НАЧ. в 1С</th><th>НАЧ.</th><th>ОПЛ.</th><th>БАЛАНС КОНЕЦ</th><th>ЗАКУП</th><th>+/-</th><th>1С +/-</th></thead>
								</table>
								<div style="width:941px; overflow-y:auto; border: 1px solid #aaaaaa;">
								   	<table id="monthes_history_tbl" function='get_dinamic_details(1)'>
								   		<tbody></tbody>
								   	</table>
								</div>
							</td>
						</tr>
					</table>

					<table style='width:100%'>
					    <tr>
					   		<td style='border:0px;'>
					   			<img src='img/arrow_down.png' style='width:18px; left:0px; cursor:pointer' onclick='calls_list_resize(2)' title='Развернуть историю'>
					   		</td>
					   		<td style='border:0px;'>
					   			<img src='img/cancel.png' style='width:18px; left:0px; cursor:pointer' onclick='calls_list_resize(3)' title='Вернуть умолчания'>
					   		</td>
					   		<td style='border:0px;'>
					   			<img src='img/arrow_top.png' style='width:18px; left:0px; cursor:pointer' onclick='calls_list_resize(1)' title='Развернуть детализацию'>
					   		</td>
					   		<td style='border:0px; width:140px; padding-left:20px'>
					   			от <input id='calls_list_from_date' class='date_picker' style='font-size:10px; width:70px; background:yellow; border-radius:5px'/>
					   		</td>
					   		<td style='border:0px; width:120px'>
					   			до <input id='calls_list_to_date' class='date_picker' style='font-size:10px; width:70px; background:yellow; border-radius:5px'/>
					   		</td>
					   		<td style='border:0px; width:130px'>
					   			<input id='calls_list_adresat' style='font-size:10px; width:130px; background:yellow; border-radius:5px; margin-top:3px' placeholder='Номер'/>
					   		</td>
					   		<td style='border:0px;'>
					   			<img src='img/cancel.png' style='width:19px; left:0px; cursor:pointer' onclick='$("#calls_list_to_date").val(""); $("#calls_list_from_date").val(""); $("#calls_list_adresat").val(""); $("#calls_list").attr("page_nom", 0); get_calls_list(0)' title='Сбросить фильтр детализации'>
					   		</td>
					   		<td style='border:0px'>
					   			<img class='to_excel' src='img/to_excel.png' onclick="get_calls_list(0,1)" title='Экспорт в Excel' style='width:33px; cursor:pointer;'>
					   		</td>
					   		<td id='detal_last_time' style='border:0px;'>
					   		</td>
					   	</tr>
					</table>
					   
					<table id="calls_list_header">
					   	<thead></thead>
					</table>

					<div style="overflow-x:hidden; overflow-y:auto; border: 1px solid #aaaaaa; width: 1402px;">
					   	<table id="calls_list" page_nom='0' function='get_calls_list(1)'>
					   		<tbody></tbody>
					   	</table>
					</div>
				</td>
				<td style="vertical-align:top; border:0px" colspan=3>
					<div>
						<table id="client_services_tbl_header">
							<thead><th>Услуга</th><th>Статус</th><th>Цена</th><th>Kэф</th><th>Дата доб/обн</th></thead>
						</table>
						<div style="width:482px; margin-left:5px">
							<div style="width:100%; overflow-y:auto; border: 1px solid #aaaaaa;">
								<table id="client_services_tbl">
									<tbody></tbody>
								</table>
							</div>
						</div>
					</div>

					<div class='detal_totals_date_div' style='width:97%; text-align:right;'>
						<input type='checkbox' id='loss_calls_checkbox' onclick='$("#calls_list_to_date").val(""); $("#calls_list_from_date").val(""); $("#calls_list_adresat").val(""); $("#calls_list").attr("page_nom", 0); get_calls_list(0)'/>
						<span style='font-size: 13px'>УБ. &nbsp;</span>
						<input type='checkbox' id='edited_calls_checkbox' onclick='$("#calls_list_to_date").val(""); $("#calls_list_from_date").val(""); $("#calls_list_adresat").val(""); $("#calls_list").attr("page_nom", 0); get_calls_list(0)'/>
						<span style='font-size: 13px'>ИЗМ.&nbsp;</span>

						<span class='month_down' style='margin-left:10px'>&#9668;</span>

						<select id='detal_totals_year' style='font-size:10px; background:yellow; border-radius:5px'>
							<?php
								$current_year=date("Y",time());

								$i=0;
								do {
									if ($i==0) {
										echo "<option value='".($current_year-$i)."' selected>".($current_year-$i)."</option>";
									} else {
										echo "<option value='".($current_year-$i)."'>".($current_year-$i)."</option>";
									}
									$i++;
								} while ($current_year-$i>2017);
							?>
						</select>

						<select id='detal_totals_month' style='font-size:10px; background:yellow; border-radius:5px'>
							<?php
								$current_month=date("m",time());

								$monthes=Array("Январь","Февраль","Март","Апрель","Май","Июнь","Июль","Август","Сентябрь","Октябрь","Ноябрь","Декабрь");

								for ($i=0;$i<12;$i++) {
									if ($i+1==$current_month) {
										echo "<option value='".($i+1)."' selected>".$monthes[$i]."</option>";
									} else {
										echo "<option value='".($i+1)."'>".$monthes[$i]."</option>";
									}
								}
							?>
						</select>

						<span class='month_up'>&#9658;</span>

						<button style='font-size:10px; border-radius:5px' onclick="$('#detal_totals').attr('filter',''); get_detal_totals();">Показать</button>

						<span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>

						<img src='img/cancel.png' style='width:18px; cursor:pointer' onclick="$('#detal_totals').attr('filter',''); $('#calls_list').attr('page_nom',0); get_calls_list(0); $('#detal_totals tbody input').prop('checked',false); $('#clear_detal_filters').prop('checked',false);" title='Сбросить фильтры'/>
					</div>

					<table id="detal_totals_header">
						<thead><th><input type='checkbox' id='clear_detal_filters' onclick='clear_detal_filters()'></th><th>Тип</th><th>Кол-во</th><th>Сумма, руб</th></thead>
					</table>
					<div style='width:482px; margin-left:5px;'>
						<div style="overflow-y:auto; border: 1px solid #aaaaaa;">
							<table id="detal_totals" filter=''>
								<tbody></tbody>
							</table>
						</div>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<div id="call_types_div">
		 <table id="call_types_tbl">
		 	<thead><th>п/п</th><th>Код</th><th>Тип</th><th>Цена за ед.</th><th class='msg' colspan=3></th></thead>
		 	<tbody>
		 			
		 	</tbody>
		 </table>
	</div>

	<div id="services_settings_div">
	 	<table>
	 		<thead><th>п/п</th><th>Код</th><th>Ном-ов</th><th>Услуга</th><th>Период.</th><th>Цена</th><th class='msg' colspan=3></th></thead>
	 		<tbody>
	 			
	 		</tbody>
	 	</table>
	</div>

	<div id="exceptions_div">
		<table id="exceptions_tbl">
			 <thead><th>Номер</th><th>Дата добавления</th><th>Комментарий</th></thead>
			 <tbody>
			 			
			 </tbody>
		</table>
	</div>

	<div id="sort_form_service" row_id="" onclick='form_sort_hide()' title='Закрыть'><table><tr><td><img id='move_up_arrow' src='img/up.png' style='width:45px'/></td><td>Укажите, куда переместить позицию,<BR>кликнув по номеру строки</td><td><img id='move_down_arrow' src='img/down.png' style='width:45px'/></td></tr></table></div>

	<!--<div id="roaming_settings_div">
		<table id="roaming_settings_header">
			<thead><th>Страна</th><th>Стоимость</th><th class='msg'></th></thead>
		</table>
		<div style="width:470px; height:700px; overflow:auto">
			<table id="roaming_settings_tbl">
			 	<tbody>
			 			
			 	</tbody>
			</table>
		</div>
	</div>-->
</body>