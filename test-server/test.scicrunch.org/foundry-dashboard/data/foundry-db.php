<?php
  function retrieveResources() {
    $resourceStmt = "select resourceName, mappingType," .
                    " scheduleFreq, resourceStatus, resourceID," .
                    " DATE_FORMAT(CONVERT_tz(creationTimeStamp, '+00:00', '-07:00'), '%m/%d/%Y %H:%i %p')" .
                    " as lastRanTimeStamp from ingestors where resourceID" .
                    " not in ('admin-Registry-test', 'rn-0003')";
    return queryDB(array(''), $resourceStmt, '');
  }

  function retrieveStatistics() {
    $statsStmt =
      "WITH " .
        "latestStats AS " .
          "(SELECT " .
            "max(s.importID) AS latestRun, " .
            "i.resourceID, " .
            "i.resourceName, " .
            "i.mappingType " .
          "FROM ingestors AS i " .
          "JOIN (SELECT * FROM ingestion_statistics WHERE resourceID != 'admin-Registry-test') AS s " .
          "ON i.resourceID = s.resourceID " .
          "GROUP BY resourceID) " .
      "SELECT " .
        "s.importID, " .
        "s.resourceID, " .
        "l.resourceName, " .
        "l.mappingType, " .
        "s.totalRecCount, " .
        "s.ingestedRecCount, " .
        "s.finishedRecCount, " .
        "s.errorCount " .
      "FROM ingestion_statistics AS s " .
      "JOIN latestStats AS l " .
      "ON s.importID = l.latestRun ";

    return queryDB(array(''), $statsStmt, '');
  }

  function retrieveLogs($resource) {
    $resourceID = $resource . '%';
    $logsStmt = "SELECT " .
                  	"*, " .
                  	"DENSE_RANK() OVER(ORDER BY requestID) AS rowID " .
                  "FROM " .
                  "((SELECT " .
                  	"'' AS logID, requestID, users.userName, status, processType, " .
                  	"DATE_FORMAT(CONVERT_tz(pr.startTime, '+00:00', '-07:00'), '%m/%d/%Y %H:%i %p') AS startTime, " .
                  	"DATE_FORMAT(CONVERT_tz(pr.endTime, '+00:00', '-07:00'), '%m/%d/%Y %H:%i %p') AS endTime, " .
                  	"CONCAT(TIMESTAMPDIFF(MINUTE, pr.startTime, pr.endTime), ' Minutes') AS timeTaken " .
                  "FROM process_request AS pr JOIN users ON pr.userID = users.userID " .
                  "WHERE resourceID like ?) " .
                  "UNION " .
                  "(SELECT " .
                  	"logID, requestID, users.userName, logs.status, logs.type, " .
                  	"DATE_FORMAT(CONVERT_tz(logs.startDate, '+00:00', '-07:00'), '%m/%d/%Y %H:%i %p') AS startDate, " .
                  	"DATE_FORMAT(CONVERT_tz(logs.endDate, '+00:00', '-07:00'), '%m/%d/%Y %H:%i %p') AS endDate, " .
                  	"CONCAT(TIMESTAMPDIFF(MINUTE, logs.startDate, logs.endDate), ' Minutes') AS timeTaken " .
                  "FROM ingestion_logs as logs join users on logs.userID = users.userID " .
                  "WHERE resourceID like ? " .
                  ")) AS a ORDER BY requestID DESC, logID DESC ";
    return queryDB(array($resourceID,$resourceID), $logsStmt, 'ss');
  }

  function retrieveSystemStatus() {
    $systemStatusStmt = "SELECT * FROM system_status WHERE recoverTimeStamp is NULL";
    return queryDB(array(''), $systemStatusStmt, '');
  }

  function queryDB($query, $sql, $paramTypes) {
    $CONFIG = include_once($_SERVER['DOCUMENT_ROOT'] . "/foundry-dashboard/config.php");
    $mysqli = new mysqli($CONFIG['host'], $CONFIG['user'], $CONFIG['password'], $CONFIG['database'], $CONFIG['port']);
    if ($mysqli->connect_error) {
      exit('Could not connect');
    }

    $results = array();
    $check = array();
    $count = 0;

    $stmt = $mysqli->prepare($sql);

    if ($query != '' && $paramTypes != '') {
      $stmt->bind_param($paramTypes, ...$query);
    }

    $stmt->execute();
    $meta = $stmt->result_metadata();

    if ($meta) {
      /** Retrieve field names **/
      while($field = $meta->fetch_field()) {
        $params[] = & $row[$field->name];
      }

      /** Set values to fields **/
      call_user_func_array(array($stmt, 'bind_result'), $params);

      while ($stmt->fetch()) {
        foreach($row as $key => $val) {
          $check[$key] = $val;
        }
        $results[$count] = $check;
        $count = $count + 1;
      }
      /** Set values to fields **/
    }

    $stmt->close();
    return $results;
  }

?>
