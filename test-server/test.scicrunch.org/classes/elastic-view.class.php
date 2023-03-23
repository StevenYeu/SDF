<?php

class ElasticRRIDManager {
    private $_name;
    private $_plural_name;
    private $_viewid;
    private $_es_index;
    private $_es_type;

    private $_fields;
    private $_facets;
    private $_fields_map;
    private $_facets_map;
    private $_special_fields;
    private $_snippet_func;

    /* special fields every rrid should have */
    private $_rrid_fields = Array(
        "name" => NULL,
        "curie" => NULL,
        "url" => NULL,
        "description" => NULL,
        "proper-citation" => NULL,
        "type" => NULL,
        "uuid" => NULL,
    );

    private function __construct($name, $plural_name, $viewid, $es_index, $es_type, $fields, $facets, $rrid_fields, $special_fields, $snippet_func) {
        $this->_name = $name;
        $this->_plural_name = $plural_name;
        $this->_viewid = $viewid;
        $this->_es_index = $es_index;
        $this->_es_type = $es_type;

        $this->_fields = $fields ?: Array();
        $this->_facets = $facets ?: Array();

        $this->_fields_map = Array();
        foreach($this->_fields as $i => $f) { $this->_fields_map[$f->name] = $i; }
        $this->_facets_map = Array();
        foreach($this->_facets as $i => $f) { $this->_facets_map[$f->name] = $i; }

        foreach($rrid_fields as $rf) {
            $this->_rrid_fields[$rf->name] = $rf;
        }
        foreach($this->_rrid_fields as $key => $rf) {
            if(is_null($rf)) {
                throw new Exception("missing rrid field: " . $key);
            }
        }

        foreach($special_fields as $sf) {
            $this->_special_fields[$sf->name] = $sf;
        }

        $this->_snippet_func = $snippet_func;
    }

