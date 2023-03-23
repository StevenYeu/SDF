<?php

function getTermHistory($user, $api_key, $term_id){
    $versions = getTermVersions($term_id);

    return $versions;
}

function getTermVersions($tid){
    $dbObj = new DbObj();
    $version = new TermVersion($dbObj);
    $versions = $version->getByTid($tid);
    $mod_versions = array();
    $term = new Term($dbObj);
    $user = new User($dbObj);

    $term->getById($versions[0]['tid']);
    if($term->orig_uid == '32290') $orig_user = 'NeuroLex';
    else {
        $user->getByID($term->orig_uid);
        $orig_user = $user->firstname . ' ' . $user->lastname;
    }

    foreach ($versions as $i => $v) {
        $arr = json_decode($v['term_info']);
        foreach ($arr->superclasses as $a){
            $term->getById($a->id);
            $a->ilx = $term->ilx;
            $a->label = $term->label;
            $a->type = $term->type;
        }
        // print_r($arr);
        $v['term_info'] = json_encode($arr);
        $v['orig_user'] = $orig_user;

        if($v['uid'] == '32290') $v['modify_user'] = 'NeuroLex';
        else {
            $user->getByID($v['uid']);
            $v['modify_user'] = $user->firstname . ' ' . $user->lastname;
        }

        $mod_versions[] = $v;
    }

    return $mod_versions;
}

?>
