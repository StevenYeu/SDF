<?php

$report = $data["report"];

if(!is_null($report)) {
    $report_items = RRIDReportItem::loadArrayBy(Array("rrid_report_id"), Array($report->id));
}

$report_item_types = Array(
    "cellline" => Array(
        "items" => Array(),
        "good" => Array(),
        "problematic" => Array(),
        "discontinued" => Array(),
    ),
    "antibody" => Array(
        "items" => Array(),
        "discontinued" => Array(),
        "subtypes" => Array(),
    ),
);
foreach($report_items as $ri) {
    if($ri->type == "cellline") {
        $report_item_types["cellline"]["items"][] = $ri;
        $idx = count($report_item_types["cellline"]["items"]) - 1;
        $good = true;
        if(strpos(strtolower($ri->getData("Comments", true)), "problematic") !== false) {
            $report_item_types["cellline"]["problematic"][$idx] = true;
            $good = false;
        }
        if(strpos(strtolower($ri->getData("Comments", true)), "discontinued") !== false) {
            $report_item_types["cellline"]["discontinued"][$idx] = true;
            $good = false;
        }
        if($good) {
            $report_item_types["cellline"]["good"][$idx] = true;
        }
        if(strpos(strtolower($ri->getData("Organism", true)), "homo sapiens") !== false) {
            $report_item_types["cellline"]["homo sapiens"][$idx] = true;
        } else {
            $report_item_types["cellline"]["not homo sapiens"][$idx] = true;
        }
    } elseif($ri->type == "antibody") {
        $report_item_types["antibody"]["items"][] = $ri;
        $idx = count($report_item_types["antibody"]["items"]) - 1;
        if(strpos(strtolower($ri->getData("Comments", true)), "discontinued") !== false) {
            $report_item_types["antibody"]["discontinued"][$idx] = true;
        }
        foreach($ri->subtypes() as $subtype) {
            if(!isset($report_item_types["antibody"]["subtypes"][$subtype->subtype])) $report_item_types["antibody"]["subtypes"][$subtype->subtype] = Array();
            $report_item_types["antibody"]["subtypes"][$subtype->subtype][$idx] = true;
        }
    }
}

$current_time = time();
$date_string = date("d M Y", $current_time);
$missing_text = '<span style="background-color:red;color:white">MISSING</span>';

$intToRoman = Array(1 => "I", 2 => "II", 3 => "III", 4 => "IV", 5 => "V", 6 => "VI", 7 => "VII", 8 => "VIII", 9 => "IX", 10 => "X");
$intToLowercase = Array(1 => "a", 2 => "b", 3 => "c", 4 => "d", 5 => "e", 6 => "f", 7 => "g", 8 => "h", 9 => "i", 10 => "j");

function antibodyLiteratureHTML($idx_array, $items, $subtype, $data_name) {
    $html = "";
    if(empty($idx_array)) return $html;
    foreach($idx_array as $ia => $tmp) {
        $item = $items[$ia];
        $subtype_obj = $item->subtype($subtype);
        if(!$subtype_obj) continue;

        $pub_id = $subtype_obj->getUserData($data_name)->data;
        $url = buildPubURL($pub_id);
        if($url === false) continue;

        $item_html = 'This validation technique was successfully used by our group for the antibody ' . $item->getData("Antibody Name", true) . ' and can be accessed at <a href="' . $url . '">' . $url . '</a>';
        $item_html = "<li>" . $item_html . "</li>";

        $html .= $item_html;
    }

    if($html) $html = "<ul>" . $html . "</ul>";
    return $html;
}

