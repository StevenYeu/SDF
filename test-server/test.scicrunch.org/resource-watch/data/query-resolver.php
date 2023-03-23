<?php

	function retrieveResolverInfo($rrid) {
		/** Construct the request and ES query**/
		$CONFIG = include_once(__DIR__ . "/../config.php");
		$headers = array('Content-Type: application/json');
		$query = htmlspecialchars(str_replace("RRID%3A", "", $rrid)); // Removes RRID: Prefix
		$esQuery =
		'{' .
			'"size": 50,' .
			'"from": 0,' .
			'"_source": ["item.identifier", "item.name",  "vendors", "item.description"],' .
			'"query": {' .
				'"bool": {' .
					'"must": [' .
						'{' .
							'"query_string": {' .
								'"fields": [' .
								'	"*"' .
								'],' .
								'"query": "\"' . $query .'\"",' .
								'"type": "cross_fields",' .
								'"default_operator": "and",' .
								'"lenient": "true"' .
							'}' .
						'}' .
					'],' .
					'"should": [' .
						'{' .
							'"match": {' .
								'"item.name": {' .
									'"query": "\"' . $query .'\"",' .
									'"boost": 20' .
								'}' .
							'}' .
						'},' .
						'{' .
							'"term": {' .
								'"item.name.aggregate": {' .
									'"term": "' . $query .'",' .
									'"boost": 2000' .
								'}' .
							'}' .
						'}' .
					']' .
				'}' .
			'}' .
		'}';

		/** Decides which ES Index to query**/
		$prefix = strtoupper(explode("_", $query)[0]); // Example: AB in AB_123456
		switch ($prefix) {
			case "AB":
				$url = "https://elastic-foundry.scicrunch.io/RIN_Antibody_pr/_search";
				break;
			case "CVCL":
				$url = "https://elastic-foundry.scicrunch.io/RIN_CellLine_pr/_search";
				break;
			default: // Handles invalid RRIDs
				$url = "";
				break;
		}

		/** Runs the ES query **/
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($curl, CURLOPT_USERPWD, $CONFIG["esUser"] . ":" . $CONFIG["esPassword"]);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $esQuery);
		$result = curl_exec($curl);
		curl_close($curl);

		return (json_decode($result, true)['hits']['hits']);
	}

?>
