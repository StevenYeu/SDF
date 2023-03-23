<?php

    if ($_SESSION["user"]->role < 1) {
        echo "No permission to view this page.";
        return;
    }

    $header = Array(
        "" => "",
        "System" => "view_id",
        "Source Name" => "source",
        "Field" => "column_name",
        "Status" => "curation_status",
        "Data Source Value" => "value",
        "Value" => "matched_value",
        "Concept Name" => "concept",
    );

    $page = 1;
    if(isset($_GET["page"]) && $_GET["page"] != "") $page = $_GET["page"];

    $currectFacets = Array();

    $view_id = "";
    if(isset($_GET["view_id"]) && $_GET["view_id"] != "") {
        $currectFacets["view_id"] = Array(
                                        "name" => "System",
                                        "type" => "s",
                                        "value" => $_GET["view_id"],
                                    );
    }

    $source = "";
    if(isset($_GET["source"]) && $_GET["source"] != "") {
        $currectFacets["source"] = Array(
                                        "name" => "Source Name",
                                        "type" => "s",
                                        "value" => $_GET["source"],
                                    );
    }

    $curation_status = "";
    if(isset($_GET["curation_status"]) && $_GET["curation_status"] != "") {
        $currectFacets["curation_status"] = Array(
                                        "name" => "Status",
                                        "type" => "s",
                                        "value" => $_GET["curation_status"],
                                    );
    }

    $limit = 20;
    $offset = $limit * ($page - 1);

    $currectFacets_string = "";
    $currectFacets_url = "";
    $paras_type = "";
    $paras = Array();
    $paras_string = "";
    if(count($currectFacets) > 0) {
        $paras_string .= "WHERE " . join("=? AND ", array_keys($currectFacets));
        foreach ($currectFacets as $key => $facet) {
            $currectFacets_string .= "<p>" . $facet["name"] . ": " . $facet["value"] . "</p>";
            $currectFacets_url .= "&" . $key . "=" . $facet["value"];
            $paras_type .= $facet["type"];
            $paras[] = $facet["value"];
        }
        $paras_string .= "=? ";
    }

    $cxn = new Connection();
    $cxn->connect();

    ## get facets
    $view_ids = $cxn->select("term_mappings", Array("DISTINCT view_id"), "", Array(), "");
    $source_names = $cxn->select("term_mappings", Array("DISTINCT source"), "", Array(), "");
    $status = $cxn->select("term_mappings", Array("DISTINCT curation_status"), "", Array(), "");
    $facets = Array(
        "System" => $view_ids,
        "Source Name" => $source_names,
        "Status" => $status,
    );

    $total_count = $cxn->select("term_mappings", Array("count(id)"), $paras_type, $paras, $paras_string)[0]["count(id)"];

    $paras_type .= "ii";
    $paras[] = $offset;
    $paras[] = $limit;
    $paras_string .= "ORDER BY column_name, view_name, id DESC LIMIT ?, ?";

    $results = $cxn->select("term_mappings", Array("*"), $paras_type, $paras, $paras_string);

    $cxn->close();

    function paginateLong($vars, $count, $per_page) {
        $page = $vars["page"];

        $facets = "";
        if($vars["facets"] != "") $facets = $vars["facets"];

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
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-mappings?page=' . ($page - 1) . $facets . '">«</a></li>';
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
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-mappings?page=1' . $facets . '">1</a></li>';
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-mappings?page=2' . $facets . '">2</a></li>';
            $html .= '<li><a href="javascript:void(0)">..</a></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $page) {
                $html .= '<li class="active"><a href="javascript:void(0)">' . $i . '</a></li>';
            } else {
                $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-mappings?page=' . $i . $facets . '">' . $i . '</a></li>';
            }
        }

        if ($page < $max_page)
            $html .= '<li><a href="/'.$vars['portalName'].'/interlex/dashboard-mappings?page=' . ($page + 1) . $facets . '">»</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)">»</a></li>';
        $html .= '</ul></div>';

        return $html;
    }
?>

