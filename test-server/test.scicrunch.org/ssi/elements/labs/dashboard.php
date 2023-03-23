<?php

require_once __DIR__ . "/../../../api-classes/labs.php";

$lab = $data["lab"];
$user = $data["user"];
$community = $data["community"];
$main_lab = $data['main_lab'];
$my_labs = $data['my_labs'];
$user_labs = $data['user_labs'];
/*
if ($lab) {
    if(!$lab || !$user || !$community) {
        return;
    }

    $lab_datasets_opt = getLabDatasets($user, NULL, $lab->id);
    if($lab_datasets_opt->success) {
        $lab_datasets = $lab_datasets_opt->data;
    } else {
        $lab_datasets = Array();
    }

}
*/



?>

<style>
    a.white_hover:hover { text-decoration: underline; color: white; }

    .scroll { 
        margin:4px, 4px; 
        padding:4px; 
        height: 100px; 
        overflow-x: hidden; 
        overflow-y: auto; 
    }

    .dataTables_info, .dataTables_length { display: none; }

    .panel > .panel-heading {
        background-image: none;
        background-color: #f7f7f7;
        color: black;
    }
</style>

<!-- <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"> -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap.min.css">

<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var dataTable = $('#example').DataTable({
            "bFilter": true,
            "bPaginate": false,
            "bInfo": false,
            "paging": false,
            "bAutoWidth": false,
        });
    });
</script>

   <?php if($community_access_request): ?>
        <h4>
            A community access request has been sent to the owner.
        </h4>
    <?php else: ?>
        <h2>Welcome to the <?php echo strtoupper($community->portalName); ?> Community!</h3>
    <?php endif; ?>

        <?php if (!$main_lab): ?>
        <h4 style="padding-left: 20px"><p>You are a <strong>General Member</strong> of the ODC Community. You can explore and <a href="<?php echo $data['community']->fullURL(); ?>/data/public" class="lab-link">access public datasets</a>. </p>
            <p>To upload, share, release, and publish your data or explore non-published data, you must be a member of a verified lab. To become a member, you can <a href="<?php echo $data['community']->fullURL() . '/community-labs/list?labid=' . $lab->id; ?>" class="lab-link">create or join a lab</a>.</p>
        <?php else: ?>
        <h4 style="padding-left: 20px"><p>You are a <strong>Lab Member</strong> of the ODC-SCI community. In addition to exploring and <a href="<?php echo $data['community']->fullURL() . "/data/public?labid=" . $lab->id; ?>" class="lab-link">accessing public datasets</a> you can <a href="<?php echo $data['community']->fullURL() . '/lab/create-dataset?labid=' . $lab->id; ?>" class="lab-link">upload</a>, share, release and publish your data or explore non-published data shared with the community.</p>
        </h4>
        <?php endif; ?>

<div class="row">
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">Lab Membership</div>
            </div>
            <div class="panel-body scroll100">
                <?php
                    if ($user_labs) {
                        foreach ($user_labs as $ul) {
                            if ($ul->isModerator($user))
                                $lab_link = '/lab/admin?labid=' . $ul->id;
                            else
                                $lab_link = '/lab?labid=' . $ul->id;

                            echo '<a href="' . $community->fullURL() . $lab_link . '" class="lab-link">' . $ul->name . "</a><br />\n";
                        }
                    } else {
                        echo "You need to be part of a Lab. ";
                        echo "<a href='" . $data['community']->fullURL() . "/community-labs/list'><span class='lab-button lab-small-button'>Join/Register</span></a>";
                    }
                ?>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">Datasets I Uploaded</div>
            </div>
            <div class="pre-scrollable panel-body" style="max-height: 60vh;">
            <?php if ($main_lab) { ?>
                <table class="table table-bordered" id="example" data-order="[]">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th width="30%">Dataset</th>
                            <th width="20%">Lab</th>
                            <th>Records</th>
                            <th>Fields</th>
                            <th>Last Updated</th>
                            <th width="15%">Data Space Status</th>
                            <th width="10%">Editorial Status</th>
                            <th width="12%">DOI Review Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
    foreach ($user_labs as $ul) {
        $lab_datasets_opt = getLabDatasets($user, NULL, $ul->id);
        if($lab_datasets_opt->success) {
            $lab_datasets = $lab_datasets_opt->data;
        } else {
            $lab_datasets = Array();
        }
?>
                    <?php foreach($lab_datasets as $ld): ?>
                        <?php if($ld->uid == $user->id): ?>
                            <tr>
                                <td><?php echo $ld->id; ?></td>
                                <td>
                                    <a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid=<?php echo $ld->id ?>">
                                            <?php echo $ld->name ?>
                                        </a></td>
                                <td><?php echo $ul->name; ?></td>
                                <td align="right"><?php echo number_format($ld->record_count) ?></td>
                                <td align="right"><?php echo number_format($ld->template()->nFields()) ?></td>
                                <td><span style="white-space: nowrap;"><?php echo date("Y-m-d", $ld->last_updated_time); ?></span></td>
                                <td><span style="color: <?php echo $ld->labStatusColor(); ?>"><?php echo $ld->labStatusPretty(); ?></span></td>
                                <td><?php echo $ld->editor_status; ?></td>
                                <td><?php echo $ld->curationStatusPretty(); ?></td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach ?>
    <?php
        unset($lab_datasets);
        } ?>
                    </tbody>
                </table>
            </div>
            <?php } else {
                echo "You need to be part of a Lab. ";
                echo "<a href='" . $data['community']->fullURL() . "/community-labs/list'><span class='lab-button lab-small-button'>Join/Register</span>";
            } ?>
        </div>
    </div>
</div>
