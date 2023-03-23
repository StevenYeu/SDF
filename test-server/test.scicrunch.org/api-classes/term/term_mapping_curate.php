<?php

function termMappingCurate($user, $api_key, $tmid, $fields){
    global $config;
    require $_SERVER["DOCUMENT_ROOT"] . '/api-classes/term/term_and_mappings.php';

    $dbObj = new DbObj();
    $tm = new TermMapping($dbObj);
    $tml = new TermMappingLogs($dbObj);

    if ($fields['action'] == 'status_change') {
        $tm->getById($tmid);
        $tm->updateDB('curation_status', $fields['curation_status']);
        $tm->updateDB('is_whole', $fields['is_whole']);
        $tm->updateDB('is_ambiguous', $fields['is_ambiguous']);
        $tm->updateDB('relation', $fields['relation']);
        $tm->updateDB('concept', $fields['concept']);
        $tm->updateDB('concept_id', $fields['concept_id']);
        $tm->updateDB('tid', $fields['tid']);

        $tml->notes = $fields['notes'];
        $tml->tmid = $tmid;
        $tml->curation_status = $fields['curation_status'];
        $tml->concept = $fields['concept'];
        $tml->concept_id = $fields['concept_id'];
        $tml->relation = $fields['relation'];
        $tml->insertDB();
    }
    elseif ($fields['action'] == 'delete'){
        //get term_mappings record
        $tm->getById($tmid);

        //get term_mapping_logs record and shove all into json
        $logs = $tml->getByTermMappingId($tmid);
        $arr = array();
        $arr['curation_logs'] = $logs;
        foreach(TermMapping::$properties as $name){
            $arr[$name] = $tm->$name;
        }
        $json = json_encode($arr);

        //insert into term_mapping_deletes
        $tmd = new TermMappingDeletes($dbObj);
        $tmd->tmid = $tmid;
        $tmd->tm_fields = $json;
        $tmd->notes = $fields['notes'];
        $tmd->uid = $user->id;
        $tmd->insertDB();

        //delete record from term_mapping_logs
        TermMappingLogs::deleteDB($dbObj, $tmid);

        //delete record from term_mappings
        TermMapping::deleteDB($dbObj, $tm->id);
    }

    return getTermAndMappings($user, $api_key, $tmid);

}

?>
