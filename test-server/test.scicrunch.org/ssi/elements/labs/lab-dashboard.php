<?php

require_once __DIR__ . "/../../../api-classes/labs.php";

$lab = $data["lab"];
$user = $data["user"];
$community = $data["community"];

if(!$lab || !$user || !$community) {
    return;
}

$lab_templates = $lab->templates();

$lab_datasets_opt = getLabDatasets($user, NULL, $lab->id);
if($lab_datasets_opt->success) {
    $lab_datasets = $lab_datasets_opt->data;
} else {
    $lab_datasets = Array();
}

$lab_members = LabMembership::loadArrayBy(Array("labid"), Array($lab->id));

$pending_members_count = 0;
foreach($lab_members as $lm) {
    if($lm->level == 0) {
        $pending_members_count += 1;
    }
}

if ($lab->isModerator($user)) {
    $view_dataset_page = 'dataset';
} else {
    $view_dataset_page = 'view-dataset';
}
?>

<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap.min.css">

<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var dataTable = $('#example').DataTable({
            "bFilter": true,
            "bPaginate": false,
            "sDom":     'ltipr',
            "bAutoWidth": false 
        });

        $("#filterbox").keyup(function() {
            dataTable.search(this.value).draw();
        });

        // Listen to change event
        $('.active-label').change(function() {
            // Store checked checkboxes
            var $checked = $('.active-box').filter(':checked');

            if ($checked.length) {
                $('.notmine').hide();
            }
            else {
                $('.notmine').show();
            }
        });           
    });
</script>

<div class="row margin-bottom-20">
    <div class="col-md-7">
        <h2><?php echo $lab->name ?></h2>
        <h4><?php echo $lab->private_description ?></h4>
    </div>
    <div class="col-md-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">Users</div>
            </div>
            <div class="panel-body scroll-height-200">
                <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Level</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($lab_members as $member): ?>
                    <?php if ($member->level == 0)
                            continue;
                            ?>
                    <tr>
                        <td><?php echo $member->user()->firstname . " " . $member->user()->lastname; ?></td>
                        <td><?php echo levelFilter($member->level); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?php if($lab->broadcast_message): ?>
            <div class="alert alert-success">
                <?php echo $lab->broadcast_message ?>
            </div>
        <?php endif ?>
    </div>
</div>

<style>
    .panel > .panel-heading {
        background-image: none;
        background-color: #f7f7f7;
        color: black;
    }

    .dataTables_filter, .dataTables_info, .dataTables_length { display: none; }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">Datasets in <strong><?php echo $lab->name; ?></strong></div>
            </div>
             
            <div class="pre-scrollable panel-body" style="max-height: 60vh;">
            <label class="active-label">
                <input type="checkbox" class="active-box" /> Only show my uploaded datasets
            </label>
                <div class="pull-right">Search <input type="text" id="filterbox"></div>
                <table class="table table-bordered table-striped" id="example" data-order="[]">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th width="30%">Dataset</th>
                        <th>Uploader</th>
                        <th>Records</th>
                        <th>Fields</th>
                        <th>Last Updated</th>
                        <th width="15%">Data Space Status</th>
                        <th width="10%">Editorial Status</th>
                        <th width="12%">DOI Review Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($lab_datasets as $ld): ?>
                    <tr <?php if ($ld->uid == $user->id)
                            echo 'class="mine"';
                            else
                                echo 'class="notmine"';
                            ?>>
                        <td align="right"><?php echo $ld->id; ?></td>
                        <td>
                            <a class="lab-link" href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid=<?php echo $ld->id; ?>"><?php echo $ld->name; ?></a>
                        </td>
                        <td><?php echo $ld->user()->lastname . ", " . $ld->user()->firstname; ?></td>
                        <td align="right"><?php echo number_format($ld->record_count) ?></td>
                        <td align="right"><?php echo number_format($ld->template()->nFields()) ?></td>
                        <td><span style="white-space: nowrap;"><?php echo date("Y-m-d", $ld->last_updated_time); ?></span></td>
                        <td><span style="color: <?php echo $ld->labStatusColor(); ?>"><?php echo $ld->labStatusPretty(); ?></span></td>
                        <td><?php echo $ld->editor_status; ?></td>
                        <td><?php echo $ld->curationStatusPretty(); ?></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
                </table>                        
            </div>
        </div>
    </div>
</div>

<?php 
    function levelFilter($level) {
        if ($level == 3)
            return "PI";
        elseif ($level == 2)
            return "Manager";
        elseif ($level == 1)
            return "Member";
    }
?>
