<?php

function communityCategorySearch($user, $api_key, $portal_name, $category, $subcategory, $query_string, $accept_header) {
    // get the community
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(!$community->id && $community->id != 0) return NULL;

    // get the category/subcategory sources
    $community->getAllCategories();

    // make sure everything is set
    if(!isset($community->urlTree[$category]) && $category !== "Any") return NULL;
    if(!is_null($subcategory) && !isset($community->urlTree[$category]["subcategories"][$subcategory])) return NULL;

    // get all the sources to get the name of later
    $holder = new Sources();
    $sources = $holder->getAllSources();

    // get the category and subcategory urls
    $urls = Array();
    $url_infos = Array();
    if($category === "Any") {
        foreach($community->urlTree as $cat_name => $cat) {
            if(isset($cat["urls"])) {
                foreach($cat["urls"] as $i => $url) {
                    $url_info = Array("category" => $cat_name, "subcategory" => NULL);
                    $source = $sources[$cat["nif"][$i]];
                    if($source) {
                        $url_info["source"] = $source->source;
                        $url_info["view"] = $source->view;
                    }
                    $url_infos[] = $url_info;
                    $urls[] = $url;
                }
            }
            if(isset($cat["subcategories"])) {
                foreach($cat["subcategories"] as $subcat_name => $subcat) {
                    foreach($subcat["urls"] as $i => $url) {
                        $url_info = Array("category" => $cat_name, "subcategory" => $subcat_name);
                        $source = $sources[$subcat["nif"][$i]];
                        if($source) {
                            $url_info["source"] = $source->source;
                            $url_info["view"] = $source->view;
                        }
                        $url_infos[] = $url_info;
                        $urls[] = $url;
                    }
                }
            }
        }
    } else {
        if(!is_null($subcategory)) {
            // get each url in the subcategory
            foreach($community->urlTree[$category]["subcategories"][$subcategory]["urls"] as $i => $url) {
                $url_info = Array("category" => $category, "subcategory" => $subcategory);
                $source = $sources[$community->urlTree[$category]["subcategories"][$subcategory]["nif"][$i]];
                if($source) {
                    $url_info["source"] = $source->source;
                    $url_info["view"] = $source->view;
                }
                $url_infos[] = $url_info;
                $urls[] = $url;
            }
        } else {
            // get each url in each subcategory
            if(isset($community->urlTree[$category]["subcategories"])) {
                foreach($community->urlTree[$category]["subcategories"] as $subcat_name => $subcat) {
                    foreach($subcat["urls"] as $i => $url) {
                        $url_info = Array("category" => $category, "subcategory" => $subcat_name);
                        $source = $sources[$subcat["nif"][$i]];
                        if($source) {
                            $url_info["source"] = $source->source;
                            $url_info["view"] = $source->view;
                        }
                        $url_infos[] = $url_info;
                        $urls[] = $url;
                    }
                }
            }
            // get each url not in a subcategory
            if(isset($community->urlTree[$category]["urls"])) {
                foreach($community->urlTree[$category]["urls"] as $i => $url) {
                    $url_info = Array("category" => $category, "subcategory" => NULL);
                    $source = $sources[$community->urlTree[$category]["nif"][$i]];
                    if($source) {
                        $url_info["source"] = $source->source;
                        $url_info["view"] = $source->view;
                    }
                    $url_infos[] = $url_info;
                    $urls[] = $url;
                }
            }
        }
    }

    // set to json if accept request header requested json
    if($accept_header == "json") {
        $count = count($urls);
        for($i = 0; $i < $count; $i++) {
            $urls[$i] = str_replace(".xml", ".json", $urls[$i]);
        }
    }

    // append query string to end of urls
    if($query_string) {
        $count = count($urls);
        for($i = 0; $i < $count; $i++) {
            $urls[$i] .= "&" . $query_string;
        }
    }

    // run query
    $results = Connection::multi($urls);
    $status_code = Connection::$lastHttpStatusCode;

    // bad query
    if($status_code > 299) {
        return NULL;
    }

    // combine results
    if($accept_header == "json") {
        // add the (sub)category name to json results
        $new_results = Array();
        foreach($results as $i => $res) {
            $jres = json_decode($res, true);
            $jres["info"] = $url_infos[$i];
            $new_results[] = json_encode($jres);
        }
        $result = "[" . implode(",", $new_results) . "]";
    } else {
        foreach($results as &$result) $result = preg_replace("/<\?xml.*\?>/", "", $result); // remove xml header from each response
        $result = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><responseWrappers>' . implode('', $results) . '</responseWrappers>';
    }

    return Array("body" => $result, "http_code" => $status_code);
}

?>