<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Mappings Dashboard',array($home, 'Term Dashboard'),array('/'.$community->portalName, '/'.$community->portalName . '/interlex/dashboard'),'Mapping Dashboard');
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
            <?php if(count($currectFacets) > 0): ?>
                <h3><b>Current Facets</b></h3>
                <?php echo $currectFacets_string ?>
                <a href="/<?php echo $community->portalName ?>/interlex/dashboard-mappings" class="btn btn-success">Reset Facets</a>
                <hr>
            <?php endif ?>
            <h3><b>Facets</b></h3>
            <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
                <?php foreach ($facets as $facet_name => $value): ?>
                    <li class="list-group-item list-toggle" data-toggle="collapse" data-parent="#sidebar-nav" href="#collapse-<?php echo $header[$facet_name] ?>">
                        <a href="javascript:void(0)"><?php echo $facet_name ?></a>
                        <ul id="collapse-<?php echo $header[$facet_name] ?>" class="collapse">
                            <?php foreach ($value as $val): ?>
                                <li style="border-top:1px solid #ddd">
                                    <!-- <a href="/scicrunch/interlex/dashboard-mappings?<?php echo $header[$facet_name] ?>=<?php echo $val[$header[$facet_name]] ?>&page=1"><?php echo $val[$header[$facet_name]] ?></a> -->
                                    <a href="?<?php echo $header[$facet_name] ?>=<?php echo $val[$header[$facet_name]] ?>" onclick="changeURL('<?php echo $header[$facet_name] ?>', '<?php echo $val[$header[$facet_name]] ?>'); return false;"><?php echo $val[$header[$facet_name]] ?></a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-5">
                    <br>
                    <?php
                        $newVars["page"] = $page;
                        $newVars["facets"] = $currectFacets_url;
                        $newVars["portalName"] = $community->portalName;
                        echo paginateLong($newVars, $total_count, $limit);
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
                                    <a href="javascript:void(0)" class="not-rin showMoreColumnsInterlexMappings" id="smc"><i class="fa fa-plus"></i> Show More Columns</a>
                                </h3>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="panel-body">
                            <?php if($total_count != 0): ?>
                                <table class="table table-bordered table-striped table-fixed" style="table-layout:fixed" id="result-table">
                                    <thead>
                                        <tr>
                                            <?php
                                                $count = 0;
                                            ?>
                                            <?php foreach ($header as $column => $val): ?>
                                                <?php
                                                    if ($count > 5) {
                                                        $thprops = 'style="position:relative" class="search-header hidden-column showing"';
                                                    } else {
                                                        $thprops = 'style="position:relative" class="search-header"';
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
                                                <?php foreach ($header as $key): ?>
                                                    <?php
                                                        $value = $result[$key]
                                                    ?>
                                                    <?php if ($count > 5): ?>
                                                        <td class="hidden-column showing"><span class="search-table-record-td"><?php echo $value ?></span></td>
                                                    <?php elseif($count == 0): ?>
                                                        <td style="text-align: center">
                                                            <div class="col-md-4"><a target="_blank" href="/<?php echo $community->portalName ?>/interlex/curate-mapping/tmid=<?php echo $result["id"] ?>"><i class="fa fa-pencil" title="Curate this value"></i></a></div>
                                                            <?php if($result["tid"] == null): ?>
                                                                <div class="col-md-4"><i style="color: #d7d6d6" class="fa fa-eye" title="Quick view"></i></div>
                                                            <?php else :?>
                                                                <?php $_SESSION["mapping_field"] = $result["column_name"]; ?>
                                                                <div class="col-md-4"><a href="javascript:void(0)" onclick="fetchTermInfo('<?php echo $result["tid"] ?>', '<?php echo $result["id"] ?>');"><i class="fa fa-eye" title="Quick view"></i></a></div>
                                                            <?php endif ?>
                                                            <div class="col-md-4"><a href="javascript:void(0)" data-target="#delete_mapping_Modal" data-toggle="modal" data-code="<?php echo $result["id"] ?>"><i class="fa fa-trash" title="Delete the mapping"></i></a></div>
                                                        </td>
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
                                                    $body_width = '120%';
                                                }
                                            ?>
                                            <script>
                                                if(<?php echo $colcount ?> > 6) {
                                                    $(".showMoreColumnsInterlexMappings").click(function(){
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
                            <?php else: ?>
                                <p><b>There are no results, please reset facets.</b></p>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="delete_mapping_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
      	<div class="modal-dialog">
        		<div class="modal-content">
          			<div class="modal-header">
          			     <a class="close dark less-right" style="color: red" onclick="cancel()"><i class="fa fa-window-close" aria-hidden="true"></i></a>
                     <b>Do you want to delete this mapping?</b>
          			</div>
          			<div class="modal-body">
                    <form method="POST" id="mapping_delete_form">
                        <div class="form-group">
                            <input id="user_id" type="hidden" name="user_id" value="<?php echo $_SESSION['user']->id ?>" />
                            <input id="mapping_id" type="hidden" name="mapping_id" value=0/>
                            Notes (if delete, reason): <input id="mapping_delete_reason" name="mapping_delete_reason" required/>
                        </div>
                        <div class="form-froup">
                            <input id="submit" type="submit" name="submit" value="Delete" class="btn btn-info">
                            <button type="button" class="btn btn-default" onclick="cancel()">Cancel</button>
                        </div>
                    </form>
          			</div>
        		</div>
        </div>
    </div>

    <div id="view_mapping_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
      	<div class="modal-dialog">
        		<div class="modal-content">
          			<div class="modal-body">
                    <a class="close dark less-right" style="color: red" data-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i></a>
                    <div class="form-group" id="term_info"></div>
          			</div>
        		</div>
        </div>
    </div>
</div>

<script>

    function changeURL(facet_name, facet_value) {
        var currentURL = window.location.href.split("?");
        var baseURL = currentURL[0];
        var currectParas = [];
        var para = facet_name + "=" + facet_value;
        var newParas = [];

        if(currentURL.length > 1 && currentURL[1] != "") currectParas = currentURL[1].split("&");
        for (var i = 0; i < currectParas.length; i++) {
            if(currectParas[i].includes("page=") == false && currectParas[i].includes(facet_name) == false) newParas.push(currectParas[i]);
        }
        newParas.push(para);
        var newURL = baseURL + "?" + "page=1&" + newParas.join("&");
        window.location.replace(newURL);
        return false;
    }

    function fetchTermInfo(tid, tmid) {
        event.preventDefault();
        $.ajax({
            url:"/php/term/fetch-term-information.php",
            method:"POST",
            data:{"tid":tid, "tmid":tmid},
            success:function(data){
                $('#term_info').html(data);
                $('#view_mapping_Modal').modal('show');
            }
        });
    }

    function cancel() {
        $('#mapping_delete_reason').val('');
        $('#delete_mapping_Modal').modal('hide');
    }

</script>

<script>
    $(document).ready(function(){
        $('#mapping_delete_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();

            var url = "/php/term/delete-mapping.php";
            $.ajax({
                url:url,
                method:"POST",
                data:form_data,
                dataType:"JSON",
                success:function(data){
                    $('#delete_mapping_Modal').modal('hide');
                    window.location.reload(false);
                }
            })
        });
    });

    $(function () {
        $('#delete_mapping_Modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var code = button.data('code'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#mapping_id').val(code);
        });
    });

</script>
