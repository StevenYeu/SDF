<?php
    include_once '/assets/plugins/purifier/HTMLPurifier.auto.php';

    $purifier_config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifier_config);

    $header = Array(
        "label" => "Label",
        "definition" => "Description",
        "ilx" => "ILX",
        "version" => "Version",
        "orig_cid" => "Created CID",
        "time" => "Modified Time",
        "cid" => "CID",
        "type" => "Type",
        "orig_time" => "Created Time",
        "status" => "Status",
    );

    $origCid = -1;
    if(isset($_GET["origCid"]) && $_GET["origCid"] != "") $origCid = $_GET["origCid"];

    $page = 1;
    if(isset($_GET["page"]) && $_GET["page"] != "") $page = $_GET["page"];

    $sort = "";
    if(isset($_GET["sort"]) && $_GET["sort"] != "") $sort = $_GET["sort"];

    $filter = "";
    if(isset($_GET["filter"]) && $_GET["filter"] != "") {
        $filter = str_replace("v_lastmodified_epoch:", "", $_GET["filter"]);
        $epoch_time = (int)str_replace("<", "", str_replace(">", "", $filter));
    }

    $cxn = new Connection();
    $cxn->connect();
    $orig_communities = $cxn->select("communities", Array("id", "shortName"), "", Array(), "WHERE id IN (SELECT DISTINCT orig_cid FROM terms)");
    $communities = $cxn->select("communities", Array("id", "shortName"), "", Array(), "WHERE id IN (SELECT DISTINCT cid FROM terms)");
    $communities = array_merge($orig_communities, $communities);
    $comms = Array();
    foreach ($communities as $comm) {
        $comms[$comm["id"]] = $comm["shortName"];
    }
    $limit = 20;
    $offset = $limit * ($page - 1);

    if($origCid == -1) {
        if($filter != "") {
            if(\helper\startsWith($filter, ">")) {
                $total_count = $cxn->select("terms", Array("count(id)"), "i", Array($epoch_time), "WHERE time > ?")[0]["count(id)"];
                if($sort == "desc") $results = $cxn->select("terms", Array("*"), "iii", Array($epoch_time, $offset, $limit), "WHERE time > ? ORDER BY orig_time DESC LIMIT ?, ?");
                else if($sort == "asc") $results = $cxn->select("terms", Array("*"), "iii", Array($epoch_time, $offset, $limit), "WHERE time > ? ORDER BY orig_time ASC LIMIT ?, ?");
                else $results = $cxn->select("terms", Array("*"), "iii", Array($epoch_time, $offset, $limit), "WHERE time > ? ORDER BY time DESC LIMIT ?, ?");
            } else if(\helper\startsWith($filter, "<")) {
                $total_count = $cxn->select("terms", Array("count(id)"), "i", Array($epoch_time), "WHERE time < ?")[0]["count(id)"];
                if($sort == "desc") $results = $cxn->select("terms", Array("*"), "iii", Array($epoch_time, $offset, $limit), "WHERE time < ? ORDER BY orig_time DESC LIMIT ?, ?");
                else if($sort == "asc") $results = $cxn->select("terms", Array("*"), "iii", Array($epoch_time, $offset, $limit), "WHERE time < ? ORDER BY orig_time ASC LIMIT ?, ?");
                else $results = $cxn->select("terms", Array("*"), "iii", Array($epoch_time, $offset, $limit), "WHERE time < ? ORDER BY time DESC LIMIT ?, ?");
            }
        } else {
            $total_count = $cxn->select("terms", Array("count(id)"), "", Array(), "")[0]["count(id)"];
            if($sort == "desc") $results = $cxn->select("terms", Array("*"), "ii", Array($offset, $limit), "ORDER BY orig_time DESC LIMIT ?, ?");
            else if($sort == "asc") $results = $cxn->select("terms", Array("*"), "ii", Array($offset, $limit), "ORDER BY orig_time ASC LIMIT ?, ?");
            else $results = $cxn->select("terms", Array("*"), "ii", Array($offset, $limit), "LIMIT ?, ?");
        }
    } else {
        if($filter != "") {
            if(\helper\startsWith($filter, ">")) {
                $total_count = $cxn->select("terms", Array("count(id)"), "ii", Array($origCid, $epoch_time), "WHERE orig_cid=? AND time > ?")[0]["count(id)"];
                if($sort == "desc") $results = $cxn->select("terms", Array("*"), "iiii", Array($origCid, $epoch_time, $offset, $limit), "WHERE orig_cid=? AND time > ? ORDER BY orig_time DESC LIMIT ?, ?");
                else if($sort == "asc") $results = $cxn->select("terms", Array("*"), "iiii", Array($origCid, $epoch_time, $offset, $limit), "WHERE orig_cid=? AND time > ? ORDER BY orig_time ASC LIMIT ?, ?");
                else $results = $cxn->select("terms", Array("*"), "iiii", Array($origCid, $epoch_time, $offset, $limit), "WHERE orig_cid=? AND time > ? ORDER BY time DESC LIMIT ?, ?");
            } else if(\helper\startsWith($filter, "<")) {
                $total_count = $cxn->select("terms", Array("count(id)"), "ii", Array($origCid, $epoch_time), "WHERE orig_cid=? AND time < ?")[0]["count(id)"];
                if($sort == "desc") $results = $cxn->select("terms", Array("*"), "iiii", Array($origCid, $epoch_time, $offset, $limit), "WHERE orig_cid=? AND time < ? ORDER BY orig_time DESC LIMIT ?, ?");
                else if($sort == "asc") $results = $cxn->select("terms", Array("*"), "iiii", Array($origCid, $epoch_time, $offset, $limit), "WHERE orig_cid=? AND time < ? ORDER BY orig_time ASC LIMIT ?, ?");
                else $results = $cxn->select("terms", Array("*"), "iiii", Array($origCid, $epoch_time, $offset, $limit), "WHERE orig_cid=? AND time < ? ORDER BY time DESC LIMIT ?, ?");
            }
        } else {
            $total_count = $cxn->select("terms", Array("count(id)"), "i", Array($origCid), "WHERE orig_cid=?")[0]["count(id)"];
            if($sort == "desc") $results = $cxn->select("terms", Array("*"), "iii", Array($origCid, $offset, $limit), "WHERE orig_cid=? ORDER BY orig_time DESC LIMIT ?, ?");
            else if($sort == "asc") $results = $cxn->select("terms", Array("*"), "iii", Array($origCid, $offset, $limit), "WHERE orig_cid=? ORDER BY orig_time ASC LIMIT ?, ?");
            else $results = $cxn->select("terms", Array("*"), "iii", Array($origCid, $offset, $limit), "WHERE orig_cid=? LIMIT ?, ?");
        }
    }
    $cxn->close();

    function paginateLong($vars, $count, $per_page) {
        $page = $vars["page"];
        $sort = "";
        if($vars["sort"] != "") $sort = '&sort=' . $vars["sort"];

        $filter = "";
        if($vars["filter"] != "") $filter = '&filter=v_lastmodified_epoch:' . $vars["filter"];

        if ($vars["origCid"] == -1) $vars["origCid"] = "";
        if($count !== NULL && $per_page) {
            $paginated_pages = (int) ceil($count / $per_page);
        }
        if($paginated_pages == 0) {
            return "";
        }
        $max_page = $paginated_pages < 500 ? $paginated_pages : 500;
        $html = '<div class="text-left">';
        $html .= '<ul class="pagination">';

        if ($page > 1)
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-history?origCid=' . $vars["origCid"] . '&page=' . ($page - 1) . $sort . $filter . '">«</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)">«</a></li>';

        if ($page - 3 > 0) {
            $start = $page - 3;
        } else
            $start = 1;
        if ($page + 3 < $max_page) {
            $end = $page + 3;
        } else
            $end = $max_page;

        if ($start > 2) {
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-history?origCid=' . $vars["origCid"] . '&page=1' . $sort . $filter . '">1</a></li>';
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-history?origCid=' . $vars["origCid"] . '&page=2' . $sort . $filter . '">2</a></li>';
            $html .= '<li><a href="javascript:void(0)">..</a></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $page) {
                $html .= '<li class="active"><a href="javascript:void(0)">' . $i . '</a></li>';
            } else {
                $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-history?origCid=' . $vars["origCid"] . '&page=' . $i . $sort . $filter . '">' . $i . '</a></li>';
            }
        }

        if ($page < $max_page)
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-history?origCid=' . $vars["origCid"] . '&page=' . ($page + 1) . $sort . $filter . '">»</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)">»</a></li>';
        $html .= '</ul></div>';

        return $html;
    }
