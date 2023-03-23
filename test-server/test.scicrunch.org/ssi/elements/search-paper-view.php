<?php

$community = $data["community"];
$vars = $data["vars"];
$server_https = $data["server-https"];
$search = $data["search"];

$rpkey_flag = false;
if(isset($_GET["rpKey"]) && $_GET["rpKey"] == "on") {
    $rpkey_flag = true;
}

$views = Array(
    "antibody" => "nif-0000-07730-1",
    "Resource" => "nlx_144509-1",
    "tool" => "nlx_144509-1",
    "Organization" => "nlx_144509-1",
    "cell line" => "SCR_013869-1",
    "organism" => "nlx_154697-1",
    "plasmid" => "nif-0000-11872-1",
    "biosample" => "nlx_143929-1",
);

## get information based on pubmed ID by elastic search
/******************************************************************************************************/
$search_manager = ElasticPMIDManager::managerByViewID("pubmed");
if(is_null($search_manager)) {
    return;
}
$results = $search_manager->searchPMID($vars['pmid']);
if($results->hitCount() == 0) {
    echo \helper\errorPage("noresource", NULL, false);
    return;
}

foreach($results as $result){
}
$title = $result->getField("title");

$authors = [];
foreach ($result->getField("Author") as $author) {
    $authors[] = $author["name"];
}

$des = $result->getField("description");
$id = $result->getField("id");
$journal = $result->getField("Journal");
$publicationYear = $result->getField("Publication Year");
$meshTerms = $result->getField("mesh-terms");
$fundings = $result->getField("grants");

/******************************************************************************************************/



## get mentions based on pubmed ID by elastic search
/******************************************************************************************************/
$search_manager = ElasticPMIDManager::managerByViewID("pubmed-mentions");
if(is_null($search_manager)) {
    return;
}
$results = $search_manager->searchPMIDMentions("PMID:".$vars['pmid']);

$RearchResources = [];
$ResearchTools = [];
$ResearchAntibodies = [];
$rridTools = [];
$rridAntibodies = [];
$rridResources = [];

foreach($results as $result){
}

foreach ($result->getField("rridMentions") as $mention) {
    if (empty($mention["type"])) {
        if(\helper\startsWith($mention["rrid"], "RRID:SAMN")) {
            $mention["type"] = "biosample";
        } else {
            switch (explode("_", $mention["rrid"])[0]) {
                case 'RRID:SCR':
                    $mention["type"] = "tool";
                    break;

                case 'RRID:AB':
                    $mention["type"] = "antibody";
                    break;

                case 'RRID:CVCL':
                    $mention["type"] = "cell line";
                    break;

                case 'RRID:Addgene':
                    $mention["type"] = "plasmid";
                    break;

                default:
                    $mention["type"] = "organism";
                    break;
            }
        }
    } else {
        if ($mention["type"] == "Resource") $mention["type"] = "tool";
    }
    if ($mention["type"] == "antibody" && !in_array($mention["rrid"], $rridAntibodies)) {
        $rridAntibodies[] = $mention["rrid"];
        $ResearchAntibodies[] = $mention;
    } else {
        $rridResources[] = $mention["rrid"];
        $RearchResources[] = $mention;
    }
}

foreach ($result->getField("resourceMentions") as $mention) {
    if (!in_array($mention["rrid"], $rridResources)) {
        $mention["type"] = "tool";
        $rridTools[] = $mention["rrid"];
        $ResearchTools[] = $mention;
    }
}

/******************************************************************************************************/



## get related publications based on $_id by elastic search
/******************************************************************************************************/
$search_manager = ElasticPMIDManager::managerByViewID("pubmed");
if(is_null($search_manager)) {
    return;
}
$results = $search_manager->searchRelatedPublications($id);

$rp_info = [];
foreach($results as $result){
    $rp_author = $result->getField("Author")[0];
    if ($rp_author["familyName"] != "" && $rp_author["initials"] != "") $rp_author_name = $rp_author["familyName"] . " " . $rp_author["initials"];
    else $rp_author_name = $rp_author["name"];
    $rp_info[] = Array(
        "pmid" => $result->getField("pmid"),
        "title" => $result->getField("title"),
        "author" => $rp_author_name,
        "publicationYear" => $result->getField("Publication Year")
    );
}

/******************************************************************************************************/
?>

