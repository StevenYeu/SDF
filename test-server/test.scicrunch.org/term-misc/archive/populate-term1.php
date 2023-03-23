<?php 
error_reporting(E_ERROR | E_WARNING);

$titles = array(
  '',
  'Label',
  'Synonym',
  'Id',
  'PMID',
  'DefiningCitation',
  'SuperCategory',
  'SpeciesOrTaxa',
  'Definition',
  'DefiningCriteria',
  'HasRole',
  'FBbtId',
  'Abbrev',
  'FBbtLink',
  'FasciculatesWith',
  'CellSomaShape',
  'CellSomaSize',
  'LocatedIn',
  'SpineDensityOnDendrites',
  'DendriteLocation',
  'BranchingMetrics',
  'AxonMyelination',
  'AxonProjectionLaterality',
  'LocationOfAxonArborization',
  'LocationOfLocalAxonArborization',
  'OriginOfAxon',
  'NeurotransmitterOrNeurotransmitterReceptors',
  'MolecularConstituents',
  'CuratorNotes'
);

$mysqli = new mysqli('localhost', 'root', 'fana', 'scicrunch') or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$file = "./neuron_data_curated.csv";

$handle = fopen($file, "r") or die("Error: Unable to read the input file.");
$count = 0;
while (($line=fgetcsv($handle,1000,",")) !== FALSE) {

    // skip the header row
    if (++$count == 1) continue;

    if (sizeof($line) != sizeof($titles)) {
       echo sizeof($line) . " != " . sizeof($titles) . " ";
       echo "Bad: line " . $count . "\n";
       //print_r($titles);
       print_r($line);
       continue;
    }

    //print_r($line);
    $id = 0;
    for ($i=0;$i < sizeof($line)-1;$i++){
       if ($i == 0) continue;

       $line[$i] = preg_replace('/:Category:/','',$line[$i]);
       $line[$i] = $mysqli->escape_string(trim($line[$i]));
       #echo $titles[$i] . ": " . $line[$i] . "\n";
    }
    #echo "\n";
//print_r(array_slice($line,1));
//print_r(array_slice($titles,1));

    $fields = array_slice($titles,1);
    $values = array_slice($line,1);
    $insert = 'INSERT INTO term_raw (' . implode(', ', $fields) . ') VALUES ("' . implode('", "', $values) . '")';
echo $insert . "\n";

    //$mysqli->query($insert) or die("Error: database error - " . $mysqli->error);
}
$mysqli->close();


?>
