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

    if ((isset($_GET['set'])) && (($_GET['set'] == 1) || ($_GET['set'] == 2))) {
        $set = $_GET['set'];
    } else {
        echo "Must be Set 1 or 2";
        exit;
    }

    if ((isset($_GET['component'])) && (in_array($_GET['component'], array("417", "443")))) {
        $component = $_GET['component'];
        switch ($component) {
            case "417":
                $title = "Free Energy Method Set $set (Stage 1)";
                break;

            case "443":
                $title = "Free Energy Method Set $set (Stage 2)";
                break;
        }
    } else {
        echo "Component ID is invalid or missing.";
        exit;
    }

    if (!(isset($_GET['set']))) {
        echo "Must have a Set value of 1 or 2.\n";
        exit;
    }

    if (isset($_GET['partial']) && ($_GET['partial'] == 1))
        $partial = 1;
    else
        $partial = 0;

    $parameters = array();
    $parameters[] = "component=" . $component;
    $parameters[] = "set=" . $_GET['set'];
    $parameters[] = "q=rmsd";
    $parameters[] = "partial=" . $partial;

    // attach debug query parameter if isadmin and debug_user is set
    if ($is_admin && (isset($debug_user)))
        $parameters[] = "debug=" . $_GET['debug_user'];

    // url path to get json data
    $json_filter = "/php/d3r/gc2/combined/free-energy/json_filter.php?" . implode('&', $parameters);

?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">

  <script src='/js/d3.min.js'></script>
  <script src="js/d3.tip.v0.6.3.js"></script>
  <link rel="stylesheet" href="css/free.css">
    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>

  <title><?php echo $title . ' - ' . $which; ?></title>

<script type='text/javascript'>
function drawChart(set, block_id){
    var margin = {
        top: 20,
        right: 20,
        bottom: 30,
        left: 60
    },
    width = 1280 - margin.left - margin.right,
    height = 200 - margin.top - margin.bottom,
    padding =-80 ;

    var x0 = d3.scale.ordinal()
        .rangeRoundBands([0, width], .1);

    var y0 = d3.scale.linear()
        .range([height, 0]);

    var y1 = d3.scale.linear()
        .range([height, 0]);

    var color = d3.scale.ordinal()
        .range(["#7b6888"]);

    var xAxis = d3.svg.axis()
        .scale(x0)
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y0)
        .orient("left");

    function make_y_axis() {
        return d3.svg.axis()
            .scale(y0)
            .orient("left")
            .ticks(15)
    }

    function make_x_axis() {
        return d3.svg.axis()
            .scale(x0)
            .orient("bottom")
            .ticks(15)
    }

    var filter = '<?php echo $json_filter; ?>';
    if (block_id == '#kendall_block') {
        filter = filter.replace('rmsd', 'kendall');
        var  y_label = "Kendall's Tau";
    } else {
      var  y_label = 'RMSEc (kcal/mol)';
    }

    var tip = d3.tip()
      .attr('class', 'd3-tip')
      .offset([-10, 0])
      .html(function(d) {
        return "<strong>" + y_label + "</strong>: " + d.value.toPrecision(2) + " +/-" + d.moe.toPrecision(2) + " (n=" + d.n + ")";
      })

    var svg = d3.select(block_id).append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom + 100)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    svg.call(tip);

    d3.json(filter, function(error, data){

        var ageNames = d3.keys(data.numerics[0]).filter(function (key) {
            return key !== "Receipt" && key !== "moe" && key !== "n" && key !== "label";
        });

        data.numerics.forEach(function (d) {
            d.moe = +d.moe
            d.ages = ageNames.map(function (name) {
                return {
                    name: name,
                    value: +d[name],
                    label: d.label,
                    moe: d.moe,
                    n: d.n
                    //add the margin of error to each individual
                    //data object
                };
            });
        });

        x0.domain(data.numerics.map(function (d) {
            return d.Receipt;
        }));
    // if abs(y) > abs(moe), then use 0
    //kendall":"-0.1","moe":"0.08"}
        y0.domain([d3.min(data.numerics, function (d) {
                return d3.min(d.ages, function (d) {
                var q=d.value
                    if (q < 0) {
                        return (q - d.moe);
                    } else {
                        if (Math.abs(q) > Math.abs(d.moe)) {
                            return 0;
                        } else {
                            return (q - d.moe);
                        }
                    }
                });
            }), d3.max(data.numerics, function (d) {
                return d3.max(d.ages, function (d) {
                    return (d.value + d.moe);
                });
            })
        ]);
        y1.domain([0, d3.max(data.numerics, function (d) {
            return d3.max(d.ages, function (d) {
                return d.value;
            });
        })]);
        svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height + ")")

            .call(xAxis)
            .selectAll("text")
            .attr("y", 0)
            .attr("x", 9)
            .attr("dy", ".35em")
            .attr("transform", "rotate(80)")
            .style("text-anchor", "start")
            .style("font-family", "courier")
            .style("font-size", "10pt");

        svg.append("g")
            .attr("class", "y axis")
            .call(yAxis)
            .append("text")
            .attr("transform", "rotate(-90)")
            .attr("y", 6)
            .attr("dy", ".71em");

            // now add titles to the axes
        svg.append("text")
            .attr("text-anchor", "middle")  // this makes it easy to centre the text as the transform is applied to the anchor
            .attr("transform", "translate("+ (padding/2) +","+(height/2)+")rotate(-90)")  // text is drawn off the screen top left, move down and out and rotate
            .text(y_label)
            .style("font-size", "14pt")
            .style("font-weight", "bold");

        svg.append("text")
            .attr("transform", "translate(" + (width / 2) + " ," + (height + margin.bottom + 50) + ")")
            .style("text-anchor", "middle")
            .text("Receipt ID")
            .style("font-size", "14pt")
            .style("font-weight", "bold");

        var receipt = svg.selectAll(".receipt")
            .data(data.numerics)
            .enter()
            .append("g")
            .attr("class", "g")
            .attr("transform", function (d) {
            return "translate(" + x0(d.Receipt) + ",0)";
        });

        svg.append("g")
            .attr("class", "grid")
            .call(make_y_axis()
                .tickSize(-width, 0, 0)
                .tickFormat("")
            )

        svg.append("g")
            .attr("class", "grid")
                .attr("transform", "translate(0," + height + ")")
            .call(make_x_axis()
                .tickSize(-height, 0, 0)
                .tickFormat("")
            )

         receipt.selectAll("g")
           .data(function (d) {
                return d.ages;
            })

            .enter()
            .append("circle")
//            .attr("class", "hollowcircle")
            .attr("r", 7)
            .attr("cx", function (d) {
                return x0.rangeBand()/2;
            })
            .attr("cy", function(d) { return y0(d.value) })
            .on('mouseover', tip.show)
            .on('mouseout', tip.hide)

        // use more 'elegant' solution of pure JS rather than PHP echoing JS
        var flagg = data.flags;
        for (i=0; i<flagg.length; i++) {
            svg.selectAll("circle")
               .filter(function(d) { return (d.label === flagg[i]) })
               .classed("mine", true);
        }

        var mine_less = data.mine_less;
        for (i=0; i<mine_less.length; i++) {
            svg.selectAll("circle")
                .filter(function(d) { return (d.label === mine_less[i]) })
                .classed("mine_less", true);
        }

        var anon_less = data.anon_less;
        for (i=0; i<anon_less.length; i++) {
            svg.selectAll("circle")
                .filter(function(d) { return (d.label === anon_less[i]) })
                .classed("anon_less", true);
        }

        var errorBarArea = d3.svg.area()
            .x(function (d) {
                return x0.rangeBand()/2;
            })
            .y0(function (d) {
                return y0(d.value - +d.moe);
            })
            .y1(function (d) {
                return y0(d.value + +d.moe);
            })

        var errorBars = receipt.selectAll("path.errorBar")
            .data(function (d) {
                return d.ages; //one error line for each data bar
            });

        errorBars.enter()
            .append("path")
            .attr("class", "errorBar");

        errorBars.attr("d", function (d) {
                return errorBarArea([d]);
            //turn the data into a one-element array
            //and pass it to the area function
            })
            .attr("stroke", "red")
            .attr("stroke-width", 1.2);
    });

        svg.append("text")
            .attr("class", "x label")
            .attr("text-anchor", "end")
            .attr("x", width)
            .attr("y", height + 70);

        svg.append("text")
            .attr("class", "x label")
            .attr("text-anchor", "end")
            .attr("x", width)
            .attr("y", height + 90)
            .text("Green circle indicates your predictions (requires login)");

}

