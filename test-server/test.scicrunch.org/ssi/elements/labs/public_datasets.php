<?php
    $base_dir = $_SERVER["DOCUMENT_ROOT"] . "/../doi-datasets/public/";
    $portalName = $data['community']->portalName;

    $connection = new Connection();
    $connection->connect();

//    $results = $connection->select("datasets ", array("id"), "s", Array("approved-doi"), "WHERE lab_status = ? ORDER BY last_updated_time asc");
    $results = $connection->select("datasets d
        inner join `dataset_fields_templates` dft on dft.id = d.dataset_fields_template_id
        inner join labs l on l.id = dft.labid
        inner join communities c on l.cid = c.id", Array("d.id"), "ss", Array($portalName, "approved-doi"), "where c.portalName = ? AND d.lab_status = ? ORDER BY last_updated_time desc");
    $connection->close();
    
?>
<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap.min.css">

<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/dataTables.bootstrap.min.js"></script>

<h1 style="font-family:'Helvetica Neue';letter-spacing:1px;"><?php echo strtoupper($portalName); ?> Public Data Sets</h1>
<h3 style="font-family:'Helvetica Neue';letter-spacing:1px;">This page lists the publicly available datasets from the <?php echo $data['community']->name; ?>.  Additional data is available to researchers as part of the <?php echo strtoupper($portalName); ?> Data Commons.  To become a part of the <?php echo strtoupper($portalName); ?> community, please first 
    <a href='/<?php echo $portalName; ?>/join?referer="/<?php echo $portalName; ?>/data/public"'>Create an Account</a> and join the ODC community.
</h3>
<hr style='border-width:4px;' />
<!-- start listing -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#example').DataTable({
            "bPaginate": false,
    "bLengthChange": false,
    "bFilter": true,
    "bInfo": false,
    "ordering": false,
    "bAutoWidth": false });
    } );
</script>

<style>
    #example h2 { font-size: 1.2em; }
    #example h3 { font-size: 1.1em; }
</style>

<table id="example" class="table">
<thead>
    <tr>
        <th></th>
    </tr>
</thead>
<tbody>
<?php 
    foreach ($results as $row){
        echo "<tr><td class='ignore_shorten showing'>\n";
        $stub_file = $base_dir . "dataset_" . $row['id'] . "/v1/stub_" . $row['id'] . ".html";
        $content = file_get_contents($stub_file);
        echo $content;
        echo "</td></tr>\n";
        /*
        echo "<a href='/" . $portalName . "/data/" . $row['id'] . "?labid=" . $_GET['labid'] . "'>Go To Metadata</a>\n";
        echo "<!-- add separator -->\n";
        echo "<hr style='border-width:4px;' />\n\n";
        */
    }
?>
</tbody>
</table>
