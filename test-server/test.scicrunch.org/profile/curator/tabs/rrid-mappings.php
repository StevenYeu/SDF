<?php
    require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/get_rrid_alternate_ids.php";
    $page = isset($_GET["page_number"]) ? \helper\aR($_GET["page_number"], "i") : 1;
    $per_page = 100;
    $offset = ($page - 1) * $per_page;

    $user = isset($_SESSION["user"]) ? $_SESSION["user"] : NULL;
    $rrids = getRRIDAlternateIDs($user, NULL, NULL, $per_page, $offset);
    $rrids = $rrids->data;

    function activeString($active){ return $active === 1 ? "active" : "inactive"; }
?>

<div class="tab-pane fade in active">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Issued RRID</th>
                        <th>Replace by</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(is_array($rrids)): ?>
                        <?php foreach($rrids as $rrid): ?>
                            <tr>
                                <td><?php echo $rrid->issued_rrid ?></td>
                                <td><a href="/resolver/<?php echo $rrid->replace_by ?>"><?php echo $rrid->replace_by ?></a></td>
                                <td><?php echo activeString($rrid->active) ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    $pagination_data = Array(
        "count" => RRIDMap::totalCount(),
        "per_page" => $per_page,
        "current_page" => $page_number,
        "params" => "",
        "base_url" => $link_base . "/rrid-mappings",
        "page_location" => "query",
        "query_param_name" => "page_number",
    );
    echo \helper\htmlElement("pagination", $pagination_data);
?>
