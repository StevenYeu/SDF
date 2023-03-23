<?php

$chart_data = getChartData();
$source_data = getSourcesWithImages();

/******************************************************************************************************************************************************************************************************/
// function definitions

function getChartData(){
    $max_refresh_time = 86400;
    $cache_file = $GLOBALS["DOCUMENT_ROOT"] . "/vars/views-all-search.php";
    if(file_exists($cache_file)) $mtime = time() - filemtime($cache_file);
    else $mtime = MAXINT;

    if($mtime > $max_refresh_time) {
        $search = new BaseSearch();
        $results = $search->doSearch("summary");
        file_put_contents($cache_file, serialize($results));
    } else {
        $results = unserialize(file_get_contents($cache_file));
    }
	return $results;
}

function getSourcesWithImages(){
    $holder = new Sources();
    $sources = $holder->getAllSources();

    $return_sources = Array();
    $found_images = Array();
    foreach($sources as $s){
        if(strlen($s->image) < 20 || isset($found_images[$s->image])) continue;
        $found_images[$s->image] = 1;
        array_push($return_sources, Array("id" => $s->nif, "image" => $s->image, 'source_name' => $s->source));
    }

    return $return_sources;
}

/******************************************************************************************************************************************************************************************************/
?>

<style>
    p {
        font-size: 12pt;
    }
</style>
<script src="/js/d3.min.js"></script>
<script src="/js/d3pie.min.js"></script>
<script src="/js/Highcharts-6.0.7/code/js/highcharts.js"></script>
<script src="/js/Highcharts-6.0.7/code/js/modules/data.js"></script>
<script src="/js/Highcharts-6.0.7/code/js/modules/drilldown.js"></script>
<link rel="stylesheet" href="/js/Highcharts-6.0.7/code/css/highcharts.css" />
<script src="/js/pie-highcharts.js"></script>
<script>
	$(function(){
        var chart_data = <?php echo json_encode($chart_data); ?>;
        buildPieChart(chart_data);

        $("#most-cited").owlCarousel({
            autoPlay: 2000,
            pagination: false
        });
	});
</script>

<div class="container margin-bottom-50" style="margin-top: 50">

    <div class="row">
        <div id="piechart" class="col-md-6"></div>
        <div class="col-md-4">
            <p style="text-align:right;">
                SciCrunch searches <?php echo count($sources) ?> views on data collected from federated data sources.  To discover new data, search for concepts you are interested in learning about.  For example, search for data on <a href="/scicrunch/data/search?q=breast%20cancer%20genetics">breast cancer genetics</a>, <a href="/scicrunch/data/search?q=c%20elegans">C. elegans</a>. <a href="/scicrunch/data/search?q=hiv%20funding">HIV funding</a>, or anything else you're interested in. Or browse through all of our <a href="/scicrunch/data/search?q=">federated data views</a>.
            </p>
        </div>
    </div>

    <hr/>

    <div class="row"><div class="col-md-4"><h3>Some of our sources</h3></div></div>
    <div class="col-md-12">
        <div class="owl-carousel" id="most-cited">
            <?php foreach($source_data as $sd): ?>
                <div class="item">
                    <a href="/scicrunch/data/source/<?php echo $sd['id'] ?>/search?q=*">
                        <img class="sponsor-img img-responsive" src="<?php echo $sd['image'] ?>" alt="<?php echo $sd["source_name"] ?>" />
                    </a>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
