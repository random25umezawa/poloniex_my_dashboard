<?php
	require "php_poloniex.php";

	$key_set = require("key_set.php");

	$pnx = new poloniex($key_set["key"],$key_set["secret"]);

	$trade_history = file_get_contents("data/trade_history.json");
	$ticker = file_get_contents("data/ticker.json");
	$open_orders = file_get_contents("data/open_orders.json");

	$recent_chart_data = file_get_contents("data/recent_chart_data.json");
?>
<link href="plugin/bootstrap.min.css" rel="stylesheet" type="text/css">
<script src="plugin/jquery.min.js"></script>
<script src="plugin/bootstrap.min.js"></script>
<script src="plugin/Chart.min.js"></script>
<style>
	.parts{
		padding:12px 30px;
	}
</style>
<div class="row">
	<div class="parts col-xl-12">
		<div class="card">
			<div class="card-header">
				recent_chart_data
			</div>
			<div class="card-block">
				<div class="row" id="recent_chart_data">
				</div>
			</div>
		</div>
	</div>
	<div class="parts col-xl-6">
		<div class="card">
			<div class="card-header">
				trade_history
			</div>
			<div class="card-block">
				<table class="" id="trade_history"></table>
			</div>
		</div>
	</div>
	<div class="parts col-xl-6">
		<div class="card">
			<div class="card-header">
				now_ihave
			</div>
			<div class="card-block">
				<table class="" id="now_ihave"></table>
			</div>
			<div class="card-block" id="total_btc"></div>
			<div class="card-block" id="total_usdt"></div>
		</div>
	</div>
</div>
<script>
	var trade_history = <?php echo $trade_history; ?>;
	var ticker = <?php echo $ticker; ?>;
	var open_orders = <?php echo $open_orders; ?>;
	var recent_chart_data = <?php echo $recent_chart_data; ?>;

	var recent_chart_data_div = $("#recent_chart_data");
	for(var pair in recent_chart_data) {
		var chart_div = $(`<div class="parts col-xl-4 col-md-6">`);
		var chart_canvas = $(`<canvas style="width:100%; height:500px;">`);
		chart_div.append(chart_canvas);
		recent_chart_data_div.append(chart_div);
		var chart_data = {labels:[],datasets:[]};
		var prices = [];
		var labels = [];
		for(var stick of recent_chart_data[pair].candleStick) {
			var class_date = new Date(stick.date*1000);
			labels.push((class_date.getMonth()+1)+"/"+class_date.getDate());
			prices.push(stick.open);
		}
		chart_data.labels = labels;
		chart_data.datasets.push({
			type:"line",
			label:pair,
			data:prices,
			borderColor:"#abc",
			backgroundColor:"#cde"
		});
		for(var open_order of open_orders[pair]) {
			chart_data.datasets.push({
				type:"line",
				label:open_order.amount,
				data:[open_order.rate],
			});
		}
		var chart_options = {responsive: true};
		var ctx = chart_canvas[0].getContext("2d");
		var _chart = new Chart(ctx,{
			type:"bar",
			data:chart_data,
			options:chart_options
		});
	}

	var table = $("#trade_history");
	var row = $(`<tr style="border-bottom:2px solid #ccc">`);
	row.append($("<th style='width:3%;'>").html("#"));
	row.append($("<th style='width:12%;'>").html("pair"));
	row.append($("<th style='width:18%;'>").html("date"));
	row.append($("<th style='width:6%;'>").html("type"));
	row.append($("<th style='width:12%;'>").html("rate"));
	row.append($("<th style='width:12%;'>").html("amount"));
	row.append($("<th style='width:12%;'>").html("total"));
	table.append($(`<thead>`).append(row));
	var tbody = $("<tbody>");
	var count = 0;
	for(var pair in trade_history) {
		for(var trade of trade_history[pair]) {
			count++;
			var row = $(`<tr style="border-bottom:1px solid #e5e5e5">`);
			row.append($("<td>").html(count));
			row.append($("<td>").html(pair));
			row.append($("<td>").html(trade.date));
			row.append($("<td>").html(trade.type));
			row.append($("<td>").html(trade.rate));
			row.append($("<td>").html(trade.amount));
			row.append($("<td>").html(trade.total));
			tbody.append(row);
		}
	}
	table.append(tbody);

	var table = $("#now_ihave");
	var row = $(`<tr style="border-bottom:2px solid #ccc">`);
	row.append($("<th style='width:3%;'>").html("#"));
	row.append($("<th style='width:12%;'>").html("type"));
	row.append($("<th style='width:12%;'>").html("pair"));
	row.append($("<th style='width:18%;'>").html("total"));
	row.append($("<th style='width:6%;'>").html("amount"));
	row.append($("<th style='width:12%;'>").html("last"));
	table.append($(`<thead>`).append(row));
	var tbody = $("<tbody>");
	var count = 0;
	var total_btc = 0;
	for(var pair in open_orders) {
		for(var order of open_orders[pair]) {
			var temp_btc = order.amount * ticker[pair].last;
			total_btc += temp_btc;
			count++;
			var row = $(`<tr style="border-bottom:1px solid #e5e5e5">`);
			row.append($("<td>").html(count));
			row.append($("<td>").html("order"));
			row.append($("<td>").html(pair));
			row.append($("<td>").html(temp_btc));
			row.append($("<td>").html(order.amount));
			row.append($("<td>").html(ticker[pair].last));
			tbody.append(row);
		}
	}
	table.append(tbody);
	$("#total_btc").html("total_btc : "+total_btc);
	$("#total_usdt").html("total_usdt : "+total_btc*ticker.USDT_BTC.last);
</script>
