<?php

// Settings
$GLOBALS['mysqli'] = new mysqli("127.0.0.1", "mySQLUser", "mySQLPassword", "MySQLDatabase", 3306);
$wcgUser = "wcgUser";
$wcgVerificationCode = ""; // Can be found in Settings -> My Profile
// End of Settings

define('COLORRESET', "\033[0m");
define('COLORRESET_NEWLINE', "\033[0m\n");
define('CLEARLINE', "\r\033[K");
define('BACK_RED',"\033[41m");
define('BACK_BLACK',"\033[40m");
define('BACK_GREEN',"\033[42m");
define('BACK_YELLOW',"\033[43m");
define('BACK_BLUE',"\033[44m");
define('BACK_MAGENTA',"\033[45m");
define('BACK_CYAN',"\033[46m");
define('BACK_LIGHT_GREY',"\033[47m");
define('BLACK', "\033[0;30m");
define('DARK_GRAY', "\033[1;30m");
define('BLUE', "\033[0;34m");
define('LIGHT_BLUE', "\033[1;34m");
define('GREEN', "\033[0;32m");
define('LIGHT_GREEN', "\033[1;32m");
define('CYAN', "\033[0;36m");
define('LIGHT_CYAN', "\033[1;36m");
define('RED', "\033[0;31m");
define('LIGHT_RED', "\033[1;31m");
define('PURPLE', "\033[0;35m");
define('LIGHT_PURPLE', "\033[1;35m");
define('BROWN', "\033[0;33m");
define('YELLOW', "\033[1;33m");
define('LIGHT_GRAY', "\033[0;37m");
define('WHITE', "\033[1;37m");

$limit  = 250;
$offset = 0;
$apiurl = "https://www.worldcommunitygrid.org/api/members/$wcgUser/results?code=$wcgVerificationCode&limit=$limit";
$startDate = date("Y-m-d_H-i-s");

$i = -1;

do {
	$i++;
	$url = $apiurl."&offset=".($i * $limit );

	echo YELLOW."URL:".LIGHT_BLUE.$url.COLORRESET_NEWLINE;
	sleep(5);

	$results_raw = file_get_contents($url);

	echo YELLOW."Got ".LIGHT_BLUE.strlen($results_raw).YELLOW." Byte".COLORRESET_NEWLINE;
	file_put_contents("/home/results_raw/".$startDate."_".sprintf("%03d",$i).".json",$results_raw);
	if($results_raw === false) {
		echo LIGHT_RED." ERROR GETTING RESULT (=== FALSE)".COLORRESET_NEWLINE;
		sleep(30);
		exit;
	}

	$results = json_decode($results_raw,true);
	if(JSON_LAST_ERROR() !== 0) {
		echo LIGHT_RED." JSON-ERROR:".JSON_LAST_ERROR().COLORRESET_NEWLINE;
		sleep(30);
		exit;
	}

	echo "\nRESULTS\n";
	echo YELLOW."ResultsAvailable:".LIGHT_BLUE.$results["ResultsStatus"]["ResultsAvailable"].COLORRESET_NEWLINE;
	echo YELLOW."ResultsReturned:".LIGHT_BLUE.$results["ResultsStatus"]["ResultsReturned"].COLORRESET_NEWLINE;
	echo YELLOW."Offset:".LIGHT_BLUE.$results["ResultsStatus"]["Offset"].COLORRESET_NEWLINE;

	$totalResults = 0;
	foreach($results["ResultsStatus"]["Results"] as $result) {
		$totalResults++;
		$array_result[] = $result;
	}
	echo YELLOW."array_result Total:".LIGHT_BLUE.count($array_result).COLORRESET_NEWLINE;
	echo "\n--------------------------------------------\n";
} while (($totalResults <= $results["ResultsStatus"]["ResultsAvailable"]) AND ($results["ResultsStatus"]["ResultsReturned"] == $limit));
echo LIGHT_GREEN."\n\nRECEIVE DONE!\n".COLORRESET_NEWLINE;

$array_result = array_reverse($array_result);
$resultsTotal = count($array_result);
$totalResults = 0;
$newResults   = 0;
$updResults   = 0;