<style>
    .tab-pane {
        background: #f8f8f8;
        padding: 15px 15px;
        border-bottom: 1px solid #dedede;
    }

    .tab-v5 {
        margin-top: 30px;
    }

    .tab-v5 .tab-content {
        margin: 0;
        padding: 0;
    }

    .tag-box {
        margin-bottom: 20px;
    }

    .map {
        width: 100%;
        height: 350px;
        border-top: solid 1px #eee;
        border-bottom: solid 1px #eee;
    }
</style>

<?php

// function getAnnotations($pmid) {
//     $url = ENVIRONMENT . '/v1/federation/data/nlx_154697-2.xml?q=*&exportType=all&filter=Reference:PMID:' . $pmid . '&count=50';
//     $xml = simplexml_load_file($url);
//     $annotations_flat = Array();
//     if($xml) {
//         foreach($xml->result->results->row as $row) {
//             $columns = Array();
//             foreach($row->data as $data) {
//                 $columns[(string) $data->name] = (string) $data->value;
//             }
//             $annotations_flat[] = $columns;
//         }
//     }
//     $annotations = Array();
//     foreach($annotations_flat as $af) {
//         $category = $af["Category"];
//         $resource_id = $af["id"];
//         if(!isset($annotations[$resource_id])) $annotations[$resource_id] = Array();
//         $annotations[$resource_id][] = $af;
//     }
//     return $annotations;
// }

// function getRelatedPapers($pmid) {
//     $url = ENVIRONMENT . "/v1/literature/moreLikePmid?pmid=" . $pmid;
//     $xml = simplexml_load_file($url);
//     $publications = Array();
//     if($xml) {
//         foreach($xml->publication as $publication) {
//             $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
//             $pub = getPaperInfo($publication);
//             $pub['schema'] = AbstractSchema::buildReferenceSchema(AbstractSchema::buildPMIDURL($protocol, $pub['pmid']));
//             $publications[] = $pub;
//         }
//     }
//     return $publications;
// }

// function getPaperInfo($paper_xml) {
//     $paper = Array();
//     $paper["pmid"] = (string)$paper_xml->attributes()->pmid;
//     $paper['title'] = (string)$paper_xml->title;
//     $paper['abstract'] = (string)$paper_xml->abstract;
//     foreach ($paper_xml->authors->author as $author) {
//         $paper['authors'][] = (string)$author;
//     }
//     foreach ($paper_xml->meshHeadings->meshHeading as $mesh) {
//         $paper['mesh'][] = (string)$mesh;
//     }
//     $paper['date'] = date("M", mktime(0,0,0,(int)$paper_xml->month,10)) . ' ' . $paper_xml->day . ', ' . $paper_xml->year;
//     $paper["year"] = $paper_xml->year;
//     $paper['journal'] = (string)$paper_xml->journal;
//     $paper['journalShort'] = (string)$paper_xml->journalShort;
//     foreach ($paper_xml->grantAgencies->grantAgency as $grant) {
//         $paper['grants'][] = (string)$grant;
//     }
//     foreach ($paper_xml->grantIds->grantId as $grant) {
//         $paper['grantIds'][] = (string)$grant;
//     }
//     return $paper;
// }

// function rridType($rrid) {
//     $rrid_sub = strtolower(substr($rrid, 5));
//     if(\helper\startsWith($rrid_sub, "nlx")) return "tool";
//     if(\helper\startsWith($rrid_sub, "scr")) return "tool";
//     if(\helper\startsWith($rrid_sub, "nif")) return "tool";
//     if(\helper\startsWith($rrid_sub, "ab")) return "antibody";
//     if(\helper\startsWith($rrid_sub, "imsr")) return "mouse";
//     if(\helper\startsWith($rrid_sub, "omics")) return "tool";
//     if(\helper\startsWith($rrid_sub, "cvcl")) return "cell line";
//     if(\helper\startsWith($rrid_sub, "scires")) return "tool";
//     if(\helper\startsWith($rrid_sub, "zfin")) return "zebra fish";
//     if(\helper\startsWith($rrid_sub, "zirc")) return "zebra fish";
//     if(\helper\startsWith($rrid_sub, "rgd")) return "rat";
//     if(\helper\startsWith($rrid_sub, "nxr")) return "frog";
//     if(\helper\startsWith($rrid_sub, "bdsc")) return "fly";
//     if(\helper\startsWith($rrid_sub, "rid")) return "tool";
//     if(\helper\startsWith($rrid_sub, "mgi")) return "mouse";
//     if(\helper\startsWith($rrid_sub, "cgc")) return "worm";
//     if(\helper\startsWith($rrid_sub, "sciex")) return "tool";
//     if(\helper\startsWith($rrid_sub, "mmrrc")) return "mouse";
//     if(\helper\startsWith($rrid_sub, "wb")) return "worm";
//     if(\helper\startsWith($rrid_sub, "birnlex")) return "tool";
//     if(\helper\startsWith($rrid_sub, "flybase")) return "fly";
//     if(\helper\startsWith($rrid_sub, "zdb")) return "fish";
//     if(\helper\startsWith($rrid_sub, "nsrrc")) return "pig";
//     if(\helper\startsWith($rrid_sub, "tsc")) return "tetrahymena";
//     if(\helper\startsWith($rrid_sub, "agsc")) return "salamander";
//     if(\helper\startsWith($rrid_sub, "dggr")) return "fly";
//     if(\helper\startsWith($rrid_sub, "ncbitaxon")) return "animal";
//     if(\helper\startsWith($rrid_sub, "xgsc")) return "fish";
//     return "";
// }

