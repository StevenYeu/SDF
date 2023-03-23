<?php
    $search = $data["search"];
    $new_filter = Array();
    foreach($newVars["filter"] as $f) {
        if(!\helper\startsWith($f, "v_lastmodified_epoch")) {
            $new_filter[] = $f;
        }
    }
    $newVars = $data["vars"];
    $newVars["filter"] = $new_filter;
    $newVars["page"] = 1;

    $date = date('Ymd', strtotime('-30 days'));   //Get 30 days back date along with current date
    $newVars["filter"][] = "gte:" . $date;
?>

<a href="<?php echo $search->generateURL($newVars) ?>">See new records</a>
