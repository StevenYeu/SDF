<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" type="text/css" href="distrochart.css">
    <script src="d3.v3.min.js" charset="utf-8"></script>

<style>
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 400px;
    background-color: #777;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 5px 0;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -30px;
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltip .tooltiptext::after {
    content: "";
    position: absolute;
    top: 100%;
    left: 50%;
    margin-left: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: #555 transparent transparent transparent;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>

</head>
<body>

<?php
if (!(isset($_GET['weeks']))) {
    echo "You must specify how many weeks to plot.";
    echo "</body>\n</html>\n";
    exit;
}

if (!(isset($_GET['plot']))) {
    echo "You must specify the type of plot.";
    echo "</body>\n</html>\n";
    exit;
}

if (!(isset($_GET['rmsd']))) {
    echo "You must specify the rmsd type.";
    echo "</body>\n</html>\n";
    exit;
}

include('../../../classes/classes.php');
$challenge = new Challenge;
$yearweek = $challenge->getCELPPYearWeek($_GET['weeks']);
$summ = $challenge->getCELPPtargetSum($yearweek);

$titlemapping['LMCSS'] = 'Candidate protein solved with ligand having largest maximum common substructure with target ligand';
$titlemapping['SMCSS'] = 'Candidate protein solved with ligand having smallest maximum common substructure with target ligand';
$titlemapping['hiTanimoto'] = 'Candidate protein solved with ligand having largest Tanimoto similarity to target ligand';
$titlemapping['hiResHolo'] = 'Highest resolution ligand-bound Candidate protein';
$titlemapping['hiResApo'] = 'Highest resolution unbound Candidate protein';

echo "<h2 style='margin-bottom: 2px;'>" . $titlemapping[$_GET['rmsd']] . " (" . $_GET['rmsd'] . ")</h2>\n";
echo "<h3 style='margin-top: 2px;'>" . ucwords($_GET['weeks']) . " weeks, " . $summ[0]['summ'] . " targets</h3>";
?>
<a class='tooltip' href='fancybox.php?weeks=<?php echo $_GET['weeks']; ?>&plot=box&rmsd=LMCSS'><strong>LMCSS<span class='tooltiptext'>Candidate protein solved with ligand having largest maximum common substructure with target ligand</span></strong></a>&nbsp; 
<a class='tooltip' href='fancybox.php?weeks=<?php echo $_GET['weeks']; ?>&plot=box&rmsd=SMCSS'><strong>SMCSS<span class='tooltiptext'>Candidate protein solved with ligand having smallest maximum common substructure with target ligand</span></strong></a>&nbsp; 
<a class='tooltip' href='fancybox.php?weeks=<?php echo $_GET['weeks']; ?>&plot=box&rmsd=hiResApo'><strong>hiResApo<span class='tooltiptext'>Highest resolution unbound Candidate protein</span></strong></a>&nbsp; 
<a class='tooltip' href='fancybox.php?weeks=<?php echo $_GET['weeks']; ?>&plot=box&rmsd=hiResHolo'><strong>hiResHolo<span class='tooltiptext'>Highest resolution ligand-bound Candidate protein</span></strong></a>&nbsp; 
<a class='tooltip' href='fancybox.php?weeks=<?php echo $_GET['weeks']; ?>&plot=box&rmsd=hiTanimoto'><strong>hiTanimoto<span class='tooltiptext'>Candidate protein solved with ligand having largest Tanimoto similarity to target ligand</span></strong></a>&nbsp; 

<div class="chart-options0">
<?php
    $link = "fancybox.php?plot=" . $_GET['plot'] . "&rmsd=" . $_GET['rmsd'] . "&weeks=";
?>    
    <button onclick="window.location.href='<?php echo $link;?>all'">All weeks</button>
    <button onclick="window.location.href='<?php echo $link;?>3'">3 Weeks</button>
    <button onclick="window.location.href='<?php echo $link;?>1'">Last week</button>
</div>


<div class="chart-wrapper" id="chart-distro1"></div>

<!--Sorry about all the inline JS. It is a quick way to show what options are available-->
<div class="chart-options">

    <p>Show: </p>
    <button onclick="note_show('box_note');  chart1.boxPlots.show({reset:true});chart1.notchBoxes.hide();chart1.dataPlots.change({showPlot:false,showBeanLines:false})">Box Plot</button>
<!--    <button onclick="note_show('box_note');  chart1.notchBoxes.show({reset:true});chart1.boxPlots.show({reset:true, showBox:false,showOutliers:true,boxWidth:20,scatterOutliers:true});chart1.dataPlots.change({showPlot:false,showBeanLines:false})">Notched Box Plot</button> -->
    <button onclick="note_show('scatter_note'); chart1.violinPlots.hide();chart1.dataPlots.show({showPlot:true, plotType:40, showBeanLines:false,colors:null});chart1.notchBoxes.hide();chart1.boxPlots.hide();">Scatter Plot</button>
</div>
<div clear="all" />
    <p id="box_note" style="display: block; width: 1000px;"><strong>Boxes</strong>: first and third quartiles (Q1, Q3). <strong>Whiskers</strong>: min and max after removal of outliers (points 1.5 times the interquartile range above Q3 and below Q1). <strong>Dots</strong>: outliers; Those above the graph range are placed at the graph maximum.</p>
    <p id="scatter_note" style="display: none; width: 1000px;">Dots above the graph maximum are placed at the maximum.</p>


<script src="distrochart.js" charset="utf-8"></script>
<script type="text/javascript">
    function note_show(what) { 
   var x = document.getElementById(what);
   if (what == 'box_note') {
       var y = document.getElementById("scatter_note");
   } else {
    var y = document.getElementById("box_note");
   }

        x.style.display = "block";
        y.style.display = "none";
}
    var chart1;
    d3.csv('/php/d3r/celpp/boxplot_cumulative.php?rmsd=<?php echo $_GET['rmsd'];?>&weeks=<?php echo $_GET['weeks'];?>&plot=<?php echo $_GET['plot']; ?>', function(error, data) {
        data.forEach(function (d) {d.value = +d.value;});

        chart1 = makeDistroChart({
            data:data,
            xName:'date',
            yName:'value',
            axisLabels: {xAxis: null, yAxis: 'Values'},
            selector:"#chart-distro1",
            chartSize:{height:550, width:960},
            constrainExtremes:true});
        chart1.renderBoxPlot();
        chart1.renderDataPlots();
        chart1.renderNotchBoxes({showNotchBox:false});
        chart1.renderViolinPlot({showViolinPlot:false});

    });
</script>
</body>
</html>
