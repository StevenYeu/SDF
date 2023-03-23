<?php 

$position = 0;
$dataset_id = $_GET['dataset_id'];

// Get Dataset fields
$dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
if (!$dataset)  
    die("No dataset found");

// Start the buffering //
ob_start();
?>
<h2 style="color:#242e5c;letter-spacing:1px;">
    <b><a href="<?php echo $community->fullURL() . "/data/" . $dataset->id; ?>"><?php echo $doi_overview[0]['title']; ?></a></b>
</h2>
<h3 style="color:#000000;">
    <b>
        <a href="http://dx.doi.org/<?php echo $doi_overview[0]['hidden_doi']; ?>">DOI:<?php echo $doi_overview[0]['hidden_doi']; ?></a>
    </b>
</h3>
<h3 style="color:#242e5c;">
    <b>DATASET CITATION:</b>
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;"><?php echo $authh_string . " (" . date("Y") . ") " . $doi_overview[0]['title'] . ". " . $dataset->lab()->community()->name . ". " . strtoupper($dataset->lab()->community()->portalName) . ":" . $_GET['dataset_id'] . " http://dx.doi.org/" . $doi_overview[0]['hidden_doi']; ?></h3>
<h3 style="color:#242e5c;">
    <b>ABSTRACT:</b>
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;">
    <u>STUDY PURPOSE:</u> <?php echo $doi_abstract[0]['study_purpose']; ?> 
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;">
    <u>DATA COLLECTED:</u> <?php echo $doi_abstract[0]['data_collected']; ?>
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;">
    <u>CONCLUSIONS:</u> <?php echo $doi_abstract[0]['conclusions']; ?>
</h3>

<?php
file_put_contents($base_dir . 'dataset_' . $dataset_id . '/stub_' . $dataset_id . '.html', ob_get_contents());
ob_end_clean();
?>