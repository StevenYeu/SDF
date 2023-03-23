<?php
    $dataset_id = $data['dataset_id'];
    
    $base_dir = $_SERVER["DOCUMENT_ROOT"] . "/../doi-datasets/public/";
    $metadata_file = $base_dir . "dataset_" . $dataset_id . "/v1/metadata_" . $dataset_id . ".html";

    $portalName = $data['community']->portalName;

/*
    // won't always have $community object, so need to get portalName ...
    $pattern = "\/(.*)\/data";
    preg_match("/" . $pattern . "/", $_SERVER['REQUEST_URI'], $matches);
    $portalName = $matches[1];
*/
    if (!(isset($_SESSION['user']))) {  // if not a user, give register / login links
?>
    <h3 id="join1">Create new <a href='/<?php echo $portalName; ?>/join'><?php echo $portalName; ?> Account</a> and/or <a
            class="btn-login"
            href="#">log in</a> to download the file.
    </h3>
    <hr>
<?php 
    } else {
?>    
    <table>  
        <tbody>
            <tr>
                <th width="800"><h1><?php echo strtoupper($portalName); ?> Public Dataset</h1></th>
                <th><div><a href="/php/file-download.php?type=doi&doi=<?php echo $portalName; ?>_<?php echo $dataset_id; ?>"><img src="/images/csv-file-format-extension.png"></a></div></th>
                <th></th>
                <th><div><a href="/php/file-download.php?type=dict&amp;doi=<?php echo $portalName; ?>_<?php echo $dataset_id; ?>"><img src="/images/csv-file-format-extension.png"></a></div></th>
            </tr>
            <tr>
                <th>&nbsp;</th>
                <th><div align="center">Data File</div></th>
                <th>&nbsp;</th>
                <th><div align="center">Data Dictionary</div></th>
            </tr>
        </tbody>
    </table>

<?php 
    }
    
    $content = file_get_contents($metadata_file);
    echo $content;
?>