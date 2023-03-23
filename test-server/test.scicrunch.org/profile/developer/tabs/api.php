<?php

$keys = APIKey::loadArrayBy(Array("uid", "active"), Array($_SESSION["user"]->id, 1));
$user_key_count = APIKey::userKeyCount($_SESSION["user"]);

?>
<div class="tab-pane fade in active" id="api-keys">
    <div class="row">
        <div class="col-md-6">
            <?php if($user_key_count < 10): ?>
                <form style="display:inline-block" action="/forms/first-api-key.php" method="post">
                    <button type="submit" class="btn btn-success">Generate an API key</button>
                </form>
            <?php endif ?>
            <?php if($user_key_count > 0): ?>
                <button class="update-text btn btn-primary">Update text</button>
            <?php endif ?>
        </div>
        <div class="col-md-2 col-md-offset-4">
            <a href="<?php echo PROTOCOL . "://" . FQDN ?>/browse/api-docs/index.html?url=<?php echo PROTOCOL ?>://<?php echo FQDN ?>/swagger-docs/swagger.json" target="_blank">
                <button class="btn btn-primary">View Docs</button>
            </a>
        </div>
    </div>
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Project Name</th>
                    <th>Description</th>
                    <th>Permissions</th>
                    <th>Created On</th>
                </tr>
                </thead>
                <tbody>
                    <?php if(!empty($keys) > 0): ?>
                        <?php foreach($keys as $key): ?>
                            <tr class="key-row">
                                <td><code class="key-val"><?php echo $key->key_val ?></code></td>
                                <td><input class="project-name" value="<?php echo $key->project_name ?>" /></td>
                                <td><input class="key-description" value="<?php echo $key->description ?>" /></td>
                                <td>
                                    <?php foreach($key->permissions() as $perm): ?>
                                        <div>
                                            <?php if($perm->active !== 1) continue; ?>
                                            <?php echo $perm->permission_type ?>
                                        </div>
                                    <?php endforeach ?>
                                </td>
                                <td><?php echo date("Y-m-d H:i:s", $key->created_time) ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
    <p>
        Please contact the help desk in the bottom right corner if you need extra permissions added to an API key.
    </p>
    <?php if($user_key_count >= 10): ?>
        <p>
            You reached the key limit.  If you need more keys, contact the help desk to make a request.
        </p>
    <?php endif ?>
</div>
