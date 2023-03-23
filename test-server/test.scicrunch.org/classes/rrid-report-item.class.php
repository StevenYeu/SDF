<?php

class RRIDReportItem extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_report_item";
    protected static $_primary_key_field = "id";

    public static function init() {
        $verify_genetic_method_pub = Array(
            "name" => "Genetic method publication",
            "description" => "If there is a publication that describes the validation technique was successfully used by your group, please add the publication information here by filling in the PMID (PubMed Identifier), for example, \"PMID:12345\".",
            "type" => "literature",
            "required" => false,
            "group" => "validation-select",
            "group-choice" => "genetic-method",
        );

        $verify_genetic_method_organism = Array(
            "name" => "Genetic method organism",
            "description" => "Information about the organism used to verify the genetic method",
            "type" => "text",
            "required" => false,
            "group" => "validation-select",
            "group-choice" => "genetic-method",
        );

        $verify_endogenous_expression_pub = Array(
            "name" => "Endogenous expression pubication",
            "description" => "If there is a publication that describes the validation technique was successfully used by your group, please add the publication information here by filling in the PMID (PubMed Identifier), for example, \"PMID:12345\".",
            "type" => "literature",
            "required" => false,
            "group" => "validation-select",
            "group-choice" => "endogenous-expression",
        );

        $verify_orthogonal_methods_pub = Array(
            "name" => "Orthogonal methods publication",
            "description" => "If there is a publication that describes the validation technique was successfully used by your group, please add the publication information here by filling in the PMID (PubMed Identifier), for example, \"PMID:12345\".",
            "type" => "literature",
            "required" => false,
            "group" => "validation-select",
            "group-choice" => "orthogonal-methods",
        );

        $verify_mass_spec_ip_pub = Array(
            "name" => "Mass spectrometry and immunoprecipitation publication",
            "description" => "If there is a publication that describes the validation technique was successfully used by your group, please add the publication information here by filling in the PMID (PubMed Identifier), for example, \"PMID:12345\".",
            "type" => "literature",
            "required" => false,
            "group" => "validation-select",
            "group-choice" => "mass-spec-ip",
        );

        $verify_independent_antibodies_pub = Array(
            "name" => "Independent antibodies publication",
            "description" => "If there is a publication that describes the validation technique was successfully used by your group, please add the publication information here by filling in the PMID (PubMed Identifier), for example, \"PMID:12345\".",
            "type" => "literature",
            "required" => false,
            "group" => "validation-select",
            "group-choice" => "independent-antibodies",
        );

        $performed_core_university = Array(
            "name" => "University",
            "description" => "",
            "type" => "text",
            "min" => 0,
            "max" => 255,
            "required" => true,
            "group" => "performed-select",
            "group-choice" => "core",
        );

        $performed_core_director = Array(
            "name" => "Core Director",
            "description" => "Name of the director of the core where this method was completed.",
            "type" => "text",
            "min" => 0,
            "max" => 255,
            "required" => true,
            "group" => "performed-select",
            "group-choice" => "core",
        );

        $performed_company = Array(
            "name" => "Company",
            "description" => "Name of the company where this method was completed.",
            "type" => "text",
            "min" => 0,
            "max" => 255,
            "required" => true,
            "group" => "performed-select",
            "group-choice" => "company",
        );

        $performed_group = Array(
            "name" => "Research group name",
            "description" => "The research group where this method was completed.",
            "type" => "text",
            "min" => 0,
            "max" => 255,
            "required" => true,
            "group" => "performed-select",
            "group-choice" => "research-group",
        );

        $info_genetic_method = Array(
            "name" => "Information",
            "description" => self::reportTexts("antibody-genetic-method"),
            "type" => "information",
            "group" => "validation-select",
            "group-choice" => "genetic-method",
        );

        $info_endogenous_expression = Array(
            "name" => "Information",
            "description" => self::reportTexts("antibody-endogenous-expression"),
            "type" => "information",
            "group" => "validation-select",
            "group-choice" => "endogenous-expression",
        );

        $info_orthgonal_methods = Array(
            "name" => "Information",
            "description" => self::reportTexts("antibody-orthogonal-methods"),
            "type" => "information",
            "group" => "validation-select",
            "group-choice" => "orthogonal-methods",
        );

        $info_mass_spec_ip = Array(
            "name" => "Information",
            "description" => self::reportTexts("antibody-mass-spec-ip"),
            "type" => "information",
            "group" => "validation-select",
            "group-choice" => "mass-spec-ip",
        );

        $info_independent_antibodies = Array(
            "name" => "Information",
            "description" => self::reportTexts("antibody-independent-antibodies"),
            "type" => "information",
            "group" => "validation-select",
            "group-choice" => "independent-antibodies",
        );

        self::$allowed_types = Array(
            "antibody" => Array(
                "viewid" => "nif-0000-07730-1",
                "user-data" => NULL,
                "subtypes" => Array(
                    "Immunoprecipitation" => Array(
                        "user-data" => Array(
                            "validation-select" => Array(
                                "name" => "Choose a validation method",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "genetic-method" => "Genetic method (best option)",
                                    "mass-spec-ip" => "Mass spectrometry and immunoprecipitation",
                                    "independent-antibodies" => "Independent antibodies"
                                ),
                            ),
                            "genetic-method-info" => $info_genetic_method,
                            "genetic-method-pub" => $verify_genetic_method_pub,
                            "genetic-method-organism" => $verify_genetic_method_organism,
                            "mass-spec-ip-info" => $info_mass_spec_ip,
                            "mass-spec-ip-pub" => $verify_mass_spec_ip_pub,
                            "independent-antibodies-info" => $info_independent_antibodies,
                            "independent-antibodies-pub" => $verify_independent_antibodies_pub,
                        ),
                    ),
                    "Histology and immunohistochemistry" => Array(
                        "user-data" => Array(
                            "validation-select" => Array(
                                "name" => "Choose a validation method",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "genetic-method" => "Genetic method (best option)",
                                    "endogenous-expression" => "Endogenous expression",
                                    "orthogonal-methods" => "Orthogonal methods",
                                    "independent-antibodies" => "Independent antibodies",
                                ),
                            ),
                            "genetic-method-info" => $info_genetic_method,
                            "genetic-method-pub" => $verify_genetic_method_pub,
                            "genetic-method-organism" => $verify_genetic_method_organism,
                            "endogenous-expression-info" => $info_endogenous_expression,
                            "endogenous-expression-pub" => $verify_endogenous_expression_pub,
                            "orthogonal-methods-info" => $info_orthogonal_methods,
                            "orthogonal-methods-pub" => $verify_orthogonal_methods_pub,
                            "independent-antibodies-info" => $info_independent_antibodies,
                            "independent-antibodies-pub" => $verify_independent_antibodies_pub,
                            "performed-select" => Array(
                                "name" => "Where was the histology and immunohistochemistry completed",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "core" => "University core",
                                    "company" => "Company",
                                    "research-group" => "Research group",
                                ),
                            ),
                            "university" => $performed_core_university,
                            "core-director" => $performed_core_director,
                            "company" => $performed_company,
                            "research-group" => $performed_group,
                        ),
                    ),
                    "Western blotting" => Array(
                        "user-data" => Array(
                            "validation-select" => Array(
                                "name" => "Choose a validation method",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "genetic-method" => "Genetic method",
                                    "endogenous-expression" => "Endogenous expression",
                                    "orthogonal-methods" => "Orthogonal methods",
                                    "independent-antibodies" => "Independent antibodies",
                                ),
                            ),
                            "genetic-method-info" => $info_genetic_method,
                            "genetic-method-pub" => $verify_genetic_method_pub,
                            "genetic-method-organism" => $verify_genetic_method_organism,
                            "endogenous-expression-info" => $info_endogenous_expression,
                            "endogenous-expression-pub" => $verify_endogenous_expression_pub,
                            "orthogonal-methods-info" => $info_orthogonal_methods,
                            "orthogonal-methods-pub" => $verify_orthogonal_methods_pub,
                            "independent-antibodies-info" => $info_independent_antibodies,
                            "independent-antibodies-pub" => $verify_independent_antibodies_pub,
                        ),
                    ),
                    "Flow cytometry" => Array(
                        "user-data" => Array(
                            "validation-select" => Array(
                                "name" => "Choose a validation method",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "genetic-method" => "Genetic method",
                                    "endogenous-expression" => "Endogenous expression",
                                    "orthogonal-methods" => "Orthogonal methods",
                                    "independent-antibodies" => "Independent antibodies",
                                ),
                            ),
                            "genetic-method-info" => $info_genetic_method,
                            "genetic-method-pub" => $verify_genetic_method_pub,
                            "genetic-method-organism" => $verify_genetic_method_organism,
                            "endogenous-expression-info" => $info_endogenous_expression,
                            "endogenous-expression-pub" => $verify_endogenous_expression_pub,
                            "orthogonal-methods-info" => $info_orthogonal_methods,
                            "orthogonal-methods-pub" => $verify_orthogonal_methods_pub,
                            "independent-antibodies-info" => $info_independent_antibodies,
                            "independent-antibodies-pub" => $verify_independent_antibodies_pub,
                            "performed-select" => Array(
                                "name" => "Where was the flow cytometry completed",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "core" => "University core",
                                    "research-group" => "Research group",
                                ),
                            ),
                            "university" => $performed_core_university,
                            "core-director" => $performed_core_director,
                            "research-group" => $performed_group,
                        ),
                    ),
                    "Sandwich assays" => Array(
                        "user-data" => Array(
                            "validation-select" => Array(
                                "name" => "Choose a validation method",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "genetic-method" => "Genetic method",
                                    "orthogonal-methods" => "Orthogonal methods",
                                    "independent-antibodies" => "Independent antibodies",
                                ),
                            ),
                            "genetic-method-info" => $info_genetic_method,
                            "genetic-method-pub" => $verify_genetic_method_pub,
                            "genetic-method-organism" => $verify_genetic_method_organism,
                            "orthogonal-methods-info" => $info_orthogonal_methods,
                            "orthogonal-methods-pub" => $verify_orthogonal_methods_pub,
                            "independent-antibodies-info" => $info_independent_antibodies,
                            "independent-antibodies-pub" => $verify_independent_antibodies_pub,
                        ),
                    ),
                    "Reverse phase protein arrays" => Array(
                        "user-data" => Array(
                            "validation-select" => Array(
                                "name" => "Choose a validation method",
                                "type" => "group-select",
                                "group-choices" => Array(
                                    "genetic-method" => "Genetic method",
                                    "endogenous-expression" => "Endogenous expression",
                                    "orthogonal-methods" => "Orthogonal methods",
                                    "independent-antibodies" => "Independent antibodies",
                                ),
                            ),
                            "genetic-method-info" => $info_genetic_method,
                            "genetic-method-pub" => $verify_genetic_method_pub,
                            "genetic-method-organism" => $verify_genetic_method_organism,
                            "endogenous-expression-info" => $info_endogenous_expression,
                            "endogenous-expression-pub" => $verify_endogenous_expression_pub,
                            "orthogonal-methods-info" => $info_orthogonal_methods,
                            "orthogonal-methods-pub" => $verify_orthogonal_methods_pub,
                            "independent-antibodies-info" => $info_independent_antibodies,
                            "independent-antibodies-pub" => $verify_independent_antibodies_pub,
                        ),
                    ),
                ),
                "rrid-view-col" => "Antibody ID",
                "rrid-name-col" => "Antibody Name",
                "pretty-type-name" => "Antibody",
            ),
            "cellline" => Array(
                "viewid" => "SCR_013869-1",
                "user-data" => Array(
                    "validation-select" => Array(
                        "name" => "Choose a validation method",
                        "type" => "group-select",
                        "group-choices" => Array("publication" => "Publication", "text" => "User provided"),
                    ),
                    "cell-line-pub" => Array(
                        "name" => "Cell line publication",
                        "description" => "If there is a publication that describes the validation technique was successfully used by your group, please add the publication information here by filling in the PMID (PubMed Identifier), for example, \"PMID:12345\".",
                        "type" => "literature",
                        "required" => true,
                        "group" => "validation-select",
                        "group-choice" => "publication",
                    ),
                    "cell-line-validation-medium" => Array(
                        "name" => "Growth medium",
                        "description" => "The growth medium used, including additives",
                        "type" => "text",
                        "required" => true,
                        "group" => "validation-select",
                        "group-choice" => "text",
                    ),
                    "cell-line-validation-growth" => Array(
                        "name" => "Growth requirements",
                        "description" => "Any additional growth requirements, including special substrates and gas mixtures",
                        "type" => "text",
                        "required" => true,
                        "group" => "validation-select",
                        "group-choice" => "text",
                    ),
                    "cell-line-validation-passage" => Array(
                        "name" => "Passage number",
                        "description" => "The passage number or population doubling level (PDL) used for experimental work",
                        "type" => "text",
                        "required" => true,
                        "group" => "validation-select",
                        "group-choice" => "text",
                    ),
                ),
                "subtypes" => NULL,
                "rrid-view-col" => "ID",
                "rrid-name-col" => "Name",
                "pretty-type-name" => "Cell line",
            ),
        );

        foreach(array_keys(self::$allowed_types) as $type) {
            $search_manager = ElasticRRIDManager::managerByViewID(self::$allowed_types[$type]["viewid"]);
            if(is_null($search_manager)) {
                continue;
            }
            self::$allowed_types[$type]["rrid-data-cols"] = array_map(function($f) {
                return $f->name;
            }, $search_manager->fields());
        }

        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "rrid_report_id"    => self::fieldDef("rrid_report_id", "i", true),
            "timestamp"         => self::fieldDef("timestamp", "i", true),
            "type"              => self::fieldDef("type", "s", true, Array("allowed_values" => array_keys(self::$allowed_types))),
            "data"              => self::fieldDef("data", "s", false),
            "rrid"              => self::fieldDef("rrid", "s", false),
            "uuid"              => self::fieldDef("uuid", "s", false),
            "updated_flag"      => self::fieldDef("updated_flag", "i", false),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }

    public static $allowed_types = Array();

    private static $_rrid_viewids = NULL;
    private $_data = NULL;
    private $_subtypes = NULL;
    private $_report;
    private $_user_data;

    public static function createNewObj(RRIDReport $rrid_report, $type, $subtype = NULL, $rrid = NULL, $v_uuid = NULL, $uid = NULL) {
        $rrid_report_id = $rrid_report->id;
        if(!$type) return NULL;
        $updated_flag = 0;
        $timestamp = time();
        $rrid = strip_tags($rrid);
                
        $record = self::retrieveData($type, $rrid, $uid);   ## input uid value -- Vicky-2019-2-21
        ## fill uuid value -- Vicky-2019-2-15
        if ($v_uuid) $uuid = $v_uuid;
        else $uuid = $record->getRRIDField("uuid");
        if(is_null($record)) return NULL;
        $data = $record->fieldsToArray();
        if(is_null($data)) return NULL;
        $jdata = json_encode($data);

        $obj = self::insertObj(Array(
            "id" => NULL,
            "rrid_report_id" => $rrid_report_id,
            "timestamp" => $timestamp,
            "type" => $type,
            "data" => $jdata,
            "rrid" => $rrid,
            "uuid" => $uuid,
            "updated_flag" => $updated_flag,
        ));
        if(!is_null($obj) && !is_null($subtype)) $obj->subtypeCreate($subtype);
        return $obj;
    }

    public static function deleteObj($obj, User $user = NULL) {
        $user_data = RRIDReportItemUserData::loadArrayBy(Array("rrid_report_item_id"), Array($obj->id));
        foreach($user_data as $ud) {
            RRIDReportItemUserData::deleteObj($ud);
        }

        $subtypes = RRIDReportItemSubtype::loadArrayBy(Array("rrid_report_item_id"), Array($obj->id));
        foreach($subtypes as $st) {
            RRIDReportItemSubtype::deleteObj($st);
        }

        $obj->saveHistory("delete", $user);
        parent::deleteObj($obj);
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm() {
        $subtypes = $this->subtypes();
        $subtypes_array = Array();
        foreach($subtypes as $st) {
            $subtypes_array[] = $st->arrayForm();
        }
        return Array(
            "id" => $this->id,
            "rrid_report_id" => $this->rrid_report_id,
            "timestamp" => $this->timestamp,
            "type" => $this->type,
            "subtypes" => $subtypes_array,
            "data" => $this->data,
            "rrid" => $this->rrid,
            "uuid" => $this->uuid,
            "updated_flag" => $this->updated_flag,
            "needs_data" => $this->needsData(),
        );
    }

    public function subtypeCreate($subtype) {
        RRIDReportItemSubtype::createNewObj($this, $subtype);
    }

    /**
     * rridDataFromViewRow
     * returns data for an rrid record (from data services) in a formatted way
     *
     * @param string viewid
     * @param Array assoc array of all the data in the record from data services
     * @return Array assoc array of the reformatted record
     */
    public static function rridDataFromViewRow($viewid, $row) {
        $rrid = null;
        $type = null;
        $name = null;
        $subtypes = null;

        if(is_null(self::$_rrid_viewids)) {
            self::refreshRRIDViewIDs();
        }
        if(isset(self::$_rrid_viewids[$viewid])) {
            $type = self::$_rrid_viewids[$viewid];
            $rrid = strip_tags($row[self::$allowed_types[$type]["rrid-view-col"]]);
            $name = strip_tags($row[self::$allowed_types[$type]["rrid-name-col"]]);
            if(is_null(self::$allowed_types[$type]["subtypes"])) {
                $subtypes = "";
            } else {
                $subtypes = implode(",", array_keys(self::$allowed_types[$type]["subtypes"]));
            }
        }

        return Array(
            "rrid" => $rrid,
            "type" => $type,
            "name" => $name,
            "subtypes" => $subtypes,
        );
    }

    /**
     * isRRIDReportView
     * check if the nifid is one used in RRID reports
     *
     * @param string report id
     * @return bool
     */
    public static function isRRIDReportView($viewid) {
        if(is_null(self::$_rrid_viewids)) {
            self::refreshRRIDViewIDs();
        }
        return isset(self::$_rrid_viewids[$viewid]);
    }

    private static function refreshRRIDViewIDs() {
        self::$_rrid_viewids = Array();
        foreach(self::$allowed_types as $type => $at) {
            self::$_rrid_viewids[$at["viewid"]] = $type;
        }
    }

    /**
     * getRecord
     * get the data from the nif services
     *
     * @param string the type of the field (ie antibody)
     * @param string the rrid
     * @return ElasticRRIDRecord or null, the data from nif services
     */
    private static function retrieveData($type, $rrid, $uid) {
        $viewid = self::$allowed_types[$type]["viewid"];
        if(is_null($viewid)) return NULL;
        $search_manager = ElasticRRIDManager::managerByViewID(self::$allowed_types[$type]["viewid"]);
        if(is_null($search_manager)) return NULL;
        $results = $search_manager->searchRRID($rrid);
        //return $results->getByIndex(0);
        if ($uid == NULL) return $results->getByIndex(0);
        else {
        //   for ($i = 0; $i < count($results); $i++) {
        //     if ($results[$i]->getRRIDField("id") == $uid) return $results->getByIndex(0);;
        //   }
          foreach($results as $result){
            if ($result->getRRIDField("id") == $uid) return $result;
          }
        }
    }

    /**
     * checkInAnyReport
     * see if this uuid is used in any report owned by the user
     * used for seeing if the record should be marked as used when user is browsing search results
     *
     * @param string the uuid
     * @param User
     * @return bool
     */
    public static function checkInAnyReport($uuid, $user) {
        if(!$user) return false;
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(
            "rrid_report a inner join rrid_report_item b on a.id=b.rrid_report_id",
            Array("count(*)"),
            "is",
            Array($user->id, $uuid),
            "where a.uid = ? and b.uuid = ?"
        );
        $cxn->close();
        return $count[0]["count(*)"] > 0;
    }

    /**
     * getData
     * get the data from a specific column
     *
     * @param string the name of the column
     * @param bool is this the real column name (from services), if not the name should be (name|ID)
     * @return string
     */
    public function getData($col_name, $real_col_name) {
        if(is_null($this->_data)) $this->refreshData();
        if($real_col_name) return $this->_data[$col_name];

        if($col_name == "name") {
            return strip_tags($this->_data[self::$allowed_types[$this->type]["rrid-name-col"]]);
        } elseif($col_name == "ID") {
            return strip_tags($this->_data[self::$allowed_types[$this->type]["rrid-view-col"]]);
        }
        return NULL;
    }

    /**
     * refreshData
     * re-json decodes the data
     *
     */
    public function refreshData() {
        $this->_data = NULL;
        $this->_data = json_decode($this->data, true);
    }

    public function subtypeInfo($subtype) {
        if(isset(self::$allowed_types[$this->type]["subtypes"][$subtype])) {
            return self::$allowed_types[$this->type]["subtypes"][$subtype];
        }
        return NULL;
    }

    public function userDataTypes() {
        $user_data = $this->userData();
        $info = self::$allowed_types[$this->type]["user-data"];
        if(!$info) return NULL;
        foreach($user_data as $ud) {
            if(isset($info[$ud->name])) {
                $info[$ud->name]["existing"] = $ud;
            }
        }
        return $info;
    }

    public function subtypes() {
        if(is_null($this->_subtypes)) {
            $this->_subtypes = RRIDReportItemSubtype::loadArrayBy(Array("rrid_report_item_id"), Array($this->id));
        }
        return $this->_subtypes;
    }

    public function subtype($name) {
        foreach($this->subtypes() as $st) {
            if($st->subtype == $name) return $st;
        }
        return NULL;
    }

    public function subtypeDelete($subtype, $delete_if_empty = true) {
        if(!is_null($subtype)) {
            $subtype_obj = RRIDReportItemSubtype::loadBy(Array("rrid_report_item_id", "subtype"), Array($this->id, $subtype));
            if(!is_null($subtype_obj)) {
                RRIDReportItemSubtype::deleteObj($subtype_obj);
            }
        }
    }

    public function report() {
        if(is_null($this->_report)) {
            $this->_report = RRIDReport::loadBy(Array("id"), Array($this->rrid_report_id));
        }
        return $this->_report;
    }

    public function getUserData($name) {
        foreach($this->userData() as $ud) {
            if($ud->name == $name) return $ud;
        }
        return NULL;
    }

    public function setUserData($name, $value) {
        $existing = RRIDReportItemUserData::loadBy(Array("name", "rrid_report_item_id"), Array($name, $this->id));
        if(!is_null($existing)) {
            $existing->data = $value;
            $existing->updateDB();
        } else {
            RRIDReportItemUserData::createNewObj($this, $name, $value);
        }
    }

    public function userData() {
        if(is_null($this->_user_data)) {
            $this->_user_data = RRIDReportItemUserData::loadArrayBy(Array("rrid_report_item_id"), Array($this->id));
        }
        return $this->_user_data;
    }

    /**
     * checkForNewData
     * get data from services and compare to existing data and updated_flag if needed
     *
     * @return bool true if there's new data
     */
    public function checkForNewData() {
        $data = self::retrieveData($this->type, $this->uuid);
        if(is_null($data)) return false;
        $this->refreshData();
        if(!($this->_data == $data)) {
            $this->data = json_encode($data);
            $this->updated_flag = 1;
            $this->updateDB();
            $this->refreshData();
            return true;
        }
        return false;
    }

    /**
     * needsData
     * returns true if this type needs more data that is not yet filled out by the user
     *
     * @return bool
     */
    public function needsData($type, $subtype_name = false) {
        if(!isset($type)) {
            $type = self::$allowed_types[$this->type];
        }

        $group_types = Array();
        foreach($type["user-data"] as $key => $ud) {
            if($ud["type"] == "group-select") {
                $existing_user_data = $this->getUserData($key);
                if(is_null($existing_user_data)) {
                    $chosen_type = array_keys($ud["group-choices"])[0];
                } else {
                    $chosen_type = $existing_user_data->data;
                }
                $group_types[$key] = $chosen_type;
            }
        }

        $required_data = Array();
        foreach($type["user-data"] as $key => $ud) {
            if($ud["type"] == "group-select") {
                continue;
            }
            if($ud["required"]) {
                if(!$ud["group"]) {
                    $required_data[] = $key;
                } elseif($ud["group"] && isset($group_types[$ud["group"]]) && $group_types[$ud["group"]] == $ud["group-choice"]) {
                    $required_data[] = $key;
                }
            }
        }

        foreach($required_data as $rd) {
            $user_data = NULL;
            if($subtype_name) {
                $subtype = $this->subtype($subtype_name);
                if(!is_null($subtype)) {
                    $user_data = $subtype->getUserData($rd);
                }
            } else {
                $user_data = $this->getUserData($rd);
            }
            if(is_null($user_data) || $user_data->data == "") {
                return true;
            }
        }

        if(!is_null($type["subtypes"])) {
            if(count($this->subtypes()) == 0) {
                return true;
            }
            foreach($type["subtypes"] as $stk => $st) {
                if(!is_null($this->subtype($stk))) {
                    if($this->needsData($st, $stk)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function reportTexts($type) {
        switch($type) {
            case "antibody-genetic-method":
                return "Complete or significant reduction in antibody signal after disruption or knockdown of the targeted gene in the organism.";
            case "antibody-endogenous-expression":
                return "Target binding is confirmed by colocalization, often combined with the use of tag-specific antibody to identify recombinant protein versus endogenous protein target.";
            case "antibody-mass-spec-ip":
                return "Target binding will be confirmed with mass spectrometry, but this does not normally give conclusive information about cross-reactivity, therefore we will confirm cross reactivity with Western Blot.";
            case "antibody-independent-antibodies":
                return "Correlation between two antibodies to the same target with nonoverlapping epitopes across several samples preferentially with differential expression of the target protein. We will immunopercipitate with the antibodies listed above and then use the second antibody in a IP-western. We have yet to identify the second antibody that will be used for this purpose, but we will make sure that the antibody is distinct from the first antibody by verifying that the epitope that the second antibody recognizes is different and if this is not possible then we will ask the antibody vendor about the original manufacturer of both antibodies.";
            case "antibody-orthogonal-methods":
                return "Correlation of protein abundance using an antibody-independent method across several samples with differential expression of the target protein.";
            default:
                return "";
        }
    }
}
RRIDReportItem::init();

?>
