<?php
$page = Array();

$vars['q'] = $query;

$community = new Community();
$community->id = 0;
$holder = new Component();
$components = $holder->getByCommunity($community->id);

$community_search = new Community();
$user_comms = isset($_SESSION['user']) ? array_keys($_SESSION['user']->levels) : false;
$community_results = $community_search->searchCommunities($user_comms, $display_query, 0, 10000);

$vars['community'] = $community;
$page['community'] = $community;

$vars['source'] = Array('nlx_144509-1');
$search = new BaseSearch($vars);
$page['summary_results'] = formatSourceObjects($search->doSearch('summary'), $query);
$page['resource_results'] = formatResourceResults($search->doSearch('single_source'), $query);
$page['community_sources'] = getSourcesInCommunities($page['summary_results']['sources'], $community_results, $_SESSION['user']);
$page['literature'] = formatLiteratureResults($search->doSearch('literature'));
$page['title'] = "Search Results";


/******************************************************************************************************************************************************************************************************/

function getSourcesInCommunities(&$source_results, &$comm_search_results, &$user){
    $holder = new Category();
    $sources_by_comm = $holder->getUsed();

    $used_sources = Array();	// build a set to check if nifid is present
    foreach($source_results as $source){
        $used_sources[$source['nifId']] = 1;
    }

    $communities = Array();	// find the sources in each community
    foreach($sources_by_comm as $source){
        $nif_source = $source->source;
        if(isset($used_sources[$nif_source])){
            $cid = $source->cid;
            if($cid == 0) continue;
            if(isset($communities[$cid])){
                array_push($communities[$cid]['sources'], $nif_source);
            }else{
                $comm = new Community();
                $comm->getByID($cid);
                $communities[$cid] = Array("community" => $comm, "sources" => Array($nif_source));
            }
        }
    }
    foreach($comm_search_results['results'] as $comm){  // get communities that had text matches but no data matches
        if(!in_array($comm->id, array_keys($communities))){
            $communities[$comm->id] = Array("community" => $comm, "sources" => Array());
        }
    }

    $bad_cids = Array();
    foreach($communities as $cid => &$comm){    // reformat communities
        if(!$comm['community']->isVisible($user) || !$comm['community']->name){
            $bad_cids[] = $cid;
            continue;
        }
        $comm['count'] = count($comm['sources']);
        $comm['link'] = '/' . $comm['community']->portalName;
        $comm['image'] = "/upload/community-logo/" . $comm['community']->logo;
        $comm['title'] = $comm['community']->name;
        $comm['body'] = $comm['community']->description;
    }

    foreach($bad_cids as $bc) unset($communities[$bc]);

    uasort($communities, function($a, $b){	// sort by number of sources in each community
        if(count($a['sources']) == count($b['sources'])) return 0;
        return (count($a['sources']) > count($b['sources'])) ? -1 : 1;
    });

    return $communities;
}

function formatSourceObjects($summary_results, $query){
    $holder = new Sources();
    $all_sources = $holder->getAllSources();

    foreach($summary_results['sources'] as &$source){
        if(!isset($all_sources[$source['nifId']])) continue;
        $source['source'] = $all_sources[$source['nifId']];
        $source['link'] = '/scicrunch/data/source/' . $source['source']->nif .'/search?q=' . $query;
        $source['image'] = $source['source']->image;
        if(!\helper\startsWith($source['image'], "/upload")) $source["image"] = "/images/no-image-available.png";
        $source['title'] = $source['source']->getTitle();
        $source['body'] = $source['source']->description;
        //$source['circle'] = getSourceCircle($source);
    }

    uasort($summary_results['sources'], function($a, $b){	// sort by number of results in each source
        if((int) $a['count'] == (int) $b['count']) return 0;
        return ((int) $a['count'] > (int) $b['count']) ? -1 : 1;
    });

    return $summary_results;
}

function formatResourceResults($resources, $query){
    foreach($resources['results'] as &$resource){
        $rid = strip_tags($resource['Resource ID']);
        $r = new Resource();
        $r->getByOriginal($rid);
        $resource['title'] = strip_tags($resource['Resource Name']);
        if(gettype($resource['body']) == "array") $resource["Description"] = "";
        else $resource['body'] = \helper\formattedDescription($resource['Description']);
        if($r->image) $resource['image'] = '/upload/resource-images/' . $r->image;
        else $resource['image'] = NULL;
        $resource['link'] = '/scicrunch/Resources/record/nlx_144509-1/' . $resource['v_uuid'] . '/search?q=' . $query; 
    }
    return $resources;
}

function formatLiteratureResults($literature){
    foreach($literature['publications'] as &$pub){
        $pub['body'] = $pub['abstract'];
        $pub['link'] = '/' . $pub['@attributes']['pmid'];
        $pub['image_like'] = '<div title="Altmetric Information" class="altmetric-embed ocrc" data-hide-no-mentions="true" data-badge-popover="right" data-badge-type="donut" data-pmid="'.$pub['@attributes']['pmid'].'"></div>';
    }
    return $literature;
}

function getHiddenClass($idx){
    if($idx == 3) return "half-hidden";
    if($idx > 3) return "full-hidden";
    return "";
}

