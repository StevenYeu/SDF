<?php
    $community = $data['community'];
    $dknet_flag = false;
    if($community->rinStyle()) $dknet_flag = true;

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

    ## $_GET["p1"] is "DKNET", $_GET["p2"] is "NIDDK, NLM", $_GET["reltype"] is relationship type, default is 3.
    $p1 = "";
    $p2 = "";
    $reltypes = Array();
    if(isset($_GET['p1']) && $_GET['p1'] != '') $p1 = $_GET["p1"];
    if(isset($_GET['p2']) && $_GET['p2'] != '') {
        $p2 = $_GET["p2"];
        $types = Array();
        if(isset($_GET['reltype']) && $_GET['reltype'] != '') $types = explode(",", $_GET["reltype"]);
        foreach (explode(",", $p2) as $idx => $value) {
            if(count($types) > 0) $reltypes[$value] = $types[$idx];
            else $reltypes[$value] = 3;   // default reltype is 3 (is recommended by)
        }
    }

    $project_keywords = check_rrids_name($p1, $cxn);    // get projects' name by p1 ([SCR_001606] => dkNET)

    if($p1 == "" || count($project_keywords) == 0) {
        echo "<h2>No data.</h2>";
        return;
    }

    $recommender_keywords = check_rrids_name($p2, $cxn);    // get recommenders' name by p2 ([SCR_011446] => NLM, [SCR_012895] => NIDDK)
    $recommender_keywords_flag = count($recommender_keywords);

    ## prepare names list for column "recommended by" id1
    $recommended_by_rrids = $cxn->select("resources", Array("version", "rid"), "i", Array(3), "WHERE rid in (select id1 from resource_relationships where reltype_id=?)");
    $recommended_related_repositories = Array();
    foreach ($recommended_by_rrids as $recommended_by_rrid) {
      $recommended_related_repositories[$recommended_by_rrid['rid']] = "<a target='_blank' href='".$community->fullURL()."/data/record/nlx_144509-1/".$recommended_by_rrid['rid']."/resolver?'>".$cxn->select("resource_columns", Array("value"), "ii", Array((int)str_replace("SCR_", "", $recommended_by_rrid['rid']), $recommended_by_rrid['version']), "WHERE rid=? AND version=? AND name='Abbreviation'")[0]['value']."</a>";
    }

    $repositories = Array();
    foreach ($project_keywords as $k1 => $v1) {
        $tmp_rrids = Array();
        $project_rrids = $cxn->select("resource_relationships", Array("DISTINCT id2"), "s", Array($k1), "WHERE id1=? AND reltype_id=3");
        $recommended_by_rrids = Array();
        foreach ($project_rrids as $project_rrid) {
            $tmp_rrids[] = "'". $project_rrid['id2']."'";
        }
        $project_rrids_s = join(", ", $tmp_rrids);

        $relationships = Array();
        if($recommender_keywords_flag) {
            foreach ($recommender_keywords as $k2 => $v2) {
                if($reltypes[$k2] == 14) $id1 = $v2 . "|||";
                else $id1 = $k2;
                $relationships[$v2] = $cxn->select("resources", Array("version", "rid"), "is", Array($reltypes[$k2], $id1), "WHERE rid IN (SELECT DISTINCT id2 FROM resource_relationships WHERE reltype_id=? AND id1=? AND id2 IN ($project_rrids_s))");
            }
        } else {
            $relationships[$v1] = $cxn->select("resources", Array("version", "rid"), "", Array(), "WHERE rid IN ($project_rrids_s)");
        }

        $repository_results = Array();
        foreach ($relationships as $key => $values) {
            foreach ($values as $value) {
                $repository_result = Array();
                $repository_result['recommended by'] = get_recommended_by($value['rid'], $recommended_related_repositories, $community, $cxn);
                $repository_result['rrid'] = $value['rid'];
                $repository_result['rid'] = (int)str_replace("SCR_", "", $value['rid']);
                $repository_result['version'] = $value['version'];
                $result = $cxn->select("resource_columns", Array("name", "value"), "ii", Array($repository_result['rid'], $value['version']), "WHERE rid=? AND version=?");
                $repository_result = formatResult($result, $repository_result);
                if(strpos(strtolower($repository_result['Additional Resource Types']), strtolower("data repository")) !== false) $repository_results[$key][] = $repository_result;
            }
            if(count($repository_results[$key]) > 0) usort($repository_results[$key], function($a, $b) {return strcmp($a["Resource Name"], $b["Resource Name"]);});
        }
        $repositories[$v1] = $repository_results;
    }

    ## get all repository scientific discipline keywords from database
    $scientific_keywords = $cxn->select("resource_columns", Array("DISTINCT value"), "s", Array("Repository Scientific Discipline"), "WHERE name=?");
    $repository_scientific_discipline_keywords = Array();
    foreach ($scientific_keywords as $scientific_keyword) {
        $repository_scientific_discipline_keywords = array_merge($repository_scientific_discipline_keywords, explode(", ", $scientific_keyword["value"]));
    }
    $repository_scientific_discipline_keywords = array_filter($repository_scientific_discipline_keywords);
    sort($repository_scientific_discipline_keywords);

    $scientific_disciplines = Array();
    $scientific_discipline_results = Array();
    $rrids = str_replace("SCR_", "", $project_rrids_s);
    foreach ($repository_scientific_discipline_keywords as $keyword) {
        $scientific_disciplines[$keyword] = $cxn->select("resource_columns", Array("rid", "version"), "ss", Array($keyword, "Repository Scientific Discipline"), "WHERE value=? AND name=? AND rid IN ($rrids)");
        foreach ($scientific_disciplines[$keyword] as $item) {
            $scientific_discipline_result = Array();
            $version = $cxn->select("resources", Array("version"), "s", Array("SCR_" . str_pad((string)$item['rid'], 6, "0", STR_PAD_LEFT)), "WHERE rid=?")[0]['version'];
            if($item['version'] != $version) continue;
            $scientific_discipline_result['recommended by'] = get_recommended_by("SCR_" . str_pad((string)$item['rid'], 6, "0", STR_PAD_LEFT), $recommended_related_repositories, $community, $cxn);
            $scientific_discipline_result['rrid'] = "SCR_" . str_pad((string)$item['rid'], 6, "0", STR_PAD_LEFT);
            $scientific_discipline_result['rid'] = $item['rid'];
            $result = $cxn->select("resource_columns", Array("name", "value"), "ii", Array($item['rid'], $item['version']), "WHERE rid=? AND version=?");
            $scientific_discipline_result = formatResult($result, $scientific_discipline_result);
            if(strpos(strtolower($scientific_discipline_result['Additional Resource Types']), "data repository") !== false) $scientific_discipline_results[$keyword][] = $scientific_discipline_result;
        }
        if(count($scientific_discipline_results[$keyword]) > 0) usort($scientific_discipline_results[$keyword], function($a, $b) {return strcmp($a["Resource Name"], $b["Resource Name"]);});
    }
    if(count($scientific_discipline_results) == 0) $repository_scientific_discipline_keywords = [];
    else $repository_scientific_discipline_keywords = array_keys($scientific_discipline_results);

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

    function check_rrids_name($rrids, $cxn) {
        if($rrids == '') return [];
        $rrids = "'".str_replace(",", "', '", $rrids)."'";
        $target_rrids = $cxn->select("resources", Array("version", "rid"), "", Array(), "WHERE rid IN ($rrids)");
        $target_names = [];
        foreach ($target_rrids as $target_rrid) {
            $target_names[$target_rrid['rid']] = $cxn->select("resource_columns", Array("value"), "ii", Array((int)str_replace("SCR_", "", $target_rrid['rid']), $target_rrid['version']), "WHERE rid=? AND version=? AND name='Abbreviation'")[0]['value'];
            if($target_rrid['rid'] == "SCR_006770") $target_names[$target_rrid['rid']] = "<a target='_blank' href='https://braininitiative.nih.gov/resources/resource-list'>".$target_names[$target_rrid['rid']]."</a>";
        }
        return $target_names;
    }

    function get_recommended_by($id, $recommended_related_repositories, $community, $cxn) {
        $recommended_by = $cxn->select("resource_relationships", Array("DISTINCT id1"), "s", Array($id), "WHERE id2=? AND reltype_id=3");
        $results = Array();
        foreach ($recommended_by as $val) {
            if($val['id1'] != "SCR_001606" || $community->rinStyle()) $results[] = $recommended_related_repositories[$val['id1']];
        }
        return join(", ", $results);
    }

    function formatResult($result, $output_result) {
        foreach ($result as $re) {
            $output_result[$re['name']] = $re['value'];

            if($output_result['Data Type Information URL'] != '') {
                $output_result['Data Type Keywords'] = "<a target='_blank' href='".$output_result['Data Type Information URL']."'>".$output_result['Data Type Keywords']."</a>";
            }
            if($output_result['Data Submission Information URL'] != '') {
                $output_result['Data_Submission'] = "<h2><b>Data Submission Information</b></h2>";
                if($output_result['Data Submission Information'] == "") $output_result['Data Submission Information'] = "Check data submition information at repository website.";
                $output_result['Data_Submission'] .= "<p><a target='_blank' href='".$output_result['Data Submission Information URL']."'>".$output_result['Data Submission Information']."</a></p>";
            }
            if($output_result['Data Access Information URL'] != '') {
                $output_result['Data_Access'] = "<h2><b>Data Access Information</b></h2>";
                if($output_result['Data Access Information'] == "") $output_result['Data Access Information'] = "Check data access information at repository website.";
                $output_result['Data_Access'] .= "<p><a target='_blank' href='".$output_result['Data Access Information URL']."'>".$output_result['Data Access Information']."</a></p>";
            }
            if($output_result['Repository Guidelines Information URL'] != '') {
                $output_result['Repository_Guidelines'] = "<h2><b>Repository Guidelines Information</b></h2>";
                $output_result['Repository_Guidelines'] .= "<p><a target='_blank' href='".$output_result['Repository Guidelines Information URL']."'>";
                if($output_result['Repository Guidelines'] != '')
                    $output_result['Repository_Guidelines'] .= $output_result['Repository Guidelines'];
                else $output_result['Repository_Guidelines'] .= "Check repository guidelines information at repository website";
                $output_result['Repository_Guidelines'] .= "</a></p>";
            } else {
                if($output_result['Repository Guidelines'] != '') {
                    $output_result['Repository_Guidelines'] = "<h2><b>Repository Guidelines Information</b></h2>";
                    $output_result['Repository_Guidelines'] .= "<p>";
                    $output_result['Repository_Guidelines'] .= $output_result['Repository Guidelines'];
                    $output_result['Repository_Guidelines'] .= "</p>";
                }
            }
            if($output_result['Data size limits'] != '') {
                $output_result['Data_size_limits'] = "<h2><b>Data size limits</b></h2>";
                $output_result['Data_size_limits'] .= "<p>".$output_result['Data size limits']."</p>";
            }
            if($output_result['Data storage fee/costs'] != '') {
                $output_result['Data_storage_fee'] = "<h2><b>Data storage fee/costs</b></h2>";
                $output_result['Data_storage_fee'] .= "<p>".$output_result['Data storage fee/costs']."</p>";
            }
            if($output_result['FAIRSharing URL'] != '') {
                $output_result['FAIRSharing_URL'] = "<h2><b>FAIRSharing URL</b></h2>";
                $output_result['FAIRSharing_URL'] .= "<p>".$output_result['FAIRSharing URL']."</p>";
            }
        }
        return $output_result;
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
                if(does_url_exists("https://scicrunch.org/upload/resource-images/".$result['rid'].".png"))
                    $html .= '<br><img src="https://scicrunch.org/upload/resource-images/'.$result['rid'].'.png" style="width: 130px" />';
                $html .= '</a></td>';
                $html .= '<td class="showing ignore_shorten">';
                $html .= '<a target="_blank" href="'.$community->fullURL().'/data/record/nlx_144509-1/'.$result['rrid'].'/resolver">RRID:'.$result['rrid'];
                if($community->rinStyle())
                    $html .= ' <span class="fa-stack fa-md" title="Resource Report"><i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i><i class="fa fa-globe fa-stack-1x fa-inverse"></i></span>';
                $html .= '</a><br>';
                if($result['Data_Submission'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Data_Submission'].'"><span class="fa-stack fa-md" title="How to submit data"><i class="fa fa-arrow-circle-up fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Data_Access'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Data_Access'].'"><span class="fa-stack fa-md" title="How to access data"><i class="fa fa-arrow-circle-down fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Repository_Guidelines'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Repository_Guidelines'].'"><span class="fa-stack fa-md" title="Guidelines/Standards"><i class="fa fa-question-circle fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Data_size_limits'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Data_size_limits'].'"><span class="fa-stack fa-md" title="Data size limits"><i class="fa fa-database fa-stack-2x"></i></span></a>&nbsp;';
                if($result['Data_storage_fee'] != "")
                    $html .= '<a href data-toggle="modal" data-target="#repositoryPopup" data-code="'.$result['Data_storage_fee'].'"><span class="fa-stack fa-md" title="Data storage fee/costs"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-usd fa-stack-1x fa-inverse"></i></span></a>&nbsp;';
                if($result['FAIRSharing_URL'] != "")
                    $html .= '<a target="_blank" href="'.$result['FAIRSharing URL'].'"><span class="fa-stack fa-md" title="FAIRSharing"><img src="https://scicrunch.org/upload/community-components/fair_82161242827b703e.png" style="width:30px;"></span></a>&nbsp;';
                $html .= '</td>';
                $html .= '<td class="showing">'.$result['Description'].'</td>';
                $html .= '<td class="showing">'.$result['Data Type Keywords'].'</td>';
                $html .= '<td class="showing ignore_shorten">'.$result['recommended by'].'</td>';
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
    <?php if($dknet_flag): ?>
        <h2 class="margin-bottom-20" style="color: #408dc9;">Where can I deposit my data?</h2>
        <p> We have organized a list of data repositories that are recommended by the following sources: NIDDK domain experts, <a target="_blank" href="https://www.nature.com/sdata/policies/repositories">Nature Scientific Data</a>, <a target="_blank" href="http://journals.plos.org/plosone/s/data-availability">PlosOne</a>, <a target="_blank" href="https://www.nlm.nih.gov/NIHbmic/nih_data_sharing_repositories.html">NLM NIH Data Sharing Repositories</a>, <a target="_blank" href="http://www.sciencemag.org/authors/science-editorial-policies">Science</a>. It is generally best practice to deposit data into a discipline-specific and community recognized repository if one is available, or into an institutional or generalist repository if no suitable specialist repository is available.</p>
    <?php endif ?>
    <?php if((count($project_keywords) == 1 && count($recommender_keywords) <= 1 && count($repository_scientific_discipline_keywords) == 0) || (count($project_keywords) == 0 && count($recommender_keywords) == 0 && count($repository_scientific_discipline_keywords) == 1)): ?>
    <?php else: ?>
        <ul>
            <?php if(!$dknet_flag): ?>
                <?php foreach ($repositories as $project => $value): ?>
                    <?php if($recommender_keywords_flag): ?>
                        <?php foreach ($value as $recommender => $v2): ?>
                            <li>
                                <a href="#<?php echo $recommender ?>-repositories">
                                    <?php echo $recommender ?>-recommended repositories
                                    <?php if(count($related_id1s_name[$project]) > 0): ?>
                                        (<?php echo join(", ", $related_id1s_name[$project]) ?>)
                                    <?php endif ?>
                                </a>
                            </i>
                        <?php endforeach ?>
                    <?php else: ?>
                        <li>
                            <a href="#<?php echo $project ?>-repositories">
                                <?php echo $project ?>-recommended repositories
                                <?php if(count($related_id1s_name[$project]) > 0): ?>
                                    (<?php echo join(", ", $related_id1s_name[$project]) ?>)
                                <?php endif ?>
                            </a>
                        </i>
                    <?php endif ?>
                <?php endforeach ?>
            <?php else: ?>
                <li><a href="#NIDDK-repositories">NIDDK-specific repositories</a></li>
                <li><a href="#NLM-repositories">NIH-supported repositories</a></li>
                <li><a href="#aaa">Institutional repository</a></li>
                <li><a href="https://dknet.org/about/suggested-data-resources-niddk-2">Other NIDDK Project-specific or consortium-specific data or sample repositories</a></li>
          <?php endif ?>
        </ul>

        <?php if(count($repository_scientific_discipline_keywords) > 0): ?>
            <p style="margin-bottom: 0;">By scientific disciplines</p>
            <ul>
                <?php foreach ($repository_scientific_discipline_keywords as $keyword): ?>
                    <li><a href="#<?php echo str_replace(' ', '-', str_replace([',', 'and '], '', $keyword)) ?>"><?php echo $keyword ?></a></li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    <?php endif ?>

    <?php if($dknet_flag): ?>
        <p style="margin-bottom: 0;">De-identified human clinical research data</p>
        <ul>
            <p>Clinical trial data is encouraged to be submitted to the <a target="_blank" href="https://clinicaltrials.gov/">ClinicalTrials.gov</a> even if it is not required. For studies include human genomic and associate phenotypic data, you can consider <a target="_blank" href="https://www.ncbi.nlm.nih.gov/gap/">NIH database of Genotypes and Phenotypes (dbGaP)</a>. Another repository that you can consider is <a target="_blank" href="https://www.icpsr.umich.edu/icpsrweb/">ICPSR</a>, which hosts a variety of human data, including many demographic and social science studies. Information on uploading data to ICPSR can be found <a target="_blank" href="https://www.icpsr.umich.edu/icpsrweb/deposit">here</a>. Before uploading data, please note that the data should be de-identified, and you should follow all your institutional IRB's requirements and receive approvals. For completed phase I-IV interventional studies, you can also share  anonymized data at <a target="_blank" href="https://vivli.org">Vivli.</a></p>
        </ul>
    <?php endif ?>
    <hr>

    <?php if(!$dknet_flag): ?>
        <?php foreach ($repositories as $project => $value): ?>
            <?php if($recommender_keywords_flag): ?>
                <?php foreach ($value as $recommender => $v2): ?>
                    <div>
                        <a id="<?php echo $recommender ?>-repositories"></a>
                        <h2><?php echo $recommender ?>-recommended repositories</h2>
                        <?php echo getRepositoryTableHTML($repository_thead, $v2, $community) ?>
                    </div>
                    <p><br><br></p>
                <?php endforeach ?>
            <?php else: ?>
                <div>
                    <a id="<?php echo $project ?>-repositories"></a>
                    <h2><?php echo $project ?>-recommended repositories</h2>
                    <?php echo getRepositoryTableHTML($repository_thead, $value[$project], $community) ?>
                </div>
                <p><br><br></p>
            <?php endif ?>
        <?php endforeach ?>
    <?php else: ?>
        <div>
            <a id="NIDDK-repositories"></a>
            <h2>NIDDK-specific repositories</h2>
            <?php echo getRepositoryTableHTML($repository_thead, $repositories["dkNET"]["NIDDK"], $community) ?>
        </div>
        <p><br><br></p>
        <div>
            <a id="NLM-repositories"></a>
            <h2>NIH-supported repositories (<a target="_blank" href="https://www.nlm.nih.gov/NIHbmic/nih_data_sharing_repositories.html">for complete and current list of NIH repositories click here</a>)</h2>
            <?php echo getRepositoryTableHTML($repository_thead, $repositories["dkNET"]["NLM"], $community) ?>
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
    <?php endif ?>

    <?php foreach ($repository_scientific_discipline_keywords as $keyword): ?>
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