    public static function managerAntibody($viewid) {
        $vendor_view = function($raw_result) {
            $vendors = Array();
            foreach($raw_result["_source"]["vendors"] as $v) {
                $v_string = $v["name"];
                if($v["uri"]) {
                    $v_string = '<a target="_blank" href="' . $v["uri"] . '">' . $v_string . '</a>';
                }
                $vendors[] = $v_string;
            }
            return implode(", ", $vendors);
        };

        $host_org_view = function($raw_result) {
            $organisms = Array();
            foreach($raw_result["_source"]["organisms"] as $org) {
                if($org["role"] != "antibody source") {
                    $organisms[] = $org["species"]["name"];
                }
            }
            return implode(", ", $organisms);
        };

        $url_view = function($raw_result) {
            return "http://antibodyregistry.org/" . $raw_result["_source"]["item"]["identifier"];
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $ratings_view = function($raw_result) {
            /* iscc rating */
            $ratings = Array();
            if(strpos(strtolower($raw_result["_source"]["item"]["notes"][0]["description"]), "rated by iscc") !== false) {
                $ratings[] = Array("text" => "Rated by Intestinal Stem Cell Consortium", "url" => "https://iscconsortium.org/resourcecatalog/");
            }

            /* encode rating */
            $encode_rating_url = NULL;
            foreach($raw_result["_source"]["vendors"] as $vendor) {
                if(strpos(strtolower($vendor["uri"]), "encodeproject.org") !== false) {
                    $encode_rating_url = $vendor["uri"];
                    break;
                }
            }
            if(!$encode_rating_url) {
                $encode_matches = NULL;
                preg_match("/ENCODE ID: ([a-zA-Z0-9]+)/", $raw_result["_source"]["item"]["notes"][0]["description"], $encode_matches);
                if(count($encode_matches) > 1) {
                    $encode_rating_url = "https://www.encodeproject.org/antibodies/" . $encode_matches[1];
                }
            }
            if($encode_rating_url) {
                $ratings[] = Array("text" => "Validation information available at ENCODE Project", "url" => $encode_rating_url);
            }

            /* atlas rating -- Vicky-2018-12-13 */
            $atlas_rating_url = NULL;
            $sigma_rating_url = NULL;
            foreach($raw_result["_source"]["vendors"] as $vendor) {

                if($vendor["uri"] && strpos(strtolower($vendor["catalogNumber"]), "hpa") !== false) {
                    $atlas_rating_url = $vendor["uri"];
                    break;
                }

                if($vendor["name"] == "Sigma-Aldrich" && strpos(strtolower($vendor["catalogNumber"]), "hpa") !== false) {
                    $sigma_rating_url = "https://www.sigmaaldrich.com/catalog/product/sigma/" . strtolower($vendor["catalogNumber"]);
                    break;
                }
            }

            if($atlas_rating_url) {
                $ratings[] = Array("text" => "Validation data available at HPA - Atlas Antibody Company", "url" => $atlas_rating_url);
            }

            if($sigma_rating_url) {
                $ratings[] = Array("text" => "Validation data available at HPA - Sigma-Aldrich Company", "url" => $sigma_rating_url);
            }

            return $ratings;
        };

        ## alerts for discontinued antibodies -- Vicky-2019-2-4
        $alerts_view = function($raw_record) {
            $alerts = Array();

            // if(strpos(strtolower($raw_record["_source"]["item"]["notes"][0]["description"]), "discontinued") !== false || strpos(strtolower($raw_record["_source"]["item"]["name"]), "discontinued") !== false) {
            //     $alerts[] = Array("text" => "Discontinued antibody", "type" => "warning");
            // }

            if(in_array("Discontinued", $raw_record["_source"]["issues"]["status"]))
                $alerts[] = Array("text" => "Discontinued antibody", "type" => "warning", "icon" => "<i class='text-danger fa fa-exclamation-triangle' style='color:orange'></i>");

            return $alerts;
        };

        $snippet_func = function($result) {
            $comment = str_replace(['<font color="#ff6347"></> ', '<font color="#000000"></> '], "", $result->getField("Comments"));
            if (strpos(strtolower($comment), "problematic") !== false) {
              $comment = "<font color='red'>".$comment."</font>";
            }
            $target = $result->getField("Target Antigen");
            if(trim($target) == ",") $target = "";
            return "<strong>Comments:</strong> " . $comment . "<br/><strong>Host Organism:</strong> " . $result->getField("Host Organism") . "<br/><strong>Clonality</strong> " . $result->getField("Clonality") . "<br/><strong>Target(s): </strong> " . $target;
        };

        $report_data_order_view = function() {
            $report_data_order = Array(
                "data_order" => Array(
                              "URL",
                              "Proper Citation",
                              "Target Antigen",
                              "Host Organism",
                              "Clonality",
                              "Comments",   /*--top--*/
                              "Antibody Name",
                              "Description",
                              "Target Organism",
                              "Clone ID",
                              "References",
                              "Antibody ID",
                              "Vendor",
                              "Catalog Number",
                          ),
                "top_info_count" => 6,
            );

            return $report_data_order;
        };

        $fields = Array(
            new ElasticRRIDField("Antibody Name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("Proper Citation", "rrid.properCitation", Array("rrid.properCitation"), "rrid.properCitation"),
            //new ElasticRRIDField("Description", "item.description", Array("item.description")),
            new ElasticRRIDField("Target Antigen", "antibodies.primary.[].targets.[].name", Array("antibodies.primary.targets.name"), "antibodies.primary.targets.name.aggregate"),
            new ElasticRRIDField("Target Organism", "organisms.target.[].species.name", Array("organisms.target.species.name"), "organisms.target.species.name.aggregate"),
            new ElasticRRIDField("Clone ID", "antibodies.primary.[].clone.identifier", Array("antibodies.primary.clone.identifier")),
            new ElasticRRIDField("References", "references.[].curie", Array("references.curie"), "references.curie.aggregate"),
            new ElasticRRIDField("Comments", "item.notes.[].description", Array("item.notes.description")),
            new ElasticRRIDField("Clonality", "antibodies.primary.[].clonality.name", Array("antibodies.primary.clonality.name"), "antibodies.primary.clonality.name.aggregate"),
            new ElasticRRIDField("Host Organism", "organisms.source.[].species.name", Array("organisms.source.species.name"), "organisms.source.species.name.aggregate"),
            new ElasticRRIDField("Antibody ID", "item.identifier", Array("item.identifier")),
            new ElasticRRIDField("Vendor", $vendor_view, Array("vendors.name", "vendors.uri"), "vendors.name.aggregate", Array("snippet-filter" => true)),
            new ElasticRRIDField("Catalog Number", "vendors.[].catalogNumber", Array("vendors.catalogNumber"), NULL, Array("snippet-filter" => true)),
            new ElasticRRIDField("Mentions Count", "mentions.[].totalMentions.count", Array("mentions.totalMentions.count"), "mentions.totalMentions.count", Array("table" => false)),
            new ElasticRRIDField("Uid", $id_view, Array(), NULL, Array("table" => false)),  ## added unique id information -- Vicky-2018-12-20
        );

        $facets = Array(
            new ElasticRRIDField("Target Antigen", "antibodies.primary.[].targets.[].name", Array(), "antibodies.primary.targets.name.aggregate"),
            new ElasticRRIDField("Target Organism", "organisms.target.[].species.name", Array(), "organisms.target.species.name.aggregate"),
            new ElasticRRIDField("Vendor", $vendor_view, Array(), "vendors.name.aggregate"),
            new ElasticRRIDField("Clonality", "antibodies.primary.[].clonality.name", Array(), "antibodies.primary.clonality.name.aggregate"),
            new ElasticRRIDField("Host Organism", "organisms.source.[].species.name", Array(), "organisms.source.species.name.aggregate"),
            new ElasticRRIDField("Mentions", "mentions.[].availability", Array(), "mentions.availability.keyword"),
            new ElasticRRIDField("Validation", "validation.isValidated", Array(), "validation.isValidated"),
            new ElasticRRIDField("Issues", "issues.status", Array(), "issues.status"),
        );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("curie", "rrid.curie", Array()),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
            new ElasticRRIDField("description", "item.description", Array()),
            new ElasticRRIDField("type", function() { return "antibody"; }, Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            new ElasticRRIDField("vendors-name", "vendors.[].name", Array()),
            new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
            new ElasticRRIDField("issues", "issues.status", Array(), "issues.status"),
            new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-18
        );

        $special_fields = Array(
            new ElasticRRIDField("ratings", $ratings_view, Array()),
            new ElasticRRIDField("alerts", $alerts_view, Array()),  ## alerts for discontinued antibodies -- Vicky-2019-2-4
            new ElasticRRIDField("report-data-order", $report_data_order_view, Array()),
        );

        ## "RIN_Antibody_prod" replaced "scr_006397_20180619" -- Vicky-2019-1-17
        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("Antibody", "Antibodies", $viewid, "RIN_Antibody_new", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("Antibody", "Antibodies", $viewid, "RIN_Antibody_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function managerTool($viewid) {
        $url_view = function($raw_result){
            foreach($raw_result["_source"]["distributions"]["current"] as $d) {
                if($d["type"] == "landing page") {
                    return $d["uri"];
                }
            }
            return "";
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $curie_view = function($raw_record) {
            return "RRID:" . $raw_record["_source"]["item"]["identifier"];
        };

        $snippet_func = function($result) {
            return $result->getRRIDField("description");
        };

        $ratings_view = function($raw_record) use($viewid) {
            $ratings = Array();
            $nitrc_rating = RRIDRating::loadBy(Array("viewid", "rrid", "source"), Array($viewid, $raw_record["_source"]["rrid"]["curie"], "nitrc"));
            if(!is_null($nitrc_rating)) {
                $ratings[] = Array(
                    "text" => "Rated at NITRC",
                    "url" => $nitrc_rating->rating["url"],
                    "count" => $nitrc_rating->rating["count"],
                    "score" => $nitrc_rating->rating["rating"],
                    "out-of" => 5,
                );
            }
            return $ratings;
        };

        $description_view = function($raw_record) {
            return \helper\formattedDescription($raw_record["_source"]["item"]["description"]);
        };

        $report_data_order_view = function() {
            $report_data_order = Array(
                "data_order" => Array(
                              "URL",
                              "Proper Citation",
                              "Description",
                              "Resource Type",
                              "References",
                              "Keywords",
                              "Parent Organization",    /*--top--*/
                              "Related Condition",
                              "Funding Agency",
                              "Related resources",
                              "Availability",
                              "Website Status",
                              "Abbreviations",
                              "Resource Name",
                              "Resource ID",
                              "Alternate IDs",
                              "Alternate URLs",
                              "Old URLs",
                          ),
                "top_info_count" => 7,
            );

            return $report_data_order;
        };

        $fields = Array(
            new ElasticRRIDField("Resource Name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("Proper Citation", "rrid.properCitation", Array("rrid.properCitation"), "rrid.properCitation"),
            new ElasticRRIDField("Resource Type", "item.types.[].name", Array("item.types.name"), "item.types.name.aggregate"),
            new ElasticRRIDField("Description", $description_view, Array("item.description"), NULL, Array("single-item" => false)),
            new ElasticRRIDField("Keywords", "item.keywords.[].keyword", Array("item.keywords.keyword"), "item.keywords.keyword"),
            new ElasticRRIDField("Resource ID", "item.identifier", Array("item.identifier")),
            new ElasticRRIDField("Parent Organization", "graph.parent.[].name", Array("graph.parents.name")),
            new ElasticRRIDField("Related Condition", "diseases.related.[].name", Array("diseases.related.name"), "diseases.related.name.aggregate"),
            new ElasticRRIDField("Funding Agency", "supportingAwards.[].agency.name", Array("supportingAwards.agency.name"), "supportingAwards.agency.name.aggregate"),
            // new ElasticRRIDField("Related To", "ancestry.relations.[].name", Array("ancestry.relations.name"), "ancestry.relations.name.aggregate"),
            new ElasticRRIDField("Related resources", "graph.related.[].name", Array("graph.related.name"), "graph.related.name.aggregate"),
            new ElasticRRIDField("References", "references.[].curie", Array("references.curie"), "references.curie.aggregate"),
            new ElasticRRIDField("Availability", "item.availability.[].description", Array("item.availability.description")),
            new ElasticRRIDField("Website Status", "item.status", Array("item.status"), "item.status"),
            new ElasticRRIDField("Alternate IDs", "item.alternateIdentifiers.[].identifier", Array("item.alternateIdentifiers.identifier")),
            new ElasticRRIDField("Alternate URLs", "distributions.alternate.[].uri", Array("distributions.alternate.uri")),
            new ElasticRRIDField("Old URLs", "distributions.deprecated.[].uri", Array("distributions.deprecated.uri")),
            new ElasticRRIDField("Abbreviations", "item.abbreviations.[].name", Array("item.abbreviations.name"), "item.abbreviations.name.aggregate"),
            new ElasticRRIDField("Mentions Count", "mentions.[].totalMentions.count", Array("mentions.totalMentions.count"), "mentions.totalMentions.count", Array("table" => false)),
        );

        $facets = Array(
            new ElasticRRIDField("Resource Type", "", Array(), "item.types.name.aggregate"),
            new ElasticRRIDField("Keywords", "", Array(), "item.keywords.keyword"),
            new ElasticRRIDField("Organism", "organisms.related.[].species.name", Array(), "organisms.related.species.name.aggregate"),
            new ElasticRRIDField("Related Condition", "", Array(), "diseases.related.name.aggregate"),
            new ElasticRRIDField("Funding Agency", "", Array(), "supportingAwards.agency.name.aggregate"),
            // new ElasticRRIDField("Relation", "", Array(), ""), -- missing
            // new ElasticRRIDField("Related To", "", Array(), "ancestry.relations.name.aggregate"),
            new ElasticRRIDField("Website Status", "", Array(), "item.status"),
            // new ElasticRRIDField("Availability", "", Array(), ""), -- missing
            new ElasticRRIDField("Mentions", "mentions.[].availability", Array(), "mentions.availability.keyword"),
        );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("curie", "rrid.curie", Array()),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
            new ElasticRRIDField("description", $description_view, Array()),
            new ElasticRRIDField("type", function() { return "tool"; }, Array()),
            new ElasticRRIDField("vendors-name", "vendors.[].name", Array()),
            new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-2
            new ElasticRRIDField("parents-name", "organization.hierarchy.ancestors.[].name", Array()),
            new ElasticRRIDField("parents-id", "organization.hierarchy.ancestors.[].curie", Array()),
        );

        $special_fields = Array(
            new ElasticRRIDField("ratings", $ratings_view, Array()),
            new ElasticRRIDField("report-data-order", $report_data_order_view, Array()),
        );

        ## "RIN_Tool_prod" replaced "scr_005400_20180824" -- Vicky-2019-1-17
        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("Tool", "Tools", $viewid, "RIN_Tool_new", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("Tool", "Tools", $viewid, "RIN_Tool_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function managerCellline($viewid) {
        $url_view = function($raw_record) {
            return "https://web.expasy.org/cellosaurus/" . $raw_record["_source"]["item"]["identifier"];
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        ## alerts for problematic and discontinued cell lines -- Vicky-2018-12-18
        $alerts_view = function($raw_record) {
            $alerts = Array();

            // if(strpos(strtolower($raw_record["_source"]["issues"]["status"]), "problematic") !== false) {
            //     $alerts[] = Array("text" => "Problematic cell line", "type" => "warning");
            // }
            //
            // if(strpos(strtolower($raw_record["_source"]["item"]["comment"]), "discontinued") !== false) {
            //     $alerts[] = Array("text" => "Discontinued cell line", "type" => "warning");
            // }

            if(in_array("Discontinued", $raw_record["_source"]["issues"]["status"]))
                $alerts[] = Array("text" => "Discontinued cell line", "type" => "warning", "icon" => "<i class='text-danger fa fa-exclamation-triangle' style='color:orange'></i>");

            if(in_array("Problematic", $raw_record["_source"]["issues"]["status"]))
                $alerts[] = Array("text" => "Problematic cell line", "type" => "warning", "icon" => "<i class='text-danger fa fa-exclamation-triangle'></i>");

            if(in_array("Warning", $raw_record["_source"]["issues"]["status"]))
                $alerts[] = Array("text" => "Issues found", "type" => "warning", "icon" => "<i class='text-danger fa fa-exclamation-triangle' style='color:orange'></i>");

            return $alerts;
        };

        $report_data_order_view = function() {
            $report_data_order = Array(
                "data_order" => Array(
                              "URL",
                              "Proper Citation",
                              "Description",
                              "Sex",
                              "Disease",
                              "References",
                              "Comments",       /*--top--*/
                              "Category",
                              "Organism",
                              "Name",
                              "Synonyms",
                              "Cross References",
                              "ID",
                              "Vendor",
                              "Catalog Number",
                              "Hierarchy",
                              "Originate from Same Individual",
                          ),
                "top_info_count" => 7,
            );

            return $report_data_order;
        };

        $snippet_func = function($result) {
            $comment = str_replace(['<font color="#ff6347"></> ', '<font color="#000000"></> '], "", $result->getField("Comments"));
            if (strpos(strtolower($result->getRRIDField("issues")), "problematic") !== false) {
              $comment = "<font color='red'>".$comment."</font>";
            }
            return "<strong>Organism:</strong> " . $result->getField("Organism") . "<br/><strong>Disease:</strong> " . $result->getField("Disease") . "<br/><strong>Category:</strong> " . $result->getField("Category") . "<br/><strong>Comments:</strong> " . $comment;
        };

        $fields = Array(
            new ElasticRRIDField("Name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("Proper Citation", "rrid.properCitation", Array("rrid.properCitation"), "rrid.properCitation"),
            new ElasticRRIDField("ID", "item.identifier", Array("item.identifier")),
            new ElasticRRIDField("Organism", "organisms.origin.[].species.name", Array("organisms.origin.species.name"), "organisms.origin.species.name.aggregate"),
            new ElasticRRIDField("Disease", "diseases.host.[].name", Array("diseases.host.name"), "diseases.host.name.aggregate"),
            new ElasticRRIDField("Comments", "item.comment", Array("item.comment")),
            new ElasticRRIDField("References", "references.[].curie", Array("references.curie"), "references.curie.aggregate"),
            new ElasticRRIDField("Category", "item.keywords.[].keyword", Array("item.keywords.keyword"), "item.keywords.keyword"),
            new ElasticRRIDField("Sex", "attributes.[].sex.value", Array("attributes.sex.value"), "attributes.sex.value"),
            new ElasticRRIDField("Synonyms", "item.synonyms.[].name", Array("item.synonyms.name"), "item.synonyms.name.aggregate"),
            new ElasticRRIDField("Vendor", "vendors.[].name", Array("vendors.name"), "vendors.name.aggregate", Array("snippet-filter" => true)),
            new ElasticRRIDField("Catalog Number", "vendors.[].catalogNumber", Array("vendors.catalogNumber")),
            new ElasticRRIDField("Cross References", "item.alternateIdentifiers.[].curie", Array("item.alternateIdentifiers.curie"), "item.alternateIdentifiers.curie.aggregate"),
            new ElasticRRIDField("Hierarchy", "graph.parent.[].curie", Array("graph.parent.curie"), "graph.parent.curie.aggregate"),
            new ElasticRRIDField("Originate from Same Individual", "graph.sibling.[].curie", Array("graph.sibling.curie"), "graph.sibling.curie.aggregate"),
            //new ElasticRRIDField("Mentions", "mentions.[].availability", Array("mentions.availability"), "mentions.availability.keyword"),
            new ElasticRRIDField("Mentions Count", "mentions.[].totalMentions.count", Array("mentions.totalMentions.count"), "mentions.totalMentions.count", Array("table" => false)),
            new ElasticRRIDField("Uid", $id_view, Array(), NULL, Array("table" => false)),
        );

        $facets = Array(
            new ElasticRRIDField("Vendor", "", Array(), "vendors.name.aggregate"),
            new ElasticRRIDField("Category", "", Array(), "item.keywords.keyword"),
            new ElasticRRIDField("Disease", "", Array(), "diseases.host.name.aggregate"),
            new ElasticRRIDField("Organism", "", Array(), "organisms.origin.species.name.aggregate"),
            new ElasticRRIDField("References", "", Array(), "references.curie.aggregate"),
            new ElasticRRIDField("Sex", "", Array(), "attributes.sex.value"),
            new ElasticRRIDField("Mentions", "mentions.[].availability", Array(), "mentions.availability.keyword"),
            new ElasticRRIDField("Issues", "issues.status", Array(), "issues.status"),
        );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
            //new ElasticRRIDField("Vendor", "vendors.[].name", Array("vendors.name"), "vendors.name.aggregate"),
            new ElasticRRIDField("curie", "rrid.curie", Array()),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
            new ElasticRRIDField("description", "item.description", Array()),
            new ElasticRRIDField("type", function() { return "Cell Line"; }, Array()),
            new ElasticRRIDField("vendors-name", "vendors.[].name", Array()),
            new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            new ElasticRRIDField("issues", "issues.status", Array(), "issues.status"),
            new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-18
        );

        $special_fields = Array(
            new ElasticRRIDField("alerts", $alerts_view, Array()),
            new ElasticRRIDField("report-data-order", $report_data_order_view, Array()),
        );

        ## "RIN_CellLine_prod" replaced "013869_20180827" -- Vicky-2019-1-17
        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("Cell Line", "Cell Lines", $viewid, "RIN_CellLine_new", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("Cell Line", "Cell Lines", $viewid, "RIN_CellLine_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function managerOrganism($viewid) {
        $url_view = function($raw_record) {
            $uri = $raw_record["_source"]["vendors"][0]["uri"];
            if($uri) {
                return $uri;
            }
            return "";
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $snippet_func = function($result) {
            $ret = "";
            $ret .= "<strong>Source Database:</strong> " . $result->getField("Database") . "<br/>";
            $ret .= "<strong>Genetic Background:</strong> " . $result->getField("Background") . "<br/>";
            $ret .= "<strong>Affected Genes:</strong> " . $result->getField("Affected Gene") . "<br/>";
            $ret .= "<strong>Genomic Alteration:</strong> " . $result->getField("Genomic Alteration") . "<br/>";
            $ret .= "<strong>Availability:</strong> " . $result->getField("Availability") . "<br/>";
            $ret .= "<strong>References:</strong> " . self::addReferenceLinks($result->getField("References")) . "<br/>";
            $ret .= "<strong>Notes:</strong> " . $result->getField("Notes");
            return $ret;
        };

        $report_data_order_view = function() {
            $report_data_order = Array(
                "data_order" => Array(
                              "URL",
                              "Proper Citation",
                              "Description",
                              "Affected Gene",
                              "Database",
                              "Notes",
                              "References",     /*--top--*/
                              "Organism Name",
                              "Database Abbreviation",
                              "Species",
                              "Phenotype",
                              "Availability",
                              "Genomic Alteration",
                              "Catalog Number",
                              "Background",
                          ),
                "top_info_count" => 7,
            );

            return $report_data_order;
        };

        $fields = Array(
            new ElasticRRIDField("Organism Name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("Proper Citation", "rrid.properCitation", Array("rrid.properCitation"), "rrid.properCitation"),
            new ElasticRRIDField("Database", "vendors.[].name", Array("vendors.name"), "vendors.name.aggregate"),
            new ElasticRRIDField("Database Abbreviation", "vendors.[].abbreviation", Array("vendors.abbreviation"), "vendors.abbreviation.keyword"),
            new ElasticRRIDField("Species", "organisms.primary.[].species.name", Array("organisms.primary.species.name"), "organisms.primary.species.name.aggregate"),
            new ElasticRRIDField("Phenotype", "phenotypes.[].name", Array("phenotypes.name"), "phenotypes.name.aggregate"),
            new ElasticRRIDField("Availability", "item.availability.[].description", Array("item.availability.description"),"item.availability.keyword"),
            new ElasticRRIDField("References", "references.[].curie", Array("references.curie"), "references.curie.aggregate"),
            new ElasticRRIDField("Notes", "item.notes.[].description", Array("item.notes.description"), NULL),
            new ElasticRRIDField("Affected Gene", "genotype.gene.[].name", Array("genotype.gene.name"), "genotype.gene.name.aggregate"),
            new ElasticRRIDField("Genomic Alteration", "genotype.genomicAlterations.[].name", Array("genotype.genomicAlterations.name"), "genotype.genomicAlterations.name.aggregate"),
            new ElasticRRIDField("Catalog Number", "item.identifier", Array("item.identifier"), NULL, Array("snippet-filter" => true)),
            new ElasticRRIDField("Background", "organisms.primary.[].background.name", Array("organisms.primary.background.name"), "organisms.primary.background.name.aggregate"),
            new ElasticRRIDField("Mentions Count", "mentions.[].totalMentions.count", Array("mentions.totalMentions.count"), "mentions.totalMentions.count", Array("table" => false)),
        );

        $facets = Array(
            new ElasticRRIDField("Database", "vendors.[].abbreviation", Array(), "vendors.abbreviation.keyword"),
            new ElasticRRIDField("Species", "organisms.primary.[].species.name", Array(), "organisms.primary.species.name.aggregate"),
            new ElasticRRIDField("Background", "organisms.primary.[].background.name", Array(), "organisms.primary.background.name.aggregate"),
            new ElasticRRIDField("Genomic Alteration", "genotype.genomicAlterations.[].name", Array(), "genotype.genomicAlterations.name.aggregate"),
            new ElasticRRIDField("Affected Gene", "genotype.gene.[].name", Array(), "genotype.gene.name.aggregate"),
            new ElasticRRIDField("Phenotype", "phenotypes.[].name", Array(), "phenotypes.name.aggregate"),
            // new ElasticRRIDField("References", "", Array(), "references.curie.keyword"), --missing
            new ElasticRRIDField("Availability", "item.availability.keyword", Array(), "item.availability.keyword"),
            new ElasticRRIDField("Mentions", "mentions.[].availability", Array(), "mentions.availability.keyword"),
        );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("curie", "rrid.curie", Array()),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
            new ElasticRRIDField("description", "item.description", Array()),
            new ElasticRRIDField("type", function() { return "Organism"; }, Array()),
            new ElasticRRIDField("vendors-name", "vendors.[].name", Array()),
            new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-18
        );

        $special_fields = Array(
            new ElasticRRIDField("report-data-order", $report_data_order_view, Array()),
        );

        ## "RIN_Organism_prod" replaced "scr_001421_20180622" -- Vicky-2019-1-17
        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("Organism", "Organisms", $viewid, "RIN_Organism_new", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("Organism", "Organisms", $viewid, "RIN_Organism_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function managerPlasmid($viewid) {
        $url_view = function($raw_record) {
            $uri = $raw_record["_source"]["vendors"][0]["uri"];
            if($uri) {
                return $uri;
            }
            return "";
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $snippet_func = function($result) {
            $ret = "";
            $ret .= "<strong>Species:</strong> " . $result->getField("Organism") . "<br/>";
            $ret .= "<strong>Genetic Insert:</strong> " . $result->getField("Insert Name") . "<br/>";
            $ret .= "<strong>Vector Backbone Description:</strong> " . $result->getField("Vector Backbone Description") . "<br/>";
            $ret .= "<strong>References:</strong> " . self::addReferenceLinks($result->getField("References")) . "<br/>";
            $ret .= "<strong>Comments:</strong> " . $result->getField("Comments");
            return $ret;
        };

        $report_data_order_view = function() {
            $report_data_order = Array(
                "data_order" => Array(
                              "URL",
                              "Proper Citation",
                              "Insert Name",
                              "Organism",
                              "Bacterial Resistance",
                              "References",
                              "Vector Backbone Description",
                              "Comments",                     /*--top--*/
                              "Plasmid Name",
                              "Relevant Mutation",
                          ),
                "top_info_count" => 8,
            );

            return $report_data_order;
        };

        $fields = Array(
            new ElasticRRIDField("Plasmid Name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("Proper Citation", "rrid.properCitation", Array("rrid.properCitation"), "rrid.properCitation.aggregate"),
            new ElasticRRIDField("Insert Name", "inserts.[].gene.name", Array("inserts.gene.name"), "inserts.gene.name.aggregate"),
            new ElasticRRIDField("Relevant Mutation", "inserts.[].gene.mutation.description", Array("inserts.gene.mutation.description"), NULL),
            new ElasticRRIDField("Organism", "organisms.inserts.[].species.name", Array("organisms.inserts.species.name"), "organisms.inserts.species.name.aggregate"),
            new ElasticRRIDField("Bacterial Resistance", "vector.growth.bacterialResistance.[].name", Array("vector.growth.bacterialResistance.name"), "vector.growth.bacterialResistance.name.aggregate"),
            new ElasticRRIDField("Comments", "item.notes.[].description", Array("item.notes.description"), NULL),
            new ElasticRRIDField("References", "references.[].curie", Array("references.curie"), "references.curie.aggregate"),
            new ElasticRRIDField("Vector Backbone Description", "vector.backbone.[].description", Array("vector.backbone.description"), NULL),
            new ElasticRRIDField("Mentions Count", "mentions.[].totalMentions.count", Array("mentions.totalMentions.count"), "mentions.totalMentions.count", Array("table" => false)),
        );

        $facets = Array(
            new ElasticRRIDField("Organism", "organisms.inserts.[].species.name", Array(), "organisms.inserts.species.name.aggregate"),
            new ElasticRRIDField("Bacterial Resistance", "vector.growth.bacterialResistance.[].name", Array(), "vector.growth.bacterialResistance.name.aggregate"),
            new ElasticRRIDField("Mentions", "mentions.[].availability", Array(), "mentions.availability.keyword"),
        );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("curie", "rrid.curie", Array()),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
            new ElasticRRIDField("description", "", Array()),
            new ElasticRRIDField("type", function() { return "Plasmid"; }, Array()),
            new ElasticRRIDField("vendors-name", "vendors.[].name", Array()),
            new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-18
        );

        $special_fields = Array(
            new ElasticRRIDField("report-data-order", $report_data_order_view, Array()),
        );

        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("Plasmid", "Plasmids", $viewid, "RIN_Addgene_new", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("Plasmid", "Plasmids", $viewid, "RIN_Addgene_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function managerBiosample($viewid) {
        $url_view = function($raw_record) {
            $uri = $raw_record["_source"]["vendors"][0]["uri"];
            if($uri) {
                return $uri;
            }
            return "";
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $snippet_func = function($result) {
            $ret = "";
            $ret .= "<strong>Organism:</strong> " . $result->getField("Species") . "<br/>";
            $ret .= "<strong>Disease:</strong> " . $result->getField("Disease") . "<br/>";
            $ret .= "<strong>Category:</strong> " . $result->getField("Category") . "<br/>";
            $ret .= "<strong>Comments:</strong> " . $result->getField("Comments");
            return $ret;
        };

         $report_data_order_view = function() {
            $report_data_order = Array(
                "data_order" => Array(
                              "URL",
                              "Proper Citation",
                              "Sex of Cell",
                              "Species",
                              "Disease",
                              "Category",
                              "Vendor",
                              "Comments",       /*--top--*/
                              "Biosample Name",
                              "NCBI Biosample ID",
                              "Cross References",
                          ),
                "top_info_count" => 8,
            );

            return $report_data_order;
        };

        $fields = Array(
            new ElasticRRIDField("Biosample Name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("Proper Citation", "rrid.properCitation", Array("rrid.properCitation"), "rrid.properCitation.aggregate"),
            new ElasticRRIDField("NCBI Biosample ID", "item.identifier", Array("item.identifier"), "item.identifier.aggregate"),
            new ElasticRRIDField("Vendor", "vendors.[].name", Array("vendors.name"), "vendors.name.aggregate"),
            new ElasticRRIDField("Sex of Cell", "attributes.[].sex.value", Array("attributes.sex.value"), "attributes.sex.value"),
            new ElasticRRIDField("Category", "item.keywords.[].name", Array("item.keywords.name"), "item.keywords.name.aggregate"),
            new ElasticRRIDField("Disease", "diseases.primary.[].name", Array("diseases.primary.name"), "diseases.primary.name.aggregate"),
            new ElasticRRIDField("Comments", "item.notes.[].description", Array("item.notes.description")),
            new ElasticRRIDField("Species", "organisms.primary.[].species.name", Array("organisms.primary.species.name"), "organisms.primary.species.name.aggregate"),
            new ElasticRRIDField("Cross References", "graph.parent.[].id", Array("graph.parent.id"), "graph.parent.id.keyword"),
            new ElasticRRIDField("Mentions Count", "mentions.[].totalMentions.count", Array("mentions.totalMentions.count"), "mentions.totalMentions.count", Array("table" => false)),
            //new ElasticRRIDField("Hierarchy", "graph.parent.[].curie", Array("graph.parent.curie"), "graph.parent.curie.aggregate"),
        );

        $facets = Array(
          new ElasticRRIDField("Sex of Cell", "attributes.[].sex.value", Array(), "attributes.sex.value"),
          new ElasticRRIDField("Category", "item.keywords.[].name", Array(), "item.keywords.name.aggregate"),
          new ElasticRRIDField("Disease", "diseases.primary.[].name", Array(), "diseases.primary.name.aggregate"),
          new ElasticRRIDField("Species", "organisms.primary.[].species.name", Array(), "organisms.primary.species.name.aggregate"),
          new ElasticRRIDField("Mentions", "mentions.[].availability", Array(), "mentions.availability.keyword"),
        );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("curie", "rrid.curie", Array()),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
            new ElasticRRIDField("description", "", Array()),
            new ElasticRRIDField("type", function() { return "Biosample"; }, Array()),
            new ElasticRRIDField("vendors-name", "vendors.[].name", Array()),
            new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-18
        );

        $special_fields = Array(
            new ElasticRRIDField("report-data-order", $report_data_order_view, Array()),
        );

        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("Biosample", "Biosamples", $viewid, "RIN_BioSample_new", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("Biosample", "Biosamples", $viewid, "RIN_BioSample_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function managerProtocol($viewid) {
        // $name_view = function($raw_record) {
        //     return $raw_record["_source"]["item"]["name"]." (Version ".$raw_record["_source"]["item"]["version"]["identifier"]. ")";
        // };

        $url_view = function($raw_record) {
            $uri = "https://dx.doi.org/".$raw_record["_source"]["item"]["curie"];
            if($uri) {
                return $uri;
            }
            return "";
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $description_view = function($raw_record) {
            return str_replace("Summary: ", "", \helper\formattedDescription($raw_record["_source"]["item"]["description"]));
        };

        // $proper_citation_view_1 = function($raw_record) {
        //     $proper_citation = Array(
        //         $raw_record["_source"]["dc"]["creators"][0]["name"],
        //         $raw_record["_source"]["dc"]["publicationYear"],
        //         $raw_record["_source"]["dc"]["title"],
        //         "protocols.io",
        //         '<a target="_blank" href="https://dx.doi.org/'.$raw_record["_source"]["dc"]["doi"].'">'.'https://dx.doi.org/'.$raw_record["_source"]["dc"]["doi"].'</a>',
        //     );
        //
        //     return join(" ", $proper_citation);
        // };

        $doi_view = function($raw_record) {
            return "DOI:".$raw_record["_source"]["item"]["curie"];
        };

        // $date_view = function($raw_record) {
        //     $date=date_create($raw_record["_source"]["dc"]["dates"][0]["date"]);
        //     return date_format($date, "Y-m-d");
        // };

        $external_url_view = function($raw_record) {
            if($raw_record["_source"]["item"]["link"] == "") return "";
            else return '<a target="_blank" href="'.$raw_record["_source"]["item"]["link"]["uri"].'">'.$raw_record["_source"]["item"]["link"]["uri"].'</a>';
        };

        $snippet_func = function($result) {
            $ret = "";
            $ret .= "<strong>Authors:</strong> " . $result->getField("Authors") . "<br/>";
            $ret .= "<strong>Group:</strong> " . $result->getField("Group") . "<br/>";
            $ret .= "<strong>Summary:</strong> " . str_replace("Summary: ", "", $result->getField("Summary")) . "<br/>";
            return $ret;
        };

         $report_data_order_view = function() {
            $report_data_order = Array(
                "data_order" => Array(
                              "URL",
                              "Authors",
                              "Group",
                              "Summary",
                              "Associated Publications",
                              "RRIDs used",
                              "Affiliations",
                              "External URL",
                              "Version",
                              "Publication Date",
                              "Proper Citation",
                          ),
                "top_info_count" => 10,
            );

            return $report_data_order;
        };

        $fields = Array(
            new ElasticRRIDField("Name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("Authors", "item.authors.[].name", Array("item.authors.name"), "item.authors.name.aggregate"),
            new ElasticRRIDField("DOI", "item.curie", Array("item.curie"), "item.curie.aggregate"),
            new ElasticRRIDField("Group", "item.groups.[].name", Array("item.groups.name"), "item.groups.name.aggregate"),
            new ElasticRRIDField("Summary", $description_view, Array("item.description")),
            new ElasticRRIDField("Associated Publications", "item.associatedPublication.[].description", Array("item.associatedPublication.description")),
            new ElasticRRIDField("RRIDs used", "resources.rrid.[].curie", Array("resources.rrid.curie"), "resources.rrid.curie.aggregate"),
            new ElasticRRIDField("Affiliations", "item.authors.[].affiliation.name", Array("item.authors.affiliation.name"), "item.authors.affiliation.name.aggregate"),
            new ElasticRRIDField("External URL", "item.link.uri", Array("item.link.uri")),
            new ElasticRRIDField("Version", "item.version", Array("item.version.identifier"), "item.version.identifier.aggregate"),
            new ElasticRRIDField("Publication Date", "item.publication.year", Array("item.publication.year"), "item.publication.year.keyword"),
            new ElasticRRIDField("Proper Citation", "item.properCitation", Array("item.properCitation")),
        );

        $facets = Array(
          new ElasticRRIDField("Group", "item.groups.[].name", Array(), "item.groups.name.aggregate"),
          new ElasticRRIDField("Authors", "item.authors.[].name", Array(), "item.authors.name.aggregate"),
          // new ElasticRRIDField("Year", "item.publication.year", Array(), "item.publication.year.keyword"),
      );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name")),
            new ElasticRRIDField("curie", $doi_view, Array("item.curie")),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "item.properCitation", Array()),
            new ElasticRRIDField("description", "", Array()),
            new ElasticRRIDField("type", function() { return "Protocol"; }, Array()),
            new ElasticRRIDField("external-url", "item.link.uri", Array("item.link.uri")),
            // new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            // new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-18
        );

        $special_fields = Array(
            new ElasticRRIDField("report-data-order", $report_data_order_view, Array()),
        );

        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("Protocol", "Protocols", $viewid, "RIN_Protocols_pr", "", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("Protocol", "Protocols", $viewid, "RIN_Protocols_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function managerAll($viewid) {
        $url_view = function($raw_record) {
            $uri = $raw_record["_source"]["vendors"][0]["uri"];
            if($uri) {
                return $uri;
            }
            return "";
        };

        ## added unique id information to the rrid_fields -- Vicky-2018-12-20
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $snippet_func = function($result) {
            $ret = "";
            // $ret .= "<strong>Organism:</strong> " . $result->getField("Species") . "<br/>";
            // $ret .= "<strong>Disease:</strong> " . $result->getField("Disease") . "<br/>";
            // $ret .= "<strong>Category:</strong> " . $result->getField("Category") . "<br/>";
            // $ret .= "<strong>Comment:</strong> " . $result->getField("Comments");
            return $ret;
        };

        $fields = Array(
          new ElasticRRIDField("ID", "item.identifier", Array("item.identifier"), NULL),
          new ElasticRRIDField("Vendors", "vendors.[].name", Array("vendors.name"), "vendors.name.aggregate", Array()),
          new ElasticRRIDField("Vendor URLs", "vendors.[].uri", Array("vendors.uri"), NULL, Array()),
          new ElasticRRIDField("Catalog Number", "vendors.[].catalogNumber", Array("vendors.catalogNumber"), NULL, Array()),
          new ElasticRRIDField("Alternate IDs", "item.alternateIdentifiers.[].identifier", Array("item.alternateIdentifiers.identifier")),
          new ElasticRRIDField("Alternate URLs", "distributions.alternate.[].uri", Array("distributions.alternate.uri")),

        );

        $facets = Array(

        );

        $rrid_fields = Array(
            new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
            new ElasticRRIDField("curie", "rrid.curie", Array()),
            new ElasticRRIDField("url", $url_view, Array()),
            new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
            new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
            new ElasticRRIDField("description", "", Array()),
            new ElasticRRIDField("type", "item.types.[].name", Array()),
            new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
            new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),   ## added mentions total count -- Vicky-2019-4-18
        );

        $special_fields = Array(

        );
        if($_SESSION['new_index'] == "true") return new ElasticRRIDManager("All", "All", $viewid, "*_new", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
        else return new ElasticRRIDManager("All", "All", $viewid, "*_pr", "rin", $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
    }

    public static function esManager($viewid, $check_snippet) {
      $viewIDs = explode(",", $viewid);
      $source_index = "";

      $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
      $data_sources = json_decode($data_sources_list, true);
      $data_sources_list = file_get_contents("ssi/elements/discovery/json/index.json");
      $data_sources += json_decode($data_sources_list, true);

      if(count(array_intersect($viewIDs, array_keys($data_sources))) == count($viewIDs)) {
          if(count($viewIDs) > 1) {
              $data_source_config = "general_config.json";
              $index = Array();
              foreach ($viewIDs as $viewID) {
                  if(isset($data_sources[$viewID]))
                      $index[] = $data_sources[$viewID]["es_index"];
              }
              // if(count($index) > 1) $index[] = "-RIN_Mentions_pr";
              $source_index = join(",", $index);
              if(count($index) > 1 && strpos($source_index, "*_") !== false) $source_index .= ",-RIN_Mentions_pr";
              $source_name = $source_plural_name = "Discovery Sources";
              $source_type = "";
          } else if(count($viewIDs) == 1) {
              $data_source_config = $data_sources[$viewIDs[0]]["config"];
              $source_index = $data_sources[$viewIDs[0]]["es_index"];
              $source_name = $data_sources[$viewIDs[0]]["name"];
              $source_plural_name = $data_sources[$viewIDs[0]]["plural_name"];
              $source_type = $data_sources[$viewIDs[0]]["es_type"];
              if($viewIDs[0] == "all") {
                  $source_index = join(",", array_column($data_sources, "es_index")).",-interlex_2019nov01,-RIN_Mentions_pr";
              }
          }
      }

      if($source_index == "") \helper\errorPage("source-id");

      $es_manager_str = file_get_contents("ssi/elements/discovery/json/" . $data_source_config);
      if ($es_manager_str === false) \helper\errorPage("source-config");

      $es_manager = json_decode($es_manager_str, true);
      if ($es_manager === null) \helper\errorPage("source-config");

      $url_view = function($raw_result){
          $url = "";
          switch ($raw_result["_source"]["item"]["types"][0]["name"]) {
              case "Resource":
                  foreach($raw_result["_source"]["distributions"]["current"] as $d) {
                      if($d["type"] == "landing page") {
                          $url = $d["uri"];
                      }
                  }
                  break;

              case "antibody":
                  $url = "http://antibodyregistry.org/" . $raw_result["_source"]["item"]["identifier"];
                  break;

              case "cell line":
                  $url = "https://web.expasy.org/cellosaurus/" . $raw_result["_source"]["item"]["identifier"];
                  break;

              case "organism":
              case "plasmid":
              case "biosample":
                  $url = $raw_result["_source"]["vendors"][0]["uri"];
                  break;
          }
          return $url;
      };

      ## added unique id information to the rrid_fields
      $id_view = function($raw_result) {
          return $raw_result["_id"];
      };

      $source_indices = function($raw_result) {
          return $raw_result["_index"];
      };

      $curie_view = function($raw_record) {
          return "RRID:" . $raw_record["_source"]["item"]["identifier"];
      };

      $description_view = function($raw_record) {
          return \helper\formattedDescription($raw_record["_source"]["item"]["description"]);
      };

      if($check_snippet) {
          $_SESSION['snippet'] = $es_manager["snippet"];
          $_SESSION['snippet_params'] = $es_manager["snippet_params"];
      }

      $snippet_func = function($result) {
          $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
          $data_sources = json_decode($data_sources_list, true);

          /* find out different snippet view based on different resource index */
          foreach ($data_sources as $source) {
              if(\helper\startsWith($result->getRRIDField("source-indices"), $source["index"])) {
                  $source_config = $source["config"];
                  break;
              }
          }
          $data_source = file_get_contents("ssi/elements/discovery/json/" . $source_config);
          if ($data_source === false) return "<font color='red'>* </font>Missing data source snippet view configuration (".$source["plural_name"].")";
          /* end */

          $source_manager = json_decode($data_source, true);
          $snippet_params = $source_manager["snippet_params"];
          $snippet_body = $source_manager['snippet_body'];

          if(isset($source_manager['snippet_url']) && $source_manager['snippet_url'] != "") $snippet_url = $source_manager['snippet_url'];
          else $snippet_url = "";

          if(isset($source_manager['snippet_name']) && $source_manager['snippet_name'] != "") $snippet_name = $source_manager['snippet_name'];
          else $snippet_name = $result->getRRIDField("name");

          $es_snippet_body = Array();
          foreach ($snippet_params as $key => $value) {
              $snippet_param = self::getResult($result, $value);
              if($value == "item.notes.[].description" || $value == "item.comment") {
                  if (strpos(strtolower($result->getRRIDField("issues")), "problematic") !== false) {
                    $snippet_param = "<font color='red'>".$snippet_param."</font>";
                  }
              }

              if($value == "item.identifier") {
                  if(\helper\startsWith($snippet_param, "pdb:")) $snippet_param = substr_replace($snippet_param, "", 0, strlen("pdb:"));
                  else if(\helper\startsWith($snippet_param, "pmid:")) $snippet_param = substr_replace($snippet_param, "", 0, strlen("pmid:"));
              }

              $param_index = "$" . $key;

              foreach ($snippet_body as $body) {
                  if(strpos($body, $param_index) !== false && $snippet_param != "") {
                      $es_snippet_body[] = str_replace($param_index, $snippet_param, $body);
                  }
              }

              if(strpos($snippet_url, $param_index) !== false) $snippet_url = str_replace($param_index, $snippet_param, $snippet_url);
              if(strpos($snippet_name, $param_index) !== false) $snippet_name = str_replace($param_index, $snippet_param, $snippet_name);
          }

          $snippet_view = Array(
              $snippet_name,
              $snippet_url,
              '<span class="truncate-long">'.join('</span><br><span class="truncate-long">', $es_snippet_body).'</span>',
          );

          return join("%_%", $snippet_view);
      };

      $ratings_view = function($raw_record) use($viewid) {
          $ratings = Array();
          $nitrc_rating = RRIDRating::loadBy(Array("viewid", "rrid", "source"), Array($viewid, $raw_record["_source"]["rrid"]["curie"], "nitrc"));
          if(!is_null($nitrc_rating)) {
              $ratings[] = Array(
                  "text" => "Rated at NITRC",
                  "url" => $nitrc_rating->rating["url"],
                  "count" => $nitrc_rating->rating["count"],
                  "score" => $nitrc_rating->rating["rating"],
                  "out-of" => 5,
              );
          }
          return $ratings;
      };

      $alerts_view = function($raw_record) {
          $alerts = Array();
          $item_type_name = $raw_record["_source"]["item"]["types"][0]["name"];

          if(in_array("Discontinued", $raw_record["_source"]["issues"]["status"]))
              $alerts[] = Array("text" => "Discontinued ".$item_type_name, "type" => "warning", "icon" => "<i class='text-danger fa fa-exclamation-triangle' style='color:orange'></i>");

          if(in_array("Problematic", $raw_record["_source"]["issues"]["status"]))
              $alerts[] = Array("text" => "Problematic ".$item_type_name, "type" => "warning", "icon" => "<i class='text-danger fa fa-exclamation-triangle'></i>");

          if(in_array("Warning", $raw_record["_source"]["issues"]["status"]))
              $alerts[] = Array("text" => "Issues found", "type" => "warning", "icon" => "<i class='text-danger fa fa-exclamation-triangle' style='color:orange'></i>");

          return $alerts;
      };

      $fields = Array(
          new ElasticRRIDField("Uid", $id_view, Array(), NULL, Array("table" => false)),
      );

      $facets = Array(
          new ElasticRRIDField("Data Sources", "_index", Array(), "_index"),
      );

      $rrid_fields = Array(
          new ElasticRRIDField("name", "item.name", Array("item.name"), "item.name.aggregate"),
          new ElasticRRIDField("curie", "rrid.curie", Array()),
          new ElasticRRIDField("url", $url_view, Array()),
          new ElasticRRIDField("id", $id_view, Array()),  ## added unique id information -- Vicky-2018-12-20
          new ElasticRRIDField("proper-citation", "rrid.properCitation", Array()),
          new ElasticRRIDField("description", $description_view, Array()),
          new ElasticRRIDField("vendors-name", "vendors.[].name", Array()),
          new ElasticRRIDField("vendors-uri", "vendors.[].uri", Array()),
          new ElasticRRIDField("type", "item.types.[].name", Array()),
          new ElasticRRIDField("uuid", "disco.v_uuid", Array(), "disco.v_uuid.keyword"),
          new ElasticRRIDField("mentionCount", "mentions.[].totalMentions.count", Array()),
          new ElasticRRIDField("source-indices", $source_indices, Array()),
          new ElasticRRIDField("issues", "issues.status", Array(), "issues.status"),
          new ElasticRRIDField("item-curie", "item.curie", Array()),
      );

      $special_fields = Array(
          new ElasticRRIDField("ratings", $ratings_view, Array()),
          new ElasticRRIDField("alerts", $alerts_view, Array()),
      );

      foreach ($es_manager as $es_field => $es_value) {
          if($es_field == "fields") {
              uasort($es_value, function($a, $b){	// sort by order number in each field
                  if((int) $a['order'] == (int) $b['order']) return 0;
                  return ((int) $a['order'] < (int) $b['order']) ? -1 : 1;
              });

              foreach ($es_value as $value) {
                  $esName = $value["name"];
                  $esField = $value["esField"];

                  if($value["isSearchable"] == "true") $esSearch = str_replace(".[].", ".", $esField);
                  else $esSearch = NULL;

                  if($value["isSortable"] == "true") {
                      if(isset($value["facetField"])) $esFacet = $value["facetField"];
                      else $esFacet = str_replace(".[].", ".", $esField) . ".aggregate";
                  }
                  else $esFacet = NULL;

                  if(isset($value["visibilities"])) $visibilities = $value["visibilities"];
                  else $visibilities = NULL;

                  if($value["isFacet"] == "true")
                      $facets[] = new ElasticRRIDField($esName, $esField, Array(), $esFacet);
                  if(isset($value["order"]))
                      $fields[] = new ElasticRRIDField($esName, $esField, Array($esSearch), $esFacet, $visibilities);
                  else {
                      if($value["isFacet"] == "false") $rrid_fields[] = new ElasticRRIDField($esName, $esField, Array($esSearch), $esFacet);
                  }
              }
          }
      }

      ## deal with special fields
      if($viewid == "rin-antibody") {
          $vendor_view = function($raw_result) {
              $vendors = Array();
              foreach($raw_result["_source"]["vendors"] as $v) {
                  $v_string = $v["name"];
                  if($v["uri"]) {
                      $v_string = '<a target="_blank" href="' . $v["uri"] . '">' . $v_string . '</a>';
                  }
                  $vendors[] = $v_string;
              }
              return implode(", ", $vendors);
          };
          $fields[] = new ElasticRRIDField("Vendor", $vendor_view, Array("vendors.name", "vendors.uri"), "vendors.name.aggregate", Array("snippet-filter" => true));
          $facets[] = new ElasticRRIDField("Vendor", $vendor_view, Array(), "vendors.name.aggregate");
      }

      if($_SESSION['new_index'] == "true") return new ElasticRRIDManager($source_name, $source_plural_name, $viewid, str_replace("_pr", "_new", $source_index), $source_type, $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
      else return new ElasticRRIDManager($source_name, $source_plural_name, $viewid, $source_index, $source_type, $fields, $facets, $rrid_fields, $special_fields, $snippet_func);
  }

    private static function _managerByViewID($viewid, $check) {
        switch($viewid) {
            case "nif-0000-07730-1":
                if($check) return true;
                return self::managerAntibody($viewid);
            case "nlx_144509-1":
                if($check) return true;
                return self::managerTool($viewid);
            case "SCR_013869-1":
                if($check) return true;
                return self::managerCellline($viewid);
            case "nlx_154697-1":
                if($check) return true;
                return self::managerOrganism($viewid);
            case "nif-0000-11872-1":
                if($check) return true;
                return self::managerPlasmid($viewid);
            case "nlx_143929-1":
                if($check) return true;
                return self::managerBiosample($viewid);
            case "protocol":
                if($check) return true;
                return self::managerProtocol($viewid);
            case "":
                if($check) return true;
                return self::managerAll($viewid);
            default:
                if($check) return false;
                return NULL;
        }
    }

    public static function managerByViewID($viewid) {
        return self::_managerByViewID($viewid, false);
    }

    public static function esManagerByViewID($viewid, $check_snippet = true) {
        return self::esManager($viewid, $check_snippet);
    }

    public static function managerExists($viewid) {
        return self::_managerByViewID($viewid, true);
    }

    public function search($query, $per_page, $page, $options, $es_query_flag=1) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");
        $post_data = Array(
            "size" => $per_page,
            "from" => ($page - 1) * $per_page,
        );

        $post_query = Array(
            "bool" => Array(
                "must" => Array(),
                "should" => Array(),
            ),
        );

        /* filters */
        if($options["filters"]) {
            foreach($options["filters"] as $filter) {
                if(count($filter) != 2) {
                    continue;
                }
                $filter_key = $filter[0];

                ## filter by date -- Vicky-2019-3-27
                if(in_array($filter_key, ["gte", "lte"])) {
                    if($filter_key == "gte") $filter_value = $filter[1]."T000000+0000";
                    else $filter_value = $filter[1]."T235959+0000";
                    $post_query["bool"]["filter"][]["range"]["provenance.creationDate"][] = Array(
                        $filter_key => $filter_value
                    );
                } else {
                    ## filter by fields
                    if(strpos($filter[1], " ") !== false) $filter_value = $filter[1];
                    else {
                        $filter_value = str_replace(['"', "'"], "", $filter[1]);
                        $filter_value = $filter_value;
                    }
                    $filter_field = $this->filterField($filter_key);
                    if(is_null($filter_field)) {
                        continue;
                    }
                    if(count($filter_field->es_filter_paths) >= 1) {  /* no need to or if only one filter for field */
                        $post_query["bool"]["must"][] = Array(
                            "query_string" => Array(
                                "fields" => $filter_field->es_filter_paths,
                                "query" => '"'.$filter_value.'"',
                                "default_operator" => "and",
                                "lenient" => "true",
                            ),
                        );
                    }
                }
            }
        }

        /* facets */
        if($options["facets"]) {
            $es_facets = Array();
            foreach($options["facets"] as $facet) {
                if(count($facet) != 2) {
                    continue;
                }
                $facet_key = $facet[0];
                $facet_value = $facet[1];
                $facet_field = $this->facetField($facet_key);
                if(is_null($facet_field)) {
                    continue;
                }
                if(!is_null($facet_field->es_facet_path)) {
                    $es_facets[$facet_field->es_facet_path][] = $facet_value;
                }
            }
            foreach ($es_facets as $facet_field_path => $facet) {
                $post_query["bool"]["filter"][] = Array(
                    "terms" => Array($facet_field_path => $facet),
                );
            }
        }

        /* sorting */
        if($options["sort"]) {
            $sort_column = $options["sort"]["column"];
            $sort_direction = $options["sort"]["direction"];
            $sort_field = $this->sortField($sort_column);
            if(!is_null($sort_field) && !is_null($sort_field->es_facet_path) && ($sort_direction == "asc" || $sort_direction == "desc")) {
                $post_sort = Array(
                    Array($sort_field->es_facet_path => $sort_direction),
                    "_score",
                );
                $post_data["sort"] = $post_sort;
            }
        }

        /* query */
        if($query && $query != "*") {
            $post_query["bool"]["must"][] = Array(
              "query_string" => Array(
                  "fields" => ["*"],
                  "query" => $query,
                  "type" => "cross_fields",
                  "default_operator" => "and",
                  "lenient" => "true",
              ),
            );

            $should_query = str_replace(['"', "( ", " )"], ["", "(", ")"], $query);
            $rrid_name_field = $this->_rrid_fields["name"];
            $post_query["bool"]["should"][] = Array(
                "match" => Array(
                    $rrid_name_field->es_filter_paths[0] => Array(
                        "query" => '"'.$should_query.'"',
                        "boost" => 20,
                    ),
                )
            );
            $post_query["bool"]["should"][] = Array(
                "term" => Array(
                    $rrid_name_field->es_facet_path => Array(
                        "term" => $should_query,
                        "boost" => 2000,
                    ),
                ),
            );
        }

        if(!empty($post_query["bool"]["must"]) || !empty($post_query["bool"]["filter"])) {
            $post_data["query"] = $post_query;
        }

        /* aggregations */
        if(!empty($this->_facets)) {
            $aggs = Array();
            foreach($this->_facets as $facet) {
                if(!is_null($facet->es_facet_path)) {
                    $agg_path = $facet->es_facet_path;
                    $agg = Array(
                        "terms" => Array(
                          "field" => $agg_path,
                          "size" => 200
                        )
                    );
                    $aggs[$facet->name] = $agg;
                }
            }
            if(!empty($aggs)) {
                $post_data["aggregations"] = $aggs;
            }
        }

        ## added debug button (show elastic query)  -- Vicky-2019-2-22
        if($es_query_flag) {
            if($_SESSION['user']->role == 2) $_SESSION['elastic_query'] = json_encode($post_data);
            else $_SESSION['elastic_query'] = "";
        }

        $result = \helper\sendPostRequest($url , json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);
        return $elastic_result;
    }

    public function searchDOI($doi) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");
        if(\helper\startsWith($doi, "DOI:")) {
            $doi = str_replace("DOI:", "", $doi);
        }
        $es_doi = str_replace(":", "\:", $doi);
        $es_doi = str_replace("/", "\/", $es_doi);
        $post_data = Array(
            "size" => 1000,
            "from" => 0,
            "query" => Array(
                "query_string" => Array(
                    "fields" => Array("item.curie"),
                    "query" => '"'.$es_doi.'"',

                )
            )
        );

        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);
        return $elastic_result;
    }

    public function searchRRID($rrid) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");
        if(!\helper\startsWith($rrid, "RRID:")) {
            $rrid = "RRID:" . $rrid;
        }
        $es_rrid = str_replace(":", "\\:", $rrid);
        $es_rrid = str_replace("/", "\\/", $es_rrid);
        $post_data = Array(
            "size" => 1000,
            "from" => 0,
            "query" => Array(
                "query_string" => Array(
                    "fields" => Array("rrid.curie"),
                    "query" => $es_rrid,
                )
            )
        );
        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);
        return $elastic_result;
    }

    public function searchChildren($rrid) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");

        $post_data = Array(
            "size" => 1000,
            "from" => 0,
            "query" => Array(
                "bool" => Array(
                    "should" => [
                        Array(
                              "match_phrase" => Array(
                                  "organization.hierarchy.parent.curie" => Array(
                                      "query" => $rrid
                                    )
                              ),
                        )
                    ]
                )
            ),
        );

        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);
        return $elastic_result;
    }

    public function searchGrandChildren($rrid) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");

        $post_data = Array(
            "size" => 1000,
            "from" => 0,
            "query" => Array(
                "bool" => Array(
                    "should" => [
                        Array(
                              "match_phrase" => Array(
                                  "organization.hierarchy.ancestors.curie" => Array(
                                      "query" => $rrid
                                    )
                              ),
                        )
                    ]
                )
            ),
        );

        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);
        return $elastic_result;
    }

    public function searchItemID($item_id) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");
        $es_item_id = str_replace(":", "\\:", $item_id);
        $es_item_id = str_replace("/", "\\/", $es_item_id);
        $post_data = Array(
            "size" => 1000,
            "from" => 0,
            "query" => Array(
                "query_string" => Array(
                    "fields" => Array("item.curie"),
                    "query" => $es_item_id,
                )
            )
        );
        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);
        // print "<pre>";print_r($elastic_result);print "</pre>";
        return $elastic_result;
    }

    public function searchResolverRRID($rrid) {
        $es_config = $GLOBALS["config"]["elastic-search"]["resolver"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");
        if(\helper\startsWith($rrid, "RRID:")) {
            $rrid = str_replace("RRID:", "", $rrid);
        }
        $lowercase_es_rrid_l = [strtolower($rrid), str_replace("scr:", "scr_", strtolower($rrid))];

        $post_data = Array(
            "query" => Array(
                "bool" => Array(
                    "should" => [
                        Array(
                              "terms" => Array(
                                  "item.alternateIdentifiers.identifier" => $lowercase_es_rrid_l,
                                  "boost" => 100,
                              ),
                        ),
                        Array(
                              "terms" => Array(
                                  "rrid.curie" => $lowercase_es_rrid_l,
                                  "boost" => 50,
                              ),
                        ),
                        Array(
                              "terms" => Array(
                                  "rrid.alternateRRIDs.curie" => $lowercase_es_rrid_l,
                                  "boost" => 20,
                              ),
                        ),
                    ]
                )
            ),
            "size" => 100
        );

        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);

        return Array("es" => $elastic_result, "json" => $result);
    }

    public function searchResolverDOI($doi) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");
        if(\helper\startsWith($doi, "DOI:")) {
            $doi = str_replace("DOI:", "", $doi);
        }
        $es_doi = str_replace(":", "\\:", $doi);
        $es_doi = str_replace("/", "\\/", $es_doi);
        $post_data = Array(
            "size" => 1000,
            "from" => 0,
            "query" => Array(
                "query_string" => Array(
                    "fields" => Array("item.curie"),
                    "query" => '"'.$es_doi.'"',
                )
            )
        );

        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticRRIDResults($this, $result);
        return Array("es" => $elastic_result, "json" => $result);
    }

    public function getAliases() {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/_cat/aliases/RIN_*_pr";
        // print $url;

        $header = Array("Content-type: application/json");
        $data = Array();

        $result = \helper\sendGetRequest($url, json_encode($data), $header, $es_config["user"] . ":" . $es_config["password"]);
        // $result = json_decode($result, true);

        return $result;
    }

    public function getIndices($indices) {
        $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
        $url = $es_config["base-url"] . "/_cat/indices/" . $indices;
        // print $url;

        $header = Array("Content-type: application/json");
        $data = Array();

        $result = \helper\sendGetRequest($url, json_encode($data), $header, $es_config["user"] . ":" . $es_config["password"]);
        // $result = json_decode($result, true);

        return $result;
    }

    public function searchUUID($uuid) {
        // return $this->search("", 1000, 1, Array("facets" => "uuid"));
        return $this->search($uuid, 1, 1, Array());
    }

    public function getField($raw_result, $name) {
        $field = $this->_fields[$this->_fields_map[$name]];
        if(!$field) {
            return NULL;
        }
        return $field->esToView($raw_result);
    }

    public function getName($plural = false) {
        if($plural) {
            return $this->_plural_name;
        }
        return $this->_name;
    }

    public function getViewID() {
        return $this->_viewid;
    }

    public function getResult($result, $field) {
        $res = \helper\derefArray($result->_raw_result["_source"], $field);
        if(is_array($res)) {
            return implode(", ", $res);
        } else {
            return $res;
        }
    }

    public function getRRIDField($raw_result, $name) {
        $field = $this->_rrid_fields[$name];
        if(!$field) {
            return NULL;
        }
        return $field->esToView($raw_result);
    }

    public function getSpecialField($raw_result, $name) {
        $field = $this->_special_fields[$name];
        if(!$field) {
            return NULL;
        }
        return $field->esToView($raw_result);
    }

    private function getRawResult($index) {
        return $this->_raw_results["hits"]["hits"][$index];
    }

    public function sortField($name) {
        $field = $this->_fields[$this->_fields_map[$name]];
        return $field;
    }

    public function fields() {
        return $this->_fields;
    }

    public function filterField($name) {
        return $this->_fields[$this->_fields_map[$name]];
    }

    public function facetField($name) {
        if($name == "uuid") {
            return $this->_rrid_fields["uuid"];
        }
        return $this->_facets[$this->_facets_map[$name]];
    }

    public static function searchOptionsFromGet($get_vars) {
        $search_options = Array();

        if(!empty($get_vars["filter"])) {
            $search_options["filters"] = Array();
            foreach($get_vars["filter"] as $f) {
                $search_options["filters"][] = explode(":", $f, 2);
            }
        }

        if(!empty($get_vars["facet"])) {
            $search_options["facets"] = Array();
            foreach($get_vars["facet"] as $f) {
                $search_options["facets"][] = explode(":", $f, 2);
            }
        }

        if($get_vars["sort"] && $get_vars["column"]) {
            $search_options["sort"] = Array("direction" => $get_vars["sort"], "column" => $get_vars["column"]);
        }

        return $search_options;
    }

    public function snippet($record) {
        if(is_callable($this->_snippet_func)) {
            return call_user_func($this->_snippet_func, $record);
        }
        return "";
    }

    ## add reference links, self::addReferenceLinks($reference_names) -- Vicky-2019-7-5
    private function addReferenceLinks ($reference_names) {
        $references = explode(",", $reference_names);
        $reference_links = array();
        foreach($references as $reference){
            $val = explode(":", $reference);
            if(count($val) > 1) {
                $value = trim($val[0]).":".$val[1];
                switch(trim($val[0])) {
                    case "DOI":
                        $reference_links[] = "<a target='_blank' href='https://dx.doi.org/".$val[1]."'>".$value."</a>";
                        break;
                    case "PMID":
                        $reference_links[] = "<a target='_blank' href='/dknet/".$val[1]."'>".$value."</a>";
                        break;
                    case "CelloPub":
                        $reference_links[] = "<a target='_blank' href='https://web.expasy.org/cellosaurus/cellopub/".str_replace("-", "", $val[1])."' >".$value."</a>";
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
        return implode(", ", $reference_links);
    }
}

class ElasticRRIDResults implements Iterator {
    private $_raw_results;
    private $_manager;
    private $_position;
    private $_records;
    private $_facets;

    public function __construct(ElasticRRIDManager $manager, $raw_results) {
        $this->_manager=  $manager;
        $this->_raw_results = $raw_results;
        $this->_position = 0;
        $this->_records = Array();
    }

    public function totalCount() {
        return $this->_raw_results["hits"]["total"];
    }

    public function hitCount() {
        return count($this->_raw_results["hits"]["hits"]);
    }

    public function getByIndex($index) {
        if(!$this->_records[$index]) {
            $this->_records[$index] = new ElasticRRIDRecord($this->_manager, $this->_raw_results["hits"]["hits"][$index]);
        }
        return $this->_records[$index];
    }

    public function facets() {
        if(is_null($this->_facets)) {
            $this->_facets = Array();
            foreach($this->_raw_results["aggregations"] as $agg_name => $agg_val) {
                $facet = Array();
                foreach($agg_val["buckets"] as $av) {
                    $facet[] = Array("value" => $av["key"], "count" => $av["doc_count"]);
                }
                $this->_facets[$agg_name] = $facet;
            }
        }
        return $this->_facets;
    }

    /* iterator functions */
    public function rewind() {
        $this->_iterator_position = 0;
    }

    public function current() {
        return $this->getByIndex($this->_iterator_position);
    }

    public function key() {
        return $this->_iterator_position;
    }

    public function next() {
        $this->_iterator_position += 1;
    }

    public function valid() {
        return isset($this->_raw_results["hits"]["hits"][$this->_iterator_position]);
    }
    /* /iterator functions */
}

class ElasticRRIDRecord {
    private $_manager;
    public $_raw_result;

    private $_cache_fields;
    private $_cache_rrid_fields;
    private $_cache_special_fields;

    public function __construct(ElasticRRIDManager $manager, $result) {
        $this->_manager = $manager;
        $this->_raw_result = $result;

        $this->_cache_fields = Array();
        $this->_cache_rrid_fields = Array();
        $this->_cache_special_fields = Array();
    }

    public function getField($name) {
        if(!isset($this->_cache_fields[$name])) {
            $this->_cache_fields[$name] = $this->_manager->getField($this->_raw_result, $name);
        }
        return $this->_cache_fields[$name];
    }

    public function getRRIDField($name) {
        if(!isset($this->_cache_rrid_fields[$name])) {
            $this->_cache_rrid_fields[$name] = $this->_manager->getRRIDField($this->_raw_result, $name);
        }
        return $this->_cache_rrid_fields[$name];
    }

    public function getSpecialField($name) {
        if(!isset($this->_cache_special_fields[$name])) {
            $this->_cache_special_fields[$name] = $this->_manager->getSpecialField($this->_raw_result, $name);
        }
        return $this->_cache_special_fields[$name];
    }

    public function fieldsToArray() {
        $fields = $this->_manager->fields();
        $data = Array();
        foreach($fields as $field) {
            $data[$field->name] = $this->getField($field->name);
        }
        return $data;
    }

    public function snippet() {
        return $this->_manager->snippet($this);
    }

    public function url(Community $community) {
        return $community->fullURL() . "/data/record/" . $this->_manager->getViewID() . "/" . str_replace("RRID:", "", $this->getRRIDField("curie")) . "/resolver";
    }
}

class ElasticRRIDField {
    public $name;
    public $es_filter_paths;
    public $es_facet_path;
    public $es_to_view_function;
    private $_visibilities = Array(
        "table" => true,
        "snippet-filter" => false,
        "single-item" => true,
        "sort" => true,
    );

    public function __construct($name, $es_to_view_function, $es_filter_paths, $es_facet_path = NULL, $visibility_modifiers = NULL) {
        $this->name = $name;
        $this->es_filter_paths = $es_filter_paths;
        ## hide "sort" if no $es_facet_path -- Vicky-2019-2-1
        if(is_null($es_facet_path)) {
          $this->_visibilities["sort"] = false;
        }

        if(!is_null($es_facet_path)) {
            $this->es_facet_path = $es_facet_path;
        } elseif(!empty($this->es_filter_paths)) {
            if ($this->es_filter_paths[0] != "mentions.availability") $this->es_facet_path = $this->es_filter_paths[0] . ".keyword";
        }
        $this->es_to_view_function = $es_to_view_function;

        foreach($visibility_modifiers as $key => $vm) {
            if(isset($this->_visibilities[$key])) {
                $this->_visibilities[$key] = $vm;
            }
        }
    }

    public function esToView($raw_result) {
        if(is_null($raw_result)) {
            return NULL;
        }
        if(is_callable($this->es_to_view_function)) {
            return call_user_func($this->es_to_view_function, $raw_result);
        }
        $result = \helper\derefArray($raw_result["_source"], $this->es_to_view_function);
        if(is_array($result)) {
            return implode(", ", $result);
        } else {
            return $result;
        }
    }

    public function visible($key) {
        if(isset($this->_visibilities[$key])) {
            return $this->_visibilities[$key];
        }
        return false;
    }
}

?>
