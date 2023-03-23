<?php

require_once __DIR__ . "/../classes/classes.php";
require_once __DIR__ . "/../api-classes/rrid-mentions.php";

\helper\scicrunch_session_start();

$query = $_GET["q"];
$facets = $_GET["facets"];
$fmt_facets = Array();
foreach($facets as $facet) {
    $facet_split = explode("|", $facet);
    if(count($facet_split) != 2) continue;
    if(!isset($fmt_facets[$facet_split[0]])) $fmt_facets[$facet_split[0]] = Array();
    $fmt_facets[$facet_split[0]][] = $facet_split[1];
}

header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=rrid-mentions.csv");
$output = fopen("php://output", "w");
fputcsv($output, Array("PMID", "Journal", "Year", "Funding cited", "RRID", "Name", "Provider"));

$offset = 0;
$offset_size = 1000;
$count = $offset_size;

while($offset < $count) {
    $rrid_mentions_return = getRRIDMentions($_SESSION["user"], NULL, $query, $fmt_facets, $offset_size, $offset);

    if(!$rrid_mentions_return->success) break;
    $rrid_mentions = $rrid_mentions_return->data["rrid-mentions"];
    $count = $rrid_mentions_return->data["count"];

    foreach($rrid_mentions as $rm) {
        $row = Array();
        $row[] = $rm->pmid;
        if($rm->literatureRecord()) {
            $row[] = $rm->literatureRecord()->journal_name;
            $row[] = $rm->literatureRecord()->publication_year;
            $grant_info_array = Array();
            foreach($rm->grantInfos() as $gi) {
                $grant_string = $gi->agency . " - " . $gi->identifier;
                if($gi->country) {
                    $grant_string .= " (" . $gi->country . ")";
                }
                $grant_info_array[] = $grant_string;
            }
            $row[] = implode(",", $grant_info_array);
        } else {
            $row[] = "";
            $row[] = "";
            $row[] = "";
        }
        $row[] = $rm->rrid;
        $row[] = $rm->name;
        $row[] = rridProvider($rm->rrid);

        fputcsv($output, $row);
    }
    fflush($output);
    ftruncate($output, ftell($output));
    $offset += $offset_size;
}
fclose($output);

exit;

function rridProvider($rrid) {
    $rrid = preg_replace("/^rrid: ?/", "", strtolower($rrid));
    static $rrid_map = Array(
        "imsr_jax" => "International Mouse Strain Resource - Jackson Labs",
        "imsr_crl" => "International Mouse Strain Resource - Charles River",
        "imsr_em" => "International Mouse Strain Resource - EMMA Mouse Repository",
        "imsr_rbrc" => "International Mouse Strain Resource - RIKEN, BioResource Center",
        "imsr_ncimr" => "International Mouse Strain Resource - NCI - Frederick",
        "mmrrc_ucd" => "Mutant Mouse Resource and Research Center - UC Davis",
        "mmrrc_mu" => "Mutant Mouse Resource and Research Center - University of Missouri",
        "mmrrc_unc" => "Mutant Mouse Resource and Research Center - University of North Carolina",
        "mgi" => "Mouse Genome Informatics",
        "bdsc" => "Bloomington Drosophila Stock Center",
        "flybase" => "FlyBase",
        "wb" => "WormBase",
        "zfin" => "Zebrafish Information Network",
        "dggr" => "Kyoto Department of Drosophila Genomics and Genetic Resources",
        "rgd" => "Rat Genome Database",
        "tsc" => "Tetrahymena Stock Center",
        "zirc" => "Zebrafish International Resource Center",
        "nxr" => "National Xenopus Resource",
        "bcbc" => "Beta Cell Biology Consortium",
        "xgsc" => "Xiphophorus Genetic Stock Center",
        "agsc" => "Ambystoma Genetic Stock Center",
        "nsrrc" => "National Swine Resource and Research Center",
        "cwru" => "Case Western Reserve University (School of Medicine)",
        "cvcl" => "Cellosaurus Cell Lines",
        "ab" => "Antibody Registry",
        "scr" => "Scicrunch Registry",
        "nlx" => "Scicrunch Registry",
        "nif" => "Scicrunch Registry",
        "rid" => "Scicrunch Registry",
        "omics" => "Scicrunch Registry",
        "scires" => "Scicrunch Registry",
        "mmrrc" => "Mutant Mouse Resource and Research Center",
    );

    foreach($rrid_map as $key => $val) {
        if(\helper\startsWith($rrid, $key)) return $val;
    }
    return "";
}

?>
