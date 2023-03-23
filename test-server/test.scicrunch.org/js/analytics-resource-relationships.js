$(function(){
    var rid = $("#resource-id").val();

    $("#refresh-graph").on("click", function(){
        console.log("running");
        relationships = getRelationships(rid);
        relationships.then(function(rels){
            buildElements(rels);
            setupGraph();
        });
    });

    function getRelationships(rid){
        console.log('getting relationships');
        return $.get("/php/resource-mention-relationships.php?rid=" + rid);
    }

    function buildElements(relationships){
        console.log(relationships);
        var found_nodes = {};
        var nodes = [];
        var edges = [];
        $.each(relationships, function(i,rel){
            addFoundNode(rel.id1, found_nodes, nodes);
            addFoundNode(rel.id2, found_nodes, nodes);
            addEdge(rel, nodes, edges);
        });
        nodes[0].group = 1;
        window.cyelements = {
            nodes: nodes,
            links: edges
        };
        console.log(cyelements);
    }

    function addFoundNode(id, found_nodes, nodes){
        if(found_nodes.hasOwnProperty(id)) return;
        found_nodes[id] = 1;
        nodes.push({id:id, group:2});
    }

    function addEdge(rel, nodes, edges){
        var source = nodes.filter(function(n){
            return n.id == rel.id1;
        })[0];
        var target = nodes.filter(function(n){
            return n.id == rel.id2;
        })[0];
        var parent_type = "type";
        if(rel.edge.startsWith("PMID:")) parent_type = "pmid";
        edges.push({
            source: source,
            target: target,
            value: 1,
            type: rel.edge,
            general_type: parent_type
        });
    }


    function setupGraph(){
        var data = window.cyelements;

        var width = 1000, height = 600;
        var node_color = d3.scale.category10();
        var link_color = d3.scale.category20();
        var force = d3.layout.force().charge(-150).linkDistance(30).size([width,height]).gravity(2);
        var svg = d3.select("#resource-relationships-graph").append("svg:svg").attr("width", width).attr("height", height).call(d3.behavior.zoom().on("zoom",redraw)).append("svg:g");
        force.nodes(data.nodes).links(data.links).start();
        var link = svg.selectAll(".link").data(data.links).enter().append('line').attr("class", "link").style("stroke", function(d,i){ return link_color(d.general_type); });
        var node = svg.selectAll(".node").data(data.nodes).enter().append("circle").attr("class", "node").attr("r", 3).call(force.drag).style("fill", function(d,i){ return node_color(d.group); });
        var tip = d3.tip().attr("class", "d3-tip").offset([-10,0]).html(function(d){
            if(d.name === undefined){
                $.get("/api/1/resource/fields/view/" + d.id).
                    then(function(data){
                        var name = data.data.fields.filter(function(n){ return n.field === "Resource Name"; })[0].value;
                        d.name = name;
                    });
            }
            return d.name;
        });
        node.call(tip);
        node.on("mouseover", tip.show);
        node.on("mouseout", tip.hide);
        var tick_counter = 0;
        force.on("tick", function(){
            tick_counter += 1;
            if(tick_counter > 1000) this.stop();
            data.nodes[0].x = width / 2;
            data.nodes[0].y = height / 2;
            link.attr("x1", function(d) { return d.source.x; })
                .attr("y1", function(d) { return d.source.y; })
                .attr("x2", function(d) { return d.target.x; })
                .attr("y2", function(d) { return d.target.y; });
            node.attr("cx", function(d) { return d.x; })
                .attr("cy", function(d) { return d.y; });
        });
        force.on("end", function(){
            force.stop();
        });
        function redraw(){
            var trans = d3.event.translate;
            var scale = d3.event.scale;
            svg.attr("transform", "translate(" + trans + ") scale(" + scale + ")");
        }
    }

});
