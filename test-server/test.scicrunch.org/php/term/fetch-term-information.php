<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/elastic-interlex.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();

    $t_result = $cxn->select("terms", Array("id, label, definition, ilx, type, version"), "i", Array($_POST["tid"]), "where id=?")[0];
    $tm_result = $cxn->select("term_mappings", Array("view_name, value, column_name"), "i", Array($_POST["tmid"]), "where id=?")[0];
    $cxn->close();

    $search_manager = ElasticInterLexManager::managerByViewID("interlex");
    if(is_null($search_manager)) {
        return;
    }

    $es_result = $search_manager->searchInterLex($t_result["ilx"]);
    foreach ($es_result as $res) {
        $preferredID = $res->getField("Preferred ID");
        $synonyms = $res->getField("Synonyms");
    }

    $output = "<b>Source Name:</b>&nbsp;&nbsp;" . $tm_result['view_name'] . "<br>";
    $output .= "<b>Field:</b>&nbsp;&nbsp;" . $tm_result['column_name'] . "<br>";
    $output .= "<b>Data Source Value:</b>&nbsp;&nbsp;" . $tm_result['value'] . "<br><hr/>";
    $output .= "<h4>". $t_result['label'] . "&nbsp;&nbsp;(" . $t_result['ilx'] . ")</h4>";
    $output .= "<b>Proferred ID:</b>&nbsp;&nbsp;" . $preferredID . "&nbsp;&nbsp;&nbsp;&nbsp;";
    $output .= "<b>Type:</b>&nbsp;&nbsp;". $t_result['type'] . "&nbsp;&nbsp;&nbsp;&nbsp;";
    $output .= "<b>Version:</b>&nbsp;&nbsp;" . $t_result['version'] . "<br>";
    $output .= "<b>Synonyms:</b>&nbsp;&nbsp;" . $synonyms . "<br>";
    $output .= "<b>Description:</b>&nbsp;&nbsp;" . $t_result['definition'] . "<br>";

    echo $output;
?>
