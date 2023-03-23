<?php
    $data_sources = $data["data-sources"];
    $nifid = $data["nifid"];
    $community = $data["community"];
    $search = $data["search"];
    $vars = $data["vars"];

    unset($_SESSION["pre_facets"]);
    unset($_SESSION["pre_filters"]);

    if(isset($vars["facet"])) $_SESSION["pre_facets"] = $vars["facet"];
    if(isset($vars["filter"])) $_SESSION["pre_filters"] = $vars["filter"];
    unset($vars["facet"]);
    unset($vars["column"]);
    unset($vars["sort"]);
    unset($vars["filter"]);
    if(!isset($vars["sources"])) $vars["sources"] = $nifid;
    $params = explode("?", $search->generateURL($vars))[1];
?>

<h3>Sources</h3>
<form class="multi-indices" url="<?php echo $community->fullURL() ?>/discovery/source/*/search?<?php echo $params ?>&changed">
    <input type="hidden" class="data-source" value="<?php echo htmlspecialchars($vars["sources"]) ?>"/>
    <input type="hidden" class="sources-count" value="<?php echo count($data_sources) ?>"/>
    <a href="javascript:void(0)" onclick="change_checkboxes('source', true)">Select All</a>&nbsp;&nbsp;&nbsp;&nbsp;
    <a href="javascript:void(0)" onclick="change_checkboxes('source', false)">Reset</a>
    <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
        <li class="list-group-item list-toggle" data-toggle="collapse" data-parent="#sidebar-nav" href="#collapse">
            <a href="javascript:void(0)">Data Sources</a>
            <ul id="collapse" class="collapse">
                <?php foreach ($data_sources as $source): ?>
                    <li style="border-top: 1px solid #ddd">
                        <label style="width:100%;padding-right:1px;font-weight:normal;" >
                        <!-- <a href="<?php echo $community->fullURL() ?>/discovery/source/<?php echo $source['index'] ?>/search?<?php echo $params ?>&changed" style="width:90%;padding-right:30px;display:inline-block;border:0" ><?php echo $source['plural_name'] ?></a> -->
                        <a style="display:inline-block;border:0"><?php echo $source['plural_name'] ?></a>
                        <div class="pull-right">
                            <div class="checkbox">
                                <?php if($source['checked']): ?>
                                    <input type="checkbox" class="indices-checkbox" style="margin-top:9px" name="source" source="<?php echo $source['index'] ?>" checked/>
                                <?php else: ?>
                                    <input type="checkbox" class="indices-checkbox" style="margin-top:9px" name="source" source="<?php echo $source['index'] ?>" />
                                <?php endif ?>
                            </div>
                        </div>
                      </label>
                    </li>
                <?php endforeach ?>
            </ul>
        </li>
    </ul>
    <button type="submit" class="btn btn-success">Submit</button>
</form>
<hr/>

<script>
    var isAllCheck = true;
    function toggle_checkboxes(cn){
        var cbarray = document.getElementsByName(cn);
        for(var i = 0; i < cbarray.length; i++){

            cbarray[i].checked = !isAllCheck
        }
        isAllCheck = !isAllCheck;
    }

    function change_checkboxes(cn, status){
        var cbarray = document.getElementsByName(cn);
        for(var i = 0; i < cbarray.length; i++){

            cbarray[i].checked = status
        }
    }
</script>
