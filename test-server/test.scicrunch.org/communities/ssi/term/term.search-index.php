<?php
    $q = "A";
    $l = "";
    $special_chars = ["", "-"];

    if(isset($_GET["q"]) && $_GET["q"] != "") $q=$_GET["q"];
    if(isset($_GET["l"]) && $_GET["l"] != "") $l=$_GET["l"];

    $search_manager = ElasticInterLexManager::managerByViewID("interlex");

    if($l != "") {
        if(strlen($l) == 1) $search_results = $search_manager->searchByChars($l." ");
        else $search_results = $search_manager->searchByChars($l);
        $results_count = number_format($search_results->totalCount());
    } else if($q != "") {
        $results = $search_manager->searchByChars($q);
        $total_results_count = number_format($results->totalCount());
        $results_count = -1;
    }


?>

<link rel="stylesheet" type="text/css" href="/assets/flaticons/font/flaticon.css">
<link rel="stylesheet" type="text/css" href="/js/term/angular-tree/css/tree-control.css" />
<link rel="stylesheet" type="text/css" href="/js/term/angular-tree/css/tree-control-attribute.css" />
<link rel="stylesheet" type="text/css" href="/css/term.css" />
<link rel="stylesheet" href="/css/resources.view.css" />

<script src="/js/node_modules/angular/angular.js"></script>
<script src="/js/angular-chips/ui-bootstrap.js"></script>
<script src="/js/angular-chips/ui-bootstrap-tpls-0.14.3.js"></script>
<script src="/js/term/angular-tree/angular-tree-control.js"></script>
<script src="/js/node_modules/angular/angular-sanitize.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/module-utilities.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-view.js"></script>



<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Index',array($home, 'Term Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard'),'Index');
?>

<div class="row" ng-show="showComments==true">
    <div class="container">
        <div class="row">
            <div class="col-md-1" style="text-align: center;">
                <br>
                <h4>Select First Letter</h4>
                <?php foreach (array_merge(range('A', 'Z'), range(0, 9)) as $char): ?>
                    <?php if(strval($char) == strval($q)): ?>
                        <div class="col-md-12" style="background:white; border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid; border-color: #e6e6e6">
                            <a href="/<?php echo $community->portalName ?>/interlex/search-index?q=<?php echo $char ?>">
                                <b style="font-size: 15px;"><?php echo $char ?></b>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="col-md-12" style="background:#e6e6e6; border-left: 1px solid; border-top: 1px solid; border-bottom: 1px solid; border-color: #e6e6e6;">
                            <a href="/<?php echo $community->portalName ?>/interlex/search-index?q=<?php echo $char ?>">
                                <span style="font-size: 15px;"><?php echo $char ?></span>
                            </a>
                        </div>
                    <?php endif ?>
                    <br>
                <?php endforeach ?>
                <p>&nbsp;</p>
            </div>

            <div class="col-md-11">
                <div class="row">
                    <div class="col-md-12">
                        <br>
                        <h1>Contents</h1>
                        <h4>Please select a two letter combination below to see a list of terms</h4>
                        <br>
                        <?php if($l == ""): ?>
                            <h2><?php echo $q ?> (<?php echo $total_results_count ?> terms)</h2>
                        <?php else: ?>
                            <h2><?php echo $l ?> (<?php echo $results_count ?> terms)</h2>
                        <?php endif ?>
                    </div>
                    <div class="col-md-12">
                        <?php
                            $chars = array_merge($special_chars, range('A', 'Z'), range(0, 9));
                        ?>
                        <?php foreach ($chars as $char): ?>
                            <a href="<?php echo $community->fullURL() ?>/interlex/search-index?q=<?php echo $q ?>&l=<?php echo $q . $char ?>">
                                <?php if($l == $q . $char): ?>
                                    <b style="font-size: 25px;">
                                <?php endif ?>
                                    <?php if($char == "0"): ?>
                                        <?php echo $q .  "0\n" ?>
                                    <?php elseif($char == ""): ?>
                                        <?php echo $q .  "&nbsp;\n" ?>
                                    <?php else: ?>
                                        <?php echo $q . $char . "\n" ?>
                                    <?php endif ?>
                                <?php if($l == $q . $char): ?>
                                    </b>
                                <?php endif ?>
                            </a>
                            &nbsp;
                        <?php endforeach ?>
                    </div>
                </div>
                <hr/>
                <div class="row">
                    <?php foreach($search_results as $idx => $record): ?>
                        <div class="single-view col-md-3">
                            <a target="_self" href="<?php echo $community->fullURL() ?>/interlex/view/<?php echo $record->getField("ID") ?>?searchTerm=<?php echo $vars['q'] ?>"><?php echo $record->getField("Name") ?></a>
                        </div>
                    <?php endforeach ?>
                </div>
                <?php if($results_count > 0): ?>
                    <hr/>
                    <div class="row">
                        <div class="col-md-12">
                            <?php foreach ($chars as $char): ?>
                                <a href="<?php echo $community->fullURL() ?>/interlex/search-index?q=<?php echo $q ?>&l=<?php echo $q . $char ?>">
                                    <?php if($l == $q . $char): ?>
                                        <b style="font-size: 25px;">
                                    <?php endif ?>
                                        <?php if($char == "0"): ?>
                                            <?php echo $q .  "0\n" ?>
                                        <?php elseif($char == ""): ?>
                                            <?php echo $q .  "&nbsp;\n" ?>
                                        <?php else: ?>
                                            <?php echo $q . $char . "\n" ?>
                                        <?php endif ?>
                                    <?php if($l == $q . $char): ?>
                                        </b>
                                    <?php endif ?>
                                </a>
                                &nbsp;
                            <?php endforeach ?>
                        </div>
                    </div>
                    <br>
                <?php elseif($results_count == 0): ?>
                    <h4>No results found.</h4>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
