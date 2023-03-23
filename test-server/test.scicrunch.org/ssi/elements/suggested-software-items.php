<?php
    $community = $data['community'];
    $dknet_flag = false;
    if($community->rinStyle()) $dknet_flag = true;

    ## get target rrids from nif_eelg database
    $cxn = new Connection();
    $cxn->connect();

    ## $_GET["p1"] is "BRAIN Initiative", $_GET["p2"] is "BRAIN Initiative"
    $p1 = "";
    $p2 = "";
    if(isset($_GET['p1']) && $_GET['p1'] != '') $p1 = $_GET["p1"];
    if(isset($_GET['p2']) && $_GET['p2'] != '') $p2 = $_GET["p2"];
    $project_keywords = check_rrids_name($p1, $cxn);
    $recommender_keywords = check_rrids_name($p2, $cxn);
    $recommender_keywords_flag = count($recommender_keywords);

    $recommended_by_rrids = $cxn->select("resources", Array("version", "rid"), "i", Array(3), "WHERE rid in (select id1 from resource_relationships where reltype_id=?)");
    $recommended_related_repositories = Array();
    foreach ($recommended_by_rrids as $recommended_by_rrid) {
      $recommended_related_repositories[$recommended_by_rrid['rid']] = "<a target='_blank' href='".$community->fullURL()."/data/record/nlx_144509-1/".$recommended_by_rrid['rid']."/resolver?'>".$cxn->select("resource_columns", Array("value"), "ii", Array((int)str_replace("SCR_", "", $recommended_by_rrid['rid']), $recommended_by_rrid['version']), "WHERE rid=? AND version=? AND name='Abbreviation'")[0]['value']."</a>";
    }

    $software = Array();
    foreach ($project_keywords as $k1 => $v1) {
        $tmp_rrids = Array();
        $project_rrids = $cxn->select("resource_relationships", Array("DISTINCT id2"), "si", Array($k1, 3), "WHERE id1=? AND reltype_id=?");
        $recommended_by_rrids = Array();
        foreach ($project_rrids as $project_rrid) {
            $tmp_rrids[] = "'". $project_rrid['id2']."'";
        }
        $project_rrids_s = join(", ", $tmp_rrids);

        $relationships = Array();
        if($recommender_keywords_flag) {
            foreach ($recommender_keywords as $k2 => $v2) {
                $relationships[$v2] = $cxn->select("resources", Array("version", "rid"), "is", Array(3, $k2), "WHERE rid IN (SELECT DISTINCT id2 FROM resource_relationships WHERE reltype_id=? AND id1=? AND id2 IN ($project_rrids_s))");
            }
        } else {
            $relationships[$v1] = $cxn->select("resources", Array("version", "rid"), "", Array(), "WHERE rid IN ($project_rrids_s)");
        }

        $software_results = Array();
        foreach ($relationships as $key => $values) {
            foreach ($values as $value) {
                $software_result = Array();
                $software_result['recommended by'] = get_recommended_by($value['rid'], $recommended_related_repositories, $cxn);
                $software_result['rrid'] = $value['rid'];
                $software_result['rid'] = (int)str_replace("SCR_", "", $value['rid']);
                $software_result['version'] = $value['version'];
                $result = $cxn->select("resource_columns", Array("name", "value"), "ii", Array($software_result['rid'], $value['version']), "WHERE rid=? AND version=?");
                $software_result = formatResult($result, $software_result);
                if(strpos(strtolower($software_result['Additional Resource Types']), "software") !== false && strpos(strtolower($software_result['Additional Resource Types']), "data repository") === false)
                    $software_results[$key][] = $software_result;
            }
        }
        $software[$v1] = $software_results;
    }

    $cxn->close();

    $software_thead = '
        <thead>
            <tr>
                <th width="20%">Software Name</th>
                <th width="20%">RRID</th>
                <th width="40%">Description</th>
                <th width="20%">Recommended By</th>
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

    function get_recommended_by($id, $recommended_related_repositories, $cxn) {
        $recommended_by = $cxn->select("resource_relationships", Array("DISTINCT id1"), "s", Array($id), "WHERE id2=? AND reltype_id=3");
        $results = Array();
        foreach ($recommended_by as $val) {
            $results[] = $recommended_related_repositories[$val['id1']];
        }
        return join(", ", $results);
    }

    function formatResult($result, $output_result) {
        foreach ($result as $re) {
            $output_result[$re['name']] = $re['value'];
            if($output_result['Data Type Information URL'] != '') {
                $output_result['Data Type Keywords'] = "<a target='_blank' href='".$output_result['Data Type Information URL']."'>".$output_result['Data Type Keywords']."</a>";
            }
        }
        return $output_result;
    }

    function getSoftwareTableHTML($thead, $results, $community) {
        $html = '';
        $html .= '<table class="table table-striped">';
        $html .= $thead;
        $html .= '<tbody>';
        if(count($results) > 0){
            foreach ($results as $result){
                $html .= '<tr>';
                $html .= '<td class="showing ignore_shorten"><a target="_blank" href="'.$result['Resource URL'].'">'.$result['Resource Name'].'</a></td>';
                $html .= '<td class="showing ignore_shorten">';
                $html .= '<a target="_blank" href="'.$community->fullURL().'/data/record/nlx_144509-1/'.$result['rrid'].'/resolver">RRID:'.$result['rrid'];
                if(in_array($community->rinStyle()))
                    $html .= ' <span class="fa-stack fa-md" title="Resource Report"><i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i><i class="fa fa-globe fa-stack-1x fa-inverse"></i></span>';
                $html .= '</a>';
                $html .= '</td>';
                $html .= '<td class="showing">'.$result['Description'].'</td>';
                $html .= '<td class="showing ignore_shorten">'.$result['recommended by'].'</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }
?>

<?php if(count($project_keywords) == 1 && count($recommender_keywords) <= 1): ?>
<?php else: ?>
    <ul>
        <?php foreach ($project_keywords as $v1): ?>
            <?php if($recommender_keywords_flag): ?>
                <?php foreach ($recommender_keywords as $v2): ?>
                    <li>
                        <a href="#<?php echo $v1.'-'.$v2 ?>-repositories">
                            <?php echo "$v1 $v2" ?>-recommended software tools
                            <?php if(count($related_id1s_name[$v1]) > 0): ?>
                                (<?php echo join(", ", $related_id1s_name[$v1]) ?>)
                            <?php endif ?>
                        </a>
                    </i>
                <?php endforeach ?>
            <?php else: ?>
                <li>
                    <a href="#<?php echo $v1 ?>-repositories">
                        <?php echo "$v1" ?>-recommended software tools
                        <?php if(count($related_id1s_name[$v1]) > 0): ?>
                            (<?php echo join(", ", $related_id1s_name[$v1]) ?>)
                        <?php endif ?>
                    </a>
                </i>
            <?php endif ?>
        <?php endforeach ?>
    </ul>
<?php endif ?>

<P><br><br><p>

<?php foreach ($project_keywords as $v1): ?>
    <?php if($recommender_keywords_flag): ?>
        <?php foreach ($recommender_keywords as $v2): ?>
            <div>
                <a id="<?php echo $v1.'-'.$v2 ?>-software"></a>
                <h2><?php echo "$v1 $v2" ?>-recommended software tools</h2>
                <?php echo getSoftwareTableHTML($software_thead, $software[$v1][$v2], $community) ?>
            </div>
            <p><br><br></p>
        <?php endforeach ?>
    <?php else: ?>
        <div>
            <a id="<?php echo $v1 ?>-software"></a>
            <h2><?php echo $v1 ?>-recommended software tools</h2>
            <?php echo getSoftwareTableHTML($software_thead, $software[$v1][$v1], $community) ?>
        </div>
        <p><br><br></p>
    <?php endif ?>
<?php endforeach ?>

<!-- design popup layout and information -->
<div class="modal fade" id="repositoryPopup" tabindex="-1" role="dialog" aria-labelledby="c-pop-up-switching-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <a class="close dark less-right" data-dismiss="modal" aria-label="Close" style="color: red"><i class="fa fa-window-close" aria-hidden="true"></i> Close</a>
            </div>
            <div class="modal-body"></div>

            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div> -->
        </div>
    </div>
</div>

<script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.LinkButton').click(function(){
                // you can parse this.value to get the link text and URL
                $('.modal-body').html(this.value);
                $('#repositoryPopup').modal('show');
            });
        });
</script>
