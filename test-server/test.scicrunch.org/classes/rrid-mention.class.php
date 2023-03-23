<?php

class RRIDMention extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_mentions";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "uid"                   => self::fieldDef("uid", "i", true),
            "timestamp"             => self::fieldDef("timestamp", "i", false),
            "rrid"                  => self::fieldDef("rrid", "s", false),
            "status"                => self::fieldDef("status", "s", false),
            "bad_rrid"              => self::fieldDef("bad_rrid", "s", false),
            "annotation_id"         => self::fieldDef("annotation_id", "s", true),
            "uri"                   => self::fieldDef("uri", "s", false),
            "pmid"                  => self::fieldDef("pmid", "s", false),
            "doi"                   => self::fieldDef("doi", "s", false),
            "pmc"                   => self::fieldDef("pmc", "s", false),
            "source"                => self::fieldDef("source", "s", false),
            "rrid_type"             => self::fieldDef("rrid_type", "s", false),
            "name"                  => self::fieldDef("name", "s", false),
            "exact"                 => self::fieldDef("exact", "s", false),
            "text_quote_selector"   => self::fieldDef("text_quote_selector", "s", false),
            "hypothesis_user"       => self::fieldDef("hypothesis_user", "s", false),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }
    protected function _get_exact($val) {
        if($this->hypothesis_user !== "scibot") return $val;
        if(\helper\startsWith($val, "RRID")) return $val;
        return "RRID:" . $val;
    }
    protected function _get_text_quote_selector($val) {
        if($this->hypothesis_user !== "scibot") return $val;
        if(strpos($val, "RRID") !== false) return $val;
        $raw_exact = $this->getRaw("exact");
        return str_replace($raw_exact, $this->exact, $val);
    }

    private $_literature_record;
    private static $providers = Array(
        Array(
            "name" => "International Mouse Strain Resource - Jackson Labs",
            "prefixes" => Array("imsr_jax"),
        ),
        Array(
            "name" => "International Mouse Strain Resource - Charles River",
            "prefixes" => Array("imsr_crl"),
        ),
        Array(
            "name" => "International Mouse Strain Resource - EMMA Mouse Repository",
            "prefixes" => Array("imsr_em"),
        ),
        Array(
            "name" => "International Mouse Strain Resource - RIKEN, BioResource Center",
            "prefixes" => Array("imsr_rbrc"),
        ),
        Array(
            "name" => "International Mouse Strain Resource - NCI - Frederick",
            "prefixes" => Array("imsr_ncimr"),
        ),
        Array(
            "name" => "Mutant Mouse Resource and Research Center - UC Davis",
            "prefixes" => Array("mmrrc_ucd"),
        ),
        Array(
            "name" => "Mutant Mouse Resource and Research Center - University of Missouri",
            "prefixes" => Array("mmrrc_mu"),
        ),
        Array(
            "name" => "Mutant Mouse Resource and Research Center - University of North Carolina",
            "prefixes" => Array("mmrrc_unc"),
        ),
        Array(
            "name" => "Mutant Mouse Resource and Research Center",
            "prefixes" => Array("mmrrc"),
        ),
        Array(
            "name" => "Mouse Genome Informatics",
            "prefixes" => Array("mgi"),
        ),
        Array(
            "name" => "Bloomington Drosophila Stock Center",
            "prefixes" => Array("bdsc"),
        ),
        Array(
            "name" => "FlyBase",
            "prefixes" => Array("flybase"),
        ),
        Array(
            "name" => "WormBase",
            "prefixes" => Array("wb"),
        ),
        Array(
            "name" => "Zebrafish Information Network",
            "prefixes" => Array("zfin"),
        ),
        Array(
            "name" => "Kyoto Department of Drosophila Genomics and Genetic Resources",
            "prefixes" => Array("dggr"),
        ),
        Array(
            "name" => "Rat Genome Database",
            "prefixes" => Array("rgd"),
        ),
        Array(
            "name" => "Tetrahymena Stock Center",
            "prefixes" => Array("tsc"),
        ),
        Array(
            "name" => "Zebrafish International Resource Center",
            "prefixes" => Array("zirc"),
        ),
        Array(
            "name" => "National Xenopus Resource",
            "prefixes" => Array("nxr"),
        ),
        Array(
            "name" => "Beta Cell Biology Consortium",
            "prefixes" => Array("bcbc"),
        ),
        Array(
            "name" => "Xiphophorus Genetic Stock Center",
            "prefixes" => Array("xgsc"),
        ),
        Array(
            "name" => "Ambystoma Genetic Stock Center",
            "prefixes" => Array("agsc"),
        ),
        Array(
            "name" => "National Swine Resource and Research Center",
            "prefixes" => Array("nsrrc"),
        ),
        Array(
            "name" => "Case Western Reserve University (School of Medicine)",
            "prefixes" => Array("cwru"),
        ),
        Array(
            "name" => "Cellosaurus Cell Lines",
            "prefixes" => Array("cvcl"),
        ),
        Array(
            "name" => "Antibody Registry",
            "prefixes" => Array("ab"),
        ),
        Array(
            "name" => "Scicrunch Registry",
            "prefixes" => Array("scr", "nlx", "nif", "rid", "omics", "scires"),
        ),
        Array(
            "name" => "Caenorhabditis Genetics Center",
            "prefixes" => Array("cgc"),
        ),
    );

    public static function createNewObj(User $user, $rrid, $status, $bad_rrid, $annotation_id, $uri, $pmid, $doi, $pmc, $source, $rrid_type, $name, $exact, $text_quote_selector, $hypothesis_user) {
        if($user->id) {
            $uid = $user->id;
        } else {
            $uid = 0;
        }

        return self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "timestamp" => time(),
            "rrid" => $rrid,
            "status" => $status,
            "bad_rrid" => $bad_rrid,
            "annotation_id" => $annotation_id,
            "uri" => $uri,
            "pmid" => $pmid,
            "doi" => $doi,
            "pmc" => $pmc,
            "source" => $source,
            "rrid_type" => $rrid_type,
            "name" => $name,
            "exact" => $exact,
            "text_quote_selector" => $text_quote_selector,
            "hypothesis_user" => $hypothesis_user,
        ));
    }

    public static function deleteObj($obj) {
        parent::deleteObj($obj);
    }

    public function arrayForm() {
        $return_array = Array(
            "timestamp" => $this->timestamp,
            "rrid" => $this->rrid,
            "status" => $this->status,
            "bad_rrid" => $this->bad_rrid,
            "annotation_id" => $this->annotation_id,
            "uri" => $this->uri,
            "pmid" => $this->pmid,
            "doi" => $this->doi,
            "pmc" => $this->pmc,
            "source" => $this->source,
            "rrid_type" => $this->rrid_type,
            "name" => $this->name,
            "exact" => $this->exact,
            "text_quote_selector" => $this->text_quote_selector,
            "literature_record" => NULL,
            "provider" => $this->provider(),
        );

        if(!is_null($this->literatureRecord())) {
            $return_array["literature_record"] = $this->literatureRecord()->arrayForm();
        }

        return $return_array;
    }

    public function literatureRecord() {
        $pmid_prefix = "PMID:";
        if(is_null($this->_literature_record)) {
            $pmid = $this->pmid;
            if(\helper\startsWith($this->pmid, $pmid_prefix)) {
                $pmid = substr($this->pmid, strlen($pmid_prefix));
            }
            $this->_literature_record = RRIDMentionsLiteratureRecord::loadBy(Array("pmid"), Array($pmid));
        }
        return $this->_literature_record;
    }

    public function grantInfos() {
        if(!is_null($this->literatureRecord())) {
            return $this->literatureRecord()->grantInfos();
        }
        return Array();
    }

    public function provider() {
        $mod_rrid = str_replace("rrid:", "", strtolower($this->rrid));
        foreach(self::$providers as $prov) {
            foreach($prov["prefixes"] as $prefix) {
                if(\helper\startsWith($mod_rrid, $prefix)) {
                    return $prov["name"];
                }
            }
        }
        return "";
    }

    public static function getByRRID($rrid, $count = MAXINT, $offset = 0) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select("rrid_mentions_view2", Array("*"), "sii", Array("%" . $rrid, $offset, $count), "where rrid like ? and title != '' group by pmid limit ?,?");
        $cxn->close();

        $return = Array();
        foreach($rows as $row) {
            $rrid_mention = RRIDMention::loadBy(Array("id"), Array($row["rrid_mention_id"]));
            if(!is_null($rrid_mention)) {
                $return[] = $rrid_mention;
            }
        }

        return $return;
    }

    public static function getCountByRRID($rrid) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("rrid_mentions_view2", Array("count(distinct pmid) as count"), "s", Array("%" . $rrid), "where rrid like ? and title != ''");
        $cxn->close();

        return $count[0]["count"];
    }

    public static function getCountByYearByRRID($rrid) {
        $cxn = new Connection();
        $cxn->connect();
        $counts = $cxn->select("rrid_mentions_view2", Array("year", "count(distinct pmid) as count"), "s", Array("%" . $rrid), "where rrid like ? and year != '' group by year");
        $cxn->close();

        return $counts;
    }

    public static function searchRRIDs($query, $facets, $limit, $offset) {
        $table = "rrid_mentions_view2";

        $select_vars = self::rridSelectVars($query, $facets);
        $query_types = $select_vars["query-types"];
        $query_vars = $select_vars["query-vars"];
        $where_string = $select_vars["where-string"];

        $count_query_types = $query_types;
        $count_query_vars = $query_vars;
        $count_where_string = $where_string;
        $query_types .= "ii";
        $query_vars[] = $offset;
        $query_vars[] = $limit;
        $where_string .= " limit ?,?";


        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select($table, Array("rrid_mention_id"), $query_types, $query_vars, $where_string);
        $count = $cxn->select($table, Array("count(*)"), $count_query_types, $count_query_vars, $count_where_string);
        $facets_journal = $cxn->select($table, Array("journal as facet", "count(*) as count"), $count_query_types, $count_query_vars, $count_where_string . " group by journal");
        $facets_year = $cxn->select($table, Array("year as facet", "count(*) as count"), $count_query_types, $count_query_vars, $count_where_string . " group by year");
        $facets_provider = $cxn->select($table, Array("provider as facet", "count(*) as count"), $count_query_types, $count_query_vars, $count_where_string . " group by provider");
        $cxn->close();

        $mentions = Array();
        foreach($rows as $row) {
            $mentions[] = RRIDMention::loadBy(Array("id"), Array($row["rrid_mention_id"]));
        }

        return Array(
            "rrid-mentions" => $mentions,
            "count" => $count[0]["count(*)"],
            "facets" => Array(
                "journal" => $facets_journal,
                "year" => $facets_year,
                "provider" => $facets_provider,
            ),
        );
    }

    private static function rridSelectVars($query, $facets) {
        $query_types = "";
        $query_vars = Array();
        $where_string = "";

        if($query) {
            $query_types .= "s";
            $query_vars [] = $query;
            $where_string .= "where MATCH(pmid, rrid_name, rrid, journal, title, year) AGAINST(? IN BOOLEAN MODE)";
        }

        $facets_where_array = Array();
        foreach($facets as $facet => $vals) {
            foreach($vals as $val) {
                switch($facet) {
                    case "year":
                        $facets_where_array[] = "year=?";
                        $query_vars[] = $val;
                        $query_types .= "s";
                        break;
                    case "funder":
                        $facets_where_array[] = "agencies like ?";
                        $query_vars[] = "%FA:" . $val . "%";
                        $query_types .= "s";
                        break;
                    case "journal":
                        $facets_where_array[] = "journal=?";
                        $query_vars[] = $val;
                        $query_types .= "s";
                        break;
                    case "provider":
                        $facets_where_array[] = "provider=?";
                        $query_vars[] = $val;
                        $query_types .= "s";
                        break;
                    default:
                        continue;
                }
            }
        }
        if(!empty($facets_where_array)) {
            if($where_string) {
                $where_string .= " AND";
            } else {
                $where_string = "where";
            }
            $where_string .= " (" . implode(" AND ", $facets_where_array) . ")";
        }

        return Array(
            "query-types" => $query_types,
            "query-vars" => $query_vars,
            "where-string" => $where_string,
        );
    }

    public static function getMentionedRRIDs($pmid) {
        $full_pmid = "PMID:" . $pmid;
        $cxn = new Connection();
        $cxn->connect();
        $mentions = $cxn->select("rrid_mentions", Array("*"), "s", Array($full_pmid), "where pmid = ? and rrid != '' and rrid is not null and (status = 'RRIDCUR:Validated' or status = 'RRIDCUR:Missing' or status = 'RRIDCUR:Duplicate' or status = '' or status = 'RRIDCUR:Unrecognized' or status = 'RRIDCUR:Unresolved') group by rrid");
        $mentions = $cxn->select("resources a", Array("a.id as primary_id", "b.*"), "s", Array($full_pmid), "right join " .
        "(select *, trim(leading 'RRID:' from rrid) as trimmed_rrid from rrid_mentions where pmid = ? and rrid is not null " . 
        "and rrid <> '' and (status = 'RRIDCUR:Validated' or status = 'RRIDCUR:Missing' or status = 'RRIDCUR:Duplicate' or status = '' or status = '' or status = 'RRIDCUR:Unrecognized' or status = 'RRIDCUR:Unresolved') group by rrid) b " .
        "on ((a.original_id = b.trimmed_rrid or a.rid = b.trimmed_rrid) and b.rrid_type = 'tool')");
        $cxn->close();
        $fmt_mentions = Array("rrids" => Array(), "data" => Array());
        foreach($mentions as $mention) {
            $rrid = $mention["rrid"];
            if($rrid == "") continue;
            if(\helper\startsWith($rrid, "RRID:") && !in_array($rrid, $fmt_mentions["rrids"])) {
                if(!$mention["name"]) $mention["name"] = $mention["rrid"];
                $fmt_mentions["rrids"][] = $mention;
            } elseif(!in_array($rrid, $fmt_mentions["data"])) {
                $fmt_mentions["data"][] = $mention;
            }
        }
        return $fmt_mentions;
    }

}
RRIDMention::init();

?>
