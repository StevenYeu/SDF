<script type="text/javascript">
    $(window).on('load', function() {
    // jQuery(window).load(function () {
        if($("#c-pop-up-warning-display").val() == "true"){
            $("#c-pop-up-switching-to-comm").modal('show');
        }
        $("#c-pop-up-dont-show-switching").click(function(){
            $("#c-pop-up-switching-to-comm").modal('hide');
            createCookie("c_pop_up_warning", true);
        });
    });
</script>

<input type="hidden" id="c-pop-up-warning-display" value="<?php echo $data['text'] ?>"/>
<div class="modal fade" id="c-pop-up-switching-to-comm" tabindex="-1" role="dialog" aria-labelledby="c-pop-up-switching-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="c-pop-up-switching-title">You have left <?php echo $data["referer"] ?> and are entering <?php echo $data["curr"] ?></h4>
            </div>
            <div class="modal-body">
                <p>You are entering the <?php echo $data["name"] ?> community.  If you were logged in at <?php echo $data["referer"] ?>, you may have to re-login to this community.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="c-pop-up-dont-show-switching">Close and don't show again</button>
            </div>
        </div>
    </div>
</div>
