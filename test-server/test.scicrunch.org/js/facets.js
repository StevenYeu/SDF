$(function () {
    var sortAlpha = function(a,b) {
        var textA = $(a).find("a").text();
        var textB = $(b).find("a").text();
        if(textA < textB) return -1;
        if(textA > textB) return 1;
        return 0;
    };

    var sortCount = function(a,b) {
        var countA = +$(a).find("a").data("count");
        var countB = +$(b).find("a").data("count");
        return countB - countA;
    };

    var clickSortFunGen = function(sortFun) {
        var clickSortFun = function() {
            var uls = $(this).siblings("ul").find("ul.collapse");
            uls.each(function(i, ul) {
                var lis = $.makeArray($(this).children("li"));
                lis.sort(sortFun);
                $(ul).empty();
                for(var i = 0; i < lis.length; i++) {
                    $(ul).append(lis[i]);
                }
            });
        };
        return clickSortFun;
    };

    $(".facet-sort-alpha").click(clickSortFunGen(sortAlpha));
    $(".facet-sort-count").click(clickSortFunGen(sortCount));
});