function rridColumns($rrid) {
    static $resource_columns = Array();
    $rrid_sub = strtolower(substr($rrid, 5));
    if(isset($resource_columns[$rrid_sub])) return $resource_columns[$rrid_sub];
    if(\helper\startsWith($rrid_sub, "scr")) {
        $resource = new Resource();
        $resource->getByRID($rrid_sub);
        if($resource->id) {
            $resource->getColumns();
            $resource_columns[$rrid_sub] = $resource->columns;
            return $resource->columns;
        }
    } elseif(
        \helper\startsWith($rrid_sub, "nlx") ||
        \helper\startsWith($rrid_sub, "nif") ||
        \helper\startsWith($rrid_sub, "omics") ||
        \helper\startsWith($rrid_sub, "scires") ||
        \helper\startsWith($rrid_sub, "rid") ||
        \helper\startsWith($rrid_sub, "sciex") ||
        \helper\startsWith($rrid_sub, "birnlex")
    ) {
        $resource = new Resource();
        $resource->getByOriginal($rrid_sub);
        if($resource->id) {
            $resource->getColumns();
            $resource_columns[$rrid_sub] = $resource->columns;
            return $resource->columns;
        }
    }
    return $rrid;
}

// $custom = new View();
// $custom->getByCommView($community->cid, $vars['view']);
//
// $holder = new View_Column();
// $tiles = $holder->getByCustom($custom->id);

// $url = Connection::environment() . '/v1/literature/pmid?pmid=' . $vars['pmid'];
// $xml = simplexml_load_file($url);
// if ($xml) {
//     $paper = getPaperInfo($xml->publication);
//     $paper['schema'] = SchemaGeneratorPublicationXML::generate($xml->publication);
// } else {
//     \helper\errorPage("nopmid", $vars["pmid"]);
//     return;
// }
//
// $annotations = getAnnotations($vars["pmid"]);
// $related_papers = getRelatedPapers($vars["pmid"]);
//
// $ocrc_link = WorldCatInterface::getHTML($vars["pmid"]);
// if(is_null($ocrc_link)) $ocrc_link = "";
//
// $url2 = Connection::environment() . '/v1/federation/data/nlx_154697-2.xml?q=*&exportType=all&filter=Publication:' . $vars['pmid'] . '&count=50';
// $xml2 = simplexml_load_file($url2);
// if ($xml2) {
//     foreach ($xml2->result->results->row as $row) {
//         $columns = array();
//         foreach ($row->data as $data) {
//             $columns[(string)$data->name] = (string)$data->value;
//         }
//         $links[] = $columns;
//     }
// }
//
// foreach ($links as $array) {
//     $info[strip_tags($array['resource_name'])]['meta'] = '/browse/resources/' . $array['id'];
//     $info[strip_tags($array['resource_name'])]['links'][] = '<a target="_blank" href="' . $array['link_url'] . '">' . $array['link_name'] . '</a>';
// }

// $used_resources = RRIDMention::getMentionedRRIDs($paper["pmid"]);
// $rdw_resources = ResourceMention::getRDWResources($paper["pmid"], $used_resources);
// $mentionsSchema = array();
// $protocol = $server_https ? 'https' : 'http';
//
// foreach ($used_resources['rrids'] as $rrid) {
//     $mentionsSchema[] = AbstractSchema::buildReferenceSchema(AbstractSchema::buildResourceURL($protocol, $rrid["rrid"]));
// }
// foreach ($rdw_resources as $rrid) {
//     $mentionsSchema[] = AbstractSchema::buildReferenceSchema(AbstractSchema::buildResourceURL($protocol, $rrid));
// }
// foreach($related_papers as $rp) {
//     $paper['schema']->mentionsSchema[] = $rp['schema'];
// }
//
// $paper['schema']->mentionsSchema = array_merge($paper['schema']->mentionsSchema, $mentionsSchema);

