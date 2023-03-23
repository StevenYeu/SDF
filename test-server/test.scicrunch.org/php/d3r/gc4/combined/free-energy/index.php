<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

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

    $set = 0;
    $sub_data = array("1147"=>"Cathepsin S", "1146"=>"BACE1a", "1470"=>"BACE1b", "1479"=>"BACE2");
    if ((isset($_GET['component'])) && (in_array($_GET['component'],  array_keys($sub_data)))) {
        $component = $_GET['component'];
        if ($component == 1146)
            $dataname = "BACE (Stage 1A)";
        elseif ($component == 1470)
            $dataname = "BACE (Stage 1B)";
        elseif ($component == 1479)
            $dataname = "BACE (Stage 2)";
        else {
//            $dataname = $sub_data[$component];
            $dataname = 'CATS';
        }
    } else {
        echo "Component ID is invalid or missing.";
        exit;
    }

    if (isset($_GET['group'])) {
        if ($_GET['group'] == 'xray')
            $group = 'xray';
        elseif ($_GET['group'] == 'default')
            $group = 'default';
        else
            die ("Invalid 'group' value.");
    }

    $title = 'Affinity Prediction';

    if (isset($_GET['partial']) && ($_GET['partial'] == 1))
        $partial = 1;
    else
        $partial = 0;

    $parameters = array();
    $parameters[] = "component=" . $component;
    $parameters[] = "set=" . $set;
    $parameters[] = "q=rmsd";
    $parameters[] = "method=free_energy";
    $parameters[] = "partial=" . $partial;
    $parameters[] = "group=" . $_GET['group'];

    // attach debug query parameter if isadmin and debug_user is set
    if ($is_admin && (isset($debug_user)))
        $parameters[] = "debug=" . $_GET['debug_user'];

    // url path to get json data
    $json_filter = "/php/d3r/gc4/combined/free-energy/json_filter.php?" . implode('&', $parameters);
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

    <style>
        .tab-content{
            display: none;
        }

        .tab-content.current{
            display: inherit;
        }
    </style>
</head>

<body>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>

  <title>GC4 <?php echo $title . ' - ' . $dataname; ?></title>

