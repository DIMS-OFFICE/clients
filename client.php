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
	<link  href="/css/loader1.css" rel="stylesheet">
	<link  href="/css/dinamic.css?<? echo rand(); ?>" rel="stylesheet">
	<link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-3.3.2.min.css" type="text/css">
    <link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-example.min.css" type="text/css">
    <link rel="stylesheet" href="https://офис.димс.рф/css/prettify.min.css" type="text/css">
    <link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-multiselect.css" type="text/css">
	<link  href="https://офис.димс.рф/css/colResizable.css" rel="stylesheet" type="text/css">
	<link  href="https://офис.димс.рф/js/contextMenu/jquery.contextMenu.css" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://офис.димс.рф/css/bootstrap-multiselect.css" type="text/css">
	<link  href="https://офис.димс.рф/css/navigation.css" rel="stylesheet" type="text/css">

	<style>
		#overal_contract_stats tr td:not(:nth-child(2)) {cursor: url('/img/cursor_up_down1.png'), auto}
		#overal_account_stats tr td:not(:nth-child(1)) {cursor: url('/img/cursor_up_down1.png'), auto}
		.balance_in_td, .balance_archive_td {cursor: url('img/edit.png'), pointer}
		.ui-datepicker-current-day {background:red!important;}
		.ui-widget-overlay {display:none!important}
		.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {background: palegreen;}
    	.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active {background:  white;}
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
    <script type="text/javascript" src="https://офис.димс.рф/js/contextMenu/jquery.contextMenu.js" defer></script>
    <script type="text/javascript" src="https://клиент.димс.рф/js/navigation.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/jquery.table2excel.min.js"></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/dinamic/main_filters1.js?<? echo rand(); ?>" defer></script>
    <script type="text/javascript" src="https://офис.димс.рф/js/dinamic/comments.js?<? echo rand(); ?>" defer></script>
    <script type="text/javascript" src="js/shared_scripts.js?r=<?echo time();?>"></script>
    <!--<script type="text/javascript" src="js/dinamic_legend.js?r=<?echo time();?>"></script>-->

    <script>
    	var user_type="";
    	var sort_field="tel_nom";
    	var sort_direction="asc";
    	var scroll_down=false;
    	var hostname=location.hostname.replace("xn--e1affem4a.","");
    </script>
    <script src="https://офис.димс.рф/js/check_auth.js" type="text/javascript"></script>
</HEAD>
</HEAD>
<body>

