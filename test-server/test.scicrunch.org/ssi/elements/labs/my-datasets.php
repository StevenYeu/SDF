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
?>

<style>
    a.white_hover:hover { text-decoration: underline; color: white; }

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

<div class="row">
    <div class="col-md-12">
        <?php if($lab->broadcast_message): ?>
            <div class="alert alert-success">
                <?php echo $lab->broadcast_message ?>
            </div>
        <?php endif ?>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">My Datasets in <?php echo $lab->name; ?></div>
            </div>
            <div class="pre-scrollable panel-body" style="max-height: 60vh;">
            <?php if ($lab_datasets) { ?>
                <table class="table table-bordered" id="example" data-order="[]">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th width="30%">Dataset</th>
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
                        <?php if($ld->uid == $user->id): ?>
                            <tr>
                                <td><?php echo $ld->id; ?></td>
                                <td>
                                    <a target="_self" href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid=<?php echo $ld->id ?>" class="lab-link">
                                            <?php echo $ld->name ?>
                                        </a></td>
                                <td align="right"><?php echo number_format($ld->record_count) ?></td>
                                <td align="right"><?php echo number_format($ld->template()->nFields()) ?></td>
                                <td><span style="white-space: nowrap;"><?php echo date("Y-m-d", $ld->last_updated_time); ?></span></td>
                                <td><span style="color: <?php echo $ld->labStatusColor(); ?>"><?php echo $ld->labStatusPretty(); ?></span></td>
                                <td><?php echo $ld->editor_status; ?></td>
                                <td><?php echo $ld->curationStatusPretty(); ?></td>
                            </tr>
                        <?php endif ?>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
            <?php } else {
                    echo "No datasets found. Click here to <a href='" . $community->fullURL() . "/lab/create-dataset?labid=" . $lab->id . "'>upload a new dataset</a>";
            } ?>
        </div>
    </div>
</div>
