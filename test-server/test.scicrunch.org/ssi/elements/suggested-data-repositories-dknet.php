<?php
    $community = $data['community'];

    // $scientific_keywords = Array(
    //     "Nucleic acid sequence",
    //     "Protein sequence",
    //     "Molecular and supramolecular structure",
    //     "Functional genomics",
    //     "Metabolomics",
    //     "Proteomics",
    //     "Cytometry and Immunology",
    //     "Chemistry and chemical biology and biochemistry",
    //     "Imaging",
    //     "Neuroscience",
    //     "All research data types",
    //     "Other domain-specific repositories",
    // );

    ## get target rrids from nif_eelg database
    $cxn = new Connection();
    $cxn->connect();

    ## prepare names list for column "recommended by" id1
    $recommended_by_rrids = $cxn->select("resources", Array("version", "rid"), "i", Array(3), "WHERE rid in (select id1 from resource_relationships where reltype_id=?)");
    $recommended_related_repositories = Array();
    foreach ($recommended_by_rrids as $recommended_by_rrid) {
      $recommended_related_repositories[$recommended_by_rrid['rid']] = "<a target='_blank' href='".$community->fullURL()."/data/record/nlx_144509-1/".$recommended_by_rrid['rid']."/resolver?'>".$cxn->select("resource_columns", Array("value"), "ii", Array((int)str_replace("SCR_", "", $recommended_by_rrid['rid']), $recommended_by_rrid['version']), "WHERE rid=? AND version=? AND name='Abbreviation'")[0]['value']."</a>";
    }

    ## get NIDDK-specific repositories
    $results = $cxn->select("resource_is_recommended_by_niddk_dknet", Array("*"), "", Array(), "");
    $niddk_specific_repositories = organize_repositories($results, $recommended_related_repositories, $community, $cxn);
    if(count($niddk_specific_repositories) > 1) usort($niddk_specific_repositories, function($a, $b) {return strcmp($a["Resource Name"], $b["Resource Name"]);});

    ## get NIH-supported repositories
    $results = $cxn->select("resource_is_recommended_by_nlm_dknet", Array("*"), "", Array(), "");
    $nih_supported_repositories = organize_repositories($results, $recommended_related_repositories, $community, $cxn);
    if(count($nih_supported_repositories) > 1) usort($nih_supported_repositories, function($a, $b) {return strcmp($a["Resource Name"], $b["Resource Name"]);});

    ## get all repository scientific discipline keywords from database
    $scientific_disciplin_repositories = Array();
    $results = $cxn->select("resource_repository_scientific_discipline_dknet", Array("*"), "", Array(), "");
    $scientific_disciplin_repositories = organize_repositories($results, $recommended_related_repositories, $community, $cxn);

    $scientific_keywords = Array();
    foreach ($scientific_disciplin_repositories as $rrid => $value) {
          $scientific_keywords[$value["Repository Scientific Discipline"]][] = $rrid;
    }
    ksort($scientific_keywords);

    $scientific_discipline_results = Array();
    foreach ($scientific_keywords as $key => $values) {
        foreach ($values as $value) {
            $scientific_disciplin_repositories[$value]["RRID"] = $value;
            $scientific_discipline_results[$key][] = formatResult($scientific_disciplin_repositories[$value]);
        }
        if(count($scientific_discipline_results[$key]) > 1) usort($scientific_discipline_results[$key], function($a, $b) {return strcmp($a["Resource Name"], $b["Resource Name"]);});
    }

    $cxn->close();

    $repository_thead = '
        <thead>
            <tr>
                <th width="20%">Repository Name</th>
                <th width="15%">RRID</th>
                <th width="35%">Description</th>
                <th width="15%">Type of Data</th>
                <th width="15%">Recommended By</th>
            </tr>
        </thead>
    ';

    function does_url_exists($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }

    function organize_repositories($results, $recommended_related_repositories, $community, $cxn) {
        $repositories = Array();
        $unset_rrids = Array();
        foreach ($results as $result) {
            $repositories[$result["rid"]][$result["name"]] = $result["value"];
            if($result["name"] == "Additional Resource Types" && strpos(strtolower($result["value"]), "data repository") === false) $unset_rrids[] = $result["rid"];
        }
        foreach ($unset_rrids as $rrid) {
            unset($repositories[$rrid]);
        }
        foreach ($repositories as $key => $value) {
            $repositories[$key] = formatResult($value);
            $repositories[$key]["RRID"] = $key;
        }
        $repositories = get_recommended_by($repositories, $recommended_related_repositories, $community, $cxn);

        return $repositories;
    }

    function get_recommended_by($repositories, $recommended_related_repositories, $community, $cxn) {
        $ids = "'".join("','", array_keys($repositories))."'";
        $recommended_by = $cxn->select("resource_relationships", Array("id1", "id2"), "", Array(), "WHERE id2 in ($ids) AND reltype_id=3");
        foreach ($recommended_by as $val) {
            if($val['id1'] != "SCR_001606" || (!$community->rinStyle())) $repositories[$val["id2"]]["recommended by"][] = $recommended_related_repositories[$val['id1']];
        }
        $recommended_by_journal = $cxn->select("journalid_dknet", Array("iso_abbr", "id2"), "", Array(), "WHERE id2 IN ($ids)");
        foreach ($recommended_by_journal as $val) {
            $repositories[$val["id2"]]["recommended by"][] = $val['iso_abbr'];
        }
        return $repositories;
    }

    function formatResult($result) {
        if($result['Data Type Information URL'] != '') {
            $result['Data Type Keywords'] = "<a target='_blank' href='".$result['Data Type Information URL']."'>".$result['Data Type Keywords']."</a>";
        }
        // if($result['Data Submission Information URL'] != '') {
        //     $result['Data_Submission'] = "<h2><b>Data Submission Information</b></h2>";
        //     if($result['Data Submission Information'] == "") $result['Data Submission Information'] = "Check data submition information at repository website.";
        //     $result['Data_Submission'] .= "<p><a target='_blank' href='".$result['Data Submission Information URL']."'>".$result['Data Submission Information']."</a></p>";
        // }
        // if($result['Data Access Information URL'] != '') {
        //     $result['Data_Access'] = "<h2><b>Data Access Information</b></h2>";
        //     if($result['Data Access Information'] == "") $result['Data Access Information'] = "Check data access information at repository website.";
        //     $result['Data_Access'] .= "<p><a target='_blank' href='".$result['Data Access Information URL']."'>".$result['Data Access Information']."</a></p>";
        // }
        if($result['Repository Guidelines Information URL'] != '') {
            $result['Repository_Guidelines'] = "<h2><b>Repository Guidelines Information</b></h2>";
            $result['Repository_Guidelines'] .= "<p><a target='_blank' href='".$result['Repository Guidelines Information URL']."'>";
            if($result['Repository Guidelines'] != '')
                $result['Repository_Guidelines'] .= $result['Repository Guidelines'];
            else $result['Repository_Guidelines'] .= "Check repository guidelines information at repository website";
            $result['Repository_Guidelines'] .= "</a></p>";
        } else {
            if($result['Repository Guidelines'] != '') {
                $result['Repository_Guidelines'] = "<h2><b>Repository Guidelines Information</b></h2>";
                $result['Repository_Guidelines'] .= "<p>";
                $result['Repository_Guidelines'] .= $result['Repository Guidelines'];
                $result['Repository_Guidelines'] .= "</p>";
            }
        }
        if($result['Data size limits'] != '') {
            $result['Data_size_limits'] = "<h2><b>Data size limits</b></h2>";
            $result['Data_size_limits'] .= "<p>".$result['Data size limits']."</p>";
        }
        if($result['Data storage fee/costs'] != '') {
            $result['Data_storage_fee'] = "<h2><b>Data storage fee/costs</b></h2>";
            $result['Data_storage_fee'] .= "<p>".$result['Data storage fee/costs']."</p>";
        }
        // if($result['FAIRSharing URL'] != '') {
        //     $result['FAIRSharing_URL'] = "<h2><b>FAIRSharing URL</b></h2>";
        //     $result['FAIRSharing_URL'] .= "<p>".$result['FAIRSharing URL']."</p>";
        // }

        return $result;
    }

    function getRepositoryTableHTML($thead, $results, $community) {
        $html = '';
        $html .= '<table class="table table-striped">';
        $html .= $thead;
        $html .= '<tbody>';
        if(count($results) > 0){
            foreach ($results as $result){
                $html .= '<tr>';
                $html .= '<td class="showing ignore_shorten"><a target="_blank" href="'.$result['Resource URL'].'">'.$result['Resource Name'];
                if(does_url_exists("https://scicrunch.org/upload/resource-images/".(int)str_replace("SCR_", "", $result['RRID']).".png"))
                    $html .= '<br><img src="https://scicrunch.org/upload/resource-images/'.(int)str_replace("SCR_", "", $result['RRID']).'.png" style="width: 130px" />';
                $html .= '</a></td>';
                $html .= '<td class="showing ignore_shorten">';
                $html .= '<a target="_blank" href="'.$community->fullURL().'/data/record/nlx_144509-1/'.$result['RRID'].'/resolver">RRID:'.$result['RRID'];
                if($community->rinStyle())
                    $html .= ' <span class="fa-stack fa-md" title="Resource Report"><i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i><i class="fa fa-globe fa-stack-1x fa-inverse"></i></span>';
                $html .= '</a><br>';
                if($result['Data Submission Information URL'] != "")
                    $html .= '<a target="_blank" href="'.$result['Data Submission Information URL'].'"><span class="fa-stack fa-md" title="How to submit data"><i class="fa fa-arrow-circle-up fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Data Access Information URL'] != "")
                    $html .= '<a target="_blank" href="'.$result['Data Access Information URL'].'"><span class="fa-stack fa-md" title="How to access data"><i class="fa fa-arrow-circle-down fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Repository_Guidelines'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Repository_Guidelines'].'"><span class="fa-stack fa-md" title="Guidelines/Standards"><i class="fa fa-question-circle fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Data_size_limits'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Data_size_limits'].'"><span class="fa-stack fa-md" title="Data size limits"><i class="fa fa-database fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Data_storage_fee'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Data_storage_fee'].'"><span class="fa-stack fa-md" title="Data storage fee/costs"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-usd fa-stack-1x fa-inverse"></i></span></a>&nbsp;';
                if($result['FAIRSharing URL'] != "")
                    $html .= '<a target="_blank" href="'.$result['FAIRSharing URL'].'"><span class="fa-stack fa-md" title="FAIRSharing"><img src="https://scicrunch.org/upload/community-components/fair_82161242827b703e.png" style="width:30px;"></span></a>&nbsp;';
                $html .= '</td>';
                $html .= '<td class="showing">'.$result['Description'].'</td>';
                $html .= '<td class="showing">'.$result['Data Type Keywords'].'</td>';
                $html .= '<td class="showing ignore_shorten">'.join(", ", $result['recommended by']).'</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
