<?php 

$position = 0;
$dataset_id = $_GET['dataset_id'];

// Get Dataset fields
$dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
if (!$dataset)  
    die("No dataset found");

$dataset_owner = new User();
$dataset_owner->getByID($dataset->uid);

// Start the buffering //
ob_start();
echo $mega_json . "\n";

?>
<table>

<tbody>

<tr>

<td width="55%" class="ignore_shorten showing"><h1 style="color: #242e5c; letter-spacing: 1px"><b><?php echo $doi_overview[0]['title']; ?></b></h1>

<div style="height:10px;"><br></div>

<h2 style="color: #000000"><b>DOI:<?php echo $doi_overview[0]['hidden_doi']; ?></b></h2>

<div style="height:10px;"><br></div>

<h2 style="color: #242e5c"><b>DATASET CITATION</b></h2>

<?php 
    $authh = array();
    for ($i=0; $i<sizeof($doi_contributor); $i++) {
        if ($doi_contributor[$i]['author'] == 1) {
            $auth = $doi_contributor[$i]['lastname'] . " " . substr($doi_contributor[$i]['firstname'], 0, 1) . ".";
            if (strlen($doi_contributor[$i]['middleinitial']))
                $auth .= " " . $doi_contributor[$i]['middleinitial'] . ".";
            $authh[] = $auth;
        }
        if ($doi_contributor[$i]['contact'] == 1) {
            $contact_position = $i;
        }
    }
    $authh_string = implode(', ', $authh);
?>
<h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><?php echo $authh_string . " (" . date("Y") . ") " . $doi_overview[0]['title'] . ". " . strtoupper($community->portalName) . ":" . $_GET['dataset_id'] . " http://doi.org/" . $doi_overview[0]['hidden_doi']; ?></h3>

<div style="height:10px;"><br></div>

<h2 style="color: #242e5c"><b>ABSTRACT</b></h2>

<h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><u>STUDY PURPOSE:</u> <?php echo $doi_abstract[0]['study_purpose']; ?></h3>

<h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><u>DATA COLLECTED:</u> <?php echo $doi_abstract[0]['data_collected']; ?></h3>

<h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><u>CONCLUSIONS:</u> <?php echo $doi_abstract[0]['conclusions']; ?></h3>

<div style="height:10px;"><br></div>

<?php 
$keyy = Array();
for ($i=0; $i<sizeof($doi_keyword); $i++) {
    $keyy[] = $doi_keyword[$i]['keyword'];
}
$keyy_string = implode('; ', $keyy);
?>

<h2 style="color: #242e5c"><b>KEYWORDS</b></h2>
<h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><?php echo $keyy_string; ?></h3>

<div style="height:10px;"><br></div>

<h2 style="color: #242e5c"><b>PROVENANCE / ORIGINATING PUBLICATIONS</b></h2>

<ul>

<?php for ($i=0; $i<sizeof($doi_publication); $i++): ?> 
    <li><h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><?php echo $doi_publication[$i]['publication'] . ". "; ?>
    <?php 
        if (isset($doi_publication[$i]['publication_doi']) && strlen($doi_publication[$i]['publication_doi'])) {
            $doi_pmid = $doi_publication[$i]['publication_doi'];
            echo '<a href="http://doi.org/' . $doi_pmid . '">doi:' . $doi_pmid . "</a>";
        } elseif (isset($doi_publication[$i]['publication_pmid']) && strlen($doi_publication[$i]['publication_pmid'])) {
            $doi_pmid = $doi_publication[$i]['publication_pmid'];
            echo "https://pubmed.ncbi.nlm.nih.gov/" . $doi_pmid;
        }
        echo ".</h3>\n";
        echo "</li>\n";
    ?>
    <dd><h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><i><?php echo $doi_publication[$i]['citation_relevance']; ?></i></h3>
    </dd>
<?php endfor; ?>
</ul>

<h2 style="color: #242e5c"><b>NOTES</b></h2>
<?php echo $notes[0]['notes']; ?></b></h2>
</td>

<td width="5%" class="ignore_shorten showing"></td>

<td width="40%" class="ignore_shorten showing"><h2 style="color: #242e5c"><b>DATASET INFO</b></h2>

<h3>Contact: <?php echo $doi_contributor[$contact_position]['lastname'] . " " . $doi_contributor[$contact_position]['firstname'] . " (" . $doi_contributor[$contact_position]['email'] . ")"; ?></h3>
<div style="height:2px;"><br></div>
<h3>Lab: <?php echo $doi_overview[0]['lab']; ?><div style="height:2px;"><br></div>
<?php echo strtoupper($community->portalName) . ' Accession:' . $_GET['dataset_id']; ?></h3>
<h3>Records in Dataset: <?php echo $doi_overview[0]['recordcount']; ?><div style="height:2px;"><br></div>
Fields per Record: <?php echo $doi_overview[0]['fields']; ?></h3>

