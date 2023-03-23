<?php

	function retrieveValidationIssueInfo($rrid) {
		$CONFIG = include_once(__DIR__ . "/../config.php");
		$mysqli = new mysqli($CONFIG['host'], $CONFIG['user'], $CONFIG['password'], $CONFIG['ResourceWatchDB'], $CONFIG['port']);
		if ($mysqli->connect_error) {
			exit('Could not connect to database');
		}

		/** TODO: add partition to speed up search **/
		$query = htmlspecialchars(str_replace("RRID%3A", "", $rrid));
		$sql = "SELECT DISTINCT scope, recordType, vendor, catalogNumber, displayMessage, process, " .
				 "display, notificationType, externalURL FROM prod_validation_issue_table " .
				 "WHERE rrid = ? AND scope != 'indirect' AND curationStatus = 'approved' " .
				 "ORDER BY vendor, scope";

		$stmt = $mysqli->prepare($sql);
		$stmt->bind_param("s", $query);
		$stmt->execute();
		$meta = $stmt->result_metadata();
		while($field = $meta->fetch_field()) {
			$params[] = & $row[$field->name];
		}

		call_user_func_array(array($stmt, 'bind_result'), $params);

		$results = array();
		$check = array();
		$count = 0;

		while ($stmt->fetch()) {
			foreach($row as $key => $val) {
				$check[$key] = $val;
			}
			$results[$count] = $check;
			$count = $count + 1;
		}

		$stmt->close();
		return $results;
	}

?>