/*****************************************************************************************************************************************************************************************************/
?>

<!-- addthis -->
<meta property="og:title" content="<?php echo $title ?>" />
<meta property="og:description" content="<?php echo strip_tags($des) ?>" />

<div>
    <div class="row">
        <script type="application/ld+json"><?php if(!is_null($paper["schema"]) && $paper["schema"] instanceof AbstractSchema) echo $paper['schema']->generateJSON() ?></script>
        <div class="col-md-8">
            <h1><?php echo $title ?></h1>
            <div>
                <?php foreach($authors as $i => $author): ?>
                    <?php if($i > 0) echo " | " ?>
                    <a href="<?php echo $search->generateURLFromDiff(Array("facet" => Array("Author:" . $author), "pmid" => "")) ?>"><?php echo $author ?></a>
                <?php endforeach ?>
            </div>
            <div>
                <?php echo join(" | ", $journal) ?> |
                <?php echo $publicationYear ?>
            </div>
            <p><a target="_blank" href="http://www.ncbi.nlm.nih.gov/pubmed/<?php echo $vars["pmid"] ?>"><img src="/images/US-NLM-PubMed-Logo.svg" class="pubmed-image" /> <i class="fa fa-external-link"></i></a><?php echo $ocrc_link ?></p>

            <p class="truncate-desc">
                <?php echo $des ?>
            </p>

            <p>
                <b>Pubmed ID:</b> <?php echo $vars['pmid'] ?>
                <a href="<?php echo PUBLICENVIRONMENT . "/v1/literature/pmid.ris?pmid=" . $vars["pmid"] ?>"><i class="fa fa-cloud-download"></i> RIS Download</a>
            </p>
            <?php if($meshTerms[0] != ""): ?>
                <p>
                    <b>Mesh terms: </b>
                    <?php foreach($meshTerms as $i => $mesh): ?>
                        <?php if($i != 0) echo " | " ?>
                        <?php echo '<a href="' . $search->generateURLFromDiff(Array("pmid" => "", "q" => "$mesh")) . '">' . $mesh . '</a>' ?>
                    <?php endforeach ?>
                </p>
            <?php endif ?>
            <div class="addthis_inline_share_toolbox"></div>
        </div>
        <div class="col-md-4">
            <div data-badge-details="right" data-badge-type="large-donut" data-pmid="<?php echo $vars["pmid"] ?>" data-hide-no-mentions="true" class="altmetric-embed"></div>
        </div>
    </div>

    <div class="tab-v5">
        <ul class="nav nav-tabs nav-tabs-js" role="tablist">
            <li class="active"><a href="#description" role="tab" data-toggle="tab">Information</a></li>
            <li><a href="#nlmlicense" role="tab" data-toggle="tab">Terms of use</a></li>
            <li><a href="#used-rrids" role="tab" data-toggle="tab">Tools and resources</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade in active" id="description">
                <div class="row">
                    <div class="col-md-4">
                        <div class="tag-box tag-box-v2 box-shadow shadow-effect-1">
                            <h2>Research resources used in this publication</h2>
                            <?php if(empty($RearchResources)): ?>
                                None found
                            <?php else: ?>
                                <ul style="max-height:250px;overflow:auto" class="literature-resources">
                                    <?php foreach($RearchResources as $rrid): ?>
                                        <li>
                                            <a target="_blank" href="<?php echo PROTOCOL . "://" . FQDN . "/" . $community->portalName . "/resolver/" . $rrid["rrid"] ?>"><?php echo $rrid["name"] . " (" . $rrid["rrid"] . ") (" . $rrid["type"] . ")" ?></a>
                                            <?php if($rrid["primary_id"]): ?>
                                                <input type="hidden" value="<?php echo $rrid["primary_id"] ?>">
                                            <?php endif ?>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tag-box tag-box-v2 box-shadow shadow-effect-1">
                            <h2>Additional research tools detected in this publication</h2>
                            <?php if(empty($ResearchTools)): ?>
                                None found
                            <?php else: ?>
                                <ul style="max-height:250px;overflow:auto" class="literature-resources">
                                    <?php foreach($ResearchTools as $rrid): ?>
                                        <li>
                                            <a target="_blank" href="<?php echo PROTOCOL . "://" . FQDN . "/" . $community->portalName . "/resolver/" . $rrid["rrid"] ?>"><?php echo rridColumns($rrid)["name"] ?> (<?php echo $rrid["rrid"] ?>)</a>
                                            <input type="hidden" value="<?php echo intval(trim($rrid, "RRID:SCR_")) ?>">
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="tag-box tag-box-v2 box-shadow shadow-effect-1">
                            <h2>Antibodies used in this publication</h2>
                            <?php if(empty($ResearchAntibodies)): ?>
                                None found
                            <?php else: ?>
                                <ul style="max-height:250px;overflow:auto" class="literature-resources">
                                    <?php foreach($ResearchAntibodies as $antibodyMention): ?>
                                        <li>
                                            <a target="_blank" href="<?php echo PROTOCOL . '://' . FQDN . '/' . $community->portalName . '/resolver/' . $antibodyMention['rrid'] ?>"><?php echo $antibodyMention["name"] ?> (<?php echo $antibodyMention["rrid"] ?>)</a>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="tag-box tag-box-v2 box-shadow shadow-effect-1">
                            <h2>Associated grants</h2>
                            <?php
                            if (count($fundings) > 0) {
                                echo '<ul style="max-height:250px;overflow:auto" class="literature-resources">';
                                foreach ($fundings as $grant) {
                                    echo '<li>';
                                    echo '<b>Agency: </b>' . $grant['agency'] . ', ' . $grant['country'];
                                    if ($grant['identifier'] != "") echo '<br><b>Id: </b>' . $grant['identifier'];
                                    echo '</li>';
                                }
                                echo "</ul>";
                            } else {
                                echo 'None';
                            }
                            ?>
                        </div>
                    </div>
                    <?php if($rpkey_flag): ?>
                        <div class="col-md-4">
                            <div class="tag-box tag-box-v2 box-shadow shadow-effect-1">
                                <h2>Related publications</h2>
                                <div style="max-height:285px; overflow:auto">
                                    <?php if(empty($rp_info)): ?>
                                        None
                                    <?php else: ?>
                                        <ul style="max-height:250px;overflow:auto" class="literature-resources">
                                            <?php foreach($rp_info as $rp): ?>
                                                <li>
                                                    <a target="_blank" href="<?php echo PROTOCOL . "://" . FQDN . "/" . $community->portalName . "/" . str_replace("pmid:", "", $rp["pmid"]) ?>">
                                                        <?php echo $rp["title"] ?> (<?php echo $rp["author"] . " " . $rp["publicationYear"] ?>)
                                                    </a>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    <?php endif ?>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
            </div>
            <div class="tab-pane fade" id="nlmlicense">
                <div class="panel">
                    <div class="panel-body">
                        <p>Publication data is provided by the National Library of Medicine &reg; and PubMed &reg;.  Data is retrieved from PubMed &reg; on a weekly schedule.  For terms and conditions see the <a href="https://www.nlm.nih.gov/databases/download/terms_and_conditions.html">National Library of Medicine Terms and Conditions.</a></p>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="used-rrids">
                <div class="panel">
                    <div class="panel-heading">
                        <?php if(empty($ResearchTools) && empty($ResearchAntibodies) && empty($RearchResources)): ?>
                            <h3>We have not found any resources mentioned in this publication.</h3>
                        <?php else: ?>
                            <h3>This is a list of tools and resources that we have found mentioned in this publication.</h3>
                        <?php endif ?>
                    </div>
                    <div class="panel-body">
                        <?php foreach(array_merge($ResearchTools, $ResearchAntibodies, $RearchResources) as $rrid): ?>
                            <div class="inner-results">
                                <hr/>
                                <h3>
                                    <a target="_blank" href="<?php echo PROTOCOL . "://" . FQDN . "/" . $community->portalName . "/resolver/" . $rrid["rrid"] ?>">
                                        <?php echo $rrid["name"] ?> (<?php echo $rrid["type"] ?>)
                                    </a>
                                </h3>
                                <h4>
                                    <?php echo $rrid["rrid"] ?>
                                </h4>
                                <p>
                                    <?php echo $rrid["description"] ?>
                                </p>
                                <a target="_blank" href="<?php echo PROTOCOL . "://" . FQDN . "/" . $community->portalName . "/resolver/" . $rrid["rrid"] ?>#used-in-literature">View all literature mentions</a>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