function antibodyValidationTechniques($idx_array, $items, $subtype) {
    $validation_used = Array();
    foreach(array_keys($idx_array) as $idx) {
        foreach($items[$idx]->subtypes() as $st) {
            if($st->subtype == $subtype) {
                foreach($st->userData() as $ud) {
                    if($ud->name == "validation-select" && $ud->data) {
                        $validation_used[$ud->data] = true;
                    }
                }
            }
        }
    }

    ob_start(); ?>

    <ul>
        <?php foreach(array_keys($idx_array) as $idx): ?>
            <li><?php echo $items[$idx]->getData("Antibody Name", true)." (RRID:".$items[$idx]->getData("Antibody ID", true).")" ?></li>
        <?php endforeach ?>
    </ul>
    <?php if($validation_used["genetic-method"]): ?>
        <p><strong>[Genetic method]:</strong> <?php echo RRIDReportItem::reportTexts("antibody-genetic-method") ?></p>
        <?php echo antibodyLiteratureHTML($idx_array, $items, $subtype, "genetic-method-pub") ?>
    <?php endif ?>
    <?php if($validation_used["endogenous-expression"]): ?>
        <p><strong>[Endogenous expression]:</strong> <?php echo RRIDReportItem::reportTexts("antibody-endogenous-expression") ?></p>
        <?php echo antibodyLiteratureHTML($idx_array, $items, $subtype, "endogenous-expression-pub") ?>
    <?php endif ?>
    <?php if($validation_used["orthogonal-methods"]): ?>
        <p><strong>[Orthogonal methods]:</strong> <?php echo RRIDReportItem::reportTexts("antibody-orthogonal-methods") ?></p>
        <?php echo antibodyLiteratureHTML($idx_array, $items, $subtype, "orthogonal-methods-pub") ?>
    <?php endif ?>
    <?php if($validation_used["mass-spec-ip"]): ?>
        <p><strong>[Mass spectrometry and immunoprecipitation]:</strong> <?php echo RRIDReportItem::reportTexts("antibody-mass-spec-ip") ?></p>
        <?php echo antibodyLiteratureHTML($idx_array, $items, $subtype, "mass-spec-ip-pub") ?>
    <?php endif ?>
    <?php if($validation_used["independent-antibodies"]): ?>
        <p><strong>[Independent antibodies]:</strong> <?php echo RRIDReportItem::reportTexts("antibody-independent-antibodies") ?></p>
        <?php echo antibodyLiteratureHTML($idx_array, $items, $subtype, "independent-antibodies-pub") ?>
    <?php endif ?>

    <?php
    return ob_get_clean();
}

function buildPubURL($pub_id) {
    if(\helper\startsWith($pub_id, "PMID:")) {
        $pmid = preg_replace("/PMID: ?/", "", $pub_id);
        $url = "https://www.ncbi.nlm.nih.gov/pubmed/" . $pmid;
    } elseif(\helper\startsWith($pub_id, "DOI:")) {
        $url = "https://dx.doi.org/" . $pub_id;
    } else {
        return false;
    }
    return $url;
}

function cleanData($data){
    $data = str_replace('<td style="padding-right: 5px">CVCL:</td>', '<td style="padding-right: 5px"></td>', $data);
    $data = str_replace('<td style="padding-right: 5px">, </td>', '<td style="padding-right: 5px"></td>', $data);
    $data = explode("</tr>", $data);
    unset($data[count($data) - 2]); ## remove Uid
    return join("</t>", $data);
}

?>

<style>
    .indent-group {
        padding-left: 30px;
    }
