<?php

$redirects = RRIDPrefixRedirect::loadArrayBy(Array(), Array());
$redirect_views = RRIDPrefixRedirect::views();

?>

<div class="tab-pane fade in active">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>View</th>
                        <th>Prefix</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($redirects as $redirect): ?>
                        <tr>
                            <td>
                                <?php echo $redirect->viewid . " (" . $redirect_views[$redirect->viewid]["name"] . ")" ?>
                            </td>
                            <td>
                                <?php echo $redirect->prefix ?>
                            </td>
                            <td>
                                <?php echo \helper\dateFormat("long", $redirect->timestamp) ?>
                            </td>
                            <td>
                                <a class="text-danger" href="/forms/rrid-prefix-redirect-delete.php?prefix=<?php echo $redirect->prefix ?>&viewid=<?php echo $redirect->viewid ?>">Delete <i class="fa fa-times"></i></a></span>
                            </td>
                        <tr>
                    <?php endforeach ?>
                    <tr>
                        <form action="/forms/rrid-prefix-redirect-add.php" method="post">
                            <td>
                                <select name="viewid">
                                    <?php foreach($redirect_views as $viewid => $rv): ?>
                                        <option value="<?php echo $viewid ?>">
                                            <?php echo $viewid . " (" . $rv["name"] . ")" ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="prefix" />
                            </td>
                            <td>
                            </td>
                            <td>
                                <input type="submit" value="Add" />
                            </td>
                        </form>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
