function onClickEventGen(chart_data){
    return function(e){
        if(e !== undefined) e.preventDefault();
        for(var i = 0; i < chart_data['sources'].length; i++){
            var source = chart_data['sources'][i];
            if(source['db'] + ":" + source['indexable'] == this.name){
                var nif = source['nifId'];
            }
        }
        if(nif) window.location = "/scicrunch/data/source/" + nif + "/search?q=*";
    }
}

function getContent(chart_data){
    var content = [];
    for(var i = 0; i < chart_data['categories'].length; i++) chart_data['categories'][i]['count'] = parseInt(chart_data['categories'][i]['count'], 10);
    var resource_types = chart_data['categories'].
        filter(function(x){ return x['parent'] == "Category"; }).
        sort(function(a,b){
            if(a['count'] == b['count']) return 0;
            if(a['count'] < b['count']) return 1;
            return -1;
        });
    for(var i = 0; i < resource_types.length; i++){
        content.push({'name': resource_types[i]['category'], 'y': resource_types[i]['count'], 'drilldown': resource_types[i]['category']});
    }
    return content;
}

function getDrilldown(chart_content, chart_data){
    var drilldown = [];
    for(var i = 0; i < chart_content.length; i++){
        var cat = chart_content[i]['name'];
        var sources_filter = chart_data['sources'].filter(function(x){
            var idx = x['category'].indexOf(cat);
            if(idx != -1 && x['parentCategory'][idx] == "Category") return true;
            return false;
        });
        drilldown.push({
            name: chart_content[i]['name'],
            id: chart_content[i]['name'],
            data: sources_filter.map(function(x){
                return [x['db'] + ":" + x['indexable'], parseInt(x['totalCount'], 10)];
            })
        });
    }
    return drilldown;
}

function buildPieChart(chart_data){
    var chart_content = getContent(chart_data);
    var chart_drilldown = getDrilldown(chart_content, chart_data);
    $("#piechart").highcharts({
        chart: { plotBackgroundColor: null, plotBorderWidth: null, plotShadow: false, type: 'pie' },
        title: { text: "Data Types" },
        tooltip: { pointFormat: '{point.y}' },
        series: [{name: "data types", colorByPoint: true, data: chart_content}],
        drilldown: {series: chart_drilldown },
        plotOptions: {
            pie: {
                cursor: 'pointer',
                point: {
                    events: {
                        click: onClickEventGen(chart_data)
                    }
                }
            }
        }
    });
}
