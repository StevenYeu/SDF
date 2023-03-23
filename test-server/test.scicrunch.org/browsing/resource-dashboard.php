<?php

$chart_data = getChartData();
$most_cited = getMostCited(25);
$n_records = getNRecords();
$n_records_text = !$n_records ? "over 14,000" : number_format($n_records);

/******************************************************************************************************************************************************************************************************/
// function definitions

function getChartData(){
    $search = new BaseSearch(Array("source" => Array("nlx_144509-1")));
    $results = $search->doSearch("single_source_facets");
    return $results;
}

function getNRecords(){
    $search = new BaseSearch(Array("source" => Array("nlx_144509-1"), "results_per_page" => 1));
    $results = $search->doSearch("single_source");
    return $results['count'];
}

function getMostCited($n){
    $max_refresh_time = 86400;
    $most_cited_file = $_SERVER['DOCUMENT_ROOT'] . '/vars/most-cited-resources.php';

    if(file_exists($most_cited_file)) $mtime = time() - filemtime($most_cited_file);
    else $mtime = MAXINT;

    if($mtime > $max_refresh_time){
        $cxn = new Connection();
        $cxn->connect();
        $resources = Array();
        $found_resources = $cxn->select("resource_mentions", Array("rid, count(*) as c"), "i", Array($n), "group by rid order by c desc limit ?");
        foreach($found_resources as $fr){
            $resource = new Resource();
            $resource->getByID($fr['rid']);
            if(!$resource->image) continue;
            $dups = $cxn->select("resource_relationships", Array("*"), "s", Array($resource->rid), "where id2=? and reltype_id=1");
            if(!empty($dups)) continue;
            $resource->getColumns();
            $url = buildURLSingleView($resource);
            $image = "/upload/resource-images/" . $resource->image;
            $resources[] = Array("name" => $resource->columns["Resource Name"], "c" => $fr['c'], "pmid_count" => $r["c"], "url" => $url, "image" => $image);
        }
        $cxn->close();
        file_put_contents($most_cited_file, serialize($resources));
    }else{
        $resources = unserialize(file_get_contents($most_cited_file));
    }

    return $resources;
}

function buildURLSingleView($resource){
    $base_url = "/scicrunch/Resources/record/nlx_144509-1/%s/search";
    $url = sprintf($base_url, $resource->uuid);
    return $url;
}

/******************************************************************************************************************************************************************************************************/
?>

<style>
    .info-text {
        font-size: 12pt;
    }
</style>
<script src="/js/d3.min.js"></script>
<script src="/js/d3pie.min.js"></script>
<script>
    $(function(){
        var chart_data = <?php echo json_encode($chart_data); ?>;
        var content = [];
        var resource_types = chart_data['Resource Type'];
        for(var i = 0; i < resource_types.length && i < 10; i++){
            content.push({'label': resource_types[i]['name'], 'value': resource_types[i]['count'] });
        }

        var pie = new d3pie("piechart", {
            header: {title: {text: "Resource Types"}},
            size: {canvasHeight: 400, canvasWidth: 500},
            labels: {inner:{format: "value"}},
            misc: {canvasPadding: {left:50}},
            data: {content: content},
            callbacks: {
                onClickSegment: function(a){
                    //window.location = "/scicrunch/Resources/source/nlx_144509-1/search?q=*&facet[]=Resource%20Type:" + a.data.label;
                    window.location = "/scicrunch/Resources/search?q=*&facet[]=Resource%20Type:" + a.data.label;
                }
            }
        });

        $("#most-cited").owlCarousel({
            autoPlay: 2000,
            pagination: false
        });
    });
</script>

<div class="container margin-bottom-50" style="margin-top: 50">

    <div class="row">
        <div class="col-md-4">
            <p class="info-text">
                The SciCrunch Registry holds metadata records that describe digital resources, e.g., software, databases, projects and also services. Most of these are produced as a result of government funding and are available to the scientific community. Resources are manually curated to make sure the information is accurate. We also use a web crawler to find literature mentions for the resources. With <?php echo $n_records_text ?> records, there's almost certainly a resource you'll find useful.  Start searching above or submit your own resource.
            </p>
        </div>
        <div id="piechart" class="col-md-6"></div>
    </div>

    <hr/>

    <div class="row"><div class="col-md-4"><h3>Most cited resources</h3></div></div>
    <div class="col-md-12">
        <div class="owl-carousel" id="most-cited">
            <?php foreach($most_cited as $resource): ?>
                <div class="item">
                    <a href="<?php echo $resource['url'] ?>">
                        <img class="sponsor-img img-responsive" src="<?php echo $resource['image'] ?>" alt="<?php echo $resource["name"] ?>" />
                    </a>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</div>
