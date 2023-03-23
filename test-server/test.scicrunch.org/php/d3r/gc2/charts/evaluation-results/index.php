<?php
    include('../../../../../classes/classes.php');
    \helper\scicrunch_session_start();

    /*
        if the user is logged in AND the user has level 3 privileges (owner/admin), then set $is_admin = true
        if admin, allow the setting of $debug_user
        if admin, $debug_user sent over as the parameter $_GET['debug'] to json_filter.php
    */
    if (isset($_SESSION['user'])) {
        $user_level = $_SESSION['user']->levels;
        if ($user_level[73] == 3) {
            $is_admin = true;
            $debug_user = $_GET['debug_user'];
        }            
        else 
            $is_admin = false;
    }

    if ((isset($_GET['component'])) && (in_array($_GET['component'], array(417))))
        $component = $_GET['component'];
    else {
        echo "Component ID is invalid or missing.";
        exit;
    }

    if (isset($_GET['results']))
        $results = $_GET['results'];
    else {
        echo "Results type is missing.";
        exit;
    }

/*
        "valid_ligand_range": "1,102",
        "maxpose":0
        }, 
    "freeenergy1": {
        "index":4,
        "txt_required":["FreeEnergyProtocol.txt","PosePredictionProtocol.txt","UserInfo.txt"], 
        "csv_required":["FreeEnergies.csv"], 
        "pdbmol_required":"\/^([a-z0-9]{4})-(FXR_\\d*)$\/i",
        "valid_ligand_specific": "17,45,46,47,48,49,91,93,95,96,98,99,100,101,102",

*/
    $ligand = $_GET['ligand'];
    $chart = $_GET['chart'];

    if ($component == 417) {
        $ligands[] = "AVG";
        $valid_ligand_range = "1,36";
        list($valid_ligand_start, $valid_ligand_end) = explode(",", $valid_ligand_range);
        for ($i=$valid_ligand_start; $i<=$valid_ligand_end; $i++) {
            $ligands[] = "FXR_" . $i;
        }
//        $ligands = array("FXR_1", "AVG"); // 44 was purposely removed
        $title = 'Compound: ';
    } elseif ($component == 280) {
        $ligands = array('MAP_01', 'MAP_02', 'MAP_03', 'MAP_04', 'MAP_05', 'MAP_06', 'MAP_07', 'MAP_08', 'MAP_09', 'MAP_11', 'MAP_12', 'MAP_13', 'MAP_14', 'MAP_15', 'MAP_16', 'MAP_17', 'MAP_18', 'MAP_19', 'MAP_20', 'MAP_21', 'MAP_22', 'MAP_23', 'MAP_25', 'MAP_26', 'MAP_27', 'MAP_28', 'MAP_29', 'MAP_30', 'MAP_31', 'MAP_32', "AVG");
        $title = 'Compound: ';
    }

    if (isset($_GET['average'])) {
        $avg = $_GET['average'];
        $ligand = " Average";
    } else {
        $avg = 0;

        if (isset($_GET['ligand']))
            $ligand = $_GET['ligand'];
        else {
            echo "Ligand is missing.";
            exit;
        }
    }

    $parameters = array();
    $parameters[] = "component=" . $_GET['component'];
    $parameters[] = "pose=" . $pose;
    $parameters[] = "ligand=" . $ligand;
    $parameters[] = "chart=" . $_GET['chart'];
    $parameters[] = "average=" . $avg;
    $parameters[] = "results=" . $_GET['results'];

    // attach debug query parameter if isadmin and debug_user is set
    if ($is_admin && (isset($debug_user)))
        $parameters[] = "debug=" . $_GET['debug_user'];        

    // url path to get json data
    $json_filter = "/php/d3r/gc2/charts/evaluation-results/json_filter.php?" . implode('&', $parameters);
    
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pose - RMSD</title>
    <link rel="stylesheet" href="css/style.css">

    <script src='/js/d3.min.js'></script>
    <script src="js/d3.tip.v0.6.3.js"></script>
</head>

<body>
<h1>Grand Challenge 2</h1>
  <?php
  echo "<h2>Pose - " . strtoupper($results) . " - ";

  if ($_GET['chart'] == 'best'): ?>
<?php echo $title . $ligand . " - Best"; ?></h2>
    <?php elseif ($_GET['chart'] == 'avg'): ?>
<?php echo $title . $ligand . " - Average" ?></h2>
    <?php else: ?>
<?php echo $title . $ligand . " - Pose 1"; ?></h2>
    <?php endif; ?>

<div>
<form name="myForm" method="get" >
<?php
    if (isset($_GET['uid'])) {
        $chall = new Challenge();
        echo '<input type="hidden" name="uid" value="' . $_GET['uid'] . '" />' . "\n";
    }
?>
<input type="hidden" name="component" value="<?php echo $component; ?>" />
<input type="hidden" name="results" value="<?php echo $results; ?>" />
<input id="chart" type="hidden" name="chart" value="<?php echo $_GET['chart']; ?>" />

