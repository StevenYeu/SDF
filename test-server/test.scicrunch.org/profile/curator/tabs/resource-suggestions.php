<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/api-classes/user_messages.php";

    // get all resource suggestions
    if(isset($_GET["status"]) && in_array($_GET["status"], ResourceSuggestion::$allowed_statuses)) $status_type = $_GET["status"];
    else $status_type = ResourceSuggestion::STATUS_PENDING;

    $resource_suggestions = ResourceSuggestion::loadArrayBy(Array("status"), Array($status_type));


    function buttonURL($status) {
        // set the get vars
        $getvars = $_GET;
        $getvars["status"] = $status;

        // get the query string from the get
        $query_string = http_build_query($getvars);

        // get the url
        $url = parse_url($_SERVER["REQUEST_URI"],  PHP_URL_PATH);

        // join url and query string
        $full_path = $url . "?" . $query_string;

        return $full_path;
    }
?>

<div class="tab-pane fade in active">
    <!-- filters -->
    <div class="btn-group" role="group">
        <a href="<?php echo buttonURL(ResourceSuggestion::STATUS_PENDING) ?>"><button type="button" class="btn btn-default<?php echo $status_type == ResourceSuggestion::STATUS_PENDING ? "active" : "" ?>">Pending</button></a>
        <a href="<?php echo buttonURL(ResourceSuggestion::STATUS_APPROVED) ?>"><button type="button" class="btn btn-default<?php echo $status_type == ResourceSuggestion::STATUS_APPROVED ? "active" : "" ?>">Approved</button></a>
        <a href="<?php echo buttonURL(ResourceSuggestion::STATUS_REJECTED) ?>"><button type="button" class="btn btn-default<?php echo $status_type == ResourceSuggestion::STATUS_REJECTED ? "active" : "" ?>">Rejected</button></a>
    </div>

    <!-- suggestions -->
    <div id="accordion-v1" class="panel-group acc-v1">
        <?php foreach($resource_suggestions as $i => $rs): ?>
            <div class="panel panel-default">
                <div class="panel panel-heading">
                    <h4 class="panel-title">
                        <a href="#collapse-<?php echo $i ?>" data-parent="#accordion-v1" data-toggle="collapse" class="accordion-toggle" aria-expanded="false">
                            <?php echo $rs->resource_name ?>
                        </a>
                    </h4>
                </div>
                <div class="panel-collapse collapse" id="collapse-<?php echo $i ?>" aria-expanded="false">
                    <div class="panel-body">
                        <dl class="dl-horizontal">
                            <?php
                                $comm = new Community();
                                $comm->getByID($rs->cid);

                                $conversation_api_data = checkExistingConversation($_SESSION["user"], NULL, "resource-suggestions", $rs->id, true);
                                $conversation_exists = ($conversation_api_data->success && !is_null($conversation_api_data->data));
                                $conversation_link = $conversation_exists ? "/account/messages?convID=" . $conversation_api_data->data->id : "/account/messages";
                            ?>
                            <dt>Resource Name</dt><dd><?php echo $rs->resource_name ?></dd>
                            <dt>Resource URL</dt><dd><?php echo $rs->resource_url ?></dd>
                            <dt>Description</dt><dd><?php echo $rs->description ?></dd>
                            <dt>Defining Citation</dt><dd><?php echo $rs->defining_citation ?></dd>
                            <dt>Community</dt><dd><?php echo $comm->portalName ?></dd>
                            <dt>Status</dt><dd><?php echo $rs->status ?></dd>
                            <dt>Submitter Email</dt><dd><?php echo $rs->email ?></dd>
                            <dt>Discussion</dt>
                            <dd>
                                <?php if(!$conversation_exists): ?><i class="fa fa-plus-circle add-conversation" style="cursor:pointer;color:blue" data-suggestionid="<?php echo $rs->id ?>"></i><?php endif ?>
                                <a class="link-conversation" target="_blank" <?php echo $conversation_exists ? '' : 'style="display:none"' ?> href="<?php echo $conversation_link ?>" data-suggestionid="<?php echo $rs->id ?>"><i class="fa fa-comments" style="cursor:pointer;color:blue"></i></a>
                            </dd>
                            <hr/>
                            <form action="/forms/resource-forms/resource-suggestion-curate.php" method="post">
                                <input type="hidden" name="rsid" value="<?php echo $rs->id ?>" />
                                <div class="form-group">
                                    <label for="curator-comment-<?php echo $i ?>">Curator comment</label>
                                    <textarea class="form-control" name="comment" id="curator-comment-<?php echo $i ?>" rows="3" placeholder="Comments about this submission"><?php echo $rs->curator_comment ?></textarea>
                                </div>
                                <div class="form-group">
                                    <div class="btn-group">
                                        <?php if($rs->status !== ResourceSuggestion::STATUS_APPROVED): ?><button type="submit" class="btn btn-success" name="submit" value="approved">Approve</button><?php endif ?>
                                        <?php if($rs->status === ResourceSuggestion::STATUS_PENDING): ?><button type="submit" class="btn btn-danger" name="submit" value="rejected">Reject</button><?php endif ?>
                                        <button type="submit" class="btn btn-default" name="submit" value="none">Save comment</button>
                                    </div>
                                </div>
                            </form>
                        </dl>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>
<script>
$(function() {
    // form submission stuff
    $("form").submit(function() {
        var submit_type = $("input[type=submit][clicked=true]").val();
        if(submit_type == "approve") {
            $(this).attr("target", "_blank");
        }
        return true;
    });
    $("form input[type=submit]").click(function() {
        $("input[type=submit]", $(this).parents("form")).removeAttr("clicked");
        $(this).attr("clicked", "true");
    });

    // create curator conversation
    $(".add-conversation").click(function() {
        var ref_id = $(this).data("suggestionid");
        var that = this;
        $.ajax({
            type: "POST",
            url: "/api/1/usermessages/conversation",
            data: {"curator": true, "reference_type": "resource-suggestions", "reference_id": ref_id},
            dataType: "json",
            success: function(response) {
                if(response.data != null) {
                    var conversation = response.data;
                    $(".link-conversation[data-suggestionid=" + ref_id + "]").attr("href", "/account/messages?convID=" + conversation.id).show();
                    $(that).hide();
                }
            }
        });
    });
});

</script>