<h3>Files: 2</h3>
<div style="height:10px;"><br></div>

<h2 style="color: #242e5c">LICENSE</h2> 

<h3>Creative Commons Attribution License (CC-BY 4.0)</h3>

<div style="height:10px;"><br></div>

<h2 style="color: #242e5c"><b>FUNDING AND ACKNOWLEDGEMENTS</b></h2>
<?php 
    $fundd = array();
    for ($i=0; $i<sizeof($doi_funding); $i++) {
        $fundd[] = $doi_funding[$i]['agency'] . " (" . $doi_funding[$i]['initials'] . ")";
    }
    $fundd_string = implode('; ', $fundd);
?>
<h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><?php echo build_fund_string($doi_funding); ?></h3>


<div style="height:10px;"><br></div>

<h2 style="color: #242e5c"><b>CONTRIBUTORS</b></h2>
<h3 style="font-family: Helvetica Neue; letter-spacing: 1px;"><dl>
<?php 
for ($i=0; $i<sizeof($doi_contributor); $i++) {
    echo "<dt>" . $doi_contributor[$i]['lastname'] . ", " . $doi_contributor[$i]['firstname'];
    if (isset($doi_contributor[$i]['middleinitial']) && strlen($doi_contributor[$i]['middleinitial'])) 
        echo " " . $doi_contributor[$i]['middleinitial'] . ".";
    if (isset($doi_contributor[$i]['orcid']) && strlen($doi_contributor[$i]['orcid'])) 
        echo " [ORCID:" . str_replace("https://orcid.org/", "", $doi_contributor[$i]['orcid']) . "]";
    echo "</dt>\n";
    echo "<dd>" . $doi_contributor[$i]['affiliation'] . "</dd>\n";
}
?>
    
</dl></h3>

</td>

</tr>

<tr>

</tr>

</tbody>

</table>
<?php
file_put_contents($base_dir . 'dataset_' . $dataset_id . '/metadata_' . $dataset_id . '.html', ob_get_contents());
ob_end_clean();
?>

<?php 
    function build_fund_string ($doi_funding) {
        $fundd = array();
        $agency_array = array();
        $agency_identifier = array();

        //build array of agencies
        for ($i=0; $i<sizeof($doi_funding); $i++) {
            $agency_array[] = $doi_funding[$i]["agency"];
        }

        $unique_agencies = array_unique($agency_array);

        // initialize counter. how many grants dictates grammar ...
        foreach ($unique_agencies as $agencyy) {
            $agency_identifier[$agencyy] = 0;
        }

        foreach ($unique_agencies as $agencyy) {
            foreach($doi_funding as $fun) {
                if ($fun['agency'] == $agencyy)
                    $agency_identifier[$agencyy]++;
            }
        }

        $fund_string = '';

        foreach ($unique_agencies as $agencyy) {
            $fund_string .= $agencyy;
            if ($agency_identifier[$agencyy] >= 2)
                $fund_string .= " grants ";
            else
                $fund_string .= " ";

            $id_array = array();


            foreach ($doi_funding as $fun) {
                if ($fun['agency'] == $agencyy)
                    $id_array[] = $fun['initials'];
            }

            if (sizeof($id_array) >2)
                $fund_string .= join(', ', $id_array);
            elseif (sizeof($id_array) == 2)
                $fund_string .= join(' and ', $id_array);
            else
                $fund_string .= $id_array[0];

            $fund_string .= ", ";
        }




        $fundd_string = substr($fund_string, 0, strlen($fund_string) - 2);

        return $fundd_string;
    }
/*
                
  

                agency_unique.forEach (function (agencyy) {
                    // write out agency name + grant(s)
                    fund_string += agencyy;
                    if (agency_identifier[agencyy] >= 2) {
                        fund_string += " grants ";
                    } else 
                        fund_string += " ";

                    var id_array = [];

                    funding_array.forEach (function (fun) {
                        if (fun.agency == agencyy) {
                            id_array.push(fun.initials);
                        }
                    });
        
                    if (id_array.length > 2)
                        fund_string += id_array.join(", ");
                    else if (id_array.length == 2) {
                        fund_string += id_array.join(" and ");
                    } else
                        fund_string += id_array[0];
        
                    fund_string += "; ";
                });
              
                 $scope.fund_string = fund_string.substring(0, fund_string.length - 2);

                function uniqueArray1( ar ) {
                  var j = {};

                  ar.forEach( function(v) {
                    j[v+ '::' + typeof v] = v;
                  });

                  return Object.keys(j).map(function(v){
                    return j[v];
                  });
                } 

                function getLength(arr) {
                    return Object.keys(arr).length;
                }

                var agency_unique = uniqueArray1(agency_array);

*/
?>                