</style>
<div style="padding:15px;border: 1px solid black">
    Date: <?php echo $date_string ?>
    <h1>Authentication of Key Biological Resources</h1>
    <hr/>
    <?php if(!empty($report_item_types["cellline"]["items"]) && !empty($report_item_types["antibody"]["items"])): ?>
        <h2>Table of Content</h2>
        <a href="#cellline"><h3>I. Cell Lines</h3></a>
        <a href="#antibodies"><h3>II. Antibodies</h3></a>
        <a href="#resource-index"><h3>III. Resource Index</h3></a>
    <?php elseif(!empty($report_item_types["cellline"]["items"])): ?>
        <h2>Table of Content</h2>
        <a href="#cellline"><h3>I. Cell Lines</h3></a>
        <a href="#resource-index"><h3>II. Resource Index</h3></a>
    <?php elseif(!empty($report_item_types["antibody"]["items"])): ?>
        <h2>Table of Content</h2>
        <a href="#antibodies"><h3>I. Antibodies</h3></a>
        <a href="#resource-index"><h3>II. Resource Index</h3></a>
    <?php endif ?>
    <hr/>
    <?php if(!empty($report_item_types["cellline"]["items"])): ?>
        <?php $level1_counter += 1; ?>
        <h2 id="cellline"><?php echo $intToRoman[$level1_counter] ?>. Cell Lines</h2>
        <div class="indent-group">
            <p>
              The authentication plan for the cell lines is based on the International Cell Line Authentication Committee (ICLAC)'s "Cell Line Checklist for Manuscripts and Grant Applications"(1).
            </p>
            <h3>A. Identification of Cell Lines</h3>
            <div class="indent-group">
                <p>The following cell lines will be used in the proposed studies:</p>
                <table class="table">
                    <tr>
                        <th>Name</th>
                        <th></th>
                        <th>RRID</th>
                        <th>Vendor</th>
                        <th>Catalog number</th>
                        <th>Species</th>
                        <th>Sex</th>
                        <th>Alerts</th>
                    </tr>
                    <?php foreach($report_item_types["cellline"]["items"] as $i => $item): ?>
                        <tr>
                            <td>
                                <?php ## changed data structure names -- Vicky-2019-1-2 ?>
                                <?php if(isset($report_item_types["cellline"]["problematic"][$i]) || isset($report_item_types["cellline"]["discontinued"][$i])): ?>
                                    <!-- <span style="color:red">&#x26A0;</span> -->
                                    <i class="text-danger fa fa-exclamation-triangle"></i>
                                <?php endif ?>
                                <a class="external" target="_blank" href="http://web.expasy.org/cellosaurus/<?php echo $item->getData("ID", true) ?>"><?php echo $item->getData("Name", true) ?></a>
                            </td>
                            <td>
                              <a target="_blank" href="/data/record/SCR_013869-1/<?php echo $item->getData("ID", true) ?>/resolver?i=<?php echo $item->getData("Uid", true) ?>" data-toggle="tooltip" title="Resource report">
                                <span class="fa-stack fa-md">
                                  <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
                                  <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
                                </span>
                              </a>
                            </td>
                            <td>
                                RRID:<?php echo $item->getData("ID", true) ?>
                            </td>
                            <td><?php echo $item->getData("Vendor", true) ?></td>
                            <td><?php echo $item->getData("Catalog Number", true) ?></td>
                            <td><?php echo $item->getData("Organism", true) ?></td>
                            <td><?php echo $item->getData("Sex", true) ?></td>
                            <td>
                                <ul>
                                    <?php if(isset($report_item_types["cellline"]["problematic"][$i])): ?>
                                        <li style="color:red">Problematic cell line</li>
                                    <?php endif ?>
                                    <?php if(isset($report_item_types["cellline"]["discontinued"][$i])): ?>
                                        <li style="color:red">Discontinued cell line</li>
                                    <?php endif ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
                <p>Source: Cellosaurus (<a href="https://web.expasy.org/cellosaurus/x">https://web.expasy.org/cellosaurus/</a>)</p><br>
                <p>
                  To verify that this is not a false cell line, misidentified, or to check this is known to be an authentic stock, check table above to see if there are any alerts, such as problematic cell lines or discontinued cell lines. dkNET obtains this information from Cellosaurus(3). Click resource name to access additional information from Cellosaurus.
                </p>
                <!-- <?php
                    $cellline_publications = Array();
                    $cellline_passages = Array();
                    foreach($report_item_types["cellline"]["items"] as $item) {
                        $validation_type = $item->getUserData("validation-select");
                        if($validation_type->data == "publication") {
                            $pub_id = $item->getUserData("cell-line-pub");
                            $cellline_url = buildPubURL($pub_id->data);
                            if($cellline_url === false) continue;
                            $cellline_publications[] = 'The establishment and characterization of the "' . $item->getData("Name", true) . '" was described in detail by the following publications:  <a href="' . $cellline_url . '">' . $cellline_url . '</a>';
                        } elseif($validation_type->data == "text") {
                            $cellline_passages[] = Array(
                                "name" => $item->getData("Name", true),
                                "medium" => $item->getUserData("cell-line-validation-medium")->data ?: $missing_text,
                                "growth" => $item->getUserData("cell-line-validation-growth")->data ?: $missing_text,
                                "passage" => $item->getUserData("cell-line-validation-passage")->data ?: $missing_text,
                            );
                        }
                    }
                ?> -->
                <!-- <?php if(!empty($cellline_publications)): ?>
                    <ul>
                        <?php foreach($cellline_publications as $cp): ?>
                            <li><?php echo $cp ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?> -->
                <?php if(!empty($cellline_passages)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Passage No. or Population Doubling Level (PDL)</th>
                                <th>Growth Medium</th>
                                <th>Additional Growth Requirements</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($cellline_passages as $cp): ?>
                                <tr>
                                    <td><?php echo $cp["name"] ?></td>
                                    <td><?php echo $cp["passage"] ?></td>
                                    <td><?php echo $cp["medium"] ?></td>
                                    <td><?php echo $cp["growth"] ?></td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                <?php endif ?>
            </div>
        </div>
        <div class="indent-group">
            <h3>B. Authentication Plan</h3>
            <div class="indent-group">
                <p>
                  The gold standard for authentication testing of cell lines is Short Tandem Repeat (STR) profiling. STR profiling should be performed and compared to results from donor tissue, or to online databases of cell line STR profiles. Authentication testing should be performed on established cell lines regardless of the application, and the test method and results included in the Materials and Methods section of any research publication. Testing should be done, at minimum, at the beginning and end of experimental work(4, 5).
                </p>
                <!--<p>
                    Authentication plan for the cell lines is based on International Cell Line Authentication Committee (ICLAC)'s "Cell Line Checklist for Manuscripts and Grant Applications" <a href="http://iclac.org/resources/cell-line-checklist/">http://iclac.org/resources/cell-line-checklist/</a>.
                    The latest version (version 1.2) of the ICLAC Cell line checklist was updated on May 9, 2014 (<a href="http://iclac.org/wp-content/uploads/ICLAC_Cell-Line-Checklist_v1_2.docx">http://iclac.org/wp-content/uploads/ICLAC_Cell-Line-Checklist_v1_2.docx</a>).
                </p>
                <h4>1. Check Misidentified Cell Lines</h4>
                <div class="indent-group">
                    <p>
                        To verify that this is not a false cell line, misidentified, or to check this is known to be an authentic stock, we will periodically check the cell line web pages at Cellosaurus (<a href="https://web.expasy.org/cellosaurus/">https://web.expasy.org/cellosaurus/</a>), a cell line knowledge resource and nomenclature authority.
                        Cellosaurus covers the information of "ICLAC register of misidentified cell lines" (latest version 8.0, released Dec. 1, 2016, <a href="http://iclac.org/wp-content/uploads/Cross-Contaminations-v8_0.pdf">http://iclac.org/wp-content/uploads/Cross-Contaminations-v8_0.pdf</a>) and provides more updated information.
                        <ul>
                            <?php
                                foreach($report_item_types["cellline"]["items"] as $x) {
                                    $url = "http://web.expasy.org/cellosaurus/" . $x->getData("ID", true);
                                    $name = $x->getData("Name", true);
                                    echo '<li>' . $name . ' (RRID: '.$x->getData("ID", true).', <a href="' . $url . '">' . $url . '</a>)</li>';
                                }
                            ?>
                        </ul>
                    </p>
                </div>
                <h4>2. Authenticate the Sample</h4>
                <div class="indent-group">
                    <p>
                        Authentication testing will be performed on established cell lines regardless of the application, and the test method and results included in the Materials and Methods section.
                        Testing will be done, at minimum, at the beginning and end of experimental work.
                    </p>
                </div>
                <h4>3. Short Tandem Repeat (STR) Profiling</h4>
                <div class="indent-group">
                    <?php if(!empty($report_item_types["cellline"]["homo sapiens"])): ?>
                        <p>
                            For human cell lines: STR will be performed and compared to results from donor tissue, or to online databases of human cell line STR profiles.
                        </p>
                        <p>
                            Detailed information can be found in the paper "Standards for Cell Line Authentication and Beyond" (<a href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC4907466/">https://www.ncbi.nlm.nih.gov/pmc/articles/PMC4907466/</a>), published by The national Institute of Standards and Technology (NIST), and published standard: ANSI/ATCC ASN-0002-2011, Authentication of Human Cell Lines:
                            Standardization of STR Profiling, published by American National Standards Institute (ANSI) (<a href="https://webstore.ansi.org/RecordDetail.aspx?sku=ANSI%2FATCC+ASN-0002-2011&gclid=EAIaIQobChMItvfblI6T3AIVkspkCh1yPguAEAAYASAAEgIaJfD_BwE">https://webstore.ansi.org/RecordDetail.aspx?sku=ANSI%2FATCC+ASN-0002-2011&gclid=EAIaIQobChMItvfblI6T3AIVkspkCh1yPguAEAAYASAAEgIaJfD_BwE</a>).
                        </p>
                    <?php endif ?>
                    <?php if(!empty($report_item_types["cellline"]["not homo sapiens"])): ?>
                        <p>
                            For non-human cell lines, best practice will vary with the species being tested. At minimum, species should be confirmed using an appropriate method such as karyotyping, isoenzyme analysis, or mitochondrial DNA typing (DNA barcoding).
                        </p>
                    <?php endif ?>
                </div>-->
            </div><br>
            <p>Sources:</p>
            <ol>
              <li>
                <a target="_blank" href="http://iclac.org/resources/cell-line-checklist/">Cell Line Checklist for Manuscripts and Grant Applications</a>, International Cell Line Authentication Committee (ICLAC), <a target="_blank" href="http://iclac.org/wp-content/uploads/ICLAC_Cell-Line-Checklist_v1_2.docx">version 1.2</a>, updated on May 9, 2014.
              </li>
              <li>
                <a target="_blank" href="https://grants.nih.gov/policy/reproducibility/guidance.htm">Guidance: Rigor and Reproducibility in Grant Applications</a>
              </li>
              <li>
                <a target="_blank" href="https://web.expasy.org/cellosaurus/">Cellosaurus (https://web.expasy.org/cellosaurus/)</a> is a cell line knowledge resource and nomenclature authority. Cellosaurus covers the information of "ICLAC register of misidentified cell lines" (<a target="_blank" href="http://iclac.org/wp-content/uploads/Cross-Contaminations-v8_0.pdf">latest version 8.0</a>, released Dec. 1, 2016,) and provides more updated information.
              </li>
              <li>
                <a target="_blank" href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC4907466/">Standards for Cell Line Authentication and Beyond, The National Institute of Standards and Technology (NIST)</a>.
              </li>
              <li>
                Published standard: <a target="_blank" href="https://webstore.ansi.org/RecordDetail.aspx?sku=ANSI%2FATCC+ASN-0002-2011&gclid=EAIaIQobChMItvfblI6T3AIVkspkCh1yPguAEAAYASAAEgIaJfD_BwE">ANSI/ATCC ASN-0002-2011</a>, Authentication of Human Cell Lines: Standardization of STR Profiling, American National Standards Institute (ANSI).
              </li>
            </ol>
        </div>
    <?php endif ?>
    <?php if(!empty($report_item_types["antibody"]["items"])): ?>
        <?php
            $level1_counter += 1;
            $level3_counter = 0
        ?>
        <hr/>
        <h2 id="antibodies"><?php echo $intToRoman[$level1_counter] ?>. Antibodies</h2>
        <div class="indent-group">
            <p>
              This authentication plan for antibodies is based on the methods suggested in "A proposal for validation of antibodies" (Uhlen M et. al., 2016)(1), the guideline published in the Journal of Comparative Neurology (Saper C, 2005)(2), and the Example Authentication of of Key Biological and/or Chemical Resources (Bandrowski A)(3).
            </p>
            <h3>A. Identification of Antibodies</h3>
            <div class="indent-group">
                <p>The following antibodies will be used in the proposed studies:</p>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th></th>
                            <th>RRID</th>
                            <th>Vendor</th>
                            <th>Catalog Number</th>
                            <th>Target Organism</th>
                            <th>Comments</th>
                            <th>Alerts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($report_item_types["antibody"]["items"] as $item): ?>
                            <tr>
                                <?php ## changed data structure names -- Vicky-2019-1-2 ?>
                                <td>
                                  <?php if (strpos(strtolower($item->getData("Comments", true)), "discontinued") !== false || strpos(strtolower($item->getData("Antibody Name", true)), "discontinued") !== false): ?>
                                      <i class="text-danger fa fa-exclamation-triangle"></i>
                                  <?php endif ?>
                                  <a class="external" target="_blank" href="http://antibodyregistry.org/<?php echo $item->getData("Antibody ID", true) ?>"><?php echo $item->getData("Antibody Name", true) ?></a>
                                </td>
                                <td>
                                  <a target="_blank" href="/data/record/nif-0000-07730-1/<?php echo $item->getData("Antibody ID", true) ?>/resolver?i=<?php echo $item->getData("Uid", true) ?>" data-toggle="tooltip" title="Resource report">
                                    <span class="fa-stack fa-md">
                                      <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
                                      <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
                                    </span>
                                  </a>
                                </td>
                                <td>RRID:<?php echo $item->getData("Antibody ID", true) ?></td>
                                <td><?php echo $item->getData("Vendor", true) ?></td>
                                <td><?php echo $item->getData("Catalog Number", true) ?></td>
                                <td><?php echo $item->getData("Target Organism", true) ?></td>
                                <!--<td>
                                    <?php if($item->subtypes()): ?>
                                        <?php
                                        $subtype_array = Array();
                                        foreach($item->subtypes() as $subtype) {
                                            $subtype_array[] = $subtype->subtype;
                                        }
                                        echo implode(", ", $subtype_array);
                                        ?>
                                    <?php endif ?>
                                </td>-->
                                <td><?php echo $item->getData("Comments", true) ?></td>
                                <td>
                                  <ul>
                                      <?php if (strpos(strtolower($item->getData("Comments", true)), "discontinued") !== false || strpos(strtolower($item->getData("Antibody Name", true)), "discontinued") !== false): ?>
                                          <li style="color:red">Discontinued antibody</li>
                                      <?php endif ?>
                                  </ul>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
                <p>Source: Antibody Registry (<a href="http://antibodyregistry.org/">http://antibodyregistry.org/</a>)</p><br>
                <p>
                  Check table above to see if there are any warning signs, such as discontinued antibodies, to determine whether there are any known issue. dkNET obtains this information from the Antibody Registry(5). Click resource name to access additional information from the Antibody Registry.
                </p>
            </div>
        </div>
        <div class="indent-group">
            <h3>B. Validation</h3>
              <ol type="a">
                  <li>
                    <p>Antibody validation must be carried out in an application- and context-specific manner (Uhlen et al., 2016), i.e., just because you used an antibody successfully in one application, doesn’t mean it will translate.  To validate an antibody, it must be shown to be specific, selective, and reproducible in the context for which it is to be used (<a target="_blank" href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3891910/">Bordeaux et al., 2014</a>).  See b. Suggested validation methods.  When selecting an antibody to use, pay special attention to the following:</p>
                    <ol type="i">
                      <li>
                        <strong>Check Target Organism</strong> - Check to see if the antibody has been developed for and tested in the target species for your experiment in the Target Organism field.
                      </li>
                      <li>
                        <strong>Check Application</strong> - Check if your planned applications included in the recommended applications provided by the vendor listed in the Comments field. We do not recommend using an antibody that has not been tested for a specific application.
                      </li>
                      <li>
                        <strong>Check Validation Information</strong> - Check validation information to see if it is a widely used reagent or if any concerns have been raised.
                      </li>
                    </ol>
                  </li>
                  <br>
                  <li>
                    <p>Suggested validation methods based on applications(1)</p>
                  </li>
              </ol>
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
              WB, western blot; IHC, immunohistochemistry; ICC, immunocytochemistry, including immunofluorescence microscopy; FS, flow sorting and analysis of cells; SA, sandwich assays, including ELISA; IP, immunoprecipitation; ChIP, chromatin immunoprecipitation; and RP, reverse-phase protein arrays.
              <br><br>
              <p>Sources:</p>
              <ol>
                <li><a target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/27595404">Uhlen M et. al. A proposal for validation of antibodies. Nature Methods, Oct;13(10):823-7. 2016.</a></li>
                <li><a target="_blank" href="https://onlinelibrary.wiley.com/doi/full/10.1002/cne.20839">Saper C. An open letter to our readers on the use of antibodies. Journal of comparative neurology, 493(4):477-8, 2005.</a></li>
                <li><a target="_blank" href="https://doi.org/10.6075/J0RB72JC">Bandrowski A.  Example Authentication of of Key Biological and/or Chemical Resources, NIH Policy on Rigor and Reproducibility Section, UC San Diego Library website.</a></li>
                <li><a target="_blank" href="https://antibodyregistry.org">AntibodyRegistry (https://antibodyregistry.org)</a></li>
                <li><a target="_blank" href="https://grants.nih.gov/policy/reproducibility/guidance.htm">Guidance: Rigor and Reproducibility in Grant Applications, National Institute of Health Office of Extramural Research Website. </a></li>
              </ol>
            <!--<h3>B. Authentication Plan</h3>
            <div class="indent-group">
                <p>
                    This authentication plan of antibodies is based on the methods suggested in (1) the table of Proposed conceptual pillars for validation of antibodies (<a target="_blank" href="https://www.nature.com/articles/nmeth.3995/tables/1">https://www.nature.com/articles/nmeth.3995/tables/1</a>) in the article "A proposal for validation of antibodies", published by Uhlen et. al in Nature Methods in 2016 (<a target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/27595404">https://www.ncbi.nlm.nih.gov/pubmed/27595404</a>), (2) the guideline published by C. Saper in  the journal of comparative neurology in 2005 (<a target="_blank" href="https://onlinelibrary.wiley.com/doi/full/10.1002/cne.20839">https://onlinelibrary.wiley.com/doi/full/10.1002/cne.20839</a>), and (3) the Example Authentication of of Key Biological and/or Chemical Resources, prepared by Dr. Anita Bandrowski and published at the section of NIH Policy on Rigor and Reproducibility at UC San Diego Library website (<a target="_blank" href="https://doi.org/10.6075/J0RB72JC">https://doi.org/10.6075/J0RB72JC</a>). Information of how to authenticating biological resources can be also found in the Rigor and Reproducibility section at dkNET (<a target="_blank" href="https://dknet.org/about/rr5">https://dknet.org/about/rr5</a>).
                </p>
                <?php $level3_counter += 1; ?>
                <h4><?php echo $level3_counter ?>. Check Raised Issues in Antibodies</h4>
                <div class="indent-group">
                    <p>
                        We will monitor antibody record(s) periodically at Antibody Registry (<a href="https://antibodyregistry.org">https://antibodyregistry.org</a>) to determine if other authors who used this antibody raised issues.
                    </p>
                    <ul>
                        <?php foreach($report_item_types["antibody"]["items"] as $x): ?>
                            <li><?php echo $x->getData("name", false) ?> (RRID:<?php echo $x->getData("ID", false) ?>, <a target="_blank" href="http://antibodyregistry.org/<?php echo $x->getData("Antibody ID", true) ?>">http://antibodyregistry.org/<?php echo $x->getData("Antibody ID", true) ?></a>)</li>
                        <?php endforeach ?>
                    </ul>
                </div>
                <?php $level3_counter += 1; ?>
                <h4><?php echo $level3_counter ?>. Validation Techniques</h4>
                <div class="indent-group">
                    <?php $level4_counter = 0; ?>
                    <?php if(isset($report_item_types["antibody"]["subtypes"]["Immunoprecipitation"])): ?>
                        <?php $level4_counter += 1; ?>
                        <h4><?php echo $intToLowercase[$level4_counter] ?>. Immunoprecipitation</h4>
                        <div class="indent-group">
                            <?php echo antibodyValidationTechniques($report_item_types["antibody"]["subtypes"]["Immunoprecipitation"], $report_item_types["antibody"]["items"], "Immunoprecipitation") ?>
                        </div>
                    <?php endif ?>
                    <?php if(isset($report_item_types["antibody"]["subtypes"]["Histology and immunohistochemistry"])): ?>
                        <?php $level4_counter += 1; ?>
                        <h4><?php echo $intToLowercase[$level4_counter] ?>. Histology and immunohistochemistry</h4>
                        <div class="indent-group">
                            <?php echo antibodyValidationTechniques($report_item_types["antibody"]["subtypes"]["Histology and immunohistochemistry"], $report_item_types["antibody"]["items"], "Histology and immunohistochemistry") ?>
                            <p>All histology and immunohistochemistry will be performed by the following Histology core(s) using standard methodologies:</p>
                            <table class="table">
                                <thead><tr><th>Antibody</th><th>Institution/company</th></tr></thead>
                                <tbody>
                                    <?php foreach($report_item_types["antibody"]["subtypes"]["Histology and immunohistochemistry"] as $idx => $tmp): ?>
                                        <?php
                                            $item = $report_item_types["antibody"]["items"][$idx];
                                            $subtype = $item->subtype("Histology and immunohistochemistry");
                                            if(!$subtype) continue;
                                            $performer = $subtype->getUserData("performed-select")->data;
                                            $institute_text = "";
                                            if($performer == "core") {
                                                $institute_text = $subtype->getUserData("university")->data . " " . $subtype->getUserData("core-director")->data;
                                            } elseif($performer == "company") {
                                                $institute_text = $subtype->getUserData("company")->data;
                                            } elseif($performer == "research-group") {
                                                $institute_text = $subtype->getUserData("research-group")->data;
                                            }
                                            if(!trim($institute_text)) {
                                                $institute_text = $missing_text;
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo $item->getData("Antibody Name", true) ?></td>
                                            <td><?php echo $institute_text ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif ?>
                    <?php if(isset($report_item_types["antibody"]["subtypes"]["Western blotting"])): ?>
                        <?php $level4_counter += 1; ?>
                        <h4><?php echo $intToLowercase[$level4_counter] ?>. Western blotting</h4>
                        <div class="indent-group">
                            <?php echo antibodyValidationTechniques($report_item_types["antibody"]["subtypes"]["Western blotting"], $report_item_types["antibody"]["items"], "Western blotting") ?>
                        </div>
                    <?php endif ?>
                    <?php if(isset($report_item_types["antibody"]["subtypes"]["Flow cytometry"])): ?>
                        <?php $level4_counter += 1; ?>
                        <h4><?php echo $intToLowercase[$level4_counter] ?>. Flow cytometry</h4>
                        <div class="indent-group">
                            <?php echo antibodyValidationTechniques($report_item_types["antibody"]["subtypes"]["Flow cytometry"], $report_item_types["antibody"]["items"], "Flow cytometry") ?>
                        </div>
                    <?php endif ?>
                    <?php if(isset($report_item_types["antibody"]["subtypes"]["Sandwich assays"])): ?>
                        <?php $level4_counter += 1; ?>
                        <h4><?php echo $intToLowercase[$level4_counter] ?>. Sandwich assays</h4>
                        <div class="indent-group">
                            <?php echo antibodyValidationTechniques($report_item_types["antibody"]["subtypes"]["Sandwich assays"], $report_item_types["antibody"]["items"], "Sandwich assays") ?>
                        </div>
                    <?php endif ?>
                    <?php if(isset($report_item_types["antibody"]["subtypes"]["Reverse phase protein arrays"])): ?>
                        <?php $level4_counter += 1; ?>
                        <h4><?php echo $intToLowercase[$level4_counter] ?>. Reverse phase protein arrays</h4>
                        <div class="indent-group">
                            <?php echo antibodyValidationTechniques($report_item_types["antibody"]["subtypes"]["Reverse phase protein arrays"], $report_item_types["antibody"]["items"], "Reverse phase protein arrays") ?>
                        </div>
                    <?php endif ?>
                    <p>Data about validation problems including lack of specificity or cross reactivity will be included in any resulting manuscript, as part of the antibody validation methods, if no manuscript is written, then relevant data will be submitted to a public data repository such as FigShare or antibodyregistry.org, and the catalog number as well as the RRID will be included in all publications, even single figures so that other authors may know that an issue has been raised about this antibody.</p>
                </div>
            </div>-->
        </div>
    <?php endif ?>
    <?php $level1_counter += 1; ?>
    <hr/>
    <h2 id="resource-index"><?php echo $intToRoman[$level1_counter] ?>. Resource Index</h2>
    <div class="indent-group">
        <?php foreach($report_item_types["cellline"]["items"] as $idx => $item): ?>
            <?php echo cleanData(\helper\htmlElement("rrid-report-item", Array("rrid-item" => $item))) ?>
            <ul>
                <?php if(isset($report_item_types["cellline"]["problematic"][$idx])): ?>
                    <li style="color:red">Problematic cell line</li>
                <?php endif ?>
                <?php if(isset($report_item_types["cellline"]["discontinued"][$idx])): ?>
                    <li style="color:red">Discontinued cell line</li>
                <?php endif ?>
            </ul>
            <hr/>
        <?php endforeach ?>
        <?php foreach($report_item_types["antibody"]["items"] as $item): ?>
            <?php echo cleanData(\helper\htmlElement("rrid-report-item", Array("rrid-item" => $item))) ?>
            <ul>
                <?php if (strpos(strtolower($item->getData("Comments", true)), "discontinued") !== false): ?>
                    <li style="color:red">Discontinued antibody</li>
                <?php endif ?>
            </ul>
            <hr/>
        <?php endforeach ?>
    </div>
</div>
