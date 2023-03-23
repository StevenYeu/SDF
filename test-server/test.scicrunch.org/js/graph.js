/*
 * Created by Paris Do 6/30/16
 */
function categoryGraph2(json) {

    ga('send', 'event', 'button', 'click', 'Category Graph');
    $('#chart').find('svg').remove();
    $('#legend').find('svg').remove();


    // dimensions of sunburst
    var width = 550;
    var height = 490;
    var radius = Math.min(width, height) / 2.1;

    // Breadcrumb dimensions: width, height, spacing, width of tip/tail.
    var b = {
        w: 150, h: 30, s: 3, t: 10
    };

    // Mapping of step names to colors.
    var color = d3.scale.category20c();

    var vis = d3.select("#chart").append("svg:svg")
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("id", "container")
        .attr("transform", "translate(" + width / 2 + "," + (height / 2 + 10) + ")");

    var partition = d3.layout.partition()
        .value(function(d) {
            return d.size;
        });

    var formatNumber = d3.format(",d");

    var x = d3.scale.linear()
        .range([0, 2 * Math.PI]);

    var y = d3.scale.linear()
        .range([0, radius]);

    var arc = d3.svg.arc()
        .startAngle(function(d) {
            return Math.max(0, Math.min(2 * Math.PI, x(d.x)));
        })
        .endAngle(function(d) {
            return Math.max(0, Math.min(2 * Math.PI, x(d.x + d.dx)));
        })
        .innerRadius(function(d) {
            return Math.max(0, y(d.y));
        })
        .outerRadius(function(d) {
            return Math.max(0, y(d.y + d.dy));
        });


    // Total size of all segments; we set this later, after loading the data
    var totalSize = 0;

    var root = json;


    // Basic setup of page elements.
    initializeBreadcrumbTrail();

    // Bounding circle underneath the sunburst, to make it easier to detect
    // when the mouse leaves the parent g.
    var circle = vis.append("svg:circle")
        .attr("r", radius)
        .style("opacity", 0);

    var g = vis.selectAll("g")
        .data(partition.nodes(root))
        .enter().append("g");

        circle.append("svg:text")
            .on("click",click)
            .style("font-size","4em")
            .style("font-weight","bold");

    var path = g.append("path")
        .attr("d", arc)
        .style("fill", colour)
        .attr("id", function(d) {
            var decode_name = decodeString(d.name);
            return decode_name;
        })
        .on("mouseover", mouseover)
        .on("click", function(d) {
            if (d.url && d.url != '') window.location = d.url;
            else click(d);
        });

    totalSize = path.node().__data__.value;

    d3.select("#container").on("mouseleave", mouseleave);

    // append labels to path
    var text =  g.append("text")
        .text(function(d, i) {
            var decode_name = decodeString(d.name);
            if ((decode_name).length > 19)
                return decode_name.substr(0, 13) + '...';
            else
                return decode_name;
        })
        .attr("opacity", function (d) {
            if (getAnglePieSlice(d) > 2.3)
                return 1;
            else
                return 0;
        })
        .classed("label", true)
        .style("font-weight", "400")
        .style("font-size", function(d) {
            if (d.name == "Facets")
                return "12";
            else
                return "8";
        })
        .attr("x", function(d) {
            return d.x;
        })
        .attr("text-anchor", "middle")
        // translate to the desired point and set the rotation
        .attr("transform", function(d) {
            if (d.depth > 0) {
                return "translate(" + arc.centroid(d) + 500 +  ")" +
                       "rotate(" + getAngle(d) + ")";
            }  else {
                return null;
            }
        })
        .attr("dx", "6") // margin
        .attr("dy", ".35em") // vertical-align
        .attr("pointer-events", "none");

    // change the color brightness of the outer ring
    function colour(d) {
        if (d.children)
            // There is a maximum of two children!
            return color(d.name);
      else
        return ColorLuminance(color(d.parent.name), 0.1);
    }

    function ColorLuminance(hex, lum) {
    	// validate hex string
    	hex = String(hex).replace(/[^0-9a-f]/gi, '');
    	if (hex.length < 6) {
    		hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
    	}
    	lum = lum || 0;

    	// convert to decimal and change luminosity
    	var rgb = "#", c, i;
    	for (i = 0; i < 3; i++) {
    		c = parseInt(hex.substr(i*2,2), 16);
    		c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);
    		rgb += ("00"+c).substr(c.length);
    	}

    	return rgb;
    }

    function click(d) {
        // fade out all text elements
        text.transition().attr("opacity", function(d) {
            if (d.name == "Facets")
                return 1;
            else
                return 0;
        });

        path.transition()
            .duration(750)
            .attrTween("d", arcTween(d))
            .each("end", function(e, i) {
                // check if the animated element's data e lies within the visible angle span given in d
                if (e.x >= d.x && e.x < (d.x + d.dx)) {
                    // get a selection of the associated text element
                    var arcText = d3.select(this.parentNode).select("text");
                    // fade in the text element and recalculate positions

                    arcText.transition().duration(750)
                        // .style("style", function(d) {if (d.name == "Database") return "red"})
                        .attr("opacity", function(d){
                        if (getAnglePieSlice(d) > 2.3)
                            return 1;
                        else
                            return 0;
                  })
                  .attr("transform", function(d) {
                      if (d.depth > 0) {
                          // here
                          return "translate(" + arc.centroid(d) + ")" +
                                 "rotate(" + getAngle(d) + ")";
                      } else
                          return null;
                  })
                  .attr("x", function(d) { return d.x; })
              }
          });
    }

    // Interpolate the scales!
    function arcTween(d) {
      var xd = d3.interpolate(x.domain(), [d.x, d.x + d.dx]),
          yd = d3.interpolate(y.domain(), [d.y, 1]),
          yr = d3.interpolate(y.range(), [d.y ? y(d.y) : 0, radius]);
      return function(d, i) {
        return i
            ? function(t) { return arc(d); }
            : function(t) { x.domain(xd(t)); y.domain(yd(t)).range(yr(t)); return arc(d); };
      };
    }

    function getAngle(d) {
        var thetaDeg = (180 / Math.PI * (arc.startAngle()(d) + arc.endAngle()(d)) / 2 - 90);
        return (thetaDeg > 90) ? thetaDeg-180 : thetaDeg;
    }

    // get the angle to see if the text can fit in the pie slice
    function getAnglePieSlice(d) {
        var theta = Math.abs((arc.startAngle()(d) - arc.endAngle()(d)) * (180/Math.PI));
        return theta;
    }

    // Dimensions of legend item: width, height, spacing, radius of rounded rect.
    var li = {
        w: 175, h: 25, s: 5, r: 3
    };

    var legend = d3.select("#legend").append("svg:svg")
        .attr("width", li.w)
        .attr("height", d3.keys(color).length * (li.h + li.s));

    var g = legend.selectAll("g")
        .data(d3.entries(color))
        .enter().append("svg:g")
        .attr("transform", function(d, i) {
                return "translate(0," + i * (li.h + li.s) + ")";
        })
        .on("click", function(d, i) {
            if (i < root.children.length)
                click(root.children[i]);
        });

    // added click feature to legend rect
    // same effect as clicking the path
    g.append("svg:rect")
        .attr("rx", li.r)
        .attr("ry", li.r)
        .attr("width", li.h)
        .attr("height", li.h)
        .style("fill", function(d, i) {
            if (i < root.children.length)
                return color(root.children[i].name);
        })
        //
        .style("opacity", function(d, i) {
            if (i >= root.children.length)
                return 0;
        });

    g.append("svg:text")
        .attr("x", li.w / 5.5)
        .attr("y", li.h / 2)
        .attr("font-family", "Helvetica Neue")
        .attr("dy", "0.35em")
        .attr("text-anchor", "left")
        .style("fill", "#555")
        .style("font-size", "13px")
        .style("line-height", "1.6")
        .style("font-weight", "400")
        .on("click", function(d, i) {
            return 0;
        })
        .text(function (d, i) {
            if (i < root.children.length) {
                return decodeString(root.children[i].name);
            }
        });

    function mouseover(d) {
        var percentage = (100 * d.value / totalSize).toPrecision(3);
        var percentageString = percentage + "%";
        if (percentage < 0.1) {
            percentageString = "< 0.1%";
        }

        d3.select("#percentage")
            .text(percentageString);

        d3.select("#explanation")
            .style("visibility", "");

        var sequenceArray = getAncestors(d);
        updateBreadcrumbs(sequenceArray, percentageString);

        // Fade all the segments.
        d3.selectAll("path")
            .style("opacity", 0.5);

        // Then highlight only those that are an ancestor of the current segment.
        vis.selectAll("path")
            .filter(function (node) {
                return (sequenceArray.indexOf(node) >= 0);
            })
            .style("opacity", 1);
    }

    // Restore everything to full opacity when moving off the visualization.
    function mouseleave(d) {

        // Hide the breadcrumb trail
        d3.select("#trail")
            .style("visibility", "hidden");

        // Deactivate all segments during transition.
        d3.selectAll("path").on("mouseover", null);

        // Transition each segment to full opacity and then reactivate it.
        d3.selectAll("path")
            .transition()
            .duration(300)
            .style("opacity", 1)
            .each("end", function () {
                d3.select(this).on("mouseover", mouseover);
            });

        d3.select("#explanation")
            .style("visibility", "hidden");
    }

    // Given a node in a partition layout, return an array of all of its ancestor
    // nodes, highest first, but excluding the root.
    function getAncestors(node) {
        var path = [];
        var current = node;
        while (current.parent) {
            path.unshift(current);
            current = current.parent;
        }
        return path;
    }

    function initializeBreadcrumbTrail() {
        // Add the svg area.
        var trail = d3.select("#sequence").append("svg:svg")
            .attr("width", width)
            .attr("height", 50)
            .attr("id", "trail");

        // Add the label at the end, for the percentage.
        trail.append("svg:text")
            .attr("id", "endlabel")
                .style("fill", "#555")
                .style("font-weight", "400");
    }


    function sizeOfPoly(d) {

        var xs_text = 20;
        var s_text = 30;
        var m_text = 40;
        var ml_text = 50;
        var l_text = 60;

        if ((d.name.length > xs_text) && (d.name.length <= s_text))
            return 180;
        else if ((d.name.length > s_text) && (d.name.length <= m_text))
            return 230;
        else if ((d.name.length > m_text) && (d.name.length <= ml_text))
            return 280;
        else if ((d.name.length > ml_text))
            return 330;
        else
            return 115;

    }

    function percentPlacement (r, l) {
        if (r == 180)
            return 350;
        else if (r == 230)
            return 400;
        else if (r == 280)
            return 450;
        else if (r == 330)
            return 500;
        else if (r == 115 && l == 1)
            return 160;
        else if (r == 115 && l == 2)
            return 280;
        else
            return 40;
    }

    function brightness(hexcolor){
        var r = parseInt(hexcolor.substr(1,3),16);
        var g = parseInt(hexcolor.substr(3,5),16);
        var b = parseInt(hexcolor.substr(5,7),16);
        var yiq = ((r*299)+(g*587)+(b*114))/1000;
        return (yiq >= 128) ? 'black' : 'white';
    }

    // Generate a string that describes the points of a breadcrumb polygon.
    function breadcrumbPoints(d, i) {
        var points = [];
        points.push("0,0");
        b.w = sizeOfPoly(d);
        points.push(b.w + ",0"); // 150, 0
        points.push(b.w + b.t + "," + (b.h / 2));
        points.push(b.w+ "," + b.h); // stay same // 150, 30
        points.push("0," + b.h);
        if (i > 0) { // Leftmost breadcrumb; don't include 6th vertex.
            points.push(b.t + "," + (b.h / 2));
        }
        return points.join(" ");
    }

    // Update the breadcrumb trail to show the current sequence and percentage.
    function updateBreadcrumbs(nodeArray, percentageString) {

        var polymult = 5;
        var polyadd = 30;

        // Data join; key function combines name and depth (= position in sequence).
        var g = d3.select("#trail")
            .selectAll("g")
            .data(nodeArray, function (d) {
                return d.name + d.depth;
            });

        // Add breadcrumb and label for entering nodes.
        var entering = g.enter().append("svg:g");

        entering.append("svg:polygon")
            .attr("points", breadcrumbPoints)
             .style("fill", colour)


        entering.append("svg:text")
            .attr("x", function (d) {
                if (d.depth > 1) {
                    b.w = sizeOfPoly(d);
                    return (b.w + b.t) / 2;
                }
                else {
                    b.w = 115;
                    return (b.w + b.t) / 2;
            }})
            .attr("y", b.h / 2)
            .attr("dy", "0.35em")
            .attr("text-anchor", "middle")
            .style("font-weight", "400")
            .style("font-size", "10px")
            .style("fill", function(d) {
                return brightness(colour(d));
            })
            .text(function (d) {
                var decode_name = decodeString(d.name);
                if (decode_name.length > 60)
                    return decode_name.substr(0, 57) + '...';
                else
                    return decode_name;
            });

        // Set position for entering and updating nodes.
        g.attr("transform", function (d, i) {
            b.w = 115;
            if (d.depth > 1)
                b.l = sizeOfPoly(d);
            else
                b.l = 115;

            return "translate(" + i * (b.w + b.s) + ", 0)";
        });

        // Remove exiting nodes.
        g.exit().remove();

        // Now move and update the percentage at the end.
        d3.select("#trail").select("#endlabel")
            .attr("x", function(d) {
                return percentPlacement(b.l, nodeArray.length)})
            .attr("y", b.h / 2)
            .attr("dy", "0.35em")
            .attr("text-anchor", "middle")
            .text(percentageString);

        // Make the breadcrumb trail visible, if it's hidden.
        d3.select("#trail")
            .style("visibility", "");

    }

    $('.background').show();
    $('.category-graph').show();

    function decodeString(s) {
        var decode_string = s;
        try {
            decode_string = decodeURIComponent(s);
        } catch(e) {

        }
        return decode_string;
    }
}

