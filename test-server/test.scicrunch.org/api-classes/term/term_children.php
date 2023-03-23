<?php

function getTermChildren($user, $api_key, $ilx){
   $dbObj = new DbObj();
   $children = array();
   foreach (Term::getTermChildren($dbObj, $ilx) as $child){
       //print_r($child);
       $secondGen = Term::getTermChildren($dbObj, $child['ilx']);
       if (count($secondGen) > 0){
           $child['has_children'] = true;
       }
       else {
           $child['has_children'] = false;
       }
       $children[] = $child;
   }
   return $children;
}

?>