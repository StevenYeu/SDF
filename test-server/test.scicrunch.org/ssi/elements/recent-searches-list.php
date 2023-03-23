<?php

$recent_searches = $data["recent-searches"];
$this_community = $data["community"];
$cid = $this_community->id ? $this_community->id : 0;

$search = new Search();
$communities = Array();

if(empty($recent_searches) || is_null($recent_searches)) return;

usort($recent_searches, function($a, $b) {
    return $a["timestamp"] < $b["timestamp"];
});

$sources = Sources::getAllSourcesStatic();
?>
<h3 class="tut-options">Recent searches</h3>
<ul>
    <?php foreach($recent_searches as $rs): ?>
        <?php
            $vars = $rs["vars"];
            $search_label = $vars["l"] ? $vars["l"] : $vars["q"];
            if($vars["page"]) $vars["page"] = 1;
            if(!isset($communities[$rs["cid"]])) {
                $comm = new Community();
                $comm->getByID($rs["cid"]);
                $communities[$rs["cid"]] = $comm;
            }
            $search->community = $communities[$rs["cid"]];
        ?>
        <li>
            <a href="<?php echo $search->generateURL($vars) ?>">
                <p>
                    <small>
                        Search for: '<strong><?php echo $search_label ?></strong>'
                        in <?php echo $vars["category"] ?>
                        <?php if($vars["nif"]): ?>
                            <?php if(isset($sources[$vars["nif"]])): ?>
                                (<?php echo $sources[$vars["nif"]]->getTitle(); ?>)
                            <?php else: ?>
                                (<?php echo $vars["nif"] ?>)
                            <?php endif ?>
                        <?php endif ?>
                        <?php if($rs["cid"] != $cid): ?>
                            from <?php echo $vars["portalName"] ?>
                        <?php endif ?>
                    </small>
                </p>
            </a>
        </li>
    <?php endforeach ?>
</ul>