/*
 * Line Graph 2
 */
function lineGraph(json){
    ga('send', 'event', 'button', 'click', 'Literature Graph');
    $('.chart').empty();
    var root = JSON.parse(decodeURIComponent(json));
    root.sort(function (a, b) {
        return a.year - b.year;
    });

    var yearArray = new Array(),countArray = new Array();
    for (var a=0;a<root.length;a++) {
        yearArray[a] = parseInt(root[a].year);
        countArray[a] = parseInt(root[a].num);
    }
    var data = d3.range(yearArray.length).map(function(i) {
        return {
            x: yearArray[i],
            y: countArray[i]
        };
    });

    var margin = {
            top: 10,
            right: 10,
            bottom: 20,
            left: 60
        },
        width = 960 - margin.left - margin.right,
        height = 500 - margin.top - margin.bottom;

    var x = d3.scale.linear()
        .domain([yearArray[0], yearArray[yearArray.length-1]])
        .range([0, width]);

    var y = d3.scale.linear()
        .domain([d3.min(countArray), d3.max(countArray)])
        .range([height, 0]);
    var formatAsPercentage = d3.format("g");
    var xAxis = d3.svg.axis()
        .scale(x)
        .tickFormat(formatAsPercentage)
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left");

    var line = d3.svg.line()
        .x(function(d) {
            return x(d.x);
        })
        .y(function(d) {
            return y(d.y);
        });

    var svg = d3.select(".chart").append("svg")
        .datum(data)
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis);

    svg.append("path")
        .attr("class", "line")
        .attr("d", line);

    svg.selectAll(".dot")
        .data(data)
        .enter().append("circle")
        .attr("class", "dot")
        .attr("cx", line.x())
        .attr("cy", line.y())
        .attr("r", 3.5)
        .on('mouseover',function(d,i){
            $('.graph-year').text(yearArray[i]);
            $('.graph-count').text(countArray[i]);
            $('.hover-text').css('left',this.cx.animVal.value-10);
            $('.hover-text').css('top',this.cy.animVal.value);
            $('.hover-text').show();
        })
        .on("mouseout",function(){$('.hover-text').hide();})
        .on('click',function(d, i){
            window.location = window.location.href + '&facet[]=Publication Year:'+yearArray[i];
        });

    $('.background').show();
    $('.category-graph').show();
}


