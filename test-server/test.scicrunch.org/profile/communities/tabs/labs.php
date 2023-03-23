<?php
    $community_labs = Lab::loadArrayBy(Array("cid"), Array($community->id));

    $templates = Array();
    foreach($community_labs as $cl) {
        $templates = array_merge($templates, DatasetFieldTemplate::loadArrayBy(Array("labid", "active"), Array($cl->id, 1)));
    }

?>

<div class="tab-pane fade <?php if($section=='dataset') echo 'in active' ?>" id="labs">
    <div class="row margin-bottom-20">
        <div class="container">
            <h2 class="text-center">Labs</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created by</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($community_labs as $cl): ?>
                        <?php
                            $lab_creator = new User();
                            $lab_creator->getByID($cl->uid);
                        ?>
                        <tr>
                            <td><?php echo $cl->name ?></td>
                            <td><?php echo $cl->public_description ?></td>
                            <td><?php echo $lab_creator->getFullName() ?></td>
                            <td><?php echo $cl->curated ?></td>
                            <td>
                                <?php if($cl->curated !== Lab::CURATED_STATUS_APPROVED): ?>
                                    <a href="/forms/community-forms/lab-review.php?review=approved&labid=<?php echo $cl->id ?>"><button class="btn btn-success">Approve</button></a>
                                <?php endif ?>
                                <?php if($cl->curated !== Lab::CURATED_STATUS_REJECTED): ?>
                                    <a href="/forms/community-forms/lab-review.php?review=rejected&labid=<?php echo $cl->id ?>"><button class="btn btn-danger">Reject</button></a>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