foreach($array_result as $result) {
	$totalResults++;
	$result["ModTime_String"] = date("Y-m-d H:i:s",$result["ModTime"]);
	$result["SentTime"] = date("Y-m-d H:i:s",strtotime($result["SentTime"]));
	if(isset($result["ReceivedTime"])) {
		$result["ReceivedTime"] = date("Y-m-d H:i:s",strtotime($result["ReceivedTime"]));
	} else {
		$result["ReceivedTime"] = NULL;
	}
	$result["ReportDeadline"] = date("Y-m-d H:i:s",strtotime($result["ReportDeadline"]));

	$sql = "SELECT * FROM `resultStatus` WHERE `WorkunitId`='".$result["WorkunitId"]."';";
	$sqlResult = func_SQLSelect($sql);
	$countResultRows = $sqlResult->num_rows;

	echo LIGHT_PURPLE."$totalResults".BLUE."/".LIGHT_PURPLE.$resultsTotal.BLUE.") ".LIGHT_PURPLE.$result["AppName"]." ".$result["ClaimedCredit"]." ".$result["CpuTime"]." ".$result["ElapsedTime"]." ".$result["ExitStatus"]." ".$result["GrantedCredit"]." ".$result["DeviceId"]." ".$result["DeviceName"]." ".$result["ModTime"]." ".$result["WorkunitId"]." ".$result["ResultId"]." ".$result["Name"]." ".$result["Outcome"]." ".$result["ReportDeadline"]." ".$result["SentTime"]." ".$result["ServerState"]." ".$result["ValidateState"]." ".$result["FileDeleteState"].COLORRESET_NEWLINE;

	if($countResultRows > 1) {
		echo LIGHT_RED."ERROR: WorkunitId ".$result["WorkunitId"]." exists $countResultRows times (Should be 0 or 1)".COLORRESET_NEWLINE;
		exit;
	}

	if($countResultRows === 0) {
		$result["LastChange"] = date("Y-m-d H:i:s");
		$ArrayToDB = array('table'=>'resultStatus','rows'=>$result);
		$newResults++;
		echo WHITE.BACK_GREEN."NEW #$newResults".BACK_BLACK.YELLOW." Returned:".LIGHT_CYAN.func_SQLInsertArray($ArrayToDB).COLORRESET_NEWLINE."\n";
	}

	if($countResultRows === 1) {
		echo LIGHT_RED.BACK_YELLOW."KNOWN".BACK_BLACK.LIGHT_BLUE." ".$result["Name"].COLORRESET_NEWLINE;
		$change = false;
		$resultRow = mysqli_fetch_assoc($sqlResult);
		$ArrayToDBUpdate = array('table'=>'resultStatus','keyName'=>'WorkunitId','keyValue'=>$result["WorkunitId"]);
		foreach($result as $key=>$value) {
			$color = LIGHT_GREEN;
			if(is_numeric($value)) {
				$resultRow[$key] = floatval($resultRow[$key]);
				$value = floatval($value);
			}

			if("$resultRow[$key]" == "$value") {
				echo LIGHT_CYAN."$key:".LIGHT_GREEN.$resultRow[$key].LIGHT_BLUE." == ".LIGHT_GREEN.$value.COLORRESET_NEWLINE;
			} else {
				$change = true;
				echo LIGHT_CYAN."$key:".YELLOW.$resultRow[$key].LIGHT_BLUE." == ".YELLOW.$value.COLORRESET_NEWLINE;
				$ArrayToDBUpdate['rows']["$key"] = $value;
			}
		}
		if($change === true) {
			$ArrayToDBUpdate['rows']["LastChange"] = date("Y-m-d H:i:s");
			$ret = func_SQLUpdateArray($ArrayToDBUpdate);
			$updResults++;
			echo YELLOW."UPD #$updResults - Returned:".LIGHT_CYAN.$ret.COLORRESET_NEWLINE;
			sleep(1);
		} else {
			echo LIGHT_GREEN."NO UPD".COLORRESET_NEWLINE;	
		}
	}
	echo "---------------------------------------------\n";
}

echo LIGHT_GREEN."Fetch Done!".COLORRESET_NEWLINE;
echo YELLOW."New:".LIGHT_CYAN.$newResults.COLORRESET_NEWLINE;
echo YELLOW."Upd:".LIGHT_CYAN.$updResults.COLORRESET_NEWLINE;

