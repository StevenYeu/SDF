<?php
include 'process-elastic-search.php';

$search = $data["search"];
$vars = $data["vars"];
$results = $data["results"];
$recent_searches = $data["recent-searches"];
$community = $data["community"];
$lit_refs = $data["lit-refs"];
$user = $data["user"];

$per_page = 20;
$search_manager = ElasticPMIDManager::managerByViewID("pubmed");
$search_options = ElasticPMIDManager::searchOptionsFromGet($vars);
$keywords_s = formatKeywords($vars["q"]);

$search_results = $search_manager->searchLiterature($keywords_s, $per_page, $vars["page"], $search_options);
$count = $search_results->totalCount();
$facets = $search_results->facets();

foreach($facets as $facet_name => $facet_values){
    if ($facet_name == "Publication Year") {
        $years = [];
        foreach($facet_values as $fy) {
            $years[] = Array(
                "year" => $fy["value"],
                "num" => $fy["count"]
              );
        }
    }
}
?>

<?php if($search->page >= Search::MAX_PAGE) echo \helper\htmlElement("too-many-pages", Array("max_page" => Search::MAX_PAGE)) ?>
<div>
    <div class="row">
        <div class="col-md-10">
            <?php
                echo \helper\htmlElement("components/search-block-slim", Array(
                    "user" => $user,
                    "vars" => $vars,
                    "community" => $community,
                    "search" => $search,
                ));
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 hidden-xs related-search">
            <div class="row" style="margin-top:10px">
                <div class="col-md-12 col-sm-4">
                    <?php echo $search->currentFacets($vars,'literature')?>
                    <?php echo \helper\htmlElement("modified-date-picker30"); ?>
                    <hr />
                    <h3 class="tut-options">Options</h3>
                    <ul class="list-group">
                        <?php if(isset($user)): ?>
                            <li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".new-collection">Create New Collection</a></li>
                            <!--<li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".add-all">Add All on Page to a Collection</a></li>-->
                        <?php else: ?>
                            <li class="list-group-item"><a href="#" class="btn-login">Log in for Collection Options</a></li>
                        <?php endif ?>
                    </ul>
                    <ul class="list-unstyled">
                        <?php if(count($years) > 1): ?>
                            <li>
                                <a href="javascript:lineGraph(<?php echo "'". str_replace("%27","\%27",str_replace('"','%22',json_encode($years)))."'" ?>)">
                                    <i class="fa fa-bar-chart-o"></i> Publication Year Chart
                                </a>
                            </li>
                        <?php endif ?>
                        <!-- <li>
                            <a href="<?php echo $results['export'] ?>">
                                <i class="fa fa-cloud-download"></i> RIS Download
                            </a>
                        </li> -->
                        <li>
                            <?php
                                $newVars = $vars;
                                if($newVars["column"] == "Publication Year") {
                                    $sortText = "Sort by relevancy";
                                    $newVars["sort"] = NULL;
                                    $newVars["column"] = NULL;
                                } else {
                                    $sortText = "Show most recent first";
                                    $newVars["sort"] = "desc";
                                    $newVars["column"] = "Publication Year";
                                }
                            ?>
                            <a href="<?php echo $search->generateURL($newVars) ?>">
                                <i class="fa fa-sort-amount-asc"></i> <?php echo $sortText ?>
                            </a>
                        </li>
                    </ul>
                    <hr/>
                </div>
                <?php if(!isset($vars["litref_pmids"])): ?>
                    <div class="col-md-12 col-sm-4">
                        <?php echo \helper\htmlElement("view-facets-pmid", Array("results" => $search_results, "search" => $search, "vars" => $vars)); ?>
                        <hr/>
                        <?php echo \helper\htmlElement("recent-searches-list", Array("recent-searches" => $recent_searches, "community" => $community)) ?>
                    </div>
                    <hr/>
                <?php endif ?>
            </div>
        </div>
        <!--/col-md-2-->

        <div class="col-md-10">
            <div class="row">
              <div class="row">
                  <div class="col-md-7" style="font-size: 20px; color: #666766">
                      <?php if($count > $per_page): ?>
                          On page <?php echo $vars["page"] ?> showing <?php echo ($per_page * ($vars["page"] - 1) + 1) . " ~ " . ($per_page * ($vars["page"] - 1) + $search_results->hitCount()) ?> papers out of <?php echo number_format($count) ?> papers
                      <?php elseif($count > 0 && $count <= $per_page): ?>
                          On page <?php echo $vars["page"] ?> showing <?php echo "1 ~ " . $search_results->hitCount() ?> papers out of <?php echo number_format($count) ?> papers
                      <?php else: ?>
                      No results found.
                      <?php endif ?>
                  </div>
                  <div class="col-md-5">
                      <?php echo $search->paginateLong($vars, "not-rin", $count, $per_page) ?>
                  </div>
              </div>
              <div class="row">
                  <div class="col-md-12">
                      <?php if ($_SESSION['user']->role == 2):  ## added debug button (show or hide elastic query)  -- Vicky-2019-2-22 ?>
                          <button class="btn btn-primary" onclick="return showHideElasticQuery()">Elastic Search Query</button>
                          <div id="ElasticQuery" style="display:none">
                            <?php echo $_SESSION['elastic_pubmed_query'] ?>
                          </div>
                      <?php endif ?>
                  </div>
              </div>

            <!-- Begin Inner Results -->
            <?php
            require_once $_SERVER[DOCUMENT_ROOT] . '/classes/schemas/schemas.class.php';
            $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
            $months = array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
            ?>

            <?php foreach ($search_results as $paper): ?>
              <?php
                  $pmid = str_replace("pmid:", "", $paper->getField("pmid"));
              ?>
              <div class="inner-results">
                  <?php
                      $schema = AbstractSchema::buildReferenceSchema(AbstractSchema::buildPMIDURL($protocol, $pmid));
                  ?>
                  <script type="application/ld+json"><?php echo $schema->generateJSON() ?></script>
                  <h3>
                      <?php echo \helper\htmlElement("collection-bookmark", Array("user" => $user, "uuid" => $pmid, "community" => $community, "view" => "literature")); ?>
                      <a href="<?php echo $community->fullURL() . '/' . $pmid . '?rpKey=on' ?>">
                          <?php echo $paper->getField("title") ?>
                      </a>
                  </h3>
                  <ul class="list-inline up-ul">
                      <?php if(count($paper->getField("Author")) > 1): ?>
                          <li><?php echo $paper->getField("Author")[0]["name"] ?>‎ et al.</li>
                      <?php else: ?>
                          <li><?php echo $paper->getField("Author")[0]["name"] ?>‎</li>
                      <?php endif ?>
                      <li><?php echo join(" | ", $paper->getField("Journal")) ?>‎</li>
                      <li><?php echo $paper->getField("Publication Year") ?>‎</li>
                  </ul>
                  <div class="overflow-h">
                    <div style="float:left" title="Altmetric Information" class="altmetric-embed ocrc" data-hide-no-mentions="true"
                         data-badge-popover="right" data-badge-type="donut"
                         data-pmid="<?php echo $pmid ?>"></div>
                     <div class="overflow-a">
                         <p><?php echo $paper->getField("description") ?></p>
                         <ul class="list-inline down-ul">
                             <?php
                                 $newVars = $vars;
                                 $newVars['parent'] = $array['parent'];
                                 $newVars['child'] = $array['child'];
                             ?>
                             <li>
                                 <a target="_blank" href="http://www.ncbi.nlm.nih.gov/pubmed/<?php echo $pmid ?>">
                                     <img src="/images/US-NLM-PubMed-Logo.svg" class="pubmed-image" style="margin-bottom:0px"/>
                                 </a>
                             </li>
                             <?php
                                 $ocrc_link = WorldCatInterface::getHTML($pmid);
                             ?>
                             <?php if(!is_null($ocrc_link)): ?>
                                 <li><?php echo $ocrc_link ?></li>
                             <?php endif ?>
                         </ul>
                     </div>
                  </div>
              </div>
              <hr/>
            <?php endforeach ?>

            <div class="margin-bottom-30"></div>

            <div class="text-left">
                <?php echo $search->paginateLong($vars)?>
            </div>
        </div>
        <!--/col-md-10-->
    </div>
