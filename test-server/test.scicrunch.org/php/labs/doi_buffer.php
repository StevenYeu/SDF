<?php 
echo "enter buffer file ... check to see if anything get sent to screen ";

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
    <b><?php echo $doi_overview[0]['title']; ?></b>
</h2>
<h3 style="color:#000000;">
    <b>
        <a href="http://dx.doi.org/<?php echo $doi_overview[0]['hidden_doi']; ?>">DOI:<?php echo $doi_overview[0]['hidden_doi']; ?></a>
    </b>
</h3>
<h3 style="color:#242e5c;">
    <b>DATASET CITATION:</b>
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;"><?php echo $authh_string . " (" . $doi_overview[0]['year'] . ") " . $doi_overview[0]['title'] . strtoupper($dataset->lab()->community()->portalName) . ": " . $_GET['dataset_id'] . " http://dx.doi.org/" . $doi_overview[0]['hidden_doi']; ?></h3>
<h3 style="color:#242e5c;">
    <b>ABSTRACT:</b>
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;">
    <u>STUDY PURPOSE:</u> <?php echo $doi_abstract[0]['study_purpose']; ?>. 
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;">
    <u>DATA COLLECTED:</u> <?php echo $doi_abstract[0]['data_collected']; ?>
</h3>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;">
    <u>PRIMARY CONCLUSION:</u> <?php echo $doi_abstract[0]['primary_conclusion']; ?>
</h3>

<?php
file_put_contents($base_dir . 'dataset_' . $dataset_id . '/stub_' . $dataset_id . '.html', ob_get_contents());
ob_end_clean();
//file_put_contents($base_dir . 'dataset_' . $dataset_id . '/stub_' . $dataset_id . '.html', ob_get_contents());

echo "end buffer file " . $base_dir . 'dataset_' . $dataset_id . '/stub_' . $dataset_id . '.html';
?>