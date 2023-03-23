<?php
    $home_url = "{$_SERVER['HTTP_HOST']}";
    $dknet_flag = false;
    if($home_url == "dknet.org") $dknet_flag = true;

    ## for test
    $actual_link = "{$_SERVER['REQUEST_URI']}";
    if(\helper\startsWith($actual_link, "/dknetbeta")) {
        $dknet_tag = "/dknetbeta";
        $dknet_flag = true;
    }
    else if(\helper\startsWith($actual_link, "/dknet")) {
        $dknet_tag = "/dknet";
        $dknet_flag = true;
    }
    ##

    if($xml_type) {
        if($protocol_flag) {
            $results = $results["es"];
            $docID = "";
        } else {
            $search_manager = ElasticRRIDManager::managerByViewID($view);
            if(is_null($search_manager)) {
                return;
            }
            $results = $search_manager->searchRRID($rrid);
        }
        $found = false;
        if($results->hitCount() != 0) $found = true;
        $result = findResult($results, $docID);
        $docID = $result->getRRIDField("id");

        switch($view) {
            case "nif-0000-07730-1":
                $type_name = "Antibody";
                $source = "Antibody Registry";
                if($dknet_flag)
                    $url = "https://dknet.org/data/source/nif-0000-07730-1/search";
                else
                    $url = "https://scicrunch.org/resources/Antibodies/search";
                $item_views = NULL;
                $source_database = NULL;
                break;
            case "nlx_144509-1":
                $resource_obj = new Resource();
                $resource_obj->getByRID(str_replace("RRID:", "", $rrid));
                $type_name = "Resource";
                $source = "SciCrunch Registry";
                if($dknet_flag)
                    $url = "https://dknet.org/data/source/nlx_144509-1/search";
                else
                    $url = "https://scicrunch.org/resources/Tools/search";
                $item_views = \helper\getViewsFromOriginalID($resource_obj->original_id);
                $source_database = NULL;
                break;
            case "SCR_013869-1":   ##change "cellline" to "Cell Line" -- Vicky-2018-11-9
                $type_name = "Cell Line";
                $source = "Cellosaurus";
                if($dknet_flag)
                    $url = "https://dknet.org/data/source/SCR_013869-1/search";
                else
                    $url = "https://scicrunch.org/resources/Cell%20Lines/search";
                $item_views = NULL;
                $source_database = NULL;
                break;
            case "nlx_154697-1":  ##change "organism" to "Organism" -- Vicky-2018-11-13
                $type_name = "Organism";
                $source = "Integrated Animals";
                if($dknet_flag)
                    $url = "https://dknet.org/data/source/nlx_154697-1/search";
                else
                    $url = "https://scicrunch.org/resources/Organisms/search";
                $item_views = NULL;
                $source_database = $result->getField("Database"); ##added source database information -- Vicky-2018-11-21
                break;
            case "nif-0000-11872-1":   ##added "plasmid" -- Vicky-2019-7-5
                $type_name = "Plasmid";
                $source = "Addgene";
                if($dknet_flag)
                    $url = "https://dknet.org/data/source/nif-0000-11872-1/search";
                else
                    $url = "https://scicrunch.org/resources/Plasmids/search";
                $source_database = NULL;
                break;
            case "nlx_143929-1":   ##added "Biosample" -- Vicky-2019-7-15
                $type_name = "Biosample";
                $source = "NCBI Biosample";
                if($dknet_flag)
                    $url = "https://dknet.org/data/source/nlx_143929-1/search";
                else
                    $url = "https://scicrunch.org/resources/Cell%20Lines/source/nlx_143929-1/search";
                $source_database = NULL;
                break;
            case "protocol":
                $type_name = "Protocol";
                $source = "Protocols.io";
                if($dknet_flag)
                    $url = "https://dknet.org/data/source/protocol/search";
                else
                    $url = "";
                $source_database = NULL;
                break;
        }

        $out_xml = '<?xml version="1.0" encoding="UTF-8"?><entity>';
        if($found) {
            //$out_xml .= '<type>' . htmlentities($objects['name']) . '</type>';
            $out_xml .= '<title>';
            if($type_name == "Plasmid")
                $out_xml .= checkURL($result->getRRIDField("url"), $result->getRRIDField("name"));
                // $out_xml .= '<a class="extenal" target="_blank" href="' . $result->getRRIDField("url") . '">' . htmlentities($result->getRRIDField("name")) . '</a>';
            else
                $out_xml .= htmlentities($result->getRRIDField("name"));
            $out_xml .= '</title>';
            $out_xml .= '<source><name>' . $source . '</name>';
            $out_xml .= '<url>' . $url . '</url></source>';
            $out_xml .= "<data>";

            /* data columns */
            foreach($search_manager->fields() as $field_name) {

                switch ($field_name->name) {
                    case "Resource ID":   ## Tools
                        $out_xml .= '<column><name>' . htmlentities($field_name->name) . '</name><value>';
                        $out_xml .= '<a class="extenal" target="_blank" href="https://scicrunch.org/resolver/' . $result->getField($field_name->name) . '">' . htmlentities($result->getField($field_name->name)) . '</a>';
                        $out_xml .= '</value></column>';
                        break;

                    case "References":
                    case "Reference":
                        $out_xml .= '<column><name>' . htmlentities($field_name->name) . '</name><value>';
                        if($result->getField($field_name->name) != "") {
                            $pmids = Array();
                            $references = Array();
                            $values = explode(", ", $result->getField($field_name->name));
                            foreach($values as $value){
                                if(\helper\startsWith($value, "DOI:")) $references[] = htmlentities('<a class="extenal" target="_blank" href="https://dx.doi.org/' . str_replace("DOI:", "", $value) . '">' . htmlentities($value) . '</a>');
                                else if (\helper\startsWith($value, "PMID:")) $pmids[] = $value;
                                else $references[] = htmlentities($value);
                            }
                            if(count($pmids) > 0) {
                                $pmid_s = join(", ", $pmids);
                                $references[] = htmlentities('<a class="extenal" target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/' . str_replace("PMID:", "", $pmid_s) . '">' . htmlentities($pmid_s) . '</a>');
                            }
                            $out_xml .= join(", ", $references);
                        }
                        $out_xml .= '</value></column>';
                        break;

                    case "Database":    ## Organisms
                        $out_xml .= '<column><name>Database Name</name><value>';
                        $out_xml .= htmlentities($result->getField($field_name->name));
                        $out_xml .= '</value></column>';
                        break;

                    case "Database Abbreviation":   ## Organisms
                        $out_xml .= '<column><name>Database</name><value>';
                        $out_xml .= checkURL($result->getRRIDField("url"), $result->getField($field_name->name));
                        // $out_xml .= '<a class="extenal" target="_blank" href="' . $result->getRRIDField("url") . '">' . htmlentities($result->getField($field_name->name)) . '</a>';
                        $out_xml .= '</value></column>';
                        break;

                    case "ID":    ## Cell Lines
                        $out_xml .= '<column><name>' . htmlentities($field_name->name) . '</name><value>';
                        $out_xml .= checkURL($result->getRRIDField("url"), $result->getField($field_name->name));
                        // $out_xml .= '<a class="extenal" target="_blank" href="' . $result->getRRIDField("url") . '">' . htmlentities($result->getField($field_name->name)) . '</a>';
                        $out_xml .= '</value></column>';
                        break;

                    case "Relevant Mutation":    ## Addgene
                        $out_xml .= '<column><name>Relevant Mutations</name><value>';
                        $out_xml .= htmlentities($result->getField($field_name->name));
                        $out_xml .= '</value></column>';
                        break;

                    case "NCBI Biosample ID":   ## Biosamples
                        $out_xml .= '<column><name>ID</name><value>';
                        $out_xml .= checkURL($result->getRRIDField("url"), $result->getField($field_name->name));
                        // $out_xml .= '<a class="extenal" target="_blank" href="' . $result->getRRIDField("url") . '">' . htmlentities($result->getField($field_name->name)) . '</a>';
                        $out_xml .= '</value></column>';
                        break;

                    case "Disease":    ## Biosamples
                        $out_xml .= '<column><name>Diseases</name><value>';
                        $out_xml .= htmlentities($result->getField($field_name->name));
                        $out_xml .= '</value></column>';
                        break;

                    case "Antibody ID":   ## Antibodies
                        $out_xml .= '<column><name>' . htmlentities($field_name->name) . '</name><value>';
                        $out_xml .= checkURL($result->getRRIDField("url"), $result->getField($field_name->name));
                        // $out_xml .= '<a class="extenal" target="_blank" href="' . $result->getRRIDField("url") . '">' . htmlentities($result->getField($field_name->name)) . '</a>';
                        $out_xml .= '</value></column>';
                        break;

                    case "Catalog Number":    ## Cell Lines & Antibodies & Organisms
                        $out_xml .= '<column><name>Cat Num</name><value>';
                        $out_xml .= htmlentities($result->getField($field_name->name));
                        $out_xml .= '</value></column>';
                        break;

                    case "Uid":   ## Cell Lines & Antibodies
                        break;

                    default:
                      $out_xml .= '<column><name>' . htmlentities($field_name->name) . '</name><value>';
                      $out_xml .= htmlentities($result->getField($field_name->name));
                      $out_xml .= '</value></column>';
                      break;
                }
            }
            $out_xml .= '<column><name>v_uuid</name><value>';
            $out_xml .= htmlentities($result->getRRIDField("uuid")) . '</value></column>';

            /* publications that use resource */
            $PMIDs = \search\searchMentionPMIDs($view, $rrid, 0, 100);

            $out_xml .= '<column><name>Publications that use this research resource</name>';
            if(empty($PMIDs["hits"]["hits"])) {
                $out_xml .= '<value></value>';
            } else {
                foreach($PMIDs["hits"]["hits"] as $hit) {
                    $out_xml .= '<value>' . $hit["_source"]["pmid"] . '</value>';
                }
            }
            $out_xml .= '</column>';

            $out_xml .= "</data>";
          } else {
              $out_xml .= "<error>Not a known entity</error>";
          }
        $out_xml .= "</entity>";

        $out_xml = \helper\cleanXML($out_xml);
        $xml = simplexml_load_string($out_xml);
        echo $xml->asXML();
        unset($_SESSION["resolver_alternated"]);
    } else if($json_type) {
        ## remove some resolver info from the json
        unset($results["json"]["took"]);
        unset($results["json"]["timed_out"]);
        unset($results["json"]["_shards"]);
        unset($results["json"]["hits"]["max_score"]);
        for($i = 0; $i < $results_count; $i++) {
            unset($results["json"]["hits"]["hits"][$i]["_index"]);
            unset($results["json"]["hits"]["hits"][$i]["_type"]);
            unset($results["json"]["hits"]["hits"][$i]["_id"]);
            unset($results["json"]["hits"]["hits"][$i]["_score"]);
            unset($results["json"]["hits"]["hits"][$i]["_source"]["dataItem"]);
            unset($results["json"]["hits"]["hits"][$i]["_source"]["provenance"]);
        }

        ## add resolver info into the json
        $results["json"]["resolver"]["uri"] = $home_url . $dknet_tag . "/resolver";
        $results["json"]["resolver"]["timestamp"] = date("c");
        if($results_count == 0 && $resolver_error) $results["json"]["resolver"]["error"] = "RRID not found";
        if($_SESSION["resolver_alternated"]) {
            $results["json"]["resolver"]["warning"] = "the RRID found was not the primary RRID it was an alternate RRID or alternate ID";
            unset($_SESSION["resolver_alternated"]);
        }
        echo json_encode($results["json"]);
    }

    function checkURL($url, $field_value) {
        $url = str_replace("&", "&amp;", $url);
        return '<a class="extenal" target="_blank" href="' . $url . '">' . htmlentities($field_value) . '</a>';
    }
?>
