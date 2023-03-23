<?php

    function formatKeywords($keywords) {
        if($keywords=="*")
          $keywords_s = $keywords;
        else {
          $str = trim($keywords);
          $str = str_replace("Cat#", "", $str);
          $str_len = strlen($str);
          $keywords_l = array();
          $tmp_s = "";
          $key_flag = false;
          $quote_flag = false;
          $bracket_flag = false;
          $bracket_count = 0;
          for ($i = 0; $i < $str_len; $i++) {
            $char = substr($str, $i, 1);
            switch ($char){
              case '"':
                if ($quote_flag) {
                  $quote_flag = false;
                  array_push($keywords_l, $tmp_s);
                  $tmp_s = "";
                } else $quote_flag = true;
                break;
              case "(":
                $bracket_count++;
                if (!$key_flag && !$quote_flag) {
                  array_push($keywords_l, $char);
                  $bracket_flag = true;
                } else {
                  $tmp_s .= $char;
                  if ($i == $str_len-1 && $key_flag) array_push($keywords_l, $tmp_s);
                }
                break;
              case ")":
                if ($quote_flag || !$bracket_flag || $bracket_count > 1) {
                  $tmp_s .= $char;
                  if ($i == $str_len-1 && $key_flag) array_push($keywords_l, $tmp_s);
                } else if ($key_flag || $bracket_flag) {
                  if ($tmp_s != "") {
                    array_push($keywords_l, $tmp_s);
                    $tmp_s = "";
                  }
                  array_push($keywords_l, $char);
                  $key_flag = false;
                  $bracket_flag = false;
                }
                $bracket_count--;
                break;
              case " ":
                if (!$quote_flag && $key_flag) {
                  $key_flag = false;
                  if ($tmp_s != "") {
                    array_push($keywords_l, $tmp_s);
                    $tmp_s = "";
                  }
                } else if ($quote_flag) $tmp_s .= $char;
                break;
              case "+":
                if ($quote_flag || $key_flag) {
                  $tmp_s .= $char;
                  if ($i == $str_len-1 && $key_flag) array_push($keywords_l, $tmp_s);
                }
                break;
              case "-":
                if (!$quote_flag && !$key_flag) {
                  array_push($keywords_l, "NOT");
                } else {
                  $tmp_s .= $char;
                  if ($i == $str_len-1 && $key_flag) array_push($keywords_l, $tmp_s);
                }
                break;
              default:
                $tmp_s .= $char;
                if (!$key_flag) $key_flag = true;
                if ($i == $str_len-1 && $key_flag) array_push($keywords_l, $tmp_s);
                break;
            }
          }

          for ($i = 0; $i < count($keywords_l); $i++){
            if (!in_array($keywords_l[$i], ["(", ")", "AND", "OR", "NOT"])) $keywords_l[$i] = '"'.$keywords_l[$i].'"';
          }
          $keywords_s = implode(" ", $keywords_l);
        }
        return $keywords_s;
    }

    function findResult($results, $docID) {
        $find_result_flag = false;
        foreach($results as $result) {
            ## check unique id -- Vicky-2018-12-20
            if ($result->getRRIDField("id") == $docID) {
                $find_result_flag = true;
                break;
            }
        }

        if(!$find_result_flag) {
            ## find a result with vendor url
            foreach($results as $result) {
                if($docID == "" && $result->getRRIDField("vendors-uri") != "") {
                    $find_result_flag = true;
                    break;
                }
            }
        }

        ## if didn't find the best one, choose the first result
        if (!$find_result_flag) $result = $results->getByIndex(0);

        return $result;
    }

    function getInformationTypeCount($keywords, $search_size, $page, $vars) {
        $data_sources_list = file_get_contents("ssi/elements/discovery/json/index.json");
        $data_sources = json_decode($data_sources_list, true);
        $results = Array();
        $newVars = $vars;
        $viewIDs = array_merge(Array("all"), explode(",", $vars["nif"]));
        foreach ($data_sources as $source => $config) {
            if(in_array($source, $viewIDs) || $vars["nif"] == "all") {
                $newVars["facet"] = checkFacets($source, $vars["facet"]);
                $search_options = ElasticRRIDManager::searchOptionsFromGet($newVars);
                $manager = ElasticRRIDManager::esManagerByViewID($source, false);
                $result = $manager->search($keywords, $search_size, $page, $search_options);
                $result_count = $result->totalCount();
                if($result_count != 0)
                    $results[$source] = Array(
                                            "name" => $config["name"],
                                            "count" => $result_count,
                                        );
            }
        }
        return $results;
    }

    function getSourcesInfo($vars) {
        $search_manager = ElasticRRIDManager::esManagerByViewID($vars["sources"]);
        $keywords_s = formatKeywords("*");
        $search_results = $search_manager->search($keywords_s, 20, 1, Array(), 0);
        $count = $search_results->totalCount();
        $source_indices = $search_results->facets()["Data Sources"];

        $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
        $data_sources = json_decode($data_sources_list, true);

        $indices = Array();

        foreach ($data_sources as $index => $val) {
            $indices[$index] = Array(
                                  "plural_name" => $val["plural_name"],
                                  "name" => $val["name"],
                                  "index" => $val["index"],
                                  "es_type" => $val["es_type"]
                              );
        }

        foreach ($source_indices as $idx => $source_index) {
            foreach ($indices as $key => $value) {
                if(\helper\startsWith($source_index["value"], $value["index"])) {
                    $source_indices[$idx]["plural_name"] = $value["plural_name"];
                    $source_indices[$idx]["name"] = $value["name"];
                    $source_indices[$idx]["index"] = $key;
                    $source_indices[$idx]["es_type"] = $value["es_type"];
                    $source_indices[$idx]["checked"] = 0;
                    if(strpos($vars["nif"], $key) !== false || $vars["nif"] == $vars["sources"]) $source_indices[$idx]["checked"] = 1;
                }
            }
        }
        usort($source_indices, function($a, $b) {return strcmp($a["plural_name"], $b["plural_name"]);});

        return $source_indices;
    }

    function getSourceInformation($type) {
        $source_types_list = file_get_contents("ssi/elements/discovery/json/discovery_source_types.json");
        $source_types = json_decode($source_types_list, true);
        $SourceInfo = Array(
            "source" => $source_types[$type]["source"],
            "category" => $source_types[$type]["category"],
            "sourceID" => $source_types[$type]["sourceID"],
            "RRID" => $source_types[$type]["RRID"],
        );
        return $SourceInfo;
    }

    function convertIndices() {
        $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
        $data_sources = json_decode($data_sources_list, true);
        $indices = Array();

        foreach ($data_sources as $index => $val) {
            $indices[$val["index"]] = $val["plural_name"];
        }
        return $indices;
    }

    function getSourceConfigFile($index) {
        $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
        $data_sources = json_decode($data_sources_list, true);
        $SourceConfigFile = "";

        foreach ($data_sources as $source) {
            if(\helper\startsWith(strtolower($index), $source["index"])) $SourceConfigFile = $source["config"];
        }

        return $SourceConfigFile;
    }

    function getFieldsURL($config) {
        $source_config_list = file_get_contents("ssi/elements/discovery/json/".$config);
        $source_config = json_decode($source_config_list, true);
        $fields_url = Array();

        foreach ($source_config["fields"] as $field) {
            $field_url = "";
            if(isset($field["url"])) $field_url = $field["url"];
            $fields_url[$field["name"]] = $field_url;
        }

        return $fields_url;
    }

    function isRIN($type) {
        $rin_types = Array(
                        "antibody",
                        "tool",
                        "resource",
                        "organization",
                        "cell line",
                        "organism",
                        "plasmid",
                        "biosample",
                        "protocol"
                    );
        if(in_array($type, $rin_types)) return true;
        else return false;
    }

    function includeRinIndices($nifid) {
        $indices = explode(",", $nifid);
        $include_rin_indices = false;
        foreach ($indices as $index) {
            if(\helper\startsWith(trim($index), "rin") || $index == "all") $include_rin_indices = true;
        }
        return $include_rin_indices;
    }

    ## modified facets order -- Vicky-2019-1-24
    function checkFacetNames($nifid) {
        $facet_names = array();
        switch($nifid) {
            case "nlx_144509-1":  ## Tools
            case "rin-tool":
                $facet_names = array("Resource Type", "Keywords", "Organism", "Related Condition", "Funding Agency", "Website Status", "Mentions");
                break;
            case "SCR_013869-1":  ## Cell lines
            case "rin-cellline":
                $facet_names = array("Vendor", "Category", "Disease", "Organism", "References", "Sex", "Mentions", "Issues");
                break;
            case "nif-0000-07730-1":  ## Antibodies
            case "rin-antibody":
                $facet_names = array("Target Antigen", "Target Organism", "Vendor", "Clonality", "Host Organism", "Mentions", "Validation", "Issues");
                break;
            case "nlx_154697-1":  ## Organisms
            case "rin-organism":
                $facet_names = array("Database", "Species", "Background", "Genomic Alteration", "Affected Gene", "Phenotype", "Availability", "Mentions");
                break;
            case "nif-0000-11872-1":  ## Plasmids
            case "rin-plasmid":
                $facet_names = array("Organism", "Bacterial Resistance", "Mentions");
                break;
            case "nlx_143929-1":  ## Biosamples
            case "rin-biosample":
                $facet_names = array("Sex of Cell", "Category", "Disease", "Species", "Mentions");
                break;
            case "ks":
                $facet_names = array("Type");
                break;
            case "ks-ebrain":
                $facet_names = array("Type");
                break;
            case "protocol":
            case "rin-protocol":
                $facet_names = array("Group", "Authors", "Year");
                break;
            // default:
            //     $facet_names = array("Type", "Mentions");
            //     break;
        }
        return $facet_names;
    }

    function checkFacets($nifid, $facets) {
        $facet_names = checkFacetNames($nifid);
        $new_facets = Array();
        foreach ($facets as $facet) {
            if(in_array(explode(":", $facet)[0], $facet_names)) $new_facets[] = $facet;
        }
        return $new_facets;
    }

    function buildLinks($reference_names, $community) {
        $references = explode(",", $reference_names);
        $reference_links = Array();
        foreach($references as $reference) {
            $val = explode(":", trim($reference), 2);
            if (count($val) > 1) {
                $value = trim($val[0]).":".$val[1];
                switch (trim($val[0])) {
                    case "PMID":
                        $reference_links[] = "<a target='_blank' href='".$community->fullURL()."/".$val[1]."?rpKey=on'>".$value."</a>";
                        break;
                    case "DOI":
                        $reference_links[] ="<a target='_blank' href='https://dx.doi.org/".$val[1]."' >".$value."</a>";
                        break;
                    case "CelloPub":
                        $reference_links[] = "<a target='_blank' href='https://web.expasy.org/cellosaurus/cellopub/".str_replace("-", "", $val[1])."' >".$value."</a>";
                        break;
                    case "RRID":
                        $reference_links[] = "<a target='_blank' href='".$community->fullURL()."/resolver/".$val[1]."'>".$value."</a>";
                        break;
                    default:
                        $reference_links[] = trim($reference);
                        break;
                }
            } else if(\helper\startsWith(trim($val[0]), "WBPaper")) {
                $reference_links[] = "<a target='_blank' href='https://wormbase.org/resources/paper/".trim($reference)."'>".trim($reference)."</a>";
            } else {
                $reference_links[] = trim($reference);
            }
        }
        return $reference_links;
    }
?>