/*
 * Line Graph 2
 */
function lineGraph2(json) {
    ga('send', 'event', 'button', 'click', 'Literature Graph');
    $('.chart').empty();
    var margin = {top: 20, right: 40, bottom: 40, left: 70},
        width = 860 - margin.left - margin.right,
        height = 500 - margin.top - margin.bottom;

    var parseDate = d3.time.format("%d-%b-%y").parse,
        bisectDate = d3.bisector(function (d) {
            return d.year;
        }).left,
        formatValue = d3.format(",.2f"),
        formatCurrency = function (d) {
            return "$" + formatValue(d);
        };

    var x = d3.scale.linear()
        .range([0, width]);

    var y = d3.scale.linear()
        .range([height, 0]);

    var xAxis = d3.svg.axis()
        .scale(x)
        .orient("bottom");

    var yAxis = d3.svg.axis()
        .scale(y)
        .orient("left");

    var line = d3.svg.line()
        .x(function (d) {
            return x(d.year);
        })
        .y(function (d) {
            return y(d.num);
        });

    var svg = d3.select(".chart").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    var data = JSON.parse(decodeURIComponent(json));
    data.sort(function (a, b) {
        return a.year - b.year;
    });

    x.domain([data[0].year, data[data.length - 1].year]);
    y.domain(d3.extent(data, function (d) {
        return d.num;
    }));

    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(xAxis);

    svg.append("g")
        .attr("class", "y axis")
        .call(yAxis)
        .append("text")
        .attr("transform", "rotate(-90)")
        .attr("y", 6)
        .attr("dy", ".71em")
        .style("text-anchor", "end")
        .text("# of Papers");

    svg.append("path")
        .datum(data)
        .attr("class", "line")
        .attr("d", line);

    var focus = svg.append("g")
        .attr("class", "focus")
        .style("display", "none");

    focus.append("circle")
        .attr("r", 4.5);

    focus.append("text")
        .attr("x", -130)
        .attr("dy", "-1.75em");

    svg.append("rect")
        .attr("class", "overlay")
        .attr("width", width)
        .attr("height", height)
        .on("mouseover", function () {
            focus.style("display", null);
        })
        .on("mouseout", function () {
            focus.style("display", "none");
        })
        .on("mousemove", mousemove);

    function mousemove() {
        var x0 = x.invert(d3.mouse(this)[0]),
            i = bisectDate(data, x0, 1),
            d0 = data[i - 1],
            d1 = data[i],
            d = x0 - d0.year > d1.year - x0 ? d1 : d0;
        focus.attr("transform", "translate(" + x(d.year) + "," + y(d.num) + ")");
        focus.select("text").text('Year: ' + d.year + ", Count: " + d.num).attr("x", (x(d.year) / width) < .15 ? 0 : -130);
        focus.on("click", function () {
            window.location = window.location.href + '&facet[]=Year:' + d.year;
        })
    }

    $('.background').show();
    $('.category-graph').show();
}
