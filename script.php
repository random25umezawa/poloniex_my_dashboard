<?php
	require "php_poloniex.php";

	$key_set = require("key_set.php");

	$pnx = new poloniex($key_set["key"],$key_set["secret"]);

	if(!file_exists("data")) {
		mkdir("data");
	}
/*
	file_put_contents("data/trade_history.json",json_encode($pnx->get_my_trade_history("all"),JSON_PRETTY_PRINT));
	file_put_contents("data/balances.json",json_encode($pnx->get_balances(),JSON_PRETTY_PRINT));
	file_put_contents("data/ticker.json",json_encode($pnx->get_ticker(),JSON_PRETTY_PRINT));
	file_put_contents("data/open_orders.json",json_encode($pnx->get_open_orders("all"),JSON_PRETTY_PRINT));

	$open_orders = json_decode(file_get_contents("data/open_orders.json"),true);
	$recent_chart_data = array();
	foreach($open_orders as $pair => $orders) {
		if($orders) {
			$last_time = 2147480000;
			foreach($orders as $order) {
				$temp_time = strtotime($order["date"]);
				$last_time = min($last_time,$temp_time);
			}
			$range = time() - $last_time;
			print($pair." ".$range.PHP_EOL);
			$recent_chart_data[$pair] = $pnx->get_recent_chart_data($pair,$range+750000,14400);
		}
	}
	file_put_contents("data/recent_chart_data.json",json_encode($recent_chart_data,JSON_PRETTY_PRINT));
*/

	$ticker = json_decode(file_get_contents("data/ticker.json"),true);
	foreach($ticker as $pair => $ticker_data_now_i_dont_use_this_data_because_i_use_only_pair_data) {
		$pair = strtoupper($pair);
		getChartData($pnx,$pair,86400);
	}

	function getChartData($pnx,$pair,$interval) {
		$chart_data_long = array();
		$temp_folder_name = sprintf("data/chart_data_%d",$interval);
		if(!file_exists($temp_folder_name)) {
			mkdir($temp_folder_name);
		}
		$temp_file_name = sprintf("%s/%s.json",$temp_folder_name,strtolower($pair));
		if(file_exists($temp_file_name)) {
			$chart_data_long = json_decode(file_get_contents($temp_file_name),true);
		}
		print($pair.PHP_EOL);
		if(!array_key_exists($pair,$chart_data_long)) {
			$chart_data_long[$pair] = array(
				"candleStick" => array(),
				"first_time" => 2147483647,
				"last_time" => 0,
			);
		}
		$last_time = $chart_data_long[$pair]["last_time"];
		if(time()-$last_time<$interval) {
			print(" skipped because of span".PHP_EOL);
			return;
		}
		$new_chart = $pnx->get_chart_data($pair,$last_time,time(),$interval);
		if(array_key_exists("error",$new_chart)) {
			print(" error: ".$new_chart["error"].PHP_EOL);
			return;
		}
		foreach($new_chart as $data) {
			if($data["date"] < 1) return;
			$chart_data_long[$pair]["first_time"] = min($chart_data_long[$pair]["first_time"],$data["date"]);
			$chart_data_long[$pair]["last_time"] = max($chart_data_long[$pair]["last_time"],$data["date"]);
			$chart_data_long[$pair]["candleStick"][$data["date"]] = $data;
		}
		file_put_contents($temp_file_name,json_encode($chart_data_long));
		sleep(2);
	}

?>
