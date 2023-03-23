<?php
$page_number = isset($_GET["page_number"]) ? \helper\aR($_GET["page_number"], "i") : 1;
$per_page = 20;
$offset = ($page_number - 1) * $per_page;
$communities = Community::getAllCommunities($per_page, $offset);

function publicCheckedText($flag){
    if($flag === 0) return ' checked="checked"';
    else return '';
}

function archiveCheckedText($flag) {
    if($flag) return 'checked="checked"';
    return '';
}

?>

<div class="tab-pane fade in active">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Portal Name</th>
                        <th>Public</th>
                        <th>Alternate Portal Name</th>
                        <th>Redirect URL</th>
                        <th>SciCrunch ID</th>
                        <th>Archive</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($communities as $comm): ?>
                        <?php
                            if($comm->id === 0) continue;
                            if($comm->rid) {
                                $comm_resource = new Resource();
                                $comm_resource->getByID($comm->rid);
                            } else {
                                $comm_resource = NULL;
                            }
                        ?>
                        <tr>
                            <form action="/forms/curator-update-community.php" method="post">
                                <input type="hidden" name="cid" value="<?php echo $comm->id ?>" />
                                <td><a href="/<?php echo $comm->portalName ?>"><?php echo $comm->portalName ?></a></td>
                                <td><input name="public" type="checkbox" <?php echo publicCheckedText($comm->private) ?> /></td>
                                <td><input name="altportalname" type="text" value="<?php echo $comm->altPortalName ?>" /></td>
                                <td><input name="redirect" type="text" value="<?php echo $comm->redirect_url ?>" /></td>
                                <td><input name="comm-resource" type="text" value="<?php if($comm_resource) echo $comm_resource->rid; ?>" /></td>
                                <td><input name="archive" type="checkbox" <?php echo archiveCheckedText($comm->isArchived()) ?> /></td>
                                <td><input class="btn btn-success" type="submit" value="Update" /></td>
                            </form>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    $pagination_data = Array(
        "count" => Community::totalCount(),
        "per_page" => $per_page,
        "current_page" => $page_number,
        "params" => "",
        "base_url" => $link_base . "/communities",
        "page_location" => "query",
        "query_param_name" => "page_number",
    );
    echo \helper\htmlElement("pagination", $pagination_data);
?>
