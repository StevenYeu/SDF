function escapeStringForHTML(rawStr) {
    return rawStr.replace(/[\u00A0-\u9999<>\&]/gim, function (i) {
        return '&#' + i.charCodeAt(0) + ';';
    })
}

var visitedResources = JSON.parse(readCookie('visitedResources') || '[]');

// Check if literature page
if ($('.literature-resources')) {
    $('.literature-resources > li > input').each(function () {
        var id = $(this).val();
        var found = false;
        for (var i = 0; i < visitedResources.length; i++) {
            if (visitedResources[i].id === id) {
                found = true;
                break;
            }
        }
        if (!found) {
            visitedResources.push({
                id: id,
                visited: false
            });
        }
    });
}

// Check if resources page
var resourceId = $('#resource-primary-id').val();
if (resourceId) {
    var found = false;
    for (var i = 0; i < visitedResources.length; i++) {
        if (visitedResources[i].id === resourceId) {
            visitedResources[i].visited = true;
            found = true;
            break;
        }
    }
    if (!found) {
        visitedResources.push({
            id: resourceId,
            visited: true
        });
    }
}

deleteCookie('visitedResources');
createCookie('visitedResources', JSON.stringify(visitedResources), 60);

var recommendationsChip = $('recommendations-chip');
var communityPortal = $('#community-portal').val();
if (recommendationsChip && communityPortal && visitedResources.length > 0) {
    // Show the recommendation chip
    $.get('/templates/recommendations-chip.html')
        .then(function success(chipHtml) {
            recommendationsChip.html(chipHtml);
            $.post('/api/1/recommendations', {
                    ids: visitedResources
                })
                .then(function success(response) {
                    updateRecommendations(response.data);
                });
        });
}

function updateRecommendations(recommendations) {
    if (recommendations.length === 0) {
        return;
    }
    recommendationsChip.find('.badge').text(recommendations.length);
    recommendationsChip.find('.panel').show();
    var html = "";
    for (var i = 0; i < recommendations.length; i++) {
        html += '<li><a href="/' + communityPortal + '/Any/record/nlx_144509-1/' + recommendations[i].uuid + '/search" onclick="ga(\'send\', \'event\', \'recommendation-item\', \'click\')">' +
            escapeStringForHTML(recommendations[i].name) + '</a></li>';
    }
    recommendationsChip.find('ul').html(html);
}