<script type='text/javascript'>
function drawChart(set, block_id){
    var margin = {
        top: 20,
        right: 20,
        bottom: 30,
        left: 60
    },
    width = 1280 - margin.left - margin.right,
    height = 300 - margin.top - margin.bottom,
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
        var  y_label = "Kendall's τ";
    } else if (block_id == '#pearson_block') {
        filter = filter.replace('rmsd', 'pearson');
        var  y_label = "Pearson's r";
    } else if (block_id == '#spearman_block') {
        filter = filter.replace('rmsd', 'spearman');
        var  y_label = "Spearman's ρ";
    } else if (block_id == '#rmsd_block') {
        var  y_label = 'RMSEc (kcal/mol)';
    } else {
        filter = filter.replace('rmsd', 'matthews');
        var  y_label = 'Matthews Correlation Coefficient';
    }

    var tip = d3.tip()
      .attr('class', 'd3-tip')
      .offset([-10, 0])
      .html(function(d) {

        if (block_id == '#matthews_block') {
            return "<strong>" + y_label + "</strong>: " + d.value.toPrecision(2);
        } else {
            if (d.moe == "0") {
                return "<strong>" + y_label + "</strong>: " + d.value.toPrecision(2) + " (n=" + d.n + ")";
            } else {
                return "<strong>" + y_label + "</strong>: " + d.value.toPrecision(2) + " +/-" + d.moe.toPrecision(2) + " (n=" + d.n + ")";
            }
        }
    })

    var svg = d3.select(block_id).append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom + 100)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    svg.call(tip);

    d3.json(filter, function(error, data){

        var ageNames = d3.keys(data.numerics[0]).filter(function (key) {
            return key !== "Receipt" && key !== "moe" && key !== "n" && key !== "label" && key !== "method" && key !== "manual" && key !== "machine";
        });

        data.numerics.forEach(function (d) {
            d.moe = +d.moe
            d.ages = ageNames.map(function (name) {
                return {
                    label: d.label,
                    name: name,
                    value: +d[name],
                    moe: d.moe,
                    method: d.method,
                    manual: d.manual,
                    machine: d.machine,
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

        var greeny = svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height + ")")

            .call(xAxis)
            .selectAll("text")
            .attr("y", 0)
            .attr("x", 9)
            .attr("dy", ".35em")
            .attr("transform", "rotate(70)")
            .style("text-anchor", "start")
            .style("font-family", "Courier")
            .style("font-size", "9pt");

        // show my receipts in green
        var greenAtributes = greeny
            .data(data.numerics)
            .attr("stroke", function(d) {
                var flagg = data.flags
                for (i=0; i<flagg.length; i++) {
                    if (d.label == flagg[i]) {
                        return "green";
                    }
                }
            });

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
            .style("font-size", "14pt");

        svg.append("text")
            .attr("transform", "translate(" + (width / 2) + " ," + (height + margin.bottom + 50) + ")")
            .style("text-anchor", "middle")
            .text("Receipt ID")
            .style("font-size", "14pt");

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
            .append("path")
            .filter(function(d){ return d.method=='ligand'; })
            .attr("class", "point")
            .attr("d", d3.svg.symbol().type("circle").size(200))
            .attr("transform", function(d) { return "translate(" + x0.rangeBand()/2 + "," + y0(d.value) + ")"; })
            .attr("fill", function(d) {
                if (d.manual == 'yes') {
                    return "#DCDCDC";
                } else {
                    return "white";
                }
            })
            .attr("stroke", function(d) {
                if (d.machine == 'yes') {
                    return "orange";
                } else {
                    return "#59a";
                }
            })
            .on('mouseover', tip.show)
            .on('mouseout', tip.hide);

        receipt.selectAll("g")
            .data(function (d) {
            return d.ages;
            })
            .enter()
            .append("path")
            .filter(function(d){ return d.method=='structure'; })
            .attr("class", "point")
            .attr("d", d3.svg.symbol().type("triangle-up").size(150))
            .attr("transform", function(d) { return "translate(" + x0.rangeBand()/2 + "," + y0(d.value) + ")"; })
            .attr("fill", function(d) {
                if (d.manual == 'yes') {
                    return "#DCDCDC";
                } else {
                    return "white";
                }
            })
            .attr("stroke", function(d) {
                if (d.machine == 'yes') {
                    return "orange";
                } else {
                    return "#59a";
                }
            })
            .on('mouseover', tip.show)
            .on('mouseout', tip.hide);

        receipt.selectAll("g")
            .data(function (d) {
            return d.ages;
            })
            .enter()
            .append("path")
            .filter(function(d){ return d.method=='free_energy'; })
            .attr("class", "point")
            .attr("d", d3.svg.symbol().type("square").size(150))
            .attr("transform", function(d) { return "translate(" + x0.rangeBand()/2 + "," + y0(d.value) + ")"; })
            .attr("fill", function(d) {
                if (d.manual == 'yes') {
                    return "#DCDCDC";
                } else {
                    return "white";
                }
            })
            .attr("stroke", function(d) {
                if (d.machine == 'yes') {
                    return "orange";
                } else {
                    return "#59a";
                }
            })
            .on('mouseover', tip.show)
            .on('mouseout', tip.hide);

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
            .text("Green text indicates your predictions (requires login)");

}

</script>

    <h1>Grand Challenge 4 - <?php echo $title; ?> - <?php echo $dataname; ?></h1>
<?php
//  if "CatS" or "kinase" folders for "scoring", ie regular kind, just want Matthews
//  if "noTIES" or "xray", then show all but matthews
    if (($_GET['group'] == 'default') || ($component == 968) || ($component == 1009)): ?>
    <div id="kendall_block" class="tab-content">
        <h2>Kendall's <span style="font-weight: normal">&tau;</span> <input class="ui-button ui-widget ui-corner-all goSpearman" type="submit" value="Spearman's &rho;" > <input class="ui-button ui-widget ui-corner-all goPearson" type="submit" value="Pearson's r" /> <input class="ui-button ui-widget ui-corner-all goRMSD" type="submit" value="RMSEc" ></h2>
        <p style="border: 1px solid #bbb; padding: 3px; width: 325px; margin-left: 900px;">
            <strong>Legend</strong></br />
            &#9651; - Structure Based; &#9711; - Ligand Based; &#9634 - Free Energy<br />
            Gray Fill - Manual Intervention<br />
            Orange Outline - Machine Learning
        </p>
    </div>

    <div id="rmsd_block" class="tab-content current">
        <h2>RMSE<sub>c</sub> <input class="ui-button ui-widget ui-corner-all goKendall" type="submit" value="Kendall's &tau;" > <input class="ui-button ui-widget ui-corner-all goSpearman" type="submit" value="Spearman's &rho;"> <input class="ui-button ui-widget ui-corner-all goPearson" type="submit" value="Pearson's r" ></h2>
        <p style="border: 1px solid #bbb; padding: 3px; width: 325px; margin-left: 900px;">
            <strong>Legend</strong></br />
            &#9651; - Structure Based; &#9711; - Ligand Based; &#9634 - Free Energy<br />
            Gray Fill - Manual Intervention<br />
            Orange Outline - Machine Learning
        </p>
    </div>

    <div id="pearson_block" class="tab-content">
        <h2>Pearson's r <input class="ui-button ui-widget ui-corner-all goKendall" type="submit" value="Kendall's &tau;" > <input class="ui-button ui-widget ui-corner-all goSpearman" type="submit" value="Spearman's &rho;"> <input class="ui-button ui-widget ui-corner-all goRMSD" type="submit" value="RMSEc" ></h2>
        <p style="border: 1px solid #bbb; padding: 3px; width: 325px; margin-left: 900px;">
            <strong>Legend</strong></br />
            &#9651; - Structure Based; &#9711; - Ligand Based; &#9634 - Free Energy<br />
            Gray Fill - Manual Intervention<br />
            Orange Outline - Machine Learning
        </p>
    </div>

    <div id="spearman_block" class="tab-content">
        <h2>Spearman's <span style="font-weight: normal">&rho;</span> <input class="ui-button ui-widget ui-corner-all goKendall" type="submit" value="Kendall's &tau;" > <input class="ui-button ui-widget ui-corner-all goPearson" type="submit" value="Pearson's r" > <input class="ui-button ui-widget ui-corner-all goRMSD" type="submit" value="RMSEc" ></h2>
        <p style="border: 1px solid #bbb; padding: 3px; width: 325px; margin-left: 900px;">
            <strong>Legend</strong></br />
            &#9651; - Structure Based; &#9711; - Ligand Based; &#9634 - Free Energy<br />
            Gray Fill - Manual Intervention<br />
            Orange Outline - Machine Learning
        </p>
    </div>
    <script type="text/javascript">
        drawChart(<?php echo $set; ?>, "#rmsd_block");
        drawChart(<?php echo $set; ?>, "#kendall_block");
        drawChart(<?php echo $set; ?>, "#pearson_block");
        drawChart(<?php echo $set; ?>, "#spearman_block");
    </script>
<?php
    else: ?>
    <div id="matthews_block" class="tab-content current">
        <h2>Matthews Correlation Coefficient</h2>
    </div>
    <script type="text/javascript">
        drawChart(<?php echo $set; ?>, "#matthews_block");
    </script>

<?php
    endif;

    if ($_GET['group'] == 'xray')
        $include_method = 'CatS1_xray_free';
    elseif ($_GET['group'] == 'default') {
        if (($component == 968) || ($component == 1009))
            $include_method = 'free_energy';
        else
            $include_method = 'noties_freeenergy';
    } else
        $include_method = 'free_energy';

    include "../spreadsheets/include_bottom_table.php"; ?>

<script>
<?php
    if (($_GET['group'] == 'default')): ?>
$(document).ready(function() {
    $('tr.tabs th').click(function(){
        var tab_id = $(this).attr('data-tab');
        if (tab_id) {
            $('ul.tabs li').removeClass('current');
            $('.tab-content').removeClass('current');

            $(this).addClass('current');
            $("#"+tab_id+"_block").addClass('current');
        }
    })

   $('#gc4results').dataTable( {
     "pageLength": 25,
     "order": [[ 10, "asc" ]]
   });

   var oTable = $('#gc4results').dataTable();
    $('.goSpearman').click(function(){
        oTable.fnSort([6,'desc']);
        $('.tab-content').removeClass('current');
        $("#spearman_block").addClass('current');
    });
    $('.goKendall').click(function(){
        oTable.fnSort([4,'desc']);
        $('.tab-content').removeClass('current');
        $("#kendall_block").addClass('current');
    });
    $('.goPearson').click(function(){
        oTable.fnSort([8,'desc']);
        $('.tab-content').removeClass('current');
        $("#pearson_block").addClass('current');
    });
    $('.goRMSD').click(function(){
        oTable.fnSort([10,'asc']);
        $('.tab-content').removeClass('current');
        $("#rmsd_block").addClass('current');
    });
<?php
    else: ?>
$(document).ready(function() {
   $('#gc4results').dataTable( {
     "pageLength": 25,
     "order": [[ 4, "desc" ]]
   });

<?php
    endif; ?>

   $('#gc4results_length').append("<span style='padding-left: 50px; font-size: 1em;'><a href='<?php echo str_replace("newcsvs", "downloadcsv", $file); ?>'><img src='/assets/flaticons/png/csv.png' height='32'></a> <a href='<?php echo str_replace("newcsvs", "downloadcsv", $file); ?>'>Download data</a></span>");
});

</script>

</body>
</html>
