'use strict';

var wordcloud_nlx = null;
var wordcloud_query = null;
var wordcloud_facet = null;
var wordcloud_data = null;

const count = 200;

function buildSearchURL(nlx, query, query_facet, query_filter, query_column, query_sort) {
    // var url = '/dknet/data/source/' + nlx + '/search?'
    var url = 'search?'
    var q = 'q=' + query;
    for (var facet in query_facet) {
        q += '&facet[]=' + query_facet[facet]
    }
    for (var filter in query_filter) {
        q += '&filter[]=' + query_filter[filter]
    }
    if (query_column != '' && query_sort != '' && query_column != null && query_sort != null) q += '&column=' + query_column + '&sort=' + query_sort;
    url += q;
    return url;
}

function buildFacetURL(nlx, query, query_facet, query_filter) {
    var url = '/api/1/elasticservices/' + nlx + '/search?'
    var q = 'q=' + query;
    if (query_facet != null && query_facet.length > 0) {
      for (var facet in query_facet) {
          q += '&facet[]=' + query_facet[facet];
      }
    }
    if (query_filter != null && query_filter.length > 0) {
      for (var filter in query_filter) {
          q += '&filter[]=' + query_filter[filter];
      }
    }
    url += q;
    url += '&count=' + count + '&minCount=1'
    return url;
}

function buildVerticalTable(classes, titles, values){
    // ## checke original facet name from faces_data & build table -- Vicky-2019-3-26
    var facet_title = titles[0];
    var count_title = titles[1];
    var name = values[0];
    var count = values[1];

    if (name.includes("...")) {
        var tmp = name.replace("...", "");
        for (var i in facets_data[facet_title]) {
            if (facets_data[facet_title][i].value.includes(tmp)) {
                name = facets_data[facet_title][i].value;
                break;
            }
        }
    }

    var table = '<table class="table table-bordered ' + classes + '"><tbody>';
    table += '<tr>';
    table += '<th>' + facet_title + '</th>';
    table += '<td>' + name + '</td>';
    table += '</tr>';
    table += '<tr>';
    table += '<th>' + count_title + '</th>';
    table += '<td>' + count + '</td>';
    table += '</tr>';
    table += '</tbody></table>';
    return table;
}

function clickCallback(item, dimension, event) {
    if (!item) {
        return;
    }
    var additional_query_facet = [wordcloud_facet + ':'+ item[2]];
    if (query_facet_array == null) query_facet_array = [];
    var url = buildSearchURL(wordcloud_nlx, wordcloud_query,
        query_facet_array.concat(additional_query_facet), query_filter_array, query_column, query_sort);
    if(typeof query_types !== 'undefined') url += query_types;
    if(typeof query_sources !== 'undefined') window.location = url + query_sources;
    else window.location = url;
}

function hoverCallback(item, dimension, event) {
    if (!item) {
        return;
    }
    var elementUnderCursor = event.target
    var tooltipTableHTML =
        buildVerticalTable('table-wordcloud-tooltip', [wordcloud_facet, 'Count'], item);
    $('.wordcloud-tooltip-tip').html(tooltipTableHTML);
}


function prepareWordCloud(nlx, query, query_facet, query_filter, facets_data, facet, callback) {
    var loading = $('.facets-wordcloud-modal-loading')
    wordcloud_nlx = nlx;
    wordcloud_facet = facet;
    wordcloud_query = query
    //var url = buildFacetURL(nlx, query, query_facet, query_filter);

    //console.log("wordcloud_data:", wordcloud_data);
    if (wordcloud_data) {
        // already loaded
        callback(nlx, facet);
        loading.hide();
    } else {
        // ## send api query to elastic services to get data by jQuery $.get
        // $.get(url, function(data){
        //     wordcloud_data = {};
        //     for (var category in data.data.facets) {
        //       wordcloud_data[category] = {
        //           list: [],
        //           max_count: 0,
        //       }
        //       for (var i in data.data.facets[category]) {
        //         var item = [data.data.facets[category][i].value, data.data.facets[category][i].count];
        //         if (item[0] != "") wordcloud_data[category].list.push(item);
        //       }
        //       wordcloud_data[category].max_count = wordcloud_data[category].list[0][1];
        //     }
        //     callback(nlx, facet);
        //     loading.hide();
        // })

        // ## loading and formatting facets_data -- Vicky-2019-3-25
        wordcloud_data = {};
        for (var category in facets_data) {
          wordcloud_data[category] = {
              list: [],
              max_count: 0,
          }
          for (var i in facets_data[category]) {
            var value = facets_data[category][i].value;
            var short_value = value;
            if (value.length > 30) short_value = value.substr(0, 30) + "...";
            var item = [short_value, facets_data[category][i].count, value];
            if (item[0] != "") wordcloud_data[category].list.push(item);
          }
          if(wordcloud_data[category].list.length) wordcloud_data[category].max_count = wordcloud_data[category].list[0][1];
        }
        callback(nlx, facet);
        loading.hide();
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
        // console.log("facets_data:", facets_data);
        prepareWordCloud(nlx, query, query_facet_array, query_filter_array, facets_data, facet, drawWordCloud);
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
