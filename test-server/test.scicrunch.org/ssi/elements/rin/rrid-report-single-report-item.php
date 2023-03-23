<?php
include 'process-elastic-search.php';

$community = $data["community"];
$user = $data["user"];
$report_id = $data["report-id"];
$uuid = $data["uuid"];

$report = RRIDReport::loadBy(Array("id", "uid"), Array($report_id, $user->id));
if(is_null($report)) {
    return;
} else {
    $report_name = $report->name;
    $item = RRIDReportItem::loadBy(Array("uuid", "rrid_report_id"), Array($uuid, $report->id));
    if(is_null($item)) {
        return;
    } else {
        $item_name = $item->getData("name", false);
        if($item->updated_flag == 1) {
            $item->updated_flag = 0;
            $item->updateDB();
        }
    }
}

$used_subtypes_set = Array();
foreach($item->subtypes() as $subtype) {
    $used_subtypes_set[$subtype->subtype] = true;
}

$report_url = $community->fullURL() . "/rin/rrid-report/" . $report->id;

?>

<?php ob_start(); ?>
<div class="profile container content">
    <div class="row">
        <div class="col-md-12">
            <div class="profile-body">
                <a href="<?php echo $report_url ?>"><button class="btn btn-primary">Return to dashboard</button></a>
                <!-- item data -->
                <h2><?php echo $item->getData("name", false) ?></h2>
                <dl class="dl-horizontal">
                    <?php foreach(RRIDReportItem::$allowed_types[$item->type]["rrid-data-cols"] as $col): ?>
                      <?php if ($col == "Uid") break;   ## removed Uid column -- Vicky-2019-2-21 ?>
                        <dt><p><?php echo $col ?></p></dt>
                        <dd>
                          <p>
                            <?php
                              switch($col) {
                                case "References":   ## added reference link -- Vicky-2019-1-15
                                  echo implode(", ", buildLinks($item->getData($col, true), $community));
                                  break;
                                /*case "Proper Citation":   ## modified Proper Citation format -- Vicky-2019-1-23
                                  $values = explode(" (", $result->getField($field_name->name));
                                  if (count($values) > 1) echo "(".trim($values[0]).", ".$values[1];
                                  else echo $values[0];
                                  break;*/
                                case "Hierarchy":   ## modified "Hierarchy" & "Originate from Same Individual" -- Vicky-2019-1-31
                                case "Originate from Same Individual":
                                  if ($item->getData($col, true) != "CVCL:") echo str_replace(":", "_", $item->getData($col, true));
                                  break;
                                case "Comments":
                                  $comment = str_replace(['<font color="#ff6347"></> ', '<font color="#000000"></> '], "", $item->getData($col, true));
                                  if (strpos(strtolower($comment), "problematic") !== false || strpos(strtolower($comment), "discontinued") !== false) {
                                    $comment = "<font color='red'>".$comment."</font>";
                                  }
                                  echo $comment;
                                  break;
                                default:
                                  echo $item->getData($col, true);
                              }
                              //echo $item->getData($col, true);
                            ?>
                          </p>
                        </dd>
                    <?php endforeach ?>
                </dl>
                <hr>
                <?php if (RRIDReportItem::$allowed_types[$item->type]["pretty-type-name"] == "Cell line"): ?>
                  <h4>Cell Line Authentication Plan</h4>
                  <p>
                    The authentication plan for the cell lines is based on the International Cell Line Authentication Committee (ICLAC)'s "Cell Line Checklist for Manuscripts and Grant Applications"(1).
                  </p>
                  <ol type="A">
                    <li>
                      <p>Check dkNET for Misidentified Cell Lines</p>
                      <p>
                        To verify that this is not a false cell line, misidentified, or to check this is known to be an authentic stock, please check the <strong>Comments</strong> field. dkNET obtains this information from Cellosaurus(2).
                      </p>
                    </li>
                    <li>
                      <p>
                        Provide the following information in the Authentication of key biological and/or chemical resources attachment(3) in the grant application:
                      </p>
                      <ol type="a">
                        <li>
                          <p>Identification of cell lines by providing Name, Vendor, Catalog#, RRID (<strong>Proper Citation</strong> field)</p>
                        </li>
                        <li>
                          <p>Short Tandem Repeat (STR) Profiling</p>
                          <p>
                            The gold standard for authentication testing of cell lines is STR profiling. STR profiling should be performed and compared to results from donor tissue, or to online databases of cell line STR profiles. Authentication testing should be performed on established cell lines regardless of the application, and the test method and results included in the Materials and Methods section of any research publication. Testing should be done, at minimum, at the beginning and end of experimental work(4, 5).
                          </p>
                        </li>
                      </ol>
                    </li>
                  </ol>
                  <p>Sources:</p>
                  <ol>
                    <li>
                      <a target="_blank" href="http://iclac.org/resources/cell-line-checklist/">Cell Line Checklist for Manuscripts and Grant Applications</a>, International Cell Line Authentication Committee (ICLAC), <a target="_blank" href="http://iclac.org/wp-content/uploads/ICLAC_Cell-Line-Checklist_v1_2.docx">version 1.2</a>, updated on May 9, 2014.
                    </li>
                    <li>
                      <a target="_blank" href="https://web.expasy.org/cellosaurus/">Cellosaurus (https://web.expasy.org/cellosaurus/)</a> is a cell line knowledge resource and nomenclature authority. Cellosaurus covers the information of "ICLAC register of misidentified cell lines" (<a target="_blank" href="http://iclac.org/wp-content/uploads/Cross-Contaminations-v8_0.pdf">latest version 8.0</a>, released Dec. 1, 2016,) and provides more updated information.
                    </li>
                    <li>
                      <a target="_blank" href="https://grants.nih.gov/policy/reproducibility/guidance.htm">Guidance: Rigor and Reproducibility in Grant Applications</a>
                    </li>
                    <li>
                      <a target="_blank" href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC4907466/">Standards for Cell Line Authentication and Beyond, The National Institute of Standards and Technology (NIST)</a>.
                    </li>
                    <li>
                      Published standard: <a target="_blank" href="https://webstore.ansi.org/RecordDetail.aspx?sku=ANSI%2FATCC+ASN-0002-2011&gclid=EAIaIQobChMItvfblI6T3AIVkspkCh1yPguAEAAYASAAEgIaJfD_BwE">ANSI/ATCC ASN-0002-2011</a>, Authentication of Human Cell Lines: Standardization of STR Profiling, American National Standards Institute (ANSI).
                    </li>
                  </ol>
                <?php else: ?>
                  <h4>Antibody Authentication Plan</h4>
                  <p>
                    This authentication plan for antibodies is based on the methods suggested in "A proposal for validation of antibodies" (Uhlen M et. al., 2016)(1), the guideline published in the Journal of Comparative Neurology (Saper C, 2005)(2), and the Example Authentication of of Key Biological and/or Chemical Resources (Bandrowski A)(3).
                  </p>

                  <ol type="A">
                    <li>
                      <p>Check dkNET for Known Issues in Antibodies</p>
                      <p>
                        Please check <strong>Comments</strong> field to determine whether there are any known issue. dkNET obtains this information from the Antibody Registry(4).
                      </p>
                    </li>
                    <li>
                      <p>
                        Provide the following information in the Authentication of key biological and/or chemical resources attachment(5) in the grant application:
                      </p>
                      <ol type="a">
                        <li>
                          <p>Identification of antibodies by providing Name, Vendor, Catalog#, RRID (<strong>Proper Citation</strong> field)</p>
                        </li>
                        <li>
                          <p>Validation</p>
                          <ol type="i">
                            <li>
                              <p>Check <strong>Target Organism</strong>, <strong>Application</strong>, and <strong>Validation Information</strong></p>
                              <ol>
                                <li>
                                  <strong>Check Target Organism</strong> - Check if the target organisms in your planned experiments are listed in the Target Organism field. If they are different, you need to authenticate the specificity (see ii Suggested validation methods) in addition to appropriate experiment controls.
                                </li>
                                <li>
                                  <strong>Check Application in Comment field</strong> - Check if your planned applications are different from the applications listed in the Comments field. If they are different, you need to authenticate the specificity (see ii Suggested validation methods) in addition to appropriate experiment controls.
                                </li>
                                <li>
                                  <strong>Check Validation Information in Comment field</strong> - Check if the validation information is available in the Comments field. If the validation information is not available or unknown, you need to authenticate the specificity (see ii Suggested validation methods) in addition to appropriate experiment controls.
                                </li>
                              </ol>
                            </li><br>
                            <li>Suggested validation methods based on applications(1)</li>
                          </ol>
                        </li>
                      </ol>
                    </li>
                    <table class="table table-striped" width="100%">
                        <tr>
                          <th class="showing" style="width:15%;">Validation strategy</th>
                          <th class="showing" style="width:17%;">Genetic</th>
                          <th class="showing" style="width:17%;">Orthogonal</th>
                          <th class="showing" style="width:17%;">Independent antibody</th>
                          <th class="showing" style="width:17%;">Tagged protein expression</th>
                          <th class="showing" style="width:17%;">IMS</th>
                        </tr>
                        <tr>
                          <td class="showing">Validation principle</td>
                          <td class="showing">The expression of the target protein is eliminated or significantly reduced by genome editing or RNA interference</td>
                          <td class="showing">Expression of the target protein is compared with an antibody-independent method</td>
                          <td class="showing">Expression of the target protein is compared using two antibodies with nonoverlapping epitopes</td>
                          <td class="showing">The target protein is expressed using a tag, preferably expressed at endogenous levels</td>
                          <td class="showing">The target protein is captured using an antibody and analyzed using MS</td>
                        </tr>
                        <tr>
                          <td class="showing">Validation criteria</td>
                          <td class="showing">Elimination or significant reduction in antibody labeling after gene disruption or mRNA knockdown<br><br>
                            <a target="_blank" href="https://dknet.org/data/source/nlx_154697-1/search"><button class="btn btn-primary" style="white-space: normal; width:95%;">Search dkNET for knockout organism</button></a>
                          </td>
                          <td class="showing">Significant correlation of protein levels detected by an antibody and an orthogonal method (e.g., MS)</td>
                          <td class="showing">Significant correlation of protein levels detected by two different antibodies recognizing independent regions of the same target protein</td>
                          <td class="showing">Significant correlation between antibody labeling and detection of the epitope tag</td>
                          <td class="showing">Target protein peptides among the most abundant detected by MS following immunocapture</td>
                        </tr>
                        <tr>
                          <td class="showing">Suitable for these applications</td>
                          <td class="showing">WB, IHC, ICC, FS, SA, IP/ChIP, RP</td>
                          <td class="showing">WB, IHC, ICC, FS, SA, RP</td>
                          <td class="showing">WB, IHC, ICC, FS, SA, IP/ChIP, RP</td>
                          <td class="showing">WB, IHC, ICC, FS</td>
                          <td class="showing">IP/ChIP</td>
                        </tr>
                    </table>
                    <small>WB, western blot; IHC, immunohistochemistry; ICC, immunocytochemistry, including immunofluorescence microscopy; FS, flow sorting and analysis of cells; SA, sandwich assays, including ELISA; IP, immunoprecipitation; ChIP, chromatin immunoprecipitation; and RP, reverse-phase protein arrays.</small>
                  </ol><br>
                  <p>Sources:</p>
                  <ol>
                    <li><a target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/27595404">Uhlen M et. al. A proposal for validation of antibodies. Nature Methods, Oct;13(10):823-7. 2016.</a></li>
                    <li><a target="_blank" href="https://onlinelibrary.wiley.com/doi/full/10.1002/cne.20839">Saper C. An open letter to our readers on the use of antibodies. Journal of comparative neurology, 493(4):477-8, 2005.</a></li>
                    <li><a target="_blank" href="https://doi.org/10.6075/J0RB72JC">Bandrowski A.  Example Authentication of of Key Biological and/or Chemical Resources, NIH Policy on Rigor and Reproducibility Section, UC San Diego Library website.</a></li>
                    <li><a target="_blank" href="https://antibodyregistry.org">AntibodyRegistry (https://antibodyregistry.org)</a></li>
                    <li><a target="_blank" href="https://grants.nih.gov/policy/reproducibility/guidance.htm">Guidance: Rigor and Reproducibility in Grant Applications, National Institute of Health Office of Extramural Research Website.</a></li>
                  </ol>
                <?php endif ?>

                <?php ## hid use and user data -- Vicky-2019-2-8 ?>
                <!--<?php if(RRIDReportItem::$allowed_types[$item->type]["subtypes"]): ?>
                    <h4>Uses</h4>
                    <?php if(!empty($used_subtypes_set)): ?>
                        <table class="table col-xl-6 col-sm-12">
                            <?php foreach($used_subtypes_set as $subtype => $true): ?>
                                <tr>
                                    <td>
                                        <?php echo $subtype ?>
                                    </td>
                                    <td>
                                        <form action="/forms/rrid-report-forms/delete-report-item-subtype.php" method="POST">
                                            <input type="hidden" name="rrid-report-id" value="<?php echo $item->report()->id ?>" />
                                            <input type="hidden" name="type" value="<?php echo $item->type ?>" />
                                            <input type="hidden" name="subtype" value="<?php echo $subtype ?>" />
                                            <input type="hidden" name="uuid" value="<?php echo $item->uuid ?>" />
                                            <input type="submit" value="Delete" class="btn btn-danger" />
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </table>
                    <?php endif ?>
                    <?php if(count(RRIDReportItem::$allowed_types[$item->type]["subtypes"]) > count($used_subtypes_set)): ?>
                        <div class="row">
                            <form action="/forms/rrid-report-forms/add-report-item-subtype.php" method="POST">
                                <div class="col-md-4">
                                    <select class="form-control" name="subtype">
                                        <option disabled selected value> -- Select a usage -- </option>
                                        <?php foreach(RRIDReportItem::$allowed_types[$item->type]["subtypes"] as $subtype_name => $subtype_val): ?>
                                            <?php if(!isset($used_subtypes_set[$subtype_name])): ?>
                                                <option value="<?php echo $subtype_name ?>"><?php echo $subtype_name ?></option>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="hidden" name="rrid-report-id" value="<?php echo $item->report()->id ?>" />
                                    <input type="hidden" name="type" value="<?php echo $item->type ?>" />
                                    <input type="hidden" name="rrid" value="<?php echo $item->rrid ?>" />
                                    <input type="submit" value="Add" class="btn btn-success" />
                                </div>
                            </form>
                        </div>
                    <?php endif ?>
                <?php endif ?>-->

                <!-- USER DATA -->
                <!--<form id="user-data-form" method="POST" action="/forms/rrid-report-forms/update-user-data.php">
                    <input type="hidden" name="item-id" value="<?php echo $item->id ?>" />-->

                    <!-- TYPES -->
                    <!--<?php
                        $user_data_types = $item->userDataTypes();
                    ?>
                    <?php if($user_data_types): ?>
                        <hr/>
                        <h4><?php echo RRIDReportItem::$allowed_types[$item->type]["pretty-type-name"] ?> user data</h4>
                        <?php foreach($user_data_types as $key => $udt): ?>
                            <?php
                                $user_data_fields = true;
                                $existing_val = isset($udt["existing"]) ? $udt["existing"]->data : "";
                                $classes = "";
                                if($udt["type"] == "literature") {
                                    $classes .= "validate-literature";
                                }
                            ?>
                            <div class="form-group <?php if($udt["group"]) echo "js-rrid-report-item-group-item" ?>">
                                <div
                                    <?php if($udt["group"]) echo 'data-group="' . $udt["group"] . '"' ?>
                                    <?php if($udt["group-choice"]) echo 'data-group-choice="' . $udt["group-choice"] . '"' ?>
                                    data-type="<?php echo $item->type ?>"
                                >
                                    <p>
                                        <strong>
                                            <u><?php echo $udt["name"] ?></u>
                                            <?php if($udt["required"]): ?>
                                                <span class="text-danger">*suggested</span>
                                            <?php endif ?>
                                        </strong><br/>
                                        <?php echo $udt["description"] ?>
                                    </p>
                                    <?php if($udt["type"] == "group-select"): ?>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <select name="type|<?php echo $item->id ?>|<?php echo $key ?>" class="form-control js-rrid-report-item-group-select" data-name="<?php echo $key ?>" data-type="<?php echo $item->type ?>">
                                                    <option <?php if($existing_val == "") echo "selected"; ?> disabled value> -- Select an option -- </option>
                                                    <?php foreach($udt["group-choices"] as $gc_key => $gc_val): ?>
                                                        <?php $selected = $gc_key == $existing_val ? 'selected="selected"' : ""; ?>
                                                        <option <?php echo $selected ?> value="<?php echo $gc_key ?>"><?php echo $gc_val ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php elseif($udt["type"] == "text" || $udt["type"] == "literature"): ?>
                                        <input
                                            type="text"
                                            name="type|<?php echo $item->id . "|" . $key ?>"
                                            value="<?php echo $existing_val ?>"
                                            class="form-control <?php echo $classes ?>"
                                        />
                                        <div class="error-msg" style="color:red"></div>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>-->
                    <!-- /TYPES -->

                    <!-- SUBTYPES -->
                    <!--<?php foreach($item->subtypes() as $subtype): ?>
                        <?php
                            $user_data_types = $subtype->userDataTypes();
                            if(!$user_data_types) continue;
                            $user_data_fields = true;
                        ?>
                        <hr/>
                        <h4>
                            <?php echo $subtype->subtype ?>
                            <span
                                class="btn btn-danger js-rrid-report-item-delete-subtype"
                                data-report-id="<?php echo $item->report()->id ?>"
                                data-type="<?php echo $item->type ?>"
                                data-subtype="<?php echo $subtype->subtype ?>"
                                data-uuid="<?php echo $item->uuid ?>"
                            >
                                Delete
                            </span>
                        </h4>
                        <?php foreach($user_data_types as $key => $udt): ?>
                            <?php
                                $existing_val = isset($udt["existing"]) ? $udt["existing"]->data : "";
                                $classes = "";
                                if($udt["type"] == "literature") {
                                    $classes .= "validate-literature";
                                }
                            ?>
                            <div class="form-group <?php if($udt["group"]) echo "js-rrid-report-item-group-item" ?>">
                                <div
                                    <?php if($udt["group"]) echo 'data-group="' . $udt["group"] . '"' ?>
                                    <?php if($udt["group-choice"]) echo 'data-group-choice="' . $udt["group-choice"] . '"' ?>
                                    data-type="<?php echo $subtype->subtype ?>"
                                >
                                    <p>
                                        <strong>
                                            <u><?php echo $udt["name"] ?></u>
                                            <?php if($udt["required"]): ?>
                                                <span class="text-danger">*suggested</span>
                                            <?php endif ?>
                                        </strong><br/>
                                        <?php echo $udt["description"] ?>
                                    </p>
                                    <?php if($udt["type"] == "group-select"): ?>
                                        <select name="subtype|<?php echo $subtype->id ?>|<?php echo $key ?>" class="form-control js-rrid-report-item-group-select" data-name="<?php echo $key ?>" data-type="<?php echo $subtype->subtype ?>">
                                            <option <?php if($existing_val == "") echo "selected"; ?> disabled value> -- Select an option -- </option>
                                            <?php foreach($udt["group-choices"] as $gc_key => $gc_val): ?>
                                                <?php $selected = $gc_key == $existing_val ? 'selected="selected"' : ""; ?>
                                                <option <?php echo $selected ?> value="<?php echo $gc_key ?>"><?php echo $gc_val ?></option>
                                            <?php endforeach ?>
                                        </select>
                                    <?php elseif($udt["type"] == "text" || $udt["type"] == "literature"): ?>
                                        <input
                                            type="text"
                                            name="subtype|<?php echo $subtype->id . "|" . $key ?>"
                                            value="<?php echo $existing_val ?>"
                                            class="form-control <?php echo $classes ?>"
                                        />
                                        <div class="error-msg" style="color:red"></div>
                                    <?php endif ?>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endforeach ?>-->
                    <!-- /SUBTYPES -->

                    <!--<?php if($user_data_fields): ?>
                        <button class="btn btn-primary">Update</button>
                    <?php endif ?>
                </form>-->
                <!-- /USER DATA -->

            </div>
        </div>
    </div>
</div>
<?php $report_html = ob_get_clean(); ?>

<?php

$report_data = Array(
    "title" => "Authentication Report Item",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports", "url" => $community->fullURL() . "/rin/rrid-report"),
        Array("text" => "Report Dashboard", "url" => $community->fullURL() . "/rin/rrid-report/overview"),
        Array("text" => $report_name, "url" => $community->fullURL() . "/rin/rrid-report/" . $report->id),
        Array("text" => $item_name, "active" => true),
    ),
    "html-body" => $report_html,
);

echo \helper\htmlElement("rin-style-page", $report_data);

?>

<script>
    $("#user-data-form").submit(function(event) {
        var valid = true;

        $("#user-data-form .validate-literature").each(function(idx) {
            var val = $(this).val();
            if(!!val && !mentionIDFormat(val)) {
                valid = false;
                $(this).siblings(".error-msg").text("Please give publications the following format: PMID:123456 or DOI:12.34.56");
            } else {
                $(this).siblings(".error-msg").text("");
            }
        });

        if(!valid) {
            event.preventDefault();
        }
    });
</script>
