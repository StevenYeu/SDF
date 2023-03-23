<?php

$community = $data["community"];
$nifid = $data["nifid"];

$top_message = $data["top"] ? true : false;

$search_manager = ElasticRRIDManager::managerByViewID($nifid);
if(is_null($search_manager)) {
    return;
}

?>

<div class="rin">
    <?php if($top_message): ?>
        <?php     # changed button message -- Vicky-2018-11-29
        switch($search_manager->getName()) {
            case "Tool":
                $message = "Can't find your Tool?  Help us by registering it into the system &mdash; it's easy.";
                $button_url = $community->fullURL() . "/about/resource?form=Resource&rel=1";
                $button_message = "Can't find your tool? Help us by registering it into the system - it's easy. Register it with the SciCrunch Registry. An RRID will be generated in 1-2 business day.";
                break;
            case "Antibody":
                $message = "Can't find your Antibody?  Help us by registering it into the system &mdash; it's easy.";
                $button_url = "http://antibodyregistry.org/add";
                $button_message = "Can't find your antibody? Help us by registering it into the system — it's easy. Register it with the Antibody Registry (an Antibody Registry account is required. Create a free Antibody Registry account if you don't have one yet). An RRID will be generated in 1-2 business days.";
                break;
            case "Cell Line":
                $message = "Can't find your Cell Line?  Help us by registering it into the system &mdash; it's easy.";
                $button_url = "https://web.expasy.org/cellosaurus/";
                $button_message = "Can't find your cell line? Help us by registering it into the system - it's easy. Register it with the Cellosaurus. An RRID will be generated in 1-2 business day.";
                break;
            case "Organism":
                $message = "Can't find your Organism?  Help us by registering it into the system &mdash; it's easy.";
                $button_url = "#cant-find-rrid";
                $button_message = "Can't find your Organism?  Help us by registering it into the system &mdash; it's easy.";
                break;
            case "Plasmid":
                $message = "Can't find your plasmid? Help us by registering it into the system &mdash; it's easy.";
                $button_url = "https://www.addgene.org/deposit/";
                $button_message = "Can't find your plasmid? Help us by registering it into the system &mdash; it's easy. Register it with Addgene.";
                break;
            case "Biosample":
                $message = "Can't find your bisample? Help us by registering it into the system &mdash; it's easy.";
                $button_url = "mailto:rii-help@scicrunch.org";
                $button_message = "Can't find your biosample? We currently only contain islet biosamples from the Integrated Islet Distribution Program (IIDP) that were registered with NCBI Biosample. If your biosamples are part of a well-organized project and would like to generate RRIDs, please contact RRID team (The Resource Identification Initiative) rii-help@scicrunch.org.";
                break;
            case "Protocol":
                $message = "Can't find your protocols? Help us by adding it into the system &mdash; it's easy.";
                $button_url = "https://www.protocols.io/";
                $button_message = "Can't find your protocols? Help us by adding it into the system - it's easy. Create and publish your protocols at Protocols.io. ";
                break;
        }
        ?>
        <div class="row">
            <div class="col-md-12">
                <h4>
                    <!--<span style="padding: 5px; vertical-align: middle" class="color-white background-orange"><?php //echo $message ?></span>-->
                    <a href="<?php echo $button_url ?>" target="_blank"><button class="btn btn-primary" style="white-space: normal;width:95%"><?php echo $button_message ?></button></a>
                </h4>
            </div>
        </div>
    <?php else: ?>
        <div id="cant-find-rrid">
            <div class="panel">
                <div class="panel-body">
                    <p>Can't find your <?php echo $search_manager->getName() ?>?</p>
                    <?php if($search_manager->getName() == "Tool"): ?>
                        <p>We recommend that you click <span class="btn btn-info help-tooltip-btn" data-name="rin-search-tips.html"><i class="fa fa-question-circle"></i></span> next to the search bar to check some helpful tips on searches and refine your search firstly. Alternatively, please register your tool with the SciCrunch Registry by adding a little information to a web form, logging in will enable users to create a provisional RRID, but it not required to submit.</p>
                        <a target="_blank" href="<?php echo $community->fullURL() ?>/about/resource?form=Resource&rel=1"><button class="btn btn-primary">Register a tool now</button></a>
                    <?php elseif($search_manager->getName() == "Antibody"): ?>
                        <p>We recommend that you click <span class="btn btn-info help-tooltip-btn" data-name="rin-search-tips.html"><i class="fa fa-question-circle"></i></span> next to the search bar to check some helpful tips on searches and refine your search firstly. If you want to find a specific antibody, it's easier to enter an RRID or a Catalog Number to search. You can refine the search results using Facets on the left side of the search results page. If you are on the table view, you can also search in a specific column by clicking the column title and enter the keywords.</p>
                        <p>If you still could not find your antibody in the search results, please help us by registering it into the system — it's easy. Register it with The Antibody Registry (an Antibody Registry account is required. Create a free Antibody Registry account if you don't have one yet). An RRID will be generated in 1-2 business days.</p>
                        <a target="_blank" href="http://antibodyregistry.org/add"><button class="btn btn-primary">Register an antibody now</button></a>
                    <?php elseif($search_manager->getName() == "Cell Line"): ?>
                        <p>We recommend that you click <span class="btn btn-info help-tooltip-btn" data-name="rin-search-tips.html"><i class="fa fa-question-circle"></i></span> next to the search bar to check some helpful tips on searches and refine your search firstly. If you want to find a specific cell line, it's easier to enter an RRID or add the vendor information to search. You can refine the search results using Facets on the left side of the search results page. If you are on the table view, you can also search in a specific column by clicking the column title and enter the keywords.</p>
                        <p>If you still could not find your cell line in the search results, please help us by registering it into the system — it's easy. Register it with the Cellosaurus. An RRID will be generated in 1-2 business days.</p>
                        <a target="_blank" href="https://web.expasy.org/cellosaurus/"><button class="btn btn-primary">Register a cell line now</button></a>
                    <?php elseif($search_manager->getName() == "Plasmid"): ?>
                        <p>We recommend that you click <span class="btn btn-info help-tooltip-btn" data-name="rin-search-tips.html"><i class="fa fa-question-circle"></i></span> next to the search bar to check some helpful tips on searches and refine your search firstly. If you want to find a specific plasmid, it's easier to enter an RRID or an Addgene Catalog Number to search. You can refine the search results using Facets on the left side of the search results page. If you are on the table view, you can also search in a specific column by clicking the column title and enter the keywords.</p>
                        <p>If you still could not find your plasmid in the search results, please help us by registering it into the system — it's easy. Register it with Addgene.</p>
                        <a target="_blank" href="https://www.addgene.org/deposit/"><button class="btn btn-primary">Register a plasmid now</button></a>
                    <?php elseif($search_manager->getName() == "Biosample"): ?>
                        <p>We recommend that you click <span class="btn btn-info help-tooltip-btn" data-name="rin-search-tips.html"><i class="fa fa-question-circle"></i></span> next to the search bar to check some helpful tips on searches and refine your search firstly. If you want to find a specific biosample, it's easier to enter an RRID or an NCBI Biosample ID to search. You can refine the search results using Facets on the left side of the search results page. If you are on the table view, you can also search in a specific column by clicking the column title and enter the keywords.</p>
                        <p>Please note that we currently only contain islet biosamples from the Integrated Islet Distribution Program (IIDP) that were registered with the NCBI Biosample. If your biosamples are part of a well-organized project and would like to generate RRIDs, please contact the RRID(The Resource Identification Initiative) team: <a href="mailto:rii-help@scicrunch.org" target="_blank">rii-help@scicrunch.org</a>. </p>
                    <?php elseif($search_manager->getName() == "Protocol"): ?>
                        <p>We recommend that you click <span class="btn btn-info help-tooltip-btn" data-name="rin-search-tips.html"><i class="fa fa-question-circle"></i></span> next to the search bar to check some helpful tips on searches and refine your search firstly. If you want to find a specific protocol and you know the DOI of the protocol already, it's easier to enter a DOI to search. You can refine the search results using Facets on the left side of the search results page. If you are on the table view, you can also search in a specific column by clicking the column title and enter the keywords.</p>
                        <p>If you still could not find your protocol in the search results, please help us by adding it into the system — it's easy. Create and publish your protocols at Protocols.io.</p>
                        <a target="_blank" href="https://www.protocols.io/"><button class="btn btn-primary">Publish your protocol now</button></a>
                    <?php elseif($search_manager->getName() == "Organism"): ?>
                        <?php
                            $organism_registrars = Array(
                                Array(
                                    "name" => "Mouse",
                                    "url" => "http://www.informatics.jax.org/mgihome/submissions/amsp_submission.cgi",
                                    "reg_name" => "Mouse Genome Informatics (MGI)",
                                ),
                                Array(
                                    "name" => "Rat",
                                    "url" => "http://rgd.mcw.edu/rgdweb/models/strainSubmissionForm.html?new=true",
                                    "reg_name" => "Rat Genome Database (RGD)",
                                ),
                                Array(
                                    "name" => "Worm",
                                    "url" => "http://www.wormbase.org/about/userguide",
                                    "reg_name" => "Wormbase",
                                ),
                                Array(
                                    "name" => "Fly",
                                    "url" => "http://flybase.org/contact/email",
                                    "reg_name" => "Flybase",
                                ),
                                Array(
                                    "name" => "Zebrafish",
                                    "url" => "http://zfin.org/action/nomenclature/line-name",
                                    "reg_name" => "Zebrafish Information Network (ZFIN)",
                                ),
                                Array(
                                    "name" => "Xiphophorus",
                                    "url" => "http://www.xiphophorus.txstate.edu/",
                                    "reg_name" => "Xiphophorus Genetic Stock Center",
                                ),
                                Array(
                                    "name" => "Frog",
                                    "url" => "http://www.xenbase.org/",
                                    "reg_name" => "Xenbase",
                                ),
                                Array(
                                    "name" => "Pig",
                                    "url" => "http://www.nsrrc.missouri.edu/",
                                    "reg_name" => "NSRRC",
                                ),
                                Array(
                                    "name" => "Tetrahymena",
                                    "url" => "https://tetrahymena.vet.cornell.edu/",
                                    "reg_name" => "Tetrahymena Stock Center",
                                ),
                                /*Array(
                                    "name" => "Amoebae (<i>D. discoideum</i>)",
                                    "url" => "http://dictybase.org/",
                                    "reg_name" => "Dictybase",
                                ),
                                Array(
                                    "name" => "Chicken(<i>Gallus gallus</i>)",
                                    "url" => "https://grants.nih.gov/grants/policy/model_organism/model_organism_brochure.pdf",
                                    "reg_name" => "Trans-NIH Gallus Initiative",
                                ),
                                Array(
                                    "name" => "Fission Yeast (<i>S. pombe</i>)",
                                    "url" => "https://grants.nih.gov/grants/policy/model_organism/model_organism_brochure.pdf",
                                    "reg_name" => "Trans-NIH S. Pombe Initiative",
                                ),
                                Array(
                                    "name" => "Filamentous fungus (<i>Neurospora</i>)",
                                    "url" => "https://grants.nih.gov/grants/policy/model_organism/model_organism_brochure.pdf",
                                    "reg_name" => "Trans-NIH Neurospora Initiative",
                                ),
                                Array(
                                    "name" => "Daphnia",
                                    "url" => "https://grants.nih.gov/grants/policy/model_organism/model_organism_brochure.pdf",
                                    "reg_name" => "Trans-NIH Daphnia Initiative",
                                ),*/
                            );
                        ?>
                        <p>We recommend that you click <span class="btn btn-info help-tooltip-btn" data-name="rin-search-tips.html"><i class="fa fa-question-circle"></i></span> next to the search bar to check some helpful tips on searches and refine your search firstly. If you want to find a specific organism, it's easier to enter an RRID or a Catalog Number to search. You can refine the search results using Facets on the left side of the search results page. If you are on the table view, you can also search in a specific column by clicking the column title and enter the keywords.</p>
                        <p>If you still could not find your organism in the search results, please help us by registering it into the system — it's easy. Organisms identifiers are registered through multiple sources depending on the species:</p>
                        <ul>
                            <?php foreach($organism_registrars as $or): ?>
                                <li>
                                    <strong><?php echo $or["name"] ?></strong>: <a href="<?php echo $or["url"] ?>" target="_blank"><?php echo $or["reg_name"] ?></a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                </div>
            </div>
        </div>
    <?php endif ?>

    <div class="cant-find-rrid-bottom">
        <div class="open">
            Can't find the RRID you're searching for?
            <span class="close-button">X</span>
        </div>
        <div class="closed">
        </div>
    </div>
</div>

<script>
    $(function() {
        $(".cant-find-rrid-bottom .open .close-button").on("click", function(event) {
            $(this).parent().parent().find(".open").hide();
            $(this).parent().parent().find(".closed").show();
            event.stopPropagation();
        });

        $(".cant-find-rrid-bottom .closed").on("click", function() {
        });

        $(".cant-find-rrid-bottom .open").on("click", function() {
            $("html, body").animate({scrollTop: $("#cant-find-rrid").offset().top}, 500);
        });

        $(".cant-find-rrid-bottom").on("click", function() {
            if($(this).find(".open").is(":visible")) {
                $("html, body").animate({scrollTop: $("#cant-find-rrid").offset().top}, 500);
            } else {
                $(this).find(".open").show();
                $(this).find(".close").hide();
            }
        });
    })
</script>
