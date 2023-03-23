<?php
    $user = $_SESSION["user"];
    $resources_owned = ResourceUserRelationship::loadArrayBy(Array("uid", "type"), Array($user->id, ResourceUserRelationship::TYPE_OWNER));
?>
<div class="tab-pane fade" id="owned-resources">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Resource</th>
                        <th>ID</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resources_owned as $ro): ?>
                        <tr>
                            <?php
                                $resource = new Resource();
                                $resource->getByID($ro->rid);
                                $resource->getColumns();
                                
                            ?>


                            <td><?php echo $resource->columns["Resource Name"] ?></td>
                            <td><?php echo $resource->rid ?></td>
<!-- Manu                            <td><a href="/browse/resources/<?php echo $resource->rid ?>"><?php echo $resource->columns["Resource Name"] ?></td>
                            <td><a href="/browse/resources/<?php echo $resource->rid ?>"><?php echo $resource->rid ?></td>
Manu -->
                            <td><a href="/<?php echo Community::getPortalName($community) ?>/about/resourcesedit/<?php echo $resource->rid ?>"><i class="fa fa-gear"></i></td>

<!-- Manu
                            <td><a href="/browse/resourcesedit/<?php echo $resource->rid ?>"><i class="fa fa-gear"></i></td>
Manu -->
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
