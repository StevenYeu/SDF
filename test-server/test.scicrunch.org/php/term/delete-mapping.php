<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();

    $result = $cxn->select("term_mappings", Array("*"), "i", Array($_POST["mapping_id"]), "WHERE id=?")[0];
    $curation_logs = $cxn->select("term_mapping_logs", Array("*"), "i", Array($_POST["mapping_id"]), "WHERE tmid=?");
    $result = Array("curation_logs" => $curation_logs) + $result;
    $cxn->delete("term_mappings", "i", Array($_POST["mapping_id"]), "where id=?");
    $tm_fields = json_encode($result);
    $cxn->insert("term_mapping_deletes", "iissii", Array(NULL, $_POST["mapping_id"], $tm_fields, $_POST["mapping_delete_reason"], $_POST["user_id"], time()));

    $cxn->close();
?>