</script>


    <h1>Grand Challenge 2</h1>
<?php
     if ($component == 417):
    // need to pass in the set. component has already been set correctly
    $include_method = "fe" . $set . "_stage1";

?>
    <h2>Free Energy Set <?php echo $set; ?> (Stage 1) - Kendall's Tau</h2>
    <div id="kendall_block"></div>

    <h2>Free Energy Set <?php echo $set; ?> (Stage 1) - RMSEc</h2>
    <div id="rmsd_block"></div>

<?php else:
        $include_method = "fe" . $set . "_stage2";
?>
    <h2>Free Energy Set <?php echo $set; ?> (Stage 2) - Kendall's Tau</h2>
    <div id="kendall_block"></div>

    <h2>Free Energy Set <?php echo $set; ?> (Stage 2) - RMSEc</h2>
    <div id="rmsd_block"></div>
<?php endif; ?>

    <script type="text/javascript">
        drawChart(<?php echo $set; ?>, "#rmsd_block");
        drawChart(<?php echo $set; ?>, "#kendall_block");
    </script>

<?php
    include "../spreadsheets/include_bottom_table.php"; ?>

<script>
$(document).ready(function() {
   $('#gc2results').dataTable( {
     "pageLength": 10,
     "order": [[ 6, "asc" ]]
   });
   $('#gc2results_length').append("<span style='padding-left: 50px; font-size: 1em;'><a href='<?php echo '../spreadsheets/csv2dec/' . $file;?>'><img src='/assets/flaticons/png/csv.png' height='32'></a> <a href='<?php echo '../spreadsheets/csv2dec/' . $file;?>'>Download data</a></span>");
});
</script>

</body>
</html>
