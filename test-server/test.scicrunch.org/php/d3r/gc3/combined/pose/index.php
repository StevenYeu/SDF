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

    if (isset($_GET['component'])) {
        $component = $_GET['component'];
        if ($component == 968)
            $dataname = "Cathepsin S (Stage1A)";
        elseif ($component == 972)
            $dataname = "Cathepsin S (Stage1B)";
        else {
            echo "Component ID is invalid or missing.";
            exit;
        }
    } else {
        echo "Component ID is missing.";
        exit;
    }


    if (isset($_GET['results']))
        $results = $_GET['results'];
    else {
        echo "Results type is missing.";
        exit;
    }

    if (isset($_GET['partial']) && ($_GET['partial']))
        $partial = 1;
    else
        $partial = 0;

    $ligand = $_GET['ligand'];
    $chart = $_GET['chart'];

    if (($component == 968) || ($component == 972)) {
        $ligands[] = "Mean";
        $ligands[] = "Median";
        $valid_ligand_range = "1,24";
        list($valid_ligand_start, $valid_ligand_end) = explode(",", $valid_ligand_range);
        for ($i=$valid_ligand_start; $i<=$valid_ligand_end; $i++) {
            $ligands[] = "CatS_" . $i;
        }
//        $ligands = array("FXR_1", "AVG"); // 44 was purposely removed
        $title = 'Compound: ';
    }

    if (isset($_GET['average'])) {
        $avg = $_GET['average'];
        $ligand = " Average";
    } else {
        $avg = 0;

        if (isset($_GET['ligand'])) {
            if (($ligand == 'Mean') || ($ligand == 'Median'))
                $ligand .= " over all";
            else
                $ligand = $_GET['ligand'];
        } else {
            echo "Ligand is missing.";
            exit;
        }
    }

    $parameters = array();
    $parameters[] = "component=" . $_GET['component'];
    $parameters[] = "pose=" . $pose;
    $parameters[] = "ligand=" . $_GET['ligand'];
    $parameters[] = "chart=" . $_GET['chart'];
    $parameters[] = "average=" . $avg;
    $parameters[] = "results=" . $_GET['results'];
    $parameters[] = "partial=" . $partial;

    // attach debug query parameter if isadmin and debug_user is set
    if ($is_admin && (isset($debug_user)))
        $parameters[] = "debug=" . $_GET['debug_user'];

    // url path to get json data
    $json_filter = "/php/d3r/gc3/combined/pose/json_filter.php?" . implode('&', $parameters);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>GC3 Pose Prediction Method - RMSD</title>

    <link rel="stylesheet" href="css/style.css">
    <script src='/js/d3.min.js'></script>
    <script src="js/d3.tip.v0.6.3.js"></script>
    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>

<h1>Grand Challenge 3 - Pose Prediction Method - <?php echo $dataname; ?></h1>
<form name="myForm" method="get" >

  <?php
  echo "<h2>Pose RMSDs (Å) ";
  ?>

    <?php if ($_GET['chart'] == 'closest'): ?>
         - Closest (lowest RMSD) <input onclick="fixButton('avg');" type="submit" value="Average" /> <input onclick="fixButton('pose');" type="submit" value="Pose 1" /></h2>
    <?php elseif ($_GET['chart'] == 'avg'): ?>
        - Average <input onclick="fixButton('closest');" type="submit" value="Closest Pose" /> <input onclick="fixButton('pose');" type="submit" value="Pose 1" /></h2>
    <?php else: ?>
        - Pose 1 <input onclick="fixButton('closest');" type="submit" value="Closest Pose" /> <input onclick="fixButton('avg');" type="submit" value="Average Pose" /></h2>
    <?php endif; ?>

<div>
<?php
    if (isset($_GET['uid'])) {
        $chall = new Challenge();
        echo '<input type="hidden" name="uid" value="' . $_GET['uid'] . '" />' . "\n";
    }
?>
<input type="hidden" name="component" value="<?php echo $component; ?>" />
<input type="hidden" name="results" value="<?php echo $results; ?>" />
<input id="chart" type="hidden" name="chart" value="<?php echo $_GET['chart']; ?>" />
<input type="hidden" name="partial" value="<?php echo $partial; ?>" />

<?php
    echo "<h2>" . $title . " <select name='ligand' onchange='this.form.submit()'>\n";
    foreach ($ligands as $aligand) {
        if ($aligand == "FXR_33")
            continue;

        if (strpos($ligand, $aligand) !== false)
            $select_text = " selected";
        else
            $select_text = "";

        if (($aligand == 'Mean') || ($aligand == 'Median'))
            echo "<option $select_text value='" . $aligand . "'>" . $aligand . " over all</option>\n";
        else
            echo "<option $select_text value='" . $aligand . "'>" . $aligand . " </option>\n";
    }
    echo "</select></h2>\n";
?>

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
    height = 300 - margin.top - margin.bottom,
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
        return "<strong><?php echo strtoupper($results); ?>:</strong> " + d.Freq.toFixed(2);
    })

  // add the SVG element
var svg = d3.select("body").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom + 100)
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
            .attr("y", height + 80)
            .text("Green bar indicates your predictions (requires login)");

function fixButton(chart) {
    document.getElementById("chart").value = chart;
}
</script>

<?php
    $include_method = "pose" . $component;
    include "../spreadsheets/include_bottom_table.php";
?>

<script>
$(document).ready(function() {
    $('tr.tabs th').click(function(){
        var tab_id = $(this).attr('data-tab');
        var uri = '<?php echo $_SERVER['REQUEST_URI']; ?>';

        if (uri.indexOf("Mean") >= 0) {
            if (tab_id === "Median")
                window.location.href='<?php echo str_replace("Mean", "Median", $_SERVER['REQUEST_URI']); ?>';
        }
        else
            if (uri.indexOf("Median") >= 0) {
                if (tab_id === "Mean")
                    window.location.href='<?php echo str_replace("Median", "Mean", $_SERVER['REQUEST_URI']); ?>';
            }
    })

    <?php
        if (!(stripos($_SERVER['REQUEST_URI'], "Median" ))):
    ?>
       $('#gc3results').dataTable( {
         "pageLength": 10,
         "order": [[ 5, "asc" ]]
       });
    <?php else: ?>
       $('#gc3results').dataTable( {
         "pageLength": 10,
         "order": [[ 4, "asc" ]]
       });

    <?php endif; ?>

   $('#gc3results_length').append("<span style='padding-left: 50px; font-size: 1em;'><a href='<?php echo str_replace("newcsvs", "downloadcsv", $file); ?>'><img src='/assets/flaticons/png/csv.png' height='32'></a> <a href='<?php echo str_replace("newcsvs", "downloadcsv", $file); ?>'>Download data</a></span>");
});
</script>



</body>
</html>
