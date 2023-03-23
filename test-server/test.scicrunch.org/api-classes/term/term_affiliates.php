<?php

function getTermAffiliates($user, $api_key){
   $dbObj = new DbObj();
   return TermAffiliates::getList($dbObj);
}

?>