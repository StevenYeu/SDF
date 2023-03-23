<ol id="joyRideHeaderContent" style="display:none">
    <li data-class="joyride-about" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>About</h2>
        <p>
            Find out information about this community and visit other pages within it.  You can also add a resource to the community here.
        </p>
    </li>
    <li data-class="joyride-community-resources" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Community Resources</h2>
        <p>
            Search through data specific to this community
        </p>
    </li>
    <li data-class="joyride-more-resources" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>More Resources</h2>
        <p>
            Search through all of SciCrunch's data.
        </p>
    </li>
    <li data-class="joyride-literature" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Literature</h2>
        <p>
            Search through literature data.
        </p>
    </li>
</ol>
<script type="text/javascript">
    $(function() {
        $(".tutorial-header-btn").click(function () {
            $("body").joyride();
            $("body").joyride("destroy");   // joyride bug, when multiple joyrides on a page
            $(".joyride-next-tip").show();
            $("#joyRideHeaderContent").joyride(
                {
                    "postStepCallback": function(index, tip) { },
                    "startOffset": 0,
                    "tip_class": false,
                }
            );
        });
    });
</script>