sleep(5);

echo func_genReport();

exit;

function func_genReport() {
	$rep = LIGHT_RED."Queue:".COLORRESET_NEWLINE;
	$sql = "SELECT MAX(`DeviceName`) as `DeviceName`,`DeviceId`,count(`ID`) as `count`, MAX(`AppName`) as `AppName`, MIN(`ReportDeadline`) as `NextDeadline` FROM `resultStatus` WHERE `ServerState`=4 GROUP BY `DeviceId`,`AppName` ORDER BY `DeviceName` ASC;";
	$sqlResult       = func_SQLSelect($sql);
	$countResultRows = $sqlResult->num_rows;
	while ($row = mysqli_fetch_assoc($sqlResult)) {
		$rep .= LIGHT_CYAN.sprintf("%-9s", $row['DeviceName']).LIGHT_BLUE.'('.$row['DeviceId'].') '.YELLOW.sprintf("%-4s", $row['AppName']).':'.LIGHT_PURPLE.sprintf("%3d",$row['count']).LIGHT_BLUE." NextDL:".LIGHT_PURPLE.date("d. H:i:s",strtotime($row['NextDeadline'])).COLORRESET_NEWLINE;
	}

	$rep .= "\n".LIGHT_RED."Pending Validation:".COLORRESET_NEWLINE;
	$sql  = "SELECT MAX(`DeviceName`) as `DeviceName`,`DeviceId`,count(`ID`) as `count`, SUM(`CpuTime`) as `CpuTime`, MAX(`AppName`) as `AppName` FROM `resultStatus` WHERE `ServerState`=5 AND `ValidateState`=0 GROUP BY `DeviceId`,`AppName` ORDER BY `DeviceName` ASC;";
	$sqlResult       = func_SQLSelect($sql);
	$countResultRows = $sqlResult->num_rows;
	while ($row = mysqli_fetch_assoc($sqlResult)) {
		$rep .= LIGHT_CYAN.sprintf("%-9s", $row['DeviceName']).LIGHT_BLUE.'('.$row['DeviceId'].') '.YELLOW.sprintf("%-4s", $row['AppName']).':'.LIGHT_PURPLE.sprintf("%3d",$row['count']).LIGHT_BLUE." CPU:".LIGHT_PURPLE.sprintf("%12s", func_secToTime(($row['CpuTime'] * 3600))).COLORRESET_NEWLINE;
	}

	$rep .= func_genReportHistory(date("Y-m-d"));
	$rep .= func_genReportHistory(date("Y-m-d",strtotime("-1 day")));
	$rep .= func_genReportHistory(date("Y-m-d",strtotime("-2 days")));
	$rep .= func_genReportHistory(date("Y-m-d",strtotime("-3 days")));

	RETURN $rep;
}

