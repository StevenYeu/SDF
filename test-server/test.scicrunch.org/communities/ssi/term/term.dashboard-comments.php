<?php

    if ($_SESSION["user"]->role < 1) {
        echo "No permission to view this page.";
        return;
    }

    ## type 0: deleted comments; type 1: current comments
    if(isset($_GET["deleted_comments"])) $comments_type = 0;
    else $comments_type = 1;
?>

<link rel="stylesheet" type="text/css" href="/assets/flaticons/font/flaticon.css">
<link rel="stylesheet" type="text/css" href="/js/term/angular-tree/css/tree-control.css" />
<link rel="stylesheet" type="text/css" href="/js/term/angular-tree/css/tree-control-attribute.css" />
<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/js/node_modules/angular/angular.js"></script>
<script src="/js/angular-chips/ui-bootstrap.js"></script>
<script src="/js/angular-chips/ui-bootstrap-tpls-0.14.3.js"></script>
<script src="/js/term/angular-tree/angular-tree-control.js"></script>
<script src="/js/node_modules/angular/angular-sanitize.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/module-utilities.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-view.js"></script>



<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Comments Dashboard',array($home, 'Term Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard'),'Comments Dashboard');
?>

<div class="row" ng-show="showComments==true">
    <div class="container">
        <div class="row">
            <div class="col-md-2 hidden-xs related-search">
                <br>
                <h3><b>Current Facets</b></h3>
                <?php if($comments_type == 0): ?>
                    Types: Deleted Comments
                <?php else: ?>
                    Types: Current Comments
                <?php endif ?>
                <hr>
                <h3><b>Facets</b></h3>
                <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
                    <li class="list-group-item list-toggle" data-toggle="collapse" data-parent="#sidebar-nav" href="#collapse-comments">
                        <a href="javascript:void(0)">Types</a>
                        <ul id="collapse-comments" class="collapse">
                            <li style="border-top:1px solid #ddd">
                                <a href="/<?php echo $community->portalName ?>/interlex/dashboard-comments">Current Comments</a>
                            </li>
                            <li style="border-top:1px solid #ddd">
                                <a href="/<?php echo $community->portalName ?>/interlex/dashboard-comments?deleted_comments">Deleted Comments</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
            <div class="col-md-10">
                <div class="row">
                    <br>
                    <div class="col-md-12">
                        <span id="comment_message"></span>
                    </div>
                    <div class="col-md-12">
                        <span id="display_comment"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="delete_comment_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
  	<div class="modal-dialog">
    		<div class="modal-content">
      			<div class="modal-header">
      				    <a class="close dark less-right" style="color: red" data-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i></a>
      			</div>
      			<div class="modal-body">
                <form method="POST" id="comment_delete_form">
                        <b>Do you want to delete this comment?</b>
                        <div class="form-group">
                            <input id="user_role" type="hidden" name="user_role" value="<?php echo $_SESSION['user']->role ?>" />
                            <input id="comment_sender_id" type="hidden" name="comment_sender_id" value="<?php echo $_SESSION['user']->id ?>" />
                            <input id="comment_id" type="hidden" name="comment_id" value=0/>
                        </div>
                        <div class="form-froup">
                            <input id="submit" type="submit" name="submit" value="Detele" class="btn btn-info">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                </form>
      			</div>
    		</div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#comment_delete_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();

            var url = "/php/term/delete-comment.php";
            process_comment_AJAX(form_data, url);
        });

        load_comment();

        function process_comment_AJAX(form_data, url) {
            $.ajax({
                url:url,
                method:"POST",
                data:form_data,
                dataType:"JSON",
                success:function(data){
                    if(data.error != ''){
                        $('#delete_comment_Modal').modal('hide');
                        $('#comment_message').html(data.error);
                    }
                    load_comment();
                }
            })
        }

        function load_comment(){
            var portal_name = "<?php echo $community->portalName ?>";
            var comments_type = <?php echo $comments_type ?>;
            $.ajax({
                url:"/php/term/fetch-comment-table.php",
                method:"POST",
                data:{"portal_name":portal_name, "comments_type":comments_type},
                success:function(data){
                    $('#display_comment').html(data);
                }
            });
        }
    });

    $(function () {
        $('#delete_comment_Modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var code = button.data('code'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#comment_id').val(code);
        });
    });
</script>