<?php
    echo "Compound: <select name='ligand' onchange='this.form.submit()'>\n";
    foreach ($ligands as $aligand) {
        if ($aligand == "FXR_33")
            continue;

        if ($aligand == $ligand)
            $select_text = " selected";
        else
            $select_text = "";
        echo "<option $select_text value='" . $aligand . "'>" . $aligand . "</option>\n";
    }
    echo "</select>\n";

?>
<input onclick="fixButton('best');" type="submit" value="Best" />
<input onclick="fixButton('avg');" type="submit" value="Average" />
<input onclick="fixButton('pose');" type="submit" value="Pose 1" />

<!-- <input id="chart" type="hidden" name="chart" value="" /> -->
</form>
</div>

<script>
// set the dimensions of the canvas
var margin = {
        top: 20,
        right: 20,
        bottom: 30,
        left: 60
    },
    width = 1280 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom,
    padding =-80 ;

// set the ranges
var x = d3.scale.ordinal().rangeRoundBands([0, width], .05);
var y = d3.scale.linear().range([height, 0]);

// define the axis
var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom")

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left")
    .ticks(10);

function make_x_axis() {
    return d3.svg.axis()
        .scale(x)
        .orient("bottom")
        .ticks(5)
}

function make_y_axis() {
    return d3.svg.axis()
        .scale(y)
        .orient("left")
        .ticks(10)
}

var tip = d3.tip()
    .attr('class', 'd3-tip')
    .offset([-10, 0])
    .html(function(d) {
        return "<strong><?php echo strtoupper($results); ?>:</strong> " + d.Freq.toPrecision(3);
    })

  // add the SVG element
var svg = d3.select("body").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom + 200)
    .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

svg.call(tip);

// load the data
d3.json("<?php echo $json_filter; ?>", function(error, data){

    data.numerics.forEach(function(d) {
        d.Letter = d.label;
        d.Freq = +d.frequency;
    });

    
    // scale the range of the data
    x.domain(data.numerics.map(function(d) { return d.Letter; }));
    y.domain([0, d3.max(data.numerics, function(d) { return d.Freq; })]);

  // add axis
    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis)
        .selectAll("text")
        .style("text-anchor", "start")
        .style("font-family", "courier")
        .attr("dx", "0.5em")
        .attr("dy", "-.1em")
        .attr("transform", "rotate(70)" );

    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis)
        .append("text")
        .attr("transform", "rotate(-90)")
        .attr("y", 5)
        .attr("dy", ".71em");

        // now add titles to the axes
    svg.append("text")
        .attr("text-anchor", "middle")  // this makes it easy to centre the text as the transform is applied to the anchor
        .attr("transform", "translate("+ (padding/2) +","+(height/3)+")rotate(-90)")  // text is drawn off the screen top left, move down and out and rotate
        .text("RMSD (Å)")
        .style("font-size", "14pt")
        .style("font-weight", "bold");

    svg.append("text")
        .attr("transform", "translate(" + (width / 2) + " ," + (height + margin.bottom + 50) + ")")
        .style("text-anchor", "middle")
        .text("Receipt ID")
        .style("font-size", "14pt")
        .style("font-weight", "bold");

    svg.append("g")
        .attr("class", "grid")
        .attr("transform", "translate(0," + height + ")")
        .call(make_x_axis()
            .tickSize(-height, 0, 0)
            .tickFormat("")
        )

    svg.append("g")
        .attr("class", "grid")
        .call(make_y_axis()
            .tickSize(-width, 0, 0)
            .tickFormat("")
        )

    // Add bar chart
    svg.selectAll("bar")
        .data(data.numerics)
        .enter().append("rect")
        .attr("class", "bar")
        .attr("x", function(d) { return x(d.Letter); })
        .attr("width", x.rangeBand())
        .attr("y", function(d) { return y(d.Freq); })
        .attr("height", function(d) { return height - y(d.Freq); })
        .on('mouseover', tip.show)
        .on('mouseout', tip.hide);

        // use more 'elegant' solution of pure JS rather than PHP echoing JS
        var flagg = data.flags;
        for (i=0; i<flagg.length; i++) {
            svg.selectAll("rect")
                .filter(function(d) { return (d.label === flagg[i]) })
                .classed("mine", true);
        }

        var mine_less = data.mine_less;
        for (i=0; i<mine_less.length; i++) {
            svg.selectAll("rect")
                .filter(function(d) { return (d.label === mine_less[i]) })
                .classed("mine_less", true);
        }

        var anon_less = data.anon_less;
        for (i=0; i<anon_less.length; i++) {
            svg.selectAll("rect")
                .filter(function(d) { return (d.label === anon_less[i]) })
                .classed("anon_less", true);
        }

});

        svg.append("text")
            .attr("class", "x label")
            .attr("text-anchor", "end")
            .attr("x", width)
            .attr("y", height + 60)
            .text("Pale color indicates an incomplete set of predictions");

        svg.append("text")
            .attr("class", "x label")
            .attr("text-anchor", "end")
            .attr("x", width)
            .attr("y", height + 80)
            .text("Green bar indicates your predictions (requires login)");

function fixButton(chart) {
    document.getElementById("chart").value = chart;
}
</script>

</body>
