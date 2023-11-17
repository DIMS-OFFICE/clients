<html>
	<title>
		Клиентское приложение
	</title>
	<head>
		<script type="text/javascript" src="https://офис.димс.рф/js/jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js.min"></script>

		<style>
			html {
		        background: url(https://офис.димс.рф/img/bg1.jpg) no-repeat center center fixed;
		        -webkit-background-size: cover;
		        -moz-background-size: cover;
		        -o-background-size: cover;
		        background-size: cover;
			}

			.main_div {
				width: 100%;
			}

			.main_button:active {
			  top: .1em;
			  left: .1em;
			  box-shadow: 0 0 0 60px rgba(0,0,0,.05) inset;
			}

			/* Контейнер для кнопки */
			.css-modal-checkbox-container {
			    height: 60px;
			    display:flex;
			    align-items:center;
			    justify-content:center;
			}

			/* Убираем флажок */
			#css-modal-checkbox {
			    display: none;
			}

			/* Кнопка для открытия */
			.main_button {
			  cursor: pointer;
			  position: relative;
			  display: inline-block;
			  width: 10em;
			  height: 2.5em;
			  line-height: 2.5em;
			  vertical-align: middle;
			  text-align: center;
			  text-decoration: none;
			  text-shadow: 0 -1px 1px #777;
			  color: #fff;
			  outline: none;
			  border: 2px solid #F64C2B;
			  border-radius: 5px;
			  box-shadow: 0 0 0 60px rgba(0,0,0,0) inset, .1em .1em .2em #800;
			  background: linear-gradient(#FB9575, #F45A38 48%, #EA1502 52%, #F02F17);
			  margin-top: 5px;   
			}
			 
			/* Контейнер, который затемняет страницу */
			#css-modal-checkbox + .cmc {
			    display: none;
			}

			#css-modal-checkbox:checked + .cmc {
			    display:flex;
			    align-items:center;
			    justify-content:center;    
			    z-index: 4;
			    position: fixed;
			    left: 0;
			    top: 0;
			    width: 100%;
			    height: 100%;
			    animation: bg 0.5s ease;
			    background: rgba(51, 122, 183, 0.7);
			}
			 
			/* Модальное окно */
			#css-modal-checkbox:checked + .cmc .cmt {
			    font-family: Verdana, sans-serif;  
			    font-size: 16px;
			    padding: 20px;
			    width:88%;
			    max-width: 600px;
			    max-height: 70%;
			    transition: 0.5s;
			    /*border: 6px solid #BFE2FF;*/
			    border-radius: 12px;
			    background: #FFF;
			    box-shadow: 0 4px 12px rgba(0,0,0,0.2), 0 16px 20px rgba(0,0,0,0.2);
			    text-align: center;
			    overflow: auto;
			    animation: scale 0.5s ease;
			}

			/* Кнопка с крестиком закрывающая окно */
			.css-modal-close {
			    content: "";
			    width: 50px;
			    height: 50px;
			    border: 6px solid #BFE2FF;
			    border-radius: 12px;
			    position: absolute;
			    z-index: 10;
			    top: 20px;
			    right: 20px;
			    box-shadow: 0 4px 12px rgba(0,0,0,0.2), 0 16px 20px rgba(0,0,0,0.2);
			    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23337AB7' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3e%3cline x1='18' y1='6' x2='6' y2='18'%3e%3c/line%3e%3cline x1='6' y1='6' x2='18' y2='18'%3e%3c/line%3e%3c/svg%3e");
			    background-color: #FFF;
			    background-size: cover;
			    animation: move 0.5s ease;
			    cursor: pointer;
			}
			 
			/* Анимации */
			@keyframes scale {
			    0% {
			        transform: scale(0);
			    }
			    100% {
			        transform: scale(1);
			    }
			}
			@keyframes move {
			    0% {
			        right: -80px;
			    }
			    100% {
			        right: 20px;
			    }
			}
			@keyframes bg {
			    0% {
			        background: rgba(51, 122, 183, 0);
			    }
			    100% {
			        background: rgba(51, 122, 183, 0.7);
			    }
			}

			#current_stats {
				margin: 0 auto;
			    width: 270px;
			    font-size: 20px;
			    text-align: center;
			    background: radial-gradient(white, lightgrey);
			    border-radius: 10px;
			    box-shadow: 0 0 0 60px rgb(0 0 0 / 0%) inset, 0.1em 0.1em 0.2em darkslategrey;
			}

			.date_down, .date_up {
				cursor: pointer;
			}

			.info_table {
				width: 100%;
				border: none;
				margin-bottom: 20px;
				border-collapse: separate;
			}
			.info_table thead th {
				font-weight: bold;
				text-align: left;
				border: none;
				padding: 10px 15px;
				background: #EDEDED;
				font-size: 14px;
				border-top: 1px solid #ddd;
			}
			.info_table tr th:first-child, .info_table tr td:first-child {
				border-left: 1px solid #ddd;
			}
			.info_table tr th:last-child, .info_table tr td:last-child {
				border-right: 1px solid #ddd;
			}
			.info_table thead tr th:first-child {
				border-radius: 20px 0 0 0;
			}
			.info_table thead tr th:last-child {
				border-radius: 0 20px 0 0;
			}
			.info_table tbody td {
				text-align: left;
				border: none;
				/*padding: 10px 15px;*/
				font-size: 12px;
				vertical-align: top;
				text-align: center;
			}
			.info_table tbody tr:nth-child(even) {
				background: #F8F8F8;
			}
			.info_table tbody tr:last-child td{
				border-bottom: 1px solid #ddd;
			}
			.info_table tbody tr:last-child td:first-child {
				border-radius: 0 0 0 20px;
			}
			.info_table tbody tr:last-child td:last-child {
				border-radius: 0 0 20px 0;
			}

			.td_date {
				width: 100px;
			}

			* {
			  scrollbar-width: thin;
			  scrollbar-color: blue orange;
			}

			/* для Chrome/Edge/Safari */
			*::-webkit-scrollbar {
			  height: 12px;
			  width: 12px;
			}
			*::-webkit-scrollbar-track {
			  background: lightgrey;
			}
			*::-webkit-scrollbar-thumb {
			  background-color: royalblue;
			  border-radius: 7px;
			  border: 2px solid orange;
			}

			#main_menu_content {
				height: 0px;
				display: none;
				overflow: hidden;
				width: 125px;
			    margin-top: 10px;
			    background: aliceblue;
			    padding: 4px;
			    border-radius: 7px;
			    border: 1px solid black;
			    box-shadow: 0 0 0 60px rgb(0 0 0 / 0%) inset, 0.1em 0.1em 0.2em darkslategrey;
			}

			#main_menu_div {
				position: absolute;
				z-index: 1000;
				left: 10px;
				height: 10px;
			}

			#main_menu_div .button {
				width: 50px;
			    height: 30px;
			    border-radius: 8px;
			    border: 0;
			    font-weight: bold;
			    font-size: 25px;
			    background: lightgrey;
			    text-align: center;
			    box-shadow: 0 0 0 60px rgb(0 0 0 / 0%) inset, 0.1em 0.1em 0.2em darkslategrey;
			    cursor: pointer;
			}

			.tel_noms_select, .functions_btn {
				cursor: pointer;
			}

			.tel_noms_select:hover, .functions_btn:hover {
				font-weight: bold;
			}
			.odd {
				background: white;
			}

			.even {
				background: lightgray;
			}

			.form_tbl {
				margin: 0 auto;
			}

			.form_tbl td {
				padding-top: 5px;
				text-align: center;
			}

			input {
				border-radius: 5px;
				background: yellow;
			}

			button {
				border-radius: 5px;
				width: 150px;
			}

			.error_msg {
				font-weight:bold; 
				color:red;
			}			
		</style>

		<script>
			tel_nom="";
			operator="";
			hash="";

			$(document).ready(function() {
				auth();

				$(document).mouseup( function(e){ // событие клика по веб-документу
					var div = $("#main_menu_div"); // тут указываем ID элемента
					if ( !div.is(e.target) // если клик был не по нашему блоку
					    && div.has(e.target).length === 0 ) { // и не по его дочерним элементам
						$("#main_menu_content").animate({height: 0}, 700, function(){
							$("#main_menu_content").hide();
						});
					}
				});

				$(".main_button").click(function(){
					action=$(this).attr("action");

					if (action=="balance_history") {
						current_date=new Date();

						current_year=current_date.getFullYear();
						current_month=current_date.getMonth();

						monthes=Array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "август", "сентября", "октября", "ноября", "декабря");

						$(".cmt").html("<span class='date_down'>&#9668;</span>&nbsp;&nbsp;&nbsp;<span id='year_month' year='"+current_year+"' month='"+current_month+"'>"+current_year+" "+monthes[current_month]+"</span>&nbsp;&nbsp;&nbsp;<span class='date_up'>&#9658;</span><HR><table class='info_table'><thead><th class='td_date'>Дата</th><th>Баланс</th><th>Нач.</th><th>Оплата</th></thead><tbody></tbody></table>");

						$(".date_down, .date_up").click(function() {
							selected_year=$("#year_month").attr("year");
							selected_month=$("#year_month").attr("month");

							if ($(this).attr("class")=="date_down") {
								new_date=addDate(new Date(selected_year, selected_month, 1), 0, -1, 0)
							} else {
								new_date=addDate(new Date(selected_year, selected_month, 1), 0, 1, 0)
							}

							selected_year=new_date.getFullYear();
							selected_month=new_date.getMonth();

							$("#year_month").attr("year", selected_year);
							$("#year_month").attr("month", selected_month);
							$("#year_month").text(selected_year+" "+monthes[selected_month]);

							get_balance_history();
						});

						get_balance_history();
					}

					if (action=="spended_history") {
						current_date=new Date();

						current_year=current_date.getFullYear();
						current_month=current_date.getMonth();

						monthes=Array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "август", "сентября", "октября", "ноября", "декабря");

						$(".cmt").html("<span class='date_down'>&#9668;</span>&nbsp;&nbsp;&nbsp;<span id='year_month' year='"+current_year+"' month='"+current_month+"'>"+current_year+" "+monthes[current_month]+"</span>&nbsp;&nbsp;&nbsp;<span class='date_up'>&#9658;</span><HR><table class='info_table'><thead><th>Категория</th><th>Объём</th><th>Цена</th><th>Сумма</th></thead><tbody></tbody></table>");

						$(".date_down, .date_up").click(function() {
							selected_year=$("#year_month").attr("year");
							selected_month=$("#year_month").attr("month");

							if ($(this).attr("class")=="date_down") {
								new_date=addDate(new Date(selected_year, selected_month, 1), 0, -1, 0)
							} else {
								new_date=addDate(new Date(selected_year, selected_month, 1), 0, 1, 0)
							}

							selected_year=new_date.getFullYear();
							selected_month=new_date.getMonth();

							$("#year_month").attr("year", selected_year);
							$("#year_month").attr("month", selected_month);
							$("#year_month").text(selected_year+" "+monthes[selected_month]);

							get_spended_history();
						});

						get_spended_history();
					}

					if (action=="detal") {
						current_date=new Date();

						current_year=current_date.getFullYear();
						current_month=current_date.getMonth();
						current_day=current_date.getDate();

						$(".cmt").html("<span class='date_down'>&#9668;</span>&nbsp;&nbsp;&nbsp;<span id='year_month' year='"+current_year+"' month='"+current_month+"' day='"+current_day+"'>"+current_year+"-"+addZero(current_month+1)+"-"+addZero(current_day)+"</span>&nbsp;&nbsp;&nbsp;<span class='date_up'>&#9658;</span><HR><table class='info_table'><thead><th>Время</th><th>Длит.</th><th>Номер</th><th></th><th>Сумма</th></thead><tbody></tbody></table>");

						$(".date_down, .date_up").click(function() {
							selected_year=$("#year_month").attr("year");
							selected_month=$("#year_month").attr("month");
							selected_day=$("#year_month").attr("day");

							if ($(this).attr("class")=="date_down") {
								new_date=addDate(new Date(selected_year, selected_month, selected_day), -1, 0, 0)
							} else {
								new_date=addDate(new Date(selected_year, selected_month, selected_day), 1, 0, 0)
							}

							selected_year=new_date.getFullYear();
							selected_month=new_date.getMonth();
							selected_day=new_date.getDate();

							$("#year_month").attr("year", selected_year);
							$("#year_month").attr("month", selected_month);
							$("#year_month").attr("day", selected_day);
							$("#year_month").text(selected_year+"-"+addZero(selected_month+1)+"-"+addZero(selected_day));

							get_calls_list();
						});

						get_calls_list();						
					}										
				});
			});

			function auth() {
				if ($.cookie('hash') == null) {
					window.location.href="start.php";

					return false;
				} else {
					hash=$.cookie('hash');

					by_hash=1;
				}

				$.ajax({
					url:"/mobile/php/auth.php",
					data:{hash:hash, by_hash:by_hash},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]=="OK") {
							hash=data["hash"];

							tel_nom=data["tel_nom"];

							$.cookie('hash', data["hash"]);

							get_current_stats();
						} else if (data["status"]=="wrong_hash") {
							window.location.href="start.php";
						}
					}
				});
			}

			function exit() {
				$.removeCookie('hash');

				window.location.href="start.php";
			}

			function change_password() {
				hash=$.cookie('hash');

				new_password1=$("#change_pass_tbl .new_password1").val();
				new_password2=$("#change_pass_tbl .new_password2").val();

				console.log(new_password1+":"+new_password2);

				if (new_password1!=new_password2) {
					$("#change_pass_tbl .error_msg").text("Пароли не совпадают");

					setTimeout(function(){
						$("#change_pass_tbl .error_msg").text("");
					},7000);

					return false;
				}

				if (new_password1.length<6) {
					$("#change_pass_tbl .error_msg").text("Пароль должен содержать минимум 6 знаков");

					setTimeout(function(){
						$("#change_pass_tbl .error_msg").text("");
					},7000);

					return false;
				}

				$.ajax({
					url:"/mobile/php/change_password.php",
					data:{new_password:new_password1, hash:hash},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]=="wrong_hash") {
							window.location.href="start.php";

							return false;
						} else if (data["status"]=="error") {
							$("#change_pass_tbl .error_msg").text("Ошибка");

							setTimeout(function(){
								$("#change_pass_tbl .error_msg").text("");
							},7000);
						} else if (data["status"]=="OK") {
							$("#change_pass_tbl .error_msg").text("Пароль изменён");

							setTimeout(function(){
								$("#change_pass_tbl .error_msg").text("");
							},7000);
						}
					}
				});
			}

			function get_current_stats() {
				hash=$.cookie('hash');

				$.ajax({
					url:"/mobile/php/get_current_stats.php",
					data:{tel_nom:tel_nom, hash:hash},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]=="error") {
							window.location.href="start.php";

							return false;
						}

						operator=data["operator"];

						$("#current_stats .current_tel_nom").text(tel_nom);
						$("#current_stats .activity_status").text(data["activity_status"]);
						$("#current_stats .current_balance").text("Баланс: "+data["current_balance"]+" руб.");
						$("#current_stats .current_spended").text("Начислено: "+data["current_spended"]+" руб.");

						txt="";
						for (i=0;i<data["group_tel_noms"].length;i++) {
							if (i%2==0) {
								txt+="<div class='tel_noms_select odd'>"+data["group_tel_noms"][i]+"</div>";
							} else {
								txt+="<div class='tel_noms_select even'>"+data["group_tel_noms"][i]+"</div>";
							}
						}

						txt+="<HR>";
						txt+="<label for='css-modal-checkbox' class='functions_btn change_pass_btn'>Сменить пароль</label>";
						txt+="<HR>";
						txt+="<div onclick='exit()' class='functions_btn'>Выход</div>";

						$("#main_menu_content").html(txt);

						$(".tel_noms_select").off();

						$(".tel_noms_select").on("click", function(){
							tel_nom=$(this).text();

							get_current_stats();
						});

						$(".change_pass_btn").off();

						$(".change_pass_btn").on("click", function(){
							txt="<table id='change_pass_tbl' class='form_tbl'>";
							txt+="<tr><td><input class='new_password1' placeholder='Новый пароль'/></td></tr>";
							txt+="<tr><td><input class='new_password2' placeholder='Новый пароль ещё раз'/></td></tr>";
							txt+="<tr><td class='error_msg' style='height:25px'></td></tr>";
							txt+="<tr><td><button>Сохранить</button></td></tr>";
							txt+="</table>";

							$(".cmt").html(txt);

							$(".form_tbl button").click(function(){
								change_password();
							})
						});
					}
				});
			}

			function get_balance_history() {
				hash=$.cookie('hash');

				year=$("#year_month").attr("year");
				month=parseInt($("#year_month").attr("month"))+1;

				$.ajax({
					url:"php/get_balance_history.php",
					data:{tel_nom:tel_nom, year:year, month:month, hash:hash},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]=="error") {
							window.location.href="start.php";

							return false;
						}

						txt="";

						for (i=0; i<data.length; i++) {
							txt+="<tr>";
							txt+="<td>"+data[i]["update_date"]+"</td>";
							txt+="<td>"+data[i]["balance"]+"</td>";
							txt+="<td>"+data[i]["spended"]+"</td>";
							txt+="<td>"+data[i]["payments"]+"</td>";
							txt+="</tr>";
						}

						$(".cmt tbody").html(txt);
					}
				});
			}

			function get_spended_history() {
				year=$("#year_month").attr("year");
				month=parseInt($("#year_month").attr("month"))+1;

				$.ajax({
					url:"../php/get_spended_details.php",
					data:{tel_nom:tel_nom, year:year, month:month},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						txt="";

						for (i=0; i<data.length; i++) {
							txt+="<tr>";
							txt+="<td>"+data[i]["category"]+"</td>";
							txt+="<td>"+data[i]["length"]+"</td>";
							txt+="<td>"+data[i]["unit_price"]+"</td>";
							txt+="<td>"+data[i]["sum"]+"</td>";
							txt+="</tr>";
						}

						$(".cmt tbody").html(txt);
					}
				});
			}

			function get_calls_list() {
				hash=$.cookie('hash');

				year=$("#year_month").attr("year");
				month=parseInt($("#year_month").attr("month"))+1;
				day=$("#year_month").attr("day");

				$.ajax({
					url:"php/get_calls_list.php",
					data:{operator:operator, tel_nom:tel_nom, year:year, month:month, day:day, hash:hash},
					type:"POST",
					async:false,
					success: function(data) {
						data=JSON.parse(data);

						if (data["status"]=="error") {
							window.location.href="start.php";

							return false;
						}

						txt="";

						if (data["calls"].length==0) {
							txt+="<tr>";
							txt+="<td colspan=5>За эту дату вызовов не было</td>";
							txt+="</tr>";
						} else {
							for (i=0; i<data["calls"].length; i++) {
								txt+="<tr>";
								txt+="<td>"+data["calls"][i]["call_time"]+"</td>";
								txt+="<td>"+data["calls"][i]["call_length"]+"</td>";
								txt+="<td>"+data["calls"][i]["phone"]+"</td>";

								if (data["calls"][i]["direction"]=="I") {
									txt+="<td style='color:red; font-weight:bold'>&#8601;</td>";
								} else {
									txt+="<td style='color:green; font-weight:bold'>&#8599;</td>";
								}

								txt+="<td>"+data["calls"][i]["price"]+"</td>";
								txt+="</tr>";
							}
						}

						$(".cmt tbody").html(txt);
					}
				});
			}

			function main_menu_show() {
				if ($("#main_menu_content").is(":visible")) {
					return false;
				}

				$("#main_menu_content").show();

				new_height=$("#main_menu_content")[0].scrollHeight;

				 $("#main_menu_content").animate({height: new_height}, 700);
			}

			function addDate(input, days, months, years) {
			    return new Date(
			      input.getFullYear() + years, 
			      input.getMonth() + months, 
			      Math.min(
			        input.getDate() + days,
			        new Date(input.getFullYear() + years, input.getMonth() + months + 1, 0).getDate()
			      )
			    );
			}

			function addZero(n) {
				if (n<10) {
					return "0"+n;
				} else {
					return n;
				}
			}		
		</script>
	</head>

	<body>
		<div class="main_div">
			<div id="main_menu_div">
				<div class="button" onclick="main_menu_show()">
					<div class="dots">
						...
					</div>
				</div>

				<div id="main_menu_content"></div>
			</div>

			<div id="current_stats">
				<div class="current_tel_nom"></div>
				<div class="activity_status"></div>
				<div class="current_balance"></div>
				<div class="current_spended"></div>
			</div>

			<div class="css-modal-checkbox-container">
			    <label for="css-modal-checkbox" class="main_button" action="balance_history">История баланса</label>
			</div>

			<div class="css-modal-checkbox-container">
			    <label for="css-modal-checkbox" class="main_button" action="spended_history">История начислений</label>
			</div>

			<div class="css-modal-checkbox-container">
			    <label for="css-modal-checkbox" class="main_button" action="detal">Детализация</label>
			</div>
		</div>
			
		<input type="checkbox" id="css-modal-checkbox" />    
		<div class="cmc">
		    <div class="cmt">
		        
		    </div>
		    <label for="css-modal-checkbox" class="css-modal-close"></label>
		</div>

	</body>
</html>