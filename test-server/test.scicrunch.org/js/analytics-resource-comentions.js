$(function(){

    var cmdata = {};
    cmdata.rid = $("#resource-id").val();
    cmdata.buildNodes = function(){
        this.nodes_obj = {};
        var that = this;
        this.largest = 0;
        for(var i = 0; i < this.edges.length; i++){
            this.addNode(this.edges[i].name1, this.edges[i].rid1, this.edges[i].uuid1);
            this.addNode(this.edges[i].name2, this.edges[i].rid2, this.edges[i].uuid2);
            if(this.edges[i].count > this.largest) this.largest = this.edges[i].count;
        }
        var i = 0, j;
        this.nodes = $.map(this.nodes_obj, function(val, idx){
            if(val.rid == that.rid){
                val.canon = true;
                j = i;
            }
            else{
                val.canon = false;
            }
            i += 1;
            return val;
        });
        if(j != 0){
            var main = this.nodes[j];
            this.nodes[j] = this.nodes[0];
            this.nodes[0] = main;
        }
    }
    cmdata.addNode = function(name, rid, uuid){
        if(this.nodes_obj[rid] === undefined && name != null) this.nodes_obj[rid] = {rid: rid, name: name, uuid: uuid};
    }
    cmdata.ready = function(){
        console.log(this.nodes);
        console.log(this.edges);
        if(this.edges.length > 0){
            this.getGraphData();
            this.buildHeatmap();
        }else{
            noResults();
        }
        window.comention_gridlock = false;
        $(".load-comention-grid").html("Refresh");
    }

    cmdata.getGraphData = function(){
        var that = this;
        this.data = [];
        for(var i = 0; i < this.nodes.length; i++){
            for(var j = 0; j < this.nodes.length; j++){
                var filter_edges = this.edges.filter(function(x){
                    if((that.nodes[i].rid == x.rid1 && that.nodes[j].rid == x.rid2) || (that.nodes[j].rid == x.rid1 && that.nodes[i].rid == x.rid2)) return true;
                    else return false;
                });
                if(filter_edges.length > 0) this.data.push([i,j,filter_edges[0].count]);
            }
        }
    };

    cmdata.buildHeatmap = function(){
        var that = this;
        $("#comention-grid").highcharts({
            chart: {
                type: "heatmap"
            },
            title: {
                text: "Co-mentions"
            },
            xAxis: {
                categories: that.nodes.map(function(x){ return x.name; })
            },
            yAxis: {
                categories: that.nodes.map(function(x){ return x.name; }),
                title: null
            },
            colorAxis: {
                min: 1,
                minColor: "#FFFFFF",
                maxColor: "#77a1e5",
                type: "logarithmic"
            },
            tooltip: {
                formatter: function(){
                    return this.series.xAxis.categories[this.point.x] + '</a><br/>' + this.series.yAxis.categories[this.point.y] + '</a><br/>' + this.point.value + ' co-mentions';
                },
                followPointer: true
            },
            plotOptions:{
                series: {
                    events: {
                        click: function(e){
                            $("#comention-grid-infoclick").html(
                                '<a target="_self" href="' + that.urlOtherResource(e.point.x) + '">' + that.nodes[e.point.x].name + '</a><br/>' +
                                '<a target="_self" href="' + that.urlOtherResource(e.point.y) + '">' + that.nodes[e.point.y].name + '</a><br/>' +
                                that.urlComentions(e.point.x, e.point.y)
                            );
                        }
                    }
                }
            },
            series: [{
                name: "comentions",
                data: that.data,
                dataLabels: {
                    enabled: false,
                    color: "#000"
                }
            }]
        });
    };

    cmdata.urlOtherResource = function(i){
        var uuid = this.nodes[i].uuid;
        var url = window.location.href;
        var uuid_re = /\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/g;
        return url.replace(uuid_re, uuid);
    }

    cmdata.urlComentions = function(x,y){
        var that = this;
        var edge = this.edges.filter(function(z){
            if((z.rid1 == that.nodes[x].rid && z.rid2 == that.nodes[y].rid) || (z.rid1 == that.nodes[y].rid && z.rid2 == that.nodes[x].rid)) return true;
            return false;
        });
        if(edge.length == 0) return "";
        edge = edge[0];
        var comentions = edge.comentions.replace(/PMID:/g, "").split(",");
        if(comentions.length == 0) return "";
        var split_comentions = splitComentions(comentions, 500);
        var html_comentions_array = Array();
        for(var i = 0; i < split_comentions.length; i++){
            console.log(split_comentions[i].join(","));
            html_comentions_array.push('<a target="_blank" href="http://www.ncbi.nlm.nih.gov/pubmed/' + split_comentions[i].join(",") + '">(' + split_comentions[i].length + ')</a>');
        }
        return "co-mentions " + html_comentions_array.join(" ");
    }

    function splitComentions(cm, n){
        if(cm.length < n) return [cm];
        var x = Array();
        var y = Array();
        var i = 0;
        for(; i < cm.length; i++){
            x.push(cm[i]);
            if((i + 1) % n == 0){
                y.push(x);
                x = Array();
            }
        }
        if(i % n != 0) y.push(x);
        return y;
    }

    function noResults(){
        $("#comention-grid").html("There were no co-mentions found");
    }

    function comentionDataGen(cmdata){
        return function(response){
            cmdata.edges = response;
            cmdata.buildNodes();
            cmdata.ready();
        };
    }
    comentionData = comentionDataGen(cmdata);

    function loadCMData(){
        var rid = $("#resource-id").val();
        var hc_string = $(".comention-grid-hc-toggle").is(":checked") ? "&hc" : "";
        $.get("/php/resource-comentions.php?rid=" + rid + "&count=20" + hc_string).then(comentionData);
    }

    $(".load-comention-grid").on("click", function(){
        if(!window.comention_gridlock){
            window.comention_gridlock = true;
            $(this).html("loading...");
        }
        loadCMData();
    });

    window.comention_gridlock = false;

});