function func_genReportHistory($date) {
	$ret = "\n".LIGHT_RED."History ($date):".COLORRESET_NEWLINE;
	$sql = "SELECT MAX(`DeviceName`) as `DeviceName`,`DeviceId`,count(`ID`) as `count`, MIN(`ReceivedTime`) as `MinReceivedTime`, MAX(`ReceivedTime`) as `MaxReceivedTime`, SUM(`CpuTime`) as `CpuTime`, MAX(`AppName`) as `AppName` FROM `resultStatus` WHERE `ReceivedTime`>='".$date." 00:00:00"."' AND `ReceivedTime`<='".$date." 23:59:59"."' GROUP BY `DeviceId`,`AppName` ORDER BY `DeviceName` ASC ;";
	$sqlResult       = func_SQLSelect($sql);
	$countResultRows = $sqlResult->num_rows;

	$array_device  = array();
	$array_project = array();
	$totalCPU   = 0;
	$totalUnits = 0;
	
	while ($row = mysqli_fetch_assoc($sqlResult)) {
		$ret .= LIGHT_CYAN.sprintf("%-9s", $row['DeviceName']).LIGHT_BLUE.'('.$row['DeviceId'].') '.YELLOW.sprintf("%-4s", $row['AppName']).':'.LIGHT_PURPLE.sprintf("%3d",$row['count']).LIGHT_BLUE." CPU:".LIGHT_PURPLE.sprintf("%12s", func_secToTime(($row['CpuTime'] * 3600))).COLORRESET_NEWLINE;
		if(!isset($array_device[$row['DeviceId']])) {
			$array_device[$row['DeviceId']]['cpu']        = 0;
			$array_device[$row['DeviceId']]['count']      = 0;
			$array_device[$row['DeviceId']]['DeviceName'] = "n/a";
		}
		if(!isset($array_project[$row['AppName']])) {
			$array_project[$row['AppName']]['cpu']   = 0;
			$array_project[$row['AppName']]['count'] = 0;
		}
		$array_device[$row['DeviceId']]['cpu']       += $row['CpuTime'];
		$array_device[$row['DeviceId']]['count']     += $row['count'];
		$array_device[$row['DeviceId']]['DeviceName'] = $row['DeviceName'];
		$array_project[$row['AppName']]['cpu']   += $row['CpuTime'];
		$array_project[$row['AppName']]['count'] += $row['count'];
		$totalCPU   += $row['CpuTime'];
		$totalUnits += $row['count'];
	}
	$ret .= "\nTOTAL Device:".COLORRESET_NEWLINE;
	foreach($array_device as $key=>$value) {
		$ret .= LIGHT_CYAN.sprintf("%-9s", $value['DeviceName']).LIGHT_BLUE."($key) WUs:".LIGHT_PURPLE.sprintf("%3d",$value['count']).LIGHT_BLUE." CPU:".LIGHT_PURPLE.sprintf("%12s", func_secToTime(($value['cpu'] * 3600))).COLORRESET_NEWLINE;
	}
	$ret .= "\nTOTAL Project:".COLORRESET_NEWLINE;
	foreach($array_project as $key=>$value) {
		$ret .= YELLOW.sprintf("%-4s", $key).LIGHT_BLUE." WUs:".LIGHT_PURPLE.sprintf("%3d",$value['count']).LIGHT_BLUE." CPU:".LIGHT_PURPLE.sprintf("%12s", func_secToTime(($value['cpu'] * 3600))).COLORRESET_NEWLINE;
	}
	$ret .= "\nTOTAL Day:".COLORRESET_NEWLINE;
	$ret .= LIGHT_BLUE."WUs: ".LIGHT_PURPLE.$totalUnits.COLORRESET_NEWLINE;
	$ret .= LIGHT_BLUE."CPU:".LIGHT_PURPLE.sprintf("%12s", func_secToTime(($totalCPU * 3600))).COLORRESET_NEWLINE;

	RETURN $ret;
}

function func_secToTime($sec) {
	$ret = "";
	if($sec < 0) { $sec = 0; }

	$d = 0;
	if($sec >= 86400) {
		$d    = floor($sec / 86400);
		$ret .= sprintf("%02d",$d).':';
		$sec -= ($d * 86400);
		
	}

	if(($sec >= 3600) OR ($d > 0)) {
		$h = floor($sec / 3600);
		$ret .= sprintf("%02d",$h).':';
		$sec -= ($h * 3600);
		
	}
	return $ret.sprintf("%02d",floor(($sec % 3600) / 60)).':'.sprintf("%02d",($sec % 60));
}

function func_SQLSelect($sql) {
	$response = $GLOBALS['mysqli']->query($sql);
	if($response === false) {
		echo LIGHT_RED."SQLSelect: ERROR:".$GLOBALS['mysqli']->error."\n".$sql.COLORRESET_NEWLINE;
		file_put_contents($GLOBALS['logFile']."SQLSelectERROR",$sql."; # Error:".$GLOBALS['mysqli']->error."\n",FILE_APPEND);
		func_out("SQLSelect: ERROR: ".$GLOBALS['mysqli']->error." - ".$sql);
		sleep(10);
	}
	RETURN $response;
}

