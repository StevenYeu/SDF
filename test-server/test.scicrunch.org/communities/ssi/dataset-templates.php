<?php

$dataset_templates = CommunityDatasetTemplate::loadArrayBy(Array("cid"), Array($community->id));

?>

<div class="container s-results" style="margin-top: 20px; margin-bottom: 20px">
    <div class="row">
        <?php if(empty($dataset_templates)): ?>
            This community does not impose a restriction on the type of datasets that you can submit.
        <?php else: ?>
            This community restricts dataset submissions to the following templates.  Any dataset submission to this community must use a template that contains at least all the fields in a required template.
        <?php endif ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Fields</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($dataset_templates as $dt): ?>
                    <?php
                        $fields = array_map(function($field) {
                            return $field->name . ": " . $field->term()->ilx;
                        }, $dt->datasetTemplate()->fields())
                    ?>
                    <tr>
                        <td><?php echo $dt->datasetTemplate()->name ?></td>
                        <td>
                            <ul>
                                <?php foreach($fields as $field): ?>
                                    <li><?php echo $field ?></li>
                                <?php endforeach ?>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