function resultsSection($more_link, $section_title, $count_message, $results, $result_count_message, $col_result_counts){
    $col_size = $col_result_counts ? "col-md-8" : "col-md-11";
    ob_start();?>

    <div class="col-md-6 col-xs-12">
        <div class="table-search-v1 panel panel-dark margin-bottom-50">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <?php if($more_link): ?><a href="<?php echo $more_link ?>"><?php endif ?>
                        <?php echo $section_title ?>
                    <?php if($more_link): ?><i class="glyphicon glyphicon-share-alt"></i></a> <?php endif ?>
                </h3>
                <?php echo $count_message ?>
            </div>
            <div class="panel-body panel-4">
                <?php $i = 0; ?>
                <?php foreach($results as $res): ?>
                    <?php if($i > 19) break ?>
                    <?php $i+=1 ?>
                    <div class="row single-result">
                        <div class="col-md-3">
                            <?php if(isset($res['image'])): ?>
                                <a href="<?php echo $res['link'] ?>"><img class="small-img img-responsive" src="<?php echo $res['image'] ?>" onerror="this.style.display='none'"/></a>
                            <?php elseif(isset($res['image_like'])): ?>
                                <?php echo $res['image_like']; ?>
                            <?php endif ?>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="<?php echo $col_size ?>">
                                    <a href="<?php echo $res['link'] ?>"><strong><?php echo $res['title'] ?></strong></a>
                                </div>
                                <?php if($col_result_counts): ?>
                                    <div class="col-md-3">
                                        <?php if(isset($res['count'])) echo $res['count'] . ' ' . $result_count_message ?>
                                    </div>
                                <?php endif ?>
                                <div class="col-md-1">
                                    <?php if($res['body']): ?><i class="info-icon glyphicon glyphicon-info-sign"></i><?php endif ?>
                                </div>
                            </div>
                            <div class="row description">
                                <div class="<?php echo $col_size ?> long-description"><?php echo strip_tags($res['body']) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach ?>
                <?php if($more_link && count($results) > 0): ?><div style="margin-top:10px"><a href="<?php echo $more_link ?>">See all results</a></div><?php endif ?>
            </div>
        </div>
    </div>

    <?php
    $html = ob_get_clean();
    return $html;
}


/******************************************************************************************************************************************************************************************************/
?>

<style>
    .full-hidden {
        display:none;
    }
    .half-hidden {
        opacity: .5;
    }
    .half-hidden:hover {
        opacity: .7;
        cursor: pointer;
    }
    .results {
        padding-bottom: 50px;
    }
    .single-result {
        border-bottom: 1px #aaa solid;
        padding-bottom: 20px;
        padding-top: 20px;
    }
    .panel-4 {
        height: 300px;
        overflow: auto;
    }
    .small-img {
        max-height:50px;
        max-width:100%;
        width:auto;
        height:auto;
    }
    .description {
        display: none;
    }
    .info-icon {
        cursor: pointer;
    }
</style>

<script src="/js/jquery.truncate.js"></script>
<script src="/assets/plugins/jquery-ui.min.js"></script>
<script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'></script>
<script>
    $(function(){
        $('.half-hidden').on('click', function(e){
            $(this).removeClass("half-hidden");
            $(this).siblings().removeClass("full-hidden");
        });
        $('.long-description').truncate({max_length: 300});

        $('.info-icon').on('click', function(e){
            $(this).parent().parent().parent().find(".description").slideToggle("fast");
        });
    });
</script>


<div class="results row">

    <!-- data sources -->
    <div class="row">
        <?php
        $count_message = number_format($page['summary_results']['totalCount']) . " results from " . count($page['summary_results']['sources']) . " data sources";
        if(count($page['summary_results']['sources']) > 20) $count_message .= " (only showing 20 data sources)";
        $result_counts = true;
        echo resultsSection("/scicrunch/data/search?q=" . $query . "&l=" . $display_query, "Data Sources", $count_message, $page['summary_results']['sources'], "Results", $result_counts);
        ?>
        <!-- /data sources -->

        <!-- communities -->
        <?php
        $count_message = number_format(count($page['community_sources'])) . " communities with matching data sources";
        $result_counts = true;
        echo resultsSection("", "Communities", $count_message, $page['community_sources'], "matching sources", $result_counts);
        ?>
        <!-- /communities -->
    </div>

    <div class="row">
        <!-- resources -->
        <?php
        $count_message = number_format($page['resource_results']['count']) . " resources";
        if($page['resource_results']['count'] > 20) $count_message .= " (only showing 20 results)";
        $result_counts = false;
        echo resultsSection("/scicrunch/Resources/search?q=" . $query . "&l=" . $display_query, "Resources", $count_message, $page['resource_results']['results'], "", $result_counts);
        ?>
        <!-- /resources -->

        <!-- literature -->
        <?php
        $count_message = number_format($page['literature']['count']) . " literature";
        if($page['literature']['count'] > 20) $count_message .= " (only showing 20 results)";
        $result_counts = false;
        echo resultsSection("/scicrunch/literature/search?q=" . $query . "&l=" . $display_query, "Literature", $count_message, $page['literature']['publications'], "", $result_counts);
        ?>
        <!-- /literature -->
    </div>

</div>