</div><!--/container-->

<ol id="joyRideTipContent">
    <li data-class="community-logo" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2><?php echo $community->name?> Resources</h2>
        <p>
            Welcome to the <?php echo $community->shortName?> Resources search. From here you can search through
            a compilation of resources used by <?php echo $community->shortName?> and see how data is organized within
            our community.
        </p>
    </li>
    <li data-class="resource-tab" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Navigation</h2>
        <p>
            You are currently on the Community Resources tab looking through categories and sources that <?php echo $community->shortName?>
            has compiled. You can navigate through those categories from here or change to a different tab to execute
            your search through. Each tab gives a different perspective on data.
        </p>
    </li>
    <li data-class="btn-login" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Logging in and Registering</h2>
        <p>
            If you have an account on <?php echo $community->shortName ?> then you can log in from here to get additional
            features in <?php echo $community->shortName ?> such as Collections, Saved Searches, and managing Resources.
        </p>
    </li>
    <li data-class="searchbar" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Searching</h2>
        <p>
            Here is the search term that is being executed, you can type in anything you want to search for. Some tips
            to help searching:
        </p>
        <ol>
            <li style="color:#fff">Use quotes around phrases you want to match exactly</li>
            <li style="color:#fff">You can manually AND and OR terms to change how we search between words</li>
            <li style="color:#fff">You can add "-" to terms to make sure no results return with that term in them (ex. Cerebellum -CA1)</li>
            <li style="color:#fff">You can add "+" to terms to require they be in the data</li>
            <li style="color:#fff">Using autocomplete specifies which branch of our semantics you with to search and can help refine your search</li>
        </ol>
    </li>
    <li data-class="tut-saved" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Save Your Search</h2>
        <p>
            You can save any searches you perform for quick access to later from here.
        </p>
    </li>
    <li data-class="tut-expansion" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Query Expansion</h2>
        <p>
            We recognized your search term and included synonyms and inferred terms along side your term to help get
            the data you are looking for.
        </p>
    </li>
    <li data-class="collection-icon" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Collections</h2>
        <p>
            If you are logged into <?php echo $community->shortName ?> you can add data records to your collections to create custom spreadsheets
            across multiple sources of data.
        </p>
    </li>
    <li data-class="tut-facets" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Facets</h2>
        <p>
            Here are the facets that you can filter your papers by.
        </p>
    </li>
    <li data-class="tut-options" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Options</h2>
        <p>
            From here we'll present any options for the literature, such as exporting your current results.
        </p>
    </li>
    <li data-class="tutorial-btn" data-button="Done" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Further Questions</h2>
        <p>
            If you have any further questions please check out our
            <a href="/<?php echo $community->portalName ?>/about/faq">FAQs Page</a> to ask questions and see our tutorials.
            Click this button to view this tutorial again.
        </p>
    </li>
</ol>
<?php echo \helper\htmlElement("collection-modals", Array("user" => $user, "community" => $community)); ?>
<div class="category-graph very-large-modal back-hide">
    <h1 style="text-align: center">Publications Per Year</h1>
    <div class="close dark">X</div>
    <div class="hover-text">
        <p style="padding:0;margin:0;margin-bottom:5px"><b>Year</b>: <span class="graph-year"></span></p>
        <p style="padding:0;margin:0"><b>Count</b>: <span class="graph-count"></span></p>
    </div>
    <div class="chart">

    </div>
    <!--    <div id="sidebar">-->
    <!--        <input type="checkbox" id="togglelegend"> Legend<br/>-->
    <!--        <div id="legend" style="visibility: hidden;"></div>-->
    <!--    </div>-->
</div>

<script>
// added debug button (show elastic query)  -- Vicky-2019-2-22
function showHideElasticQuery() {
        var ele = document.getElementById("ElasticQuery");
        if(ele.style.display == "block") {
                ele.style.display = "none";
          }
        else {
            ele.style.display = "block";
        }
    }
</script>
