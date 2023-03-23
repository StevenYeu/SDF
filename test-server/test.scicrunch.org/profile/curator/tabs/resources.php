<?php

$query = isset($_GET["query"]) ? \helper\aR(rawurldecode($_GET["query"]), "s") : "";
$statusVar = isset($_GET["status"]) ? \helper\aR($_GET["status"], "s") : NULL;
$page_number = isset($_GET["page_number"]) ? \helper\aR($_GET["page_number"], "i") : 1;
$holder = new Resource_Fields();
$fields = $holder->getPage1();
$facets = isset($_GET["column"]) && is_array($_GET["column"]) ? filter_var_array($_GET["column"]) : NULL;

$per_page = 50;
$role = $_SESSION['user'] ? $_SESSION['user']->role : 0;
$holder = new Resource();
$results = $holder->searchColumns($query, ($page_number - 1) * $per_page, $per_page, $fields,$facets,$statusVar);	// last 2 arguments don't matter for old method
$resource_user = new User();
$splits = explode(' ', $query);

$comms = array();
$firstComm = new Community();
$firstComm->name = 'SciCrunch';
$firstComm->id = 0;
$comms[0] = $firstComm;
$link_base_resources = $link_base . "/resources";

/******************************************************************************************************************************************************************************************************/

function word_map($a) {
    return '\b' . preg_quote($a, "~") . '\b';
}

/******************************************************************************************************************************************************************************************************/
?>

<div class="panel">
    <div class="panel-heading">
        <ul class="list-unstyled">
            <?php
            $holder = new Connection();
            $holder->connect();
            $curate = $holder->select('resources',array('status','count(status)'),null,array(),'where status is not null group by status order by status asc');
            $holder->close();
            foreach ($curate as $row) {
                if($statusVar && $statusVar==$row['status'])
                    echo '<li class="active"><a href="' . $link_base_resources . '?status=' . $row['status'] . '">' . $row['status'].' ('.$row['count(status)'] . ')</a></li>';
                else
                    echo '<li><a href="' . $link_base_resources . '?status=' . $row['status'] . '">' . $row['status'].' ('.$row['count(status)'] . ')</a></li>';
            }
            ?>
        </ul>
        <?php echo \helper\htmlElement("browse-search-bar", Array(
            "id" => NULL,
            "mode" => NULL,
            "search_bar_type" => "simple",
            "search_message" => "Search for resources",
            "search_action" => $link_base_resources,
            "query_label" => "query",
            "searchText" => "",
            "filter" => NULL,
            "search_banner_type" => NULL,
            "docroot" => $GLOBALS["DOCUMENT_ROOT"],
            "type" => "resources",
            "display_query" => $query,
        )); ?>
        <a href="<?php echo $link_base ?>/resources-recent">Recent updates</a>
    </div>

    <div class="panel-body">
        <span class="results-number">Showing <?php echo count($results['results']) ?> out of <?php echo number_format($results['count']) ?> Resources on page <?php echo $page_number ?></span>
        <!-- Begin Inner Results -->

        <?php

        if(count($results['results']) > 0):
            foreach ($results['results'] as $data):
                $resource_user->getByID($data->uid);
                $str_name = preg_replace("~(" . implode("|", array_map('word_map', $splits)) . ")~i", "<strong>$1</strong>", $data->columns['Resource Name']);
                $str_name = str_replace("<strong></strong>", "", $str_name);
                $str_description = preg_replace("~(" . implode("|", array_map('word_map', $splits)) . ")~i", "<strong>$1</strong>", $data->columns['Description']);
                $str_description = str_replace("<strong></strong>", "", $str_description);
                $str_description = \helper\decodeUTF8($str_description);
                if(isset($comms[$data->cid])){
                    $newComm = $comms[$data->cid];
                } else {
                    $newComm = new Community();
                    $newComm->getByID($data->cid);
                    $comms[$data->cid] = $newComm;
                }
        ?>

                <div class="inner-results">
		    <!-- h3><a target="_blank" href="/browse/resourcesedit/<?php echo $data->rid ?>"><i class="fa fa-file-text-o"> <?php echo $str_name ?></i></a -->
		    <h3><a target="_blank" href="/<?php /* Manu */ echo $community->shortName?>/browse/resourcesedit/<?php echo $data->rid ?>"><i class="fa fa-file-text-o"> <?php echo $str_name ?></i></a>
                    <?php if(isset($_SESSION['user']) && $_SESSION['user']->role>0 && isset($data->score)): ?><span style="color:#aaa">(Score = <?php echo $data->score ?>)</span>
                    <?php endif ?>
                    </h3>
                        <div class="overflow-h">
                        <ul class="list-inline down-ul">
                            <li><?php echo $data->type ?></li>

                            <?php if($newComm->id==0): ?>
                                <li><?php echo $newComm->name ?></li>
                            <?php else: ?>
                                <li><a href="/<?php echo $newComm->portalName ?>"><?php echo $newComm->shortName ?></a></li>
                            <?php endif ?>

                            <?php if ($data->uid == 0): ?>
                                <li><?php echo Connection::longTimeDifference($data->insert_time) ?> - by Anonymous</li>
                            <?php else: ?>
                                <li><?php echo Connection::longTimeDifference($data->insert_time) ?> - submitted by <?php echo $resource_user->getFullName() ?></li>
                            <?php endif; ?>

                            <?php if(isset($_SESSION['user']) && $_SESSION['user']->role>0): ?>
                                <li>Curation Status: <?php echo $data->status ?></li>
                            <?php endif; ?>

                        </ul>
                    </div>
                </div>
                <hr/>
        <?php
            endforeach;
        endif;
        ?>
        <?php
            $params = "query=" . $query;
            if(isset($statusVar)) $params .= "&status=" . $statusVar;
            $pagination_data = Array(
                "count" => $results["count"],
                "per_page" => $per_page,
                "current_page" => $page_number,
                "params" => $params,
                "base_url" => $link_base_resources,
                "page_location" => "query",
                "query_param_name" => "page_number",
            );
            echo \helper\htmlElement("pagination", $pagination_data);
        ?>

    </div>
</div>
