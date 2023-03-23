<a href="javascript:void(0)" class="hidden-default-toggle"> Filter by last modified time</a>
<div class="hidden-default">
    <input id="modified-date-picker" style="color:black" />
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
    $("#modified-date-picker").datepicker();

    function modifiedDatePicker(action) {
        var time = $("#modified-date-picker").datepicker("getDate");
        if(time === null) return;
        var epoch_time = time.getTime() / 1000;
        var query = window.location.search
            .replace(/filter\[\]=v_lastmodified_epoch:(%3E|%3C)\d+&?/gi, "")
            .replace(/filter\[\]=v_status:N&?/gi, "")
            .replace(/&$/, "");
        var path = window.location.pathname.replace(/page\/\d+\/search/, "search");
        var fullpath = path + query;
        var query_delim = "&";
        if(window.location.search === "") {
            query_delim = "?";
        }
        var modified_fullpath = fullpath + query_delim + "filter[]=v_lastmodified_epoch:" + action + epoch_time;
        window.location.href = modified_fullpath;
    }

    $("#modified-date-picker-before").click(function() {
        modifiedDatePicker("<");
    });

    $("#modified-date-picker-after").click(function() {
        modifiedDatePicker(">");
    });

    $(".hidden-default-toggle").click(function() {
        $(".hidden-default").slideToggle();
    });
});
</script>