?>

<div class="container">
    <br>
    <h2 class="margin-bottom-20" style="color: #408dc9;">Where can I deposit my data?</h2>
    <p> We have organized a list of data repositories that are recommended by the following sources: NIDDK domain experts, <a target="_blank" href="https://www.nature.com/sdata/policies/repositories">Nature Scientific Data</a>, <a target="_blank" href="http://journals.plos.org/plosone/s/data-availability">PLOS One</a>, <a target="_blank" href="https://www.nlm.nih.gov/NIHbmic/nih_data_sharing_repositories.html">NLM NIH Data Sharing Repositories</a>, <a target="_blank" href="http://www.sciencemag.org/authors/science-editorial-policies">Science</a>. It is generally best practice to deposit data into a discipline-specific and community recognized repository if one is available, or into an institutional or generalist repository if no suitable specialist repository is available.</p>
    <ul>
        <li><a href="#NIDDK-repositories">NIDDK-specific repositories</a></li>
        <li><a href="#NLM-repositories">NIH-supported repositories</a></li>
        <li><a href="#Institutional-repository">Institutional repository</a></li>
        <li><a href="https://dknet.org/about/suggested-data-resources-niddk-2">Other NIDDK Project-specific or consortium-specific data or sample repositories</a></li>

    </ul>

    <?php if(count($scientific_keywords) > 0): ?>
        <p style="margin-bottom: 0;">By scientific disciplines</p>
        <ul>
            <?php foreach ($scientific_keywords as $keyword => $val): ?>
                <li><a href="#<?php echo str_replace(' ', '-', str_replace([',', 'and '], '', $keyword)) ?>"><?php echo $keyword ?></a></li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>

    <p style="margin-bottom: 0;">De-identified human clinical research data</p>
    <ul>
        <p>Clinical trial data is encouraged to be submitted to the <a target="_blank" href="https://clinicaltrials.gov/">ClinicalTrials.gov</a> even if it is not required. For studies include human genomic and associate phenotypic data, you can consider <a target="_blank" href="https://www.ncbi.nlm.nih.gov/gap/">NIH database of Genotypes and Phenotypes (dbGaP)</a>. Another repository that you can consider is <a target="_blank" href="https://www.icpsr.umich.edu/icpsrweb/">ICPSR</a>, which hosts a variety of human data, including many demographic and social science studies. Information on uploading data to ICPSR can be found <a target="_blank" href="https://www.icpsr.umich.edu/icpsrweb/deposit">here</a>. Before uploading data, please note that the data should be de-identified, and you should follow all your institutional IRB's requirements and receive approvals. For completed phase I-IV interventional studies, you can also share  anonymized data at <a target="_blank" href="https://vivli.org">Vivli.</a></p>
    </ul>
    <hr>

    <div>
        <a id="NIDDK-repositories"></a>
        <h2>NIDDK-specific repositories</h2>
        <?php echo getRepositoryTableHTML($repository_thead, $niddk_specific_repositories, $community) ?>
    </div>
    <p><br><br></p>
    <div>
        <a id="NLM-repositories"></a>
        <h2>NIH-supported repositories (<a target="_blank" href="https://www.nlm.nih.gov/NIHbmic/nih_data_sharing_repositories.html">for complete and current list of NIH repositories click here</a>)</h2>
        <?php echo getRepositoryTableHTML($repository_thead, $nih_supported_repositories, $community) ?>
    </div>
    <p><br><br></p>
    <div>
        <a id="Institutional-repository"></a>
        <h2>Institutional repository</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="showing ignore_shorten">Does your institution have a repository for managing and publishing your data?  Usually, these services are provided by the library.  <br><a target="_blank" href="http://v2.sherpa.ac.uk/cgi/search/repository/advanced?screen=Search&amp;repository_name_merge=ALL&amp;repository_name=&amp;repository_org_name_merge=ALL&amp;repository_org_name=&amp;type=institutional&amp;content_types_merge=ANY&amp;content_subjects_merge=ANY&amp;org_country_browse=us&amp;org_country_browse_merge=ANY&amp;satisfyall=ALL&amp;order=preferred_name&amp;_action_search=Search">The Directory of Open Access Resources provides a directory of institutional repositories.</a></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php foreach ($scientific_keywords as $keyword => $val): ?>
        <p><br><br></p>
        <div>
            <a id="<?php echo str_replace(' ', '-', str_replace([',', 'and '], '', $keyword)) ?>"></a>
            <h2><?php echo $keyword ?></h2>
            <?php echo getRepositoryTableHTML($repository_thead, $scientific_discipline_results[$keyword], $community) ?>
        </div>
    <?php endforeach ?>
</div>

<!-- design popup layout and information -->
<div class="modal fade" id="repositoryPopup" tabindex="-1" role="dialog" aria-labelledby="c-pop-up-switching-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <a class="close dark less-right" data-dismiss="modal" aria-label="Close" style="color: red"><i class="fa fa-window-close" aria-hidden="true"></i> Close</a>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
        $(function () {
            $('#repositoryPopup').on('show.bs.modal', function (event) {
                var icon = $(event.relatedTarget); // icon that triggered the modal
                var value = icon.data('code'); // Extract info from data-* attributes
                $('.modal-body').html(value);
            });
        });
</script>
