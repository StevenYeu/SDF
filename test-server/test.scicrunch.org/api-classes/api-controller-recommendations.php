<?php
use Symfony\Component\HttpFoundation\Request;
require_once __DIR__."/../classes/connection.class.php";

class AssocRule {
    public $ruleId;
    public $confidence;
    public $lhs;
    public $rhs;

    public function __construct($ruleId, $confidence) {
        $this->ruleId = $ruleId;
        $this->confidence = $confidence;
        $this->lhs = array();
        $this->rhs = array();
    }

    public function addLhs($rid) {
        $this->lhs[] = $rid;
    }

    public function addRhs($rid) {
        $this->rhs[] = $rid;
    }

    public function isValidRecommendation($rids) {
        // Make sure everything on the lhs is in the provided ids
        for($i = 0; $i < sizeof($this->lhs); $i++ ) {
            $found = false;
            for ($j = 0; $j < sizeof($rids); $j++) {
                if ($rids[$j]["id"] === $this->lhs[$i]) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
         }

         // Make sure there's at least 1 value on the rhs that's not visited=         
         for($i = 0; $i < sizeof($this->rhs); $i++ ) {
             for ($j = 0; $j < sizeof($rids); $j++) {
                 if ($rids[$j]["visited"] && $rids[$j]["id"] === $this->rhs[$i]) {
                     continue 2;
                 }
             }
             // current element on the rhs is valid for a recommendation
             return true;
         }
        return false; 
    }
}

$app->post($AP."/recommendations", function (Request $request) use($app) {
    $ids = $request->request->get("ids");
    if (!$ids) {
        return appReturn($app, APIReturnData::quick400("No ids provided"), false, false);
    }
    $validIds = true;
    foreach ($ids as &$idObj) {
        $validIdObj = is_array($idObj) && is_numeric($idObj["id"]);
        $validIds &= $validIdObj;
        if ($idObj["visited"] === 'true') {
            $idObj["visited"] = true;
        } else if ($idObj["visited"] === 'false') {
            $idObj["visited"] = false;
        } else {
            $validIds = false;
        }
        if ($validIdObj) {
            $idObj["id"] = intval($idObj["id"]);
        }
    }

    if (!$validIds) {
        return appReturn($app, APIReturnData::quick400("Invalid ids"), false, false);
    }

    $rawIds = array_map(function($idObj) {
        return $idObj["id"];
    } , $ids);

    $connection = new Connection();
    $connection->connect();
    $results = $connection->select("resource_association_rules", array('*'), str_repeat("i", sizeof($rawIds)), $rawIds,
    "where ruleid in ( select ruleid from resource_association_rules where rid in (" . implode(',', array_fill(0, sizeof($rawIds), '?')) . ") and lhs = 1)");
    $rules = array();
    foreach($results as $result) {
        $ruleId = $result["ruleid"];
        if (!isset($rules[$ruleId])) {
            $rules[$ruleId] = new AssocRule($ruleId, $result["confidence"]);
        }
        if ($result["lhs"] === 1) {
            $rules[$ruleId]->addLhs($result["rid"]);
        } else {
            $rules[$ruleId]->addRhs($result["rid"]);
        }
    }
    $GLOBALS['ids'] = $ids;
    $rules = array_filter($rules, function ($rule) {
        return $rule->isValidRecommendation($GLOBALS['ids']);
    });
    usort($rules, function ($a, $b) {
        // Pick the one with a higher confidence
        if ($a->confidence !== $b->confidence) {
            return $a->confidence - $b->confidence;
        }
        // If confidence is same, pick the one that spans more rids on the lhs
        return sizeof($a->lhs) - sizeof($b->lhs);
    });
    $recommendations = array();
    $priorityIndex = 0;
    foreach($rules as $rule) {
        foreach ($rule->rhs as $rid) {
            $id = (string) $rid;
            $visited = false;
            for ($i = 0; $i < sizeof($ids); $i++) {
                if ($ids[$i]["visited"] && ((string) $ids[$i]["id"]) === $id) {
                    $visited = true;
                    break;
                }
            }
            if (!isset($recommendations[$id]) && !$visited) {
                $recommendations[$id] = $priorityIndex++;
            }
        }
    }
    $recommendations_keys = array_keys($recommendations);
    if (sizeof($recommendations) === 0) {
        $connection->close();
        return appReturn($app, APIReturnData::build(array(), true), false, false);
    }

    // Get uuid and name from the db
    $results = $connection->select("resources a inner join (select b.value as name, b.rid from resource_columns b inner join ".
    "(select rid, max(version) as version from resource_versions where rid in (". implode(',', array_fill(0, sizeof($recommendations_keys), '?')) .") and status='curated' group by rid) " .
    "c on (b.rid = c.rid and b.version = c.version) where b.name = 'Resource Name') d on a.id = d.rid", array("a.uuid", "d.*"), str_repeat("i", sizeof($recommendations_keys)), $recommendations_keys);
    $connection->close();
    
    // sort based on the rules ordering calculated above
    $GLOBALS['recommendations'] = $recommendations;
    usort($results, function ($a, $b) {
        return $recommendations[$a["rid"]] - $recommendations[$b["rid"]];
    });

    return appReturn($app, APIReturnData::build($results, true), false, false);
});
?>