function func_SQLInsertArray($params) {
	if((!isset($params['table'])) OR (!isset($params['rows']))) { echo "RETURN 0\n"; RETURN "0"; }
	$errorFile = "SQLInsertArrayToDBCheck-ERROR_".date("Y-m-d").".txt";

	$sqlInsert = "INSERT INTO `".$params['table']."` SET ";

	foreach($params['rows'] as $key=>$value) {
		if(is_array($value)) {
			$value = json_encode($value);
		}
		$sqlInsert .= "`$key`='".$GLOBALS['mysqli']->real_escape_string($value)."', ";
	}
	$sqlInsert = rtrim($sqlInsert,', ');
	$sqlInsert .= "; ";
	echo __LINE__ .") SQL-INSERT:".$sqlInsert."\n";
	$response = $GLOBALS['mysqli']->query($sqlInsert);
	if(!$response) { // Sometimes there are errors when value='', therefore, replace with NULL
		sleep(3);
		$sqlInsert = str_replace("''", 'NULL', $sqlInsert);
		$response = $GLOBALS['mysqli']->query($sqlInsert);
	}

	if(!$response) {
		$Id = 0;
		$logString = $sqlInsert."; # Error:".$GLOBALS['mysqli']->error;
		echo $logString."\nResponse:"; var_dump($response);
		echo LIGHT_RED." ERROR MYSQL INSERT!!! \n".COLORRESET_NEWLINE;
		file_put_contents($GLOBALS['logFileDir'].$errorFile,$logString."\n",FILE_APPEND);
		RETURN $Id;
	}
	$Id = mysqli_insert_id($GLOBALS['mysqli']);
	RETURN $Id;
}

function func_SQLUpdate($sqlUpdate) {
	$errorFile = "SQLUpdate-ERROR_".date("Y-m-d").".txt";
	$response = $GLOBALS['mysqli']->query($sqlUpdate);
	$affectedRows = $GLOBALS['mysqli']->affected_rows;
	if($affectedRows == 0) { $color=LIGHT_RED; } else { $color=LIGHT_GREEN; }
	if(!$response) { // Sometimes there are errors when value='', therefore, replace with NULL
		sleep(3);
		$sqlUpdate = str_replace("''", 'NULL', $sqlUpdate);
		$response = $GLOBALS['mysqli']->query($sqlUpdate);
		$affectedRows = $GLOBALS['mysqli']->affected_rows;
	}

	if(!$response) {
		file_put_contents($GLOBALS['logFile'].$errorFile.$errorFile,$sqlUpdate." # Error:".$GLOBALS['mysqli']->error."\n",FILE_APPEND);
		RETURN 0;
	}
	file_put_contents($GLOBALS['logFileSQLUpdate'],$sqlUpdate."; # Affected:".$affectedRows."\n",FILE_APPEND);

	func_SQLPost(array('sqlQuery'=>$sqlUpdate,'ret'=>$affectedRows,'error'=>"".$GLOBALS['mysqli']->error));
	RETURN $affectedRows;
}

function func_SQLUpdateArray($params) {
	if((!isset($params['table'])) OR (!isset($params['rows'])) OR (!isset($params['keyName'])) OR (!isset($params['keyValue']))) { RETURN 0; }
	$errorFile = "SQLUpdateArrayToDBCheck-ERROR_".date("Y-m-d").".txt";
	$errorFileRounded = "SQLUpdateArrayToDBCheck-Rounded_".date("Y-m-d").".txt";

	$sqlUpdate = "UPDATE `".$params['table']."` SET ";

	foreach($params['rows'] as $key=>$value) {
	$sqlUpdate .= "`$key`='".$GLOBALS['mysqli']->real_escape_string($value)."', ";
	}
	$sqlUpdate = rtrim($sqlUpdate,', ');
	$sqlUpdate .= " WHERE `".$params['keyName']."`='".$params['keyValue']."';";
	$response = $GLOBALS['mysqli']->query($sqlUpdate);
	$affectedRows = $GLOBALS['mysqli']->affected_rows;
	if($affectedRows == 0) { $color=LIGHT_RED; } else { $color=LIGHT_GREEN; }
	if(!$response) { // Sometimes there are errors when value='', therefore, replace with NULL
		sleep(3);
		$sqlUpdate = str_replace("''", 'NULL', $sqlUpdate);
		$response = $GLOBALS['mysqli']->query($sqlUpdate);
		$affectedRows = $GLOBALS['mysqli']->affected_rows;
	}

	if(!$response) {
		file_put_contents($GLOBALS['logFile'].$errorFile.$errorFile,$sqlUpdate." # Error:".$GLOBALS['mysqli']->error."\n",FILE_APPEND);
		RETURN 0;
	}

	RETURN $affectedRows;
}
?>
