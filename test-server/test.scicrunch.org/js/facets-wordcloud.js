'use strict';

var wordcloud_nlx = null;
var wordcloud_query = null;
var wordcloud_facet = null;
var wordcloud_data = null;

const count = 200;

function buildSearchURL(nlx, query, query_facet, query_filter) {
    //var url = '/scicrunch/data/source/' + nlx + '/search?'
    var url = window.location.pathname + "?";
    var q = 'q=' + query;
    for (var facet in query_facet) {
        q += '&facet[]=' + query_facet[facet]
    }
    for (var filter in query_filter) {
        q += '&filter[]=' + query_filter[filter]
    }
    url += q;
    return url;
}

function buildFacetURL(nlx, query, query_facet, query_filter) {
    var url = '/api/1/dataservices/federation/facets/' + nlx + '.json?'
    var q = 'q=' + query;
    q += '&orMultiFacets=true'
    for (var facet in query_facet) {
        q += '&facet=' + query_facet[facet]
    }
    for (var filter in query_filter) {
        q += '&filter=' + query_filter[filter]
    }
    url += q;
    url += '&count=' + count + '&minCount=1'
    return url;
}

function buildVerticalTable(classes, titles, values){
    var table = '<table class="table table-bordered ' + classes + '"><tbody>'
    for (var i = 0, len_row = titles.length; i < len_row; i++) {
        table += '<tr>'
        table += '<th>' + titles[i] + '</th>'
        for (var j = 0, len_col = values.length; j < len_col; j++) {
            table += '<td>' + values[j][i] + '</td>'
        }
        table += '</tr>'
    }
    table += '</tbody></table>'
    return table;
}

function clickCallback(item, dimension, event) {
    if (!item) {
        return;
    }
    var additional_query_facet = [wordcloud_facet + ':'+ item[0]];
    if (query_facet_array == null) query_facet_array = [];
    var url = buildSearchURL(wordcloud_nlx, wordcloud_query,
        query_facet_array.concat(additional_query_facet), query_filter_array);
    window.location = url;
}

function hoverCallback(item, dimension, event) {
    if (!item) {
        return;
    }
    var elementUnderCursor = event.target
    var tooltipTableHTML =
        buildVerticalTable('table-wordcloud-tooltip', [wordcloud_facet, 'Count'], [item]);
    $('.wordcloud-tooltip-tip').html(tooltipTableHTML);
}


function prepareWordCloud(nlx, query, query_facet, query_filter, facet, callback) {
    var loading = $('.facets-wordcloud-modal-loading')
    wordcloud_nlx = nlx;
    wordcloud_facet = facet;
    wordcloud_query = query
    var url = buildFacetURL(nlx, query, query_facet, query_filter);
    if (wordcloud_data) {
        // already loaded
        callback(nlx, facet);
        loading.hide();
    } else {
        $.getJSON(url, function(data){
            wordcloud_data = {};
            for (var i = 0; i < data.length; i++) {
                var category = data[i].category;
                if (!wordcloud_data[category]) {
                    wordcloud_data[category] = {
                        list: [],
                        max_count: 0,
                    }
                }
                for (var j = 0; j < data[i].facets.length; j++) {
                    var itemValue = data[i].facets[j].value
                    itemValue = $('<p>' + itemValue + '</p>').text()
                    var item = [itemValue, data[i].facets[j].count];
                    wordcloud_data[category].max_count =
                        Math.max(data[i].facets[j].count, wordcloud_data[category].max_count);
                    wordcloud_data[category].list.push(item);
                }
            }
            callback(nlx, facet);
            loading.hide();
        })
    }
}

function drawWordCloud(nlx, facet){
    var canvas = $('.facets-wordcloud-area');
    var options = {
        list: wordcloud_data[facet].list,
        gridSize: 15,
        weightFactor: function (size) {
            return Math.max(size / wordcloud_data[facet].max_count * 100, 10);
        },
        classes: 'wordcloud-tooltip-item',
        click: clickCallback,
        hover: hoverCallback,
        fontFamily: 'Times, serif',
        color: 'random-dark',
        backgroundColor: '#fff',
        minRotation: 0,
        maxRotation: 0
    }
    WordCloud(canvas[0], options)
    canvas.one('wordcloudstop', function (e) {
        canvas.append("<div class=\"wordcloud-tooltip-tip\">Tooltip here</div>")
        $('.wordcloud-tooltip-item').mousemove(tooltipitemMousemoveCallback);
        $('.wordcloud-tooltip-item').mouseout(tooltipitemMouseoutCallback);
        canvas.off('wordcloudstop')
    });
}

function tooltipitemMouseoutCallback(e) {
    var $tip = $(this).parent().find('.wordcloud-tooltip-tip')
    $tip.stop().
        animate(
            {opacity:0}, {
                duration: 100,
                easing: 'easeOutQuint',
                complete: function(){
                    $tip.css('display', 'none')
                }
            });
}

function tooltipitemMousemoveCallback(e) {
    var parentElementOffset = $(this).parent().offset();
    var xPos = e.pageX - parentElementOffset.left;
    var yPos = e.pageY - parentElementOffset.top;
    $(this).parent().find('.wordcloud-tooltip-tip').css({
        'top' : yPos + 10,
        'left' : xPos + 10}
    );
    $(this).parent().find('.wordcloud-tooltip-tip').css('display', 'block')
        .stop().animate({opacity: 1},{
            duration: 100,
            easing: 'easeOutQuint',
        });
}

$(document).ready(function () {
    var modal = $('#facets-wordcloud-modal');
    var span = $('.facets-wordcloud-close');
    var buttons = $('.show-facets-wordcloud');

    window.showWordCloud = function (nlx, query, facet){
        var loading = $('.facets-wordcloud-modal-loading');
        var title = $('.facets-wordcloud-modal-title');
        var canvas = $('.facets-wordcloud-area');
        canvas.empty();
        loading.show();
        title.text('Word Cloud of Top ' + count + ' Facets in ' + facet )
        modal.css({ 'display': 'block' });
        $('.invis-hide').hide();
        $('.body-hide').removeClass('active');
        prepareWordCloud(nlx, query, query_facet_array, query_filter_array, facet, drawWordCloud);
    }
    window.addEventListener('click',
        function(event) {
            if (event.target == modal[0]) {
                modal.css('display', 'none');
            } else if (event.target == span[0]) {
                modal.css('display', 'none');
            }
        } , false)

})
