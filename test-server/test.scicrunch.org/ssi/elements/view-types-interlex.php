<?php
    $vars = $data["vars"];
    $types = $data["types"];
    $results_types = $data["results_types"];
    $community = $data["community"];
    $view_type = $data["view_type"];

    if($types != "") $checked_types = explode(",", $types);
    else $checked_types = Array("term");

    if($view_type == "table") $url = "/" . $community->portalName . "/interlex/table/search?q=" . $vars["q"] . "&l=" . $vars["l"] . "&types=%*%&changed";
    else $url = "/" . $community->portalName . "/interlex/search?q=" . $vars["q"] . "&l=" . $vars["l"] . "&types=%*%&changed";

?>

<div>
    <h3>Types</h3>
    <form class="multi-types" url="<?php echo $url ?>">
        <a href="javascript:void(0)" onclick="change_checkboxes('results_type', true)">Select All</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="javascript:void(0)" onclick="change_checkboxes('results_type', false)">Reset</a>
        <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
            <li class="list-group-item" data-parent="#sidebar-nav">
                <ul>
                <?php foreach ($results_types as $results_type): ?>
                    <li style="border-top: 1px solid #ddd">
                        <label style="width:100%;padding-right:1px;font-weight:normal;" >
                        <a style="display:inline-block;border:0"><?php echo $results_type ?></a>
                        <div class="pull-right">
                            <div class="checkbox">
                                <?php if(in_array($results_type, $checked_types)): ?>
                                    <input type="checkbox" class="types-checkbox" style="margin-top:9px" name="results_type" results_type="<?php echo $results_type ?>" checked/>
                                <?php else: ?>
                                    <input type="checkbox" class="types-checkbox" style="margin-top:9px" name="results_type" results_type="<?php echo $results_type ?>" />
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

    <hr />

    <script>
        function change_checkboxes(cn, status){
            var cbarray = document.getElementsByName(cn);
            for(var i = 0; i < cbarray.length; i++){
                cbarray[i].checked = status
            }
        }
    </script>
</div>