?>

<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term History Dashboard',array($home, 'Term Dashboard'),array('/'.$community->portalName, '/'.$community->portalName . '/interlex/dashboard'),'History Dashboard');
?>

<style>
    .table-fixed > thead > tr > th, .table-fixed > thead > tr > td {
        min-width: 180px;
        background-color: white;
    }
    .table-fixed td {

        /* These are technically the same, but use both */
        overflow-wrap: break-word;
        word-wrap: break-word;

        word-break: break-word;

        /* Adds a hyphen where the word breaks, if supported (No Blink) */
        -ms-hyphens: auto;
        -moz-hyphens: auto;
        -webkit-hyphens: auto;
        hyphens: auto;
    }
    .grey-option {
        background-color: rgb(149, 165, 166);
    }
</style>

<div class='container'>
    <div class="row">
        <div class="col-md-2 hidden-xs related-search">
            <br>
            <h3><b>Options</b></h3>
            <a href="javascript:void(0)" class="hidden-default-link"> Sort by created time</a>
            <div class="hidden-default-l">
                <div class="btn-group">
                    <?php
                        $filter_s = "";
                        if($filter != "") $filter_s = '&filter=v_lastmodified_epoch:' . $filter;
                    ?>
                    -&nbsp;<a href="/<?php echo $community->portalName ?>/interlex/dashboard-history?origCid=<?php echo $vars["origCid"] ?>&page=1&sort=desc<?php echo $filter_s ?>">Sort Descending</a><br>
                    -&nbsp;<a href="/<?php echo $community->portalName ?>/interlex/dashboard-history?origCid=<?php echo $vars["origCid"] ?>&page=1&sort=asc<?php echo $filter_s ?>">Sort Ascending</a>
                </div>
            </div>
            <br>
            <a href="/<?php echo $community->portalName ?>/interlex/dashboard-history?origCid=<?php echo $vars["origCid"] ?>&page=1<?php echo $filter_s ?>"> Sort by label</a>
            <br>
            <a href="javascript:void(0)" class="hidden-default-toggle"> Filter by last modified time</a>
            <div class="hidden-default">
                <input id="modified-date-picker" style="color:black" />
                <div class="btn-group">
                    <a href="javascript:void(0)" id="modified-date-picker-before"><button class="btn btn-default">Before</button></a>
                    <a href="javascript:void(0)" id="modified-date-picker-after"><button class="btn btn-default">After</button></a>
                </div>
            </div>
            <hr />
            <h3><b>Facets</b></h3>
            <table class="table table-bordered table-striped table-fixed" style="table-layout:fixed">
                <thead>
                    <tr>
                      <th>Orignal CID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orig_communities as $comm): ?>
                        <tr>
                            <td><a href="/<?php echo $community->portalName ?>/interlex/dashboard-history?origCid=<?php echo $comm['id'] ?>&page=1"><?php echo $comm['shortName'] ?></a></td>
                        </tr>
                    <?php endforeach ?>
                    <tr>
                </tbody>
            </table>

        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-5">
                    <br>
                    <?php
                        $newVars["page"] = $page;
                        $newVars["origCid"] = $origCid;
                        $newVars["sort"] = $sort;
                        $newVars["filter"] = $filter;
                        $newVars["portalName"] = $community->portalName;
                        echo paginateLong($newVars, $total_count, $limit)
                    ?>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-grey margin-bottom-50">
                        <div class="panel-heading">
                            <h3 class="panel-title pull-left">
                                <i class="fa fa-globe"></i> Total <?php echo $total_count ?> Results
                            </h3>
                            <div class="pull-right">
                                <h3 class="panel-title">
                                    <a href="javascript:void(0)" class="not-rin showMoreColumnsInterlexHistory" id="smc"><i class="fa fa-plus"></i> Show More Columns</a>
                                </h3>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="panel-body">
                            <table class="table table-bordered table-striped table-fixed" style="table-layout:fixed" id="result-table">
                                <thead>
                                    <tr>
                                        <?php
                                            $count = 0;
                                        ?>
                                        <?php foreach ($header as $column): ?>
                                            <?php
                                                if ($count > 5) {
                                                    $thprops = 'style="position:relative" class="hidden-column showing"';
                                                } else {
                                                    $thprops = 'style="position:relative"';
                                                }
                                            ?>
                                            <th <?php echo $thprops ?>>
                                                <?php echo $column ?>
                                            </th>
                                            <?php
                                                $count++;
                                            ?>
                                        <?php endforeach ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $colcount = 0;
                                    ?>
                                    <?php foreach($results as $result): ?>
                                        <?php
                                            $count = 0;
                                        ?>
                                        <tr>
                                            <?php foreach ($header as $key => $val): ?>
                                                <?php
                                                    switch ($key) {
                                                        case 'label':
                                                            $value = "<a target='_self' href='/". $community->portalName. "/interlex/view/" . $result['ilx'] . "'>" . $result[$key] . "</a>";
                                                            break;

                                                        case 'orig_cid':
                                                        case 'cid':
                                                            $value = $comms[$result[$key]];
                                                            break;

                                                        case 'ilx':
                                                            $linearray = explode('_', $result[$key]);
                                                            $value = strtoupper($linearray[0]) . ':' . $linearray[1];
                                                            break;

                                                        case 'time':
                                                        case 'orig_time':
                                                            $value = gmdate('m/d/Y', $result[$key]);
                                                            break;

                                                        default:
                                                            $value = $result[$key];
                                                            break;
                                                    }
                                                    $value = $purifier->purify($value);
                                                ?>
                                                <?php if ($count > 5): ?>
                                                    <td class="hidden-column showing"><span class="search-table-record-td"><?php echo $value ?></span></td>
                                                <?php else: ?>
                                                    <td>
                                                        <span class="search-table-record-td"><?php echo $value ?></span>
                                                    </td>
                                                <?php endif ?>
                                                <?php
                                                    $count++;
                                                    $colcount = $count;
                                                ?>
                                            <?php endforeach ?>
                                        </tr>
                                        <?php
                                            //changes the body width when displaying more columns
                                            $body_width = ' ';
                                            if($colcount > 6) {
                                                $body_width = '150%';
                                            }
                                        ?>
                                        <script>
                                            if(<?php echo $colcount ?> > 6) {
                                                $(".showMoreColumnsInterlexHistory").click(function(){
                                                    if($(this).hasClass("active")){
                                                        $("body").css("width", "100%");
                                                        $("#left-nav-facets").removeAttr("style");
                                                    } else {
                                                        $("body").css("width", "<?php echo $body_width ?>");
                                                        $("#left-nav-facets").css("width", "220px");
                                                    }
                                                });
                                            }
                                        </script>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hidden-default {
        display: none;
    }
    .hidden-default-l {
        display: none;
    }
</style>

<script>
$(function() {
    $("#modified-date-picker").datepicker();

    function modifiedDatePicker(action) {
        var time = $("#modified-date-picker").datepicker("getDate");
        if(time === null) return;
        var epoch_time = time.getTime() / 1000;
        var query = window.location.search
            .replace(/filter=v_lastmodified_epoch:(%3E|%3C)\d+&?/gi, "")
            .replace(/filter=v_status:N&?/gi, "")
            .replace(/&$/, "");
        var path = window.location.pathname.replace(/page\/\d+\/search/, "search");
        var fullpath = path + query;
        var query_delim = "&";
        if(window.location.search === "") {
            query_delim = "?";
        }
        var modified_fullpath = fullpath + query_delim + "filter=v_lastmodified_epoch:" + action + epoch_time;
        window.location.href = modified_fullpath;
    }

    $("#modified-date-picker-before").click(function() {
        modifiedDatePicker("<");
    });

    $("#modified-date-picker-after").click(function() {
        modifiedDatePicker(">");
    });

    $(".hidden-default-toggle").click(function() {
        $(".hidden-default").slideToggle();
    });

    $(".hidden-default-link").click(function() {
        $(".hidden-default-l").slideToggle();
    });
});
</script>