<script>
	var server="https://офис.димс.рф";
	var operator=<?php echo "'".$_GET["operator"]."'";?>;
	var date_picker_is_active=false;
	var user_name="";
	var api=0;

	$(document).ready(function() {
		check_auth();

		$("#main_tabs").tabs({
			fx: { opacity: "toggle", duration: "slow" },
			spinner: 'Загрузка...'
		});

		if (operator=="meg") {
			$("#operator_logo").attr("href", "https://офис.димс.рф/img/megafon_icon.png");
			$("#main_tabs .ui-tabs-nav").before("<img src='https://офис.димс.рф/img/megafon_logo.png' style='width:68px; position:absolute; left:7px; top:6px'>");
		} else if (operator=="tele2") {
			$("#operator_logo").attr("href", "https://офис.димс.рф/img/"+operator+"_icon.ico");
			$("#main_tabs .ui-tabs-nav").before("<img src='https://офис.димс.рф/img/"+operator+"_logo.png' style='width:68px; position:absolute; left:7px; top:6px'>");
		} else {
			$("#operator_logo").attr("href", "https://офис.димс.рф/img/"+operator+"_icon.png");
			$("#main_tabs .ui-tabs-nav").before("<img src='https://офис.димс.рф/img/"+operator+"_logo.png' style='width:68px; position:absolute; left:7px; top:6px'>");
		}

		if (operator!="bee") {
			$("#call_types_bee_plus").hide();
		}

		make_navigation();

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
				},
				"Сохранить": function() {
					if ($(this).dialog("option","title")=="Редактировать комментарий") {
						date=$("#alert_dialog .date_picker").val();
						old_date=$("#alert_dialog textarea").attr("date");
						comment_id=$("#alert_dialog textarea").attr("comment_id");
						tel_nom=$("#alert_dialog textarea").attr("tel_nom");
						save_comment(comment_id, tel_nom, date, old_date, $("#alert_dialog textarea").val(), 2, 0);
					} else {
						comment_id=$("#alert_dialog textarea").attr("comment_id");
						tel_nom=$("#alert_dialog input").val();
						date= $("#alert_dialog textarea").attr("date");
						save_comment(comment_id, tel_nom, date, 0, $("#alert_dialog textarea").val(), 1, 0);
					}
					$("#alert_dialog textarea").blur();
				},
				"Изменить": function() {
					if ($(this).dialog("option","title")=="Редактировать комментарий") {
						date=$("#alert_dialog .date_picker").val();
						old_date=$("#alert_dialog textarea").attr("date");
						comment_id=$("#alert_dialog textarea").attr("comment_id");
						tel_nom=$("#alert_dialog textarea").attr("tel_nom");
						save_comment(comment_id, tel_nom, date, old_date, $("#alert_dialog textarea").val(), 2, 1);
						$(this).dialog("close");
					} else {
						comment_id=$("#alert_dialog textarea").attr("comment_id");
						tel_nom=$("#alert_dialog input").val();
						date= $("#alert_dialog textarea").attr("date");
						save_comment(comment_id, tel_nom, date, date, $("#alert_dialog textarea").val(), 1, 1);
					}
					$("#alert_dialog textarea").focus();
				}
			},
			open:function(event,ui) {
				$(this).dialog("option", "position", "center");
			},
			close:function(event,ui) {
				date_picker_is_active=false;
			},
			resizable: true,
			maxHeight: 500,
			width:300
		});

		$("#alert_dialog_profile").dialog({
			modal: true,
			resizable: false,
			autoOpen: false,
			buttons: {
				"Сохранить": function() {
					$(this).dialog("close");
					save_user_profile();
				},
			},
			resizable: true,
			maxHeight: 500,
			width:300,
			title: "Профиль пользователя"
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
		
		$.contextMenu({
	        selector: '.to_number', 
	        build: function ($trigger, e) {
                return {
			        callback: function(key, options) {
			        	if (key=="mark_as_done") {
			        		attention_id=$(e.target).attr("row_id");
			        		attention_on_off(0, attention_id);
			        		get_attentions();
			        	}
					},
			   		items: {
			        	"mark_as_done": {name: "Отжать", icon: ""}    
			        }
			    };
		    }
	    });

		$("body").append("<script type='text/javascript' src='js/colorPicker.js'/>");

		$("#dinamic_main_tbl_header .sort").click(function(){
			if ($(".loading2").is(":visible")) {
				return false;
			}

			$("#dinamic_main_tbl_header .sort").find("span").html("");

			sort_field=$(this).attr("sort_field");
			sort_direction=$(this).attr("sort_direction");

			$("#dinamic_main_tbl_header .sort").each(function(){
				sort_field1=$(this).attr("sort_field");

				if ((sort_field1=="spended" || sort_field1=="update") && sort_field!=sort_field1) {
					$(this).attr("sort_direction", "desc");
				} else {
					$(this).attr("sort_direction", "asc");
				}
			});

			if (sort_direction=="asc") {
				$(this).attr("sort_direction", "desc");
				$(this).find("span").html("&#11014");
			} else {
				$(this).attr("sort_direction", "asc");
				$(this).find("span").html("&#11015");
			}
			get_dinamic_main(0);
		});

		$("#dinamic_main_tbl").parent().on("scroll", function(){
			if ($(this).scrollTop() + $(this).height() >= $("#dinamic_main_tbl").height()-100 && !data_loading) {
				data_loading=true;
			    draw_main_tbl(0);
			}

			div_height=$(this).height();

			if ($(this).scrollTop()>div_height) {
				$("#scroll_btn").show();
			} else {
				$("#scroll_btn").hide();
			}

			if ($(this).scrollTop() < $("#dinamic_main_tbl").height()-div_height) {
				$("#scroll_down_btn").show();
			} else {
				$("#scroll_down_btn").hide();
			}
		});

		$("#scroll_btn").click(function(){
			$("#dinamic_main_div").animate({scrollTop:0}, 1000);
		});

		$("#scroll_down_btn").click(function(){
			$(".loading2").show();
			scroll_down=true;
			setTimeout(function(){
				draw_main_tbl(0);
			},100);
		});

		$("#date_picker").blur(function(){
			date_picker_is_active=false;
		});

		$(document).keyup(function(e){
			if (date_picker_is_active==false) {
				return false;
			}

			e.stopPropagation();

			d=$("#date_picker").datepicker("getDate");
			if (e.keyCode==39) {
				d.setDate(d.getDate()+1);
			} else if (e.keyCode==37) {
				d.setDate(d.getDate()-1);
			} else if (e.keyCode==38) {
				d.setDate(d.getDate()-7);
			} else if (e.keyCode==40) {
				d.setDate(d.getDate()+7);
			} else if (e.keyCode==13) {
				$("#date_picker").datepicker("setDate", d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate());
				$(this).parent().next().find("a").css("border", "1px solid red");
				$(this).parent().next().find("a").focus();
				get_comments_by_date();
				return false;
			}

			$("#date_picker").datepicker("setDate", d.getFullYear()+"-"+(d.getMonth()+1)+"-"+d.getDate());
			$(this).parent().next().find("a").css("border", "1px solid red");
			$(this).parent().next().find("a").focus();

			get_comments_dates();
		});

		$("#search_comments_by_phone").bind("keyup", function(e){
			if (e.keyCode==13) {
				search_comments_by_phone($(this).val());
			}
		});

		$("#search_attentions_by_phone").bind("keyup", function(e){
			if (e.keyCode==13) {
				$("#roaming_td table tbody tr:not(:first)").empty();

				get_attentions(0,0);
				
				//get_roaming_list();
			}
		});

		$("#search_attentions_by_phone").click(function(){
			$(this).val("");
		});

		body_height=document.body.clientHeight;
		$("#main_tabs").css("height", body_height);
		$("#dinamic_main_div").css("height", body_height-157);
		$("#scroll_down_btn").css("top", body_height-64);

		//alert("Высота страницы: "+body_height+"; Высота истории: "+body_height*0.4+"; Высота детализации: "+body_height*0.31);
		//if (body_height>800) {
			$("#dinamic_details_tbl").parent().css("height", body_height*0.4);
			$("#dinamic_services_tbl").parent().css("height", body_height*0.4);
			$("#dinamic_packeges_tbl").parent().css("height", body_height*0.4);
			$("#calls_list").parent().css("height", body_height*0.31);
			$("#detal_totals").parent().css("height", body_height*0.31);
		/*} else {
			$("#dinamic_details_tbl").parent().css("height", body_height-400);
			$("#dinamic_services_tbl").parent().css("height", body_height-400);
			$("#dinamic_packeges_tbl").parent().css("height", body_height-400);
			$("#calls_list").parent().css("height", body_height-430);
			$("#detal_totals").parent().css("height", body_height-430);
		}*/
		$("#comments_td div").css("height", body_height-390);
		
		$("#legend_services, #legend").css("height", body_height-64);

		body_width=document.body.clientWidth;

		$("#main_tabs").css("width", body_width);

		get_contracts_names();

		$("#dinamic_overview").show();
		$("#dinamic_details").hide();
		get_attentions(0,0);
		get_comments_today();
		get_comments_dates();
		//get_roaming_list();
		get_dinamic_main(1, "tel_nom", "asc");
		
	});

	contracts_names=Array();

	function get_contracts_names() {
		$.ajax({
			url: "/php/get_contracts_names.php",
			data: {operator:operator},
			type: "POST",
			async:false,
			success: function (data) {
				contracts_names=JSON.parse(data);
			}
		});
	}

	var main_tbl_data=Array();

	function get_dinamic_main(build_filters, loss_filter, ss_spended_diff_filter, year, month) {
		$(".loading2").show();

		$("#dinamic_details").hide();
		$("#dinamic_overview").show();

		if (typeof loss_filter == 'undefined') {
			loss_filter=0;
		}

		if (typeof ss_spended_diff_filter == 'undefined') {
			ss_spended_diff_filter=0;
		}

		if (loss_filter==0 && ss_spended_diff_filter==0) {
			$("#filter_msg").text("");
		}

		status=$("#number_status option:selected").val();

		show_block_numbers=0;
		if (status==1) {
			show_block_numbers=1;
		}

		show_removed_numbers=0;
		if (status==4) {
			show_removed_numbers=1;
		}

		tarif_filter=Array();

		$("#tarif_sel option:selected").each(function(){
			tarif_filter.push($(this).val());
		});

		contract_filter=Array();

		$("#contract_sel option:selected").each(function(){
			contract_filter.push($(this).val());
		});

		account_filter=Array();

		$("#account_sel option:selected").each(function(){
			account_filter.push($(this).val());
		});

		client_groups_filter=$("#client_groups_sel option:selected").val();

		calc_total_stats=0;
		if (typeof client_groups_filter !== "undefined") {
			calc_total_stats=1;
		}

		$.ajax({
			url:"/php/get_main_tbl.php",
			data:{operator:operator, sort_field:sort_field, sort_direction:sort_direction, tarif_filter:JSON.stringify(tarif_filter), contract_filter:JSON.stringify(contract_filter), account_filter:JSON.stringify(account_filter), client_groups_filter:client_groups_filter, status:status, tel_nom:$("#tel_nom_input").val(), loss_filter:loss_filter, ss_spended_diff_filter:ss_spended_diff_filter, year:year, month:month},
			type:"POST",
			success: function(data) {
				var data=JSON.parse(data);

				var tarifs_filter=Array();
				var accounts_filter=Array();
				var contracts_filter=Array();
				var client_groups_filter=Array();

				nom=0;
				data_empty=Array();
				
				$("#dinamic_main_tbl tbody").empty();
				$("#main_tbl").attr("page_nom",0);
				$("#dinamic_main_div").scrollTop(0);
				main_tbl_data=Array();

				if (data==null) {
					$(".loading2").hide();
					return false;
				}

				ii=-1;
				for (var i=0; i<data["result"].length; i++) {
					main_tbl_data[i]=Array();

					start_field=sort_field.replace("remain","start");

					ii++;

					main_tbl_data[ii]["tel_nom"]=data["result"][i]["tel_nom"];
					main_tbl_data[ii]["name"]=data["result"][i]["name"];
					main_tbl_data[ii]["tarif"]=data["result"][i]["tarif"];
					main_tbl_data[ii]["refresh_date"]=data["result"][i]["refresh_date"];
					main_tbl_data[ii]["blocks"]=data["result"][i]["blocks"];
					main_tbl_data[ii]["contract"]=data["result"][i]["contract"];
					main_tbl_data[ii]["account"]=data["result"][i]["account"];
					main_tbl_data[ii]["balance"]=data["result"][i]["balance"];
					main_tbl_data[ii]["spended"]=data["result"][i]["spended"];
					main_tbl_data[ii]["blocks"]=data["result"][i]["blocks"];
					main_tbl_data[ii]["comment"]=data["result"][i]["comment"];

					if (main_tbl_data[ii]["name"]==null) {
						main_tbl_data[ii]["name"]="";
					}

					if (data["result"][i]["actual"]>0) {
						actual=new Date();
						actual.setTime(data["result"][i]["actual"]+"000");
						main_tbl_data[ii]["actual"]=addZero(actual.getMonth()+1)+"-"+addZero(actual.getDate())+" "+addZero(actual.getHours())+":"+addZero(actual.getMinutes());
					} else {
						main_tbl_data[ii]["actual"]="";
					}

					if (data["result"][i]["refresh_date"]=="70-01-01") {
						main_tbl_data[ii]["refresh_date"]="";
					} else {
						main_tbl_data[ii]["refresh_date"]=data["result"][i]["refresh_date"];
					}

					if (tarifs_filter.indexOf(data["result"][i]["tarif"])==-1 && data["result"][i]["tarif"]!="Удалён") { 
						tarifs_filter.push(data["result"][i]["tarif"]);
					}

					if (accounts_filter.indexOf(data["result"][i]["account"])==-1 && parseInt(data["result"][i]["account"])!=0) { 
						accounts_filter.push(data["result"][i]["account"]);
					}

					if (contracts_filter.indexOf(data["result"][i]["contract"])==-1 && parseInt(data["result"][i]["contract"])!=0) { 
						contracts_filter.push(data["result"][i]["contract"]);
					}

					if (client_groups_filter.indexOf(data["result"][i]["client_group"])==-1 && data["result"][i]["client_group"]!="") { 
						client_groups_filter.push(data["result"][i]["client_group"]);
					}
				}

				if (build_filters==1) {
					tarifs_filter.sort();
					accounts_filter.sort();
					contracts_filter.sort();
					client_groups_filter.sort();

					client_groups_filter.unshift("Не в группе");
					client_groups_filter.unshift("Все группы");

					make_filters(accounts_filter, contracts_filter, tarifs_filter, client_groups_filter);

					var tel_noms=Array();
					for (i=0; i<main_tbl_data.length; i++) {
						tel_noms.push(parseInt(main_tbl_data[i]["tel_nom"]));
					}
					tel_noms.sort();

					for (i=0;i<tel_noms.length;i++) {
						$("#comment_tel_nom").append("<option value='"+tel_noms[i]+"'>"+tel_noms[i]+"</option>");
					}

					$("#dinamic_overview_header").show();
				}

				draw_main_tbl(data["calc_total_stats"], data["total_balance"], data["total_spended"]);

				if (main_tbl_data.length*18>$("#dinamic_main_div").height()) {
					$("#scroll_down_btn").show();
				} else {
					$("#scroll_down_btn").hide();
				}

				$(".loading2").hide();
			}
		});
	}

	function draw_main_tbl(calc_total_stats, total_balance, total_spended) {
		page_nom=parseInt($("#main_tbl").attr("page_nom"));

		if (page_nom==0) {
			$("#dinamic_main_tbl tbody").empty();
		}

		if (scroll_down==true) {
			max=main_tbl_data.length;
		} else {
			max=page_nom*60+60;
		}

		if (calc_total_stats==1) {
			txt="<tr style='background:yellow'>";
			txt+="<td colspan=6></td>";
			txt+="<td>"+number_format(total_balance, 2, ',', ' ')+"</td>";
			txt+="<td>"+number_format(total_spended, 2, ',', ' ')+"</td>";
			txt+="<td colspan=2></td>";
			txt+="</tr>";

			$("#dinamic_main_tbl tbody").append(txt);
		}

		for (i=page_nom*60; i<max; i++) {
			if (typeof main_tbl_data[i]=='undefined') {
				break;
			}

			class_block="";
			if (main_tbl_data[i]["blocks"]!="-" && main_tbl_data[i]["blocks"]!="") {
				class_block="class_block";
				main_tbl_data[i]["class"]="class_block";
			} else {
				main_tbl_data[i]["class"]="";
			}

			spended=number_format(main_tbl_data[i]["spended"], 2, ",", " ");

			balance=number_format(main_tbl_data[i]["balance"], 2, ",", " ");

			txt="<tr tel_nom='"+main_tbl_data[i]["tel_nom"]+"' status='"+main_tbl_data[i]["tarif"]+"' class='"+class_block+"' title='"+main_tbl_data[i]["blocks"]+"'>";
			txt+="<td class='noExcel'><input type='checkbox' style='margin-left:5px' class='checkbox'/></td>";
			txt+="<td class='main_nom'>"+(i+1)+"</td>";
			txt+="<td class='main_account' style='display:none;'>"+main_tbl_data[i]["account"]+"</td>";
			txt+="<td class='main_contract' style='display:none;'>"+main_tbl_data[i]["contract"]+"</td>";
			txt+="<td><a class='main_tel_nom' href='javascript:'>"+main_tbl_data[i]["tel_nom"]+"</a></td>";
			txt+="<td class='main_name' title='"+main_tbl_data[i]["name"]+"'><div class='short_text'>"+main_tbl_data[i]["name"]+"</div></td>";
			txt+="<td class='main_tarif' title='"+main_tbl_data[i]["tarif"]+"'><div class='short_text'>"+main_tbl_data[i]["tarif"]+"</div></td>";
			txt+="<td class='main_refresh_date'>"+main_tbl_data[i]["refresh_date"]+"</td>";
			txt+="<td class='main_balance'>"+balance+"</td>";
			txt+="<td class='main_spended'>"+spended+"</td>";
			txt+="<td class='main_comment' title='"+main_tbl_data[i]["comment"]+"'><div class='short_text'>"+main_tbl_data[i]["comment"]+"</div></td>";
			txt+="<td class='main_actual'>"+main_tbl_data[i]["actual"]+"</td>";

			$("#dinamic_main_tbl tbody").append(txt);

			$(".main_tel_nom:contains('"+main_tbl_data[i]["tel_nom"]+"')").bind("click", function(){
				tel_nom=$(this).parent().parent().attr("tel_nom");

				window.open("client_details.php?operator="+operator+"&tel_nom="+tel_nom, "_blank");
			});
		}

		$(".main_comment").on("click", function(){
			tel_nom=$(this).parent().attr("tel_nom");
			comment=$(this).find("div").html();

			txt="<div id='new_single_comment_div' style='width:400px; height:110px; position: absolute; z-index: 1000; top: 25%; left: 40%; background: grey; border-radius:10px; border:2px solid black' tel_nom="+tel_nom+">";
			txt+="<div style='width:100%; text-align:center; font-weight:bold'>Комментарий</div>";
			txt+="<input class='new_single_comment' style='width:300px' value='"+comment+"'/><BR>";
			txt+="<button onclick='save_single_comment()' style='margin-top:5px'>Сохранить</button>";
			txt+="<button style='margin-left:5px'>Отменить</button>";
			txt+="<div class='error' style='width:100%; font-weight:bold; color:red; display:none'>Какая-то ошибка</div>";
			txt+="</div>";

			$("body").append(txt);
			
			$("#new_single_comment_div .new_single_comment").focus();

			$("#new_single_comment_div button:eq(1)").click(function(){
				$("#new_single_comment_div").remove();
			});
		});

		if (scroll_down==true) {
			$("#main_tbl").attr("page_nom",1000);
			$("#dinamic_main_div").animate({scrollTop:$("#dinamic_main_tbl").height()},1000);
			scroll_down=false;
			$(".loading2").hide();
		} else {
			$("#main_tbl").attr("page_nom", page_nom+1);
		}

		data_loading=false;
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

	function to_start_page() {
		window.location.href="/";
	}

	function to_excel(tbl_name, name) {
		$("#"+tbl_name).table2excel({
			exclude: ".noExcel",
			name: name+" "+operator,
			filename: operator+"_dinamic"
		});
	}

	function short_left(str) {
		str=String(str);
		if (str.length>5) {
			return str.substring(2);
		} else {
			return str;
		}
	}

	function month_select_show(action) {
		//action 1 - 1S
		//action 2 - loss

		$("#monthes_select").remove();

		if (action==1) {
			action="1S";
			var offset = $('#1s_btn').offset();
		} else {
			action="loss";
			var offset = $('#loss_btn').offset();
		}

		$("body").append("<div id='monthes_select' action='"+action+"'></div>");

		$("#monthes_select").css("left", offset.left+10);
		$("#monthes_select").css("top", offset.top+32);

		monthes=Array("январь", "февраль", "март", "апрель", "май", "июнь", "июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь");

		d=new Date();
		current_year=d.getFullYear();
		current_month=d.getMonth()+1;

		year_month=Array();

		txt="";

		for (year=2015; year<current_year+1; year++) {
			if (year==current_year) {
				to_month=current_month;
			} else {
				to_month=12;
			}

			if (year==current_year-1) {
				from_month1=1;
			} else {
				from_month1=1;
			}

			for (month=from_month1; month<to_month+1; month++) {
				txt="<div class='option' year='"+year+"' month='"+month+"' type='by_month'>"+year+" "+monthes[month-1]+"</div>";

				$("#monthes_select").prepend(txt);
			}

			txt="<div class='option all_year' year='"+year+"' type='by_year'>"+year+" ВЕСЬ ГОД</div>";

			$("#monthes_select").prepend(txt);
		} 			

		$("#monthes_select .option").click(function(){
			action=$(this).parent().attr("action");
			year=$(this).attr("year");
			month=$(this).attr("month");
			type=$(this).attr("type");

			$("#monthes_select").remove();

			get_stats(year, month, type, action, "");
		});

		function get_stats(year, month, type, $action, sort_direction) {
			$.ajax({
				url:"/php/dinamic/get_stats.php",
				data:{operator:operator, year:year, month:month, type:type, action:action, sort_direction:sort_direction},
				type:"POST",
				success: function(data) {
					data=JSON.parse(data);

					$("#stats_div").remove();

					txt="<div id='stats_div' style='text-align:center; width:680px; height:470px; position: absolute; z-index: 1001; top: 20%; left: 32%; background: rgb(80,80,80); border-radius:10px; border:2px solid black' year='"+year+"' month='"+month+"' year='"+action+"'>";
					txt+="<div>";
					txt+="<table><tr>";

					if (type=="by_month") { 
						monthes=Array("январь", "февраль", "март", "апрель", "май", "июнь", "июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь");
						period=year+" "+monthes[month-1];
						hide_arrows=false;
					} else {
						period=year+" ВЕСЬ ГОД";
						hide_arrows=true;
					}

					if (action=="1S") {
						if (hide_arrows==false) {
							txt+="<td style='width:520px; border:0; font-weight:bold; color:yellow; text-align:center'>";
							txt+="<span class='month_down'>&#9668;</span>&nbsp;";
							txt+="НЕСОВПАДЕНИЯ с 1С за "+period
							txt+="&nbsp;<span class='month_up'>&#9658;</span>";
							txt+="</td>";
						} else {
							txt+="<td style='width:520px; border:0; font-weight:bold; color:yellow; text-align:center'>";
							txt+="НЕСОВПАДЕНИЯ с 1С за "+period
							txt+="</td>";
						}
					} else {
						if (hide_arrows==false) {
							txt+="<td style='width:520px; border:0; font-weight:bold; color:yellow; text-align:center'>";
							txt+="<span class='month_down'>&#9668;</span>&nbsp;";
							txt+="УБЫТОК за "+period
							txt+="&nbsp;<span class='month_up'>&#9658;</span>";
							txt+="</td>";
						} else {
							txt+="<td style='width:520px; border:0; font-weight:bold; color:yellow; text-align:center'>";
							txt+="УБЫТОК за "+period
							txt+="</td>";
						}
					}

					txt+="<td style='border:0; width:150px; text-align:right'><img class='close_btn' src='/img/cancel.png' style='width:25px; cursor:pointer'/></td>";
					txt+="</tr></table>";
					txt+="</div>";
					txt+="<table style='margin-left: 40px'>";
					txt+="<tr>";
					
					tel_noms_list=Array();

					if (action=="1S") {
						txt+="<td style='border:0'>";
						txt+="<table id='stats_tbl_1s_thead'>";
						txt+="<thead><th>п/п</th><th>ПО</th><th>МЕСЯЦ</th><th>1С</th><th>НАЧ</th><th>+/-</th></thead>";
						txt+="</table>";

						txt+="<div style='width:475px; height:401px; overflow-y:auto; margin:0 auto'>";
						txt+="<table id='stats_tbl_1s' class='stats_tbl'>";
						txt+="<tbody>";

						for (i=0; i<data["result"].length; i++) {
							txt+="<tr tel_nom='"+data["result"][i]["tel_nom"]+"'>";
							txt+="<td>"+(i+1)+"</td>";
							txt+="<td class='tel_nom' operator='"+data["result"][i]["operator"]+"'><a href='javascript:'>"+data["result"][i]["tel_nom"]+"</a></td>";
							txt+="<td>"+data["result"][i]["year_month"]+"</td>";
							txt+="<td>"+data["result"][i]["1s"]+"</td>";
							txt+="<td>"+data["result"][i]["spended"]+"</td>";
							txt+="<td>"+data["result"][i]["diff"]+"</td>";
							txt+="</tr>";
						}
					} else {
						txt+="<td style='border:0; padding-left: 25px'>";
						txt+="<table id='stats_tbl_thead'>";

						if (sort_direction=="desc") {
							new_sort_direction="asc";
							arrow="&#11015";
						} else if (sort_direction=="asc") {
							new_sort_direction="desc";
							arrow="&#11014";
						} else {
							new_sort_direction="desc";
							arrow="";
						}

						txt+="<thead><th>п/п</th><th>ПО</th><th>МЕСЯЦ</th><th style='cursor:pointer' class='sort' sort_direction='"+new_sort_direction+"'><span >"+arrow+"</span>УБЫТОК</th></thead>";
						txt+="</table>";

						txt+="<div style='width:405px; height:401px; overflow-y:auto; margin:0 auto'>";
						txt+="<table id='stats_tbl' class='stats_tbl'>";
						txt+="<tbody>";

						total_spended_diff=0;
						for (i=0; i<data["result"].length; i++) {
							total_spended_diff+=data["result"][i]["spended_diff"];

							txt+="<tr tel_nom='"+data["result"][i]["tel_nom"]+"'>";
							txt+="<td>"+(i+1)+"</td>";
							txt+="<td class='tel_nom'><a href='javascript:'>"+data["result"][i]["tel_nom"]+"</a></td>";
							txt+="<td>"+data["result"][i]["year_month"]+"</td>";
							txt+="<td>"+data["result"][i]["spended_diff_formated"]+"</td>";
							txt+="</tr>";
						}

						total_spended_diff=(Math.round(total_spended_diff*100)/100);

						if (data["result"].length>0) {
							txt+="<tr style='background:yellow'><td style='border:0'></td><td style='border:0'><td style='border:0; color:black; font-weight:bold'>ИТОГО:</td><td style='color:black; font-weight:bold'>"+number_format(total_spended_diff, 2, ",", " ")+"</td></tr>";
						} else {
							txt+="<tr><td>За этот период данных нет</td></tr>";
						}
					}

					txt+="</tbody></table>";
					txt+="</div>";
					txt+="</td>";

					txt+="<td style='border:0'>";

					if (action=="loss") {
						txt+="<div class='total_spended_diff'></div>";
					} else {
						txt+="<div class='total_spended_diff' style='border:0px; background: rgb(80,80,80)'></div>";
					}

					txt+="<div style='margin-left: 11px; width: 123px; overflow-y: auto; font-size: 12px; text-align: center; background: aliceblue; height: 401px;'>";

					txt+="<div class='clickable'>Все номера</div>";
					for (i=0; i<data["tel_noms_list"].length; i++) {
						txt+="<div class='clickable'>"+data["tel_noms_list"][i]+"</div>";
					}

					txt+="</div>";
					txt+="</td>";
					txt+="</tr>";
					txt+="</div>";

					$("body").append(txt);
					$("#stats_div").draggable();

					$("#stats_div .month_down").on("click", function() {
						year=$("#stats_div").attr("year");
						month=$("#stats_div").attr("month");

						newDate=deltaDate(new Date(year, month-1, 1), 0, -1, 0);

						get_stats(newDate.getFullYear(), newDate.getMonth()+1, "by_month", $action, "")
					});

					$("#stats_div .month_up").on("click", function() {
						year=$("#stats_div").attr("year");
						month=$("#stats_div").attr("month");

						newDate=deltaDate(new Date(year, month-1, 1), 0, 1, 0);

						get_stats(newDate.getFullYear(), newDate.getMonth()+1, "by_month", $action, "")
					});

					if (action=="loss") {
						$("#stats_div .total_spended_diff").text(number_format(total_spended_diff, 2, ",", " "));

						$("#stats_div .sort").bind("click", function(){
							sort_direction=$(this).attr("sort_direction");

							get_stats(year, month, type, action, sort_direction);
						});
					}
					$("#stats_div .tel_nom").bind("click", function(){
						tel_nom=$(this).text();
						operator=$(this).attr("operator");

						window.open("client_details.php?operator="+operator+"&tel_nom="+tel_nom, "_blank");
					});

					$("#stats_div .clickable").bind("click", function() {
						selected_tel_nom=$(this).text();

						if (selected_tel_nom=="Все номера") {
							$(".stats_tbl tr").show();
						} else {
							$(".stats_tbl tbody tr").each(function(){
								if (selected_tel_nom==$(this).attr("tel_nom")) {
									$(this).show();
								} else {
									$(this).hide();
								}
							});
						}
					});

					$("#stats_div .close_btn").bind("click", function() {
						$("#stats_div").remove();
					});
				}
			});
		}

		$(document).mouseup(function(e){ // событие клика по веб-документу
			var div = $( "#monthes_select" ); // тут указываем ID элемента
			if ( !div.is(e.target) // если клик был не по нашему блоку
			    && div.has(e.target).length === 0 ) { // и не по его дочерним элементам
				div.remove(); // скрываем его
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

	function to_office() {
		window.open("https://xn--h1alkk.xn--d1aimu.xn--p1ai/dinamic1.php?operator="+operator, "_blank");
			
		return false;
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

<table id='color_picker'></table>

<div id="sort_form_service" row_id="" onclick='form_sort_hide()' title='Закрыть'><table><tr><td><img id='move_up_arrow' src='img/up.png' style='width:45px'/></td><td>Укажите, куда переместить позицию,<BR>кликнув по номеру строки</td><td><img id='move_down_arrow' src='img/down.png' style='width:45px'/></td></tr></table></div>

<div id="alert_dialog"></div>
<div id="alert_dialog_profile"></div>

<div id="scroll_btn"><img src='https://офис.димс.рф/img/arrow_top.png' style='width:38px'/></div>
<div id="scroll_down_btn"><img src='https://офис.димс.рф/img/arrow_down.png' style='width:38px'/></div>

<div id="sort_form_service" row_id="" onclick='form_sort_hide()' title='Закрыть'><table><tr><td><img id='move_up_arrow' src='https://офис.димс.рф/img/up.png' style='width:45px'/></td><td>Укажите, куда переместить позицию,<BR>кликнув по номеру строки</td><td><img id='move_down_arrow' src='https://офис.димс.рф/img/down.png' style='width:45px'/></td></tr></table></div>

<div id="sort_form" row_id="" onclick='form_sort_hide()' title='Закрыть'><table><tr><td><img id='move_up_arrow' src='https://офис.димс.рф/img/up.png' style='width:45px'/></td><td>Укажите, куда переместить позицию,<BR>кликнув по номеру строки</td><td><img id='move_down_arrow' src='https://офис.димс.рф/img/down.png' style='width:45px'/></td></tr></table></div>

<div id="main_tabs">
   <ul>
   	     <li><a href="#dinamic_main" onclick="selected_tab=1; get_dinamic_main(1); get_attentions(0,0); get_comments_today();">Динамика</a></li>
   	     <li><a href="#call_types_div" onclick="selected_tab=2; $('#dinamic_details').hide(); bee_plus=false; get_call_types_prices();">Типы</a></li>
   	     <li id='call_types_bee_plus'><a href="#call_types_div" onclick="selected_tab=6; $('#dinamic_details').hide(); bee_plus=true; get_call_types_prices()">Типы BEE+</a></li>
   	     <li><a href="#services_settings_div" onclick="selected_tab=3; get_services_settings()">Услуги</a></li>
   	     <li><a href="#exceptions_div" onclick="selected_tab=5; get_exceptions()">Исключения</a></li>
   	     <li id="to_office"><div href="javascript:" onclick="to_office()">в ОФ</div></li>
   	     <!--<li><a href="#roaming_settings_div" onclick="selected_tab=4; get_roaming_settings()">В стране</a></li>-->
   	     <li><a href="" onclick="window.location.reload(true)">Обновить</a></li>
   </ul>

   <div id="dinamic_main">
   		<div id="dinamic_overview" style="text-align:left;">
   			<div id="dinamic_overview_header" style="display:none">
		   		<input id="tel_nom_input" onclick="this.value=''" placeholder='Поиск по номеру'/>

		   		<select id="client_groups_sel" onchange="get_dinamic_main(0);"></select>

		   		<select id="contract_sel" style="width:150px" multiple onchange="get_dinamic_main(0);"></select>
		   		<select id="account_sel" style="width:150px" multiple onchange="get_dinamic_main(0);"></select>
		   		<select id="tarif_sel" style="width:300px" multiple onchange="get_dinamic_main(0);"></select>

		   		<select id="number_status" onchange="get_dinamic_main(0);">
		   			<option value='1'>Все</option>
		   			<option value='2'>Заблок-нные</option>
		   			<option value='3'>Активные</option>
		   			<option value='4'>Удалённые</option>
		   		</select>

		   		<img src='https://офис.димс.рф/img/find.jpg' style='width:32px; cursor:pointer; margin-left:20px' onclick='show_checked()' title='Показать выбранные'/>
		   		<img id="1s_btn" src="https://офис.димс.рф/img/1s.png" style='width:31px; cursor:pointer' onclick="month_select_show(1)" title="Несовпадения с 1С" style="width:27px; cursor:pointer; margin-left:80px;">
		   		<img id="loss_btn" src="https://офис.димс.рф/img/loss.png" style='width:32px; cursor:pointer' onclick="month_select_show(2)" title="Убыток" style="width:27px; cursor:pointer; margin-left:80px;">
		   		<img src='https://офис.димс.рф/img/cancel.png' style='width:35px; cursor:pointer' onclick='cancel_filters()' title='Сбросить фильтры'/>
		   		<img src="https://офис.димс.рф/img/to_excel.png" onclick="to_excel('dinamic_main_tbl', 'Динамика')" title="Экспорт в Excel" style="width:50px; margin-left:40px; cursor:pointer;">
		   		<div id="filter_msg" style="display:inline; font-size:10px; font-weight:bold; text-decoration: underline; margin-left:20px;"></div>
		   	</div>

	   		<table id="main_tbl" page_nom=0>
	   			<tr>
	   				<td rowspan=2 style='vertical-align:top;'>
	   					<table id="dinamic_main_tbl_header">
							<thead>
					   			<th class='noExcel'><input type="checkbox" id="main_select_all" onclick="select_or_clear_all()"/></th>
					   			<th class='noExcel'>п/п</th>
					   			<th class='sort noExcel' sort_field='tel_nom' sort_direction='desc' title='Сортировать'><span>&#11014;</span>ПО</th>
					   			<th class='noExcel'>ФИО</th>
					   			<th class='noExcel'>ТП</th>
					   			<th class='sort noExcel' sort_field='refresh_date' sort_direction='asc' title='Сортировать'><span></span>ОБН.</th>
					   			<th class='sort noExcel' sort_field='balance' sort_direction='desc' title='Сортировать'><span></span>БАЛ.</th>
					   			<th class='sort noExcel' sort_field='spended' sort_direction='desc' title='Сортировать'><span></span>НАЧ.</th>
					   			<th class='sort noExcel' title='Сортировать'><span></span>КОММЕНТ</th>
					   			<th class='sort noExcel' sort_field='actual' sort_direction='desc' title='Сортировать'><span></span>АКТУАЛ.</th>
					   		</thead>
	   					</table>
				   		<div id='dinamic_main_div'>
					   		<table id="dinamic_main_tbl">
					   				<tbody></tbody>
					   		</table>
					   	</div>
					</td>
					<td style="vertical-align:top; height:200px; padding-left:15px;">
			   			<div id="date_picker"></div>
			   		</td>
			   		<td rowspan=2 id="roaming_td" style="text-align:center; vertical-align:top; width:205px;">
			   			<div style="width:230px; height:755px; overflow-y:auto">
					   		<table id="roaming_tbl" style='width:200px'>
					   			<thead onclick='window.open("reports.php?roaming_show=1")'><th>!!!ВНИМАНИЕ!!!</th></thead>
					   			<tbody>
					   				<tr><td><input id='search_attentions_by_phone' style='background:yellow; border-radius:8px; width:130px; margin-left:32px; margin-top:5px; font-size:13px; text-align:center' placeholder='Поиск по номеру'/></td></tr>
					   			</tbody>
					   		</table>
					   	</div>
				   	</td>
			   	</tr>
			   	<tr>
				   	<td id="comments_td" style="text-align:center; vertical-align:top; width:205px;">
				   		<input id='search_comments_by_phone' style='background:yellow; border-radius:8px; width:130px; margin-left:15px; margin-top:5px; font-size:13px; text-align:center' onclick="this.value=''" placeholder='Поиск по номеру'/>
				   		<div style='width:205px; height:300px; margin-left:15px; overflow-y:auto;'></div>
				   	</td>
			   	</tr>
			</table>
		</div>
 </div>

<div id="call_types_div">
	<table id="call_types_tbl">
		 <thead><th>п/п</th><th>Код</th><th>Тип</th><th>Цена за ед.</th><th class='msg' colspan=3></th></thead>
		 <tbody>
		 			
		 </tbody>
		</table>
</div>

<div id="services_settings_div">
	 <table id="dinamic_legend_services_tbl">
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
</div>

</body>
</html>