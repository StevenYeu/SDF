<?php
    $pre_facets = Array();
    $pre_filters = Array();

    if(isset($_SESSION["pre_facets"]) && count($_SESSION["pre_facets"]) > 0) {
        foreach ($_SESSION["pre_facets"] as $facet) {
            if ($facet == "Mentions:available") $pre_facets[] = "Mentions:yes";
            else if ($facet == "Validation:true") $pre_facets[] = "Validation:information available";
            else if ($facet == "Issues:warning") $pre_facets[] = "Issues:issues found";
            else $pre_facets[] = $facet;
        }
    }
    if(isset($_SESSION["pre_filters"]) && count($_SESSION["pre_filters"]) > 0) {
        foreach ($_SESSION["pre_filters"] as $filter) {
            $tmp = explode(":", $filter);
            if(in_array($tmp[0], ["gte", "lte"])) {
                $date = DateTime::createFromFormat('Ymd', $tmp[1]);
                $tmp[1] = $date->format('m/d/Y');
                if($tmp[0] == "gte") $tmp[0] = "Records added after";
                else $tmp[0] = "Records added before";
                $filter = join(" ", $tmp);
            }
            $pre_filters[] = $filter;
        }
    }
?>

<h4>Sources have been changed.</h4>
<h4>Previous Facets and Filters</h4>
<?php foreach ($pre_facets as $pre_facet): ?>
    <p><?php echo $pre_facet." (facet)" ?></p>
<?php endforeach ?>
<?php foreach ($pre_filters as $pre_filter): ?>
    <p><?php echo $pre_filter." (filter)" ?></p>
<?php endforeach ?>
<hr/>
