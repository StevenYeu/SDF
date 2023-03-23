<a href="javascript:void(0)" class="hidden-default-toggle"> Filter by records added date</a>
<div class="hidden-default">
    <input id="modified-date-picker30" style="color:black" />
    <div class="btn-group">
        <a href="javascript:void(0)" id="modified-date-picker-before"><button class="btn btn-default">Before</button></a>
        <a href="javascript:void(0)" id="modified-date-picker-after"><button class="btn btn-default">After</button></a>
    </div>
</div>

<style>
    .hidden-default {
        display: none;
    }
</style>
<script>
$(function() {
    $("#modified-date-picker30").datepicker();

    function modifiedDatePicker(action) {
        var time = $("#modified-date-picker30").datepicker("getDate");
            time = $.datepicker.formatDate("yymmdd", time);
        if(time == "") return;
        var query = window.location.search.replace(/filter\[\]=(gte|lte):\d+&?/gi, "")
                  .replace(/filter\[\]=(gte|lte):/gi, "");
        if(query.includes("&changed")){
            query = query.replace(/facet\[\]=([a-z|0-9|%]+)&?/gi, "");
            query = query.replace("&changed", "");
        }
        var path = window.location.pathname.replace(/page\/\d+\/search/, "search");
        var fullpath = path + query;
        var query_delim = "&";
        if(window.location.search === "") {
            query_delim = "?";
        }
        var modified_fullpath = fullpath + query_delim + "filter[]=" + action + ":" + time;
        modified_fullpath = modified_fullpath.replace("&&", "&")
                          .replace("?&", "?");
        window.location.href = modified_fullpath;
    }

    $("#modified-date-picker-before").click(function() {
        modifiedDatePicker("lte");
    });

    $("#modified-date-picker-after").click(function() {
        modifiedDatePicker("gte");
    });

    $(".hidden-default-toggle").click(function() {
        $(".hidden-default").slideToggle();
    });
});
</script>
