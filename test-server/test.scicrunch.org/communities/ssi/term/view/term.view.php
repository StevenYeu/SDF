<?php
require_once $GLOBALS["DOCUMENT_ROOT"] . "/classes/schemas/schema-generator-term.class.php";

$url_path = parse_url(explode("?", $_SERVER["REQUEST_URI"])[0], PHP_URL_PATH);
$array = explode("/", $url_path);
$ilx = array_pop($array);
$ilx = preg_replace("/\?.+$/", '', $ilx);

$protocol = $_SERVER['SERVER_PORT']  == 443 ?'https':'http';
$host = $protocol . "://" . $_SERVER['HTTP_HOST'];
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
$back = isset($_GET['searchTerm']) ? true : false;
$searchTerm = preg_replace("/\'/", '"', $searchTerm);

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

<style>
    .flag {
      padding: 0px 4px;
      border-radius: 20px;
      font-size: 14px;
      color: white;
      align-content: center;
    }
</style>

<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term View',array($home, 'Term Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard'),str_replace("_", ":", strtoupper($ilx)));
?>
<div class="container content" ng-app="termViewApp" ng-cloak>
    <div ng-controller="termViewCtrl">
        <?php if($schema): ?>
            <script type="application/ld+json"><?php echo $schema->generateJSON() ?></script>
        <?php endif ?>
        <ng-include src="'/templates/term/term-messages.html'"></ng-include>

        <input id="ilx" type="hidden" name="ilx" value="<?= $ilx ?>">
        <input id="cid" type="hidden" name="cid" value="<?= $community->id ?>">
        <input id="cname" type="hidden" name="cname" value="<?= $community->name ?>">
        <input id="action" type="hidden" value="<?php echo $_GET['action'] ?>">
        <input id="what" type="hidden" value="<?php echo $_GET['what'] ?>">
        <input id="searchTerm" type="hidden" name="searchTerm" value='<?= $searchTerm ?>'>
        <div ng-show="error == true">
            <pre class="alert alert-danger" >{{ errorMsg }}</pre>
        </div>
        <div ng-show="message.length > 0">
            <pre class="alert alert-success" >{{ message }}</pre>
        </div>

        <div ng-hide="error === true">
            <div class="row">

                <div class="col-md-7">
                    <h3 style="display:inline">
                        {{ term.label | parseHtml }}
                        <!-- <span ng-show="term.type=='cde'" class="flag" style="background-color: green;">CDE <i class="fa fa-info-circle"></i></span>
                        <span ng-show="term.type=='TermSet'" class="flag" style="background-color: orange;">TermSet <i class="fa fa-info-circle"></i></span>
                        <span ng-show="term.type=='pde'" class="flag" style="background-color: blue;">PDE <i class="fa fa-info-circle"></i></span>
                        <span ng-show="term.type=='fde'" class="flag" style="background-color: purple;">FDE <i class="fa fa-info-circle"></i></span>
                        <span ng-show="term.type=='annotation'" class="flag" style="background-color: red;">Annotation <i class="fa fa-info-circle"></i></span>
                        <span ng-show="term.type=='relationship'" class="flag" style="background-color: grey;">Relationship <i class="fa fa-info-circle"></i></span> -->
                    </h3>
                    &nbsp;&nbsp;<a target="_self" href="/<?php echo $community->portalName; ?>/interlex/edit/{{ term.id }}?searchTerm=<?php echo $searchTerm ?>"><i class="fa fa-pencil" aria-hidden="true" style="color:#009900;"></i></a>
                    <span ng-show="showComments==true" style="font-size:14px">
                        &nbsp;&nbsp;<a href="javascript:void(0)" ng-click="changeCommentsStatus()" style="color:#408dc9"><i class="fa fa-comments-o"></i> <i>Hide Comments (<span id="display_comments_count_1"></span>)</i></a>
                    </span>
                    <span ng-show="showComments==false" style="font-size:14px">
                        &nbsp;&nbsp;<a href="javascript:void(0)" ng-click="changeCommentsStatus()" style="color:#408dc9"><i class="fa fa-comments-o"></i> <i>Show Comments (<span id="display_comments_count_2"></span>)</i></a>
                    </span>
                    <!-- &nbsp;&nbsp;<span id="subscription"></span> -->
                    <br>
                    <a target="_blank" href="http://uri.interlex.org/base/{{term.ilx}}">http://uri.interlex.org/base/{{term.ilx}}</a>
                </div>

                <div class="col-md-3">
                    <div>
                        <span>
                            <a target="_blank" href="/<?php echo $community->portalName?>/interlex/create">
                                <i class="fa fa-plus" aria-hidden="true"></i> Add new term
                            </a>
                        </span>
                    </div>
                    <div ng-show="term.community.hasOwnProperty('id') && term.community.id != null">
                        <span ng-show="term.community.status == 'suggested'" style="color:#000099;">
                            <i class="fa fa-star-half-o" aria-hidden="true" style="color:#000099;"></i> Term has been suggested to community
                        </span>
                        <span ng-show="term.community.status == 'approved'" style="color:#009900;">
                            <i class="fa fa-star" aria-hidden="true" style="color:#009900;"></i> Term has been approved for community
                        </span>
                        <span ng-show="term.community.status == 'denied'" style="color:#990000;">
                            <i class="fa fa-star" aria-hidden="true" style="color:#990000;"></i> Term has been denied for community
                        </span>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']->role > 1): ?>
                        <span ng-click="approveCommunity()">
                            <br>
                            <i class="fa fa-pencil" aria-hidden="true" style="color:#009900;"></i> Approve/deny term for community
                        </span>
                        <input id="uid_curated" type="hidden" name="uid_curated" value="<?= $_SESSION['user']->id ?>">
                        <?php endif ?>
                    </div>

                    <div ng-show="!term.community.hasOwnProperty('id') || term.community.id == null">
                        <?php if(isset($_SESSION['user'])): ?>
                            <span ng-click="suggestTerm()">
                                <i class="fa fa-star-o" aria-hidden="true" style="color:#009900;"></i> Suggest term to community
                            </span>
                            <input id="uid_suggested" type="hidden" name="uid_suggested" value="<?= $_SESSION['user']->id ?>">
                        <?php else: ?>
                            <span>Log in to suggest term to community</span>
                        <?php endif ?>
                    </div>
                </div>

                <div class="col-md-2 pull-right">
                    <div>
                        <span ng-show="<?php echo $back; ?>">
                            <a target="_self" href="/<?php echo $community->portalName?>/interlex/search?q={{searchTerm}}">
                                <i class="fa fa-search" aria-hidden="true"></i> Back to search results
                            </a>
                        </span>
                    </div>
                    <div>
                        <span>
                            <a target="_self" href="/<?php echo $community->portalName?>/interlex/search">
                                <i class="fa fa-search" aria-hidden="true"></i> New search
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-10">
                    <strong>Preferred ID:</strong> {{term.preferredId}} &nbsp;&nbsp;&nbsp;
                    <strong>Type:</strong> {{term.type}} &nbsp;&nbsp;&nbsp;
                    <span ng-if="term.type == 'term' || term.type == 'cde'">
                        <strong>OWL Equivalent:</strong> owl:Class &nbsp;&nbsp;&nbsp;
                    </span>
                    <span ng-if="term.type == 'relationship'">
                        <strong>OWL Equivalent:</strong> owl:ObjectProperty &nbsp;&nbsp;&nbsp;
                    </span>
                    <span ng-if="term.type == 'annotation'">
                        <strong>OWL Equivalent:</strong> owl:AnnotationProperty &nbsp;&nbsp;&nbsp;
                    </span>
                    <strong>Version:</strong> {{ term.version}} &nbsp;&nbsp;&nbsp;
                    <strong>Last Update:</strong> {{term.time | epochToDateTime | date:'yyyy-MM-dd HH:mm'}}
        <!--                 <strong>Ontology URLs:</strong>&nbsp;&nbsp;&nbsp; -->
        <!--                 <span ng-repeat="o in term.ontologies"> -->
        <!--                     <a href="{{ o.url }}" target="_blank">{{ o.url }}</a> &nbsp;&nbsp;&nbsp; -->
        <!--                 </span> -->
                </div>
                <div class="col-md-2">
                     <span class="pull-right" ng-show="term.ilx.length > 0">
                        <strong><i class="fa fa-download"></i> Export:</strong>
                        <select name="export_file" id="export_file" onchange="exportFile()">
                            <option value="" selected="selected">Choose file type</option>
                            <option value="http://uri.interlex.org/base/{{term.ilx}}.jsonld">JSON-LD</option>
                            <option value="http://uri.interlex.org/base/{{term.ilx}}.ttl">Turtle</option>
                            <option value="http://uri.interlex.org/base/{{term.ilx}}.n3">N3</option>
                            <option value="http://uri.interlex.org/base/{{term.ilx}}.owl">OWL</option>
                        </select>
                         <!-- <a target="_blank" href="/php/term/to-json.php?ilx={{term.ilx}}">
                             <i class="flaticon-json-file" data-toggle="popover" data-content="JSON"></i></a>
                         <a target="_blank" href="/php/term/to-xml.php?ilx={{term.ilx}}">
                             <i class="flaticon-xml" data-toggle="popover" data-content="XML"></i></a>
                         <a target="_blank" href="/php/term/to-ttl.php?ilx={{term.ilx}}">
                             <i class="flaticon-file" data-toggle="popover" data-content="Turtle"></i></a> -->
                    </span>
                </div>
                <div class="col-md-12">{{ stripNewline(term.definition)}}</div>
            </div>

            <div class="row" ng-show="term.comment != null && term.comment != undefined && term.comment.length > 0">
                <div class="col-md-12">
                    <p><strong>Comment:</strong> {{ term.comment}}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <strong>Last Modified By:</strong> {{ last_modify_user }} &nbsp;&nbsp;&nbsp;
                    <strong>Originally Submitted By:</strong> {{ orig_user }}
                </div>
            <?php if($_SESSION['user']->role > 1): ?>
                <div class="col-md-4">
                    <span class="pull-right" id="subscription"></span>
                </div>
            </div>
            <?php else: ?>
            </div>
            <br>
            <?php endif ?>

            <div class="tab-v5">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#general" role="tab" data-toggle="tab"><strong>General</strong></a></li>
                    <li ng-show="term.type=='TermSet'"><a href="#collection" role="tab" data-toggle="tab"><strong>Collection</strong></a></li>
        <!--             ng-click="getFirstGeneration()" -->
                    <li><a href="#children" role="tab" data-toggle="tab"><strong>Children</strong></a></li>
                    <li><a href="#relationships" role="tab" data-toggle="tab"><strong>Relationships</strong></a></li>
                    <li><a href="#annotations" role="tab" data-toggle="tab"><strong>Annotations</strong></a></li>
                    <li><a href="#referencedby" role="tab" data-toggle="tab"><strong>Referenced By</strong></a></li>
                    <li><a href="#versions" role="tab" data-toggle="tab"><strong>History</strong></a></li>
                </ul>
            </div>

            <div class="tab-content">
                <?php include_once 'communities/ssi/term/view/term.view.collection.php';?>
                <?php include_once 'communities/ssi/term/view/term.view.general.php';?>
                <?php include_once 'communities/ssi/term/view/term.view.children.php';?>
                <?php include_once 'communities/ssi/term/view/term.view.relationships.php';?>
                <?php include_once 'communities/ssi/term/view/term.view.annotations.php';?>
                <?php include_once 'communities/ssi/term/view/term.view.referencedby.php';?>
                <?php include_once 'communities/ssi/term/view/term.view.versions.php';?>
            </div>

            <div class="row" ng-show="showComments==true">
                <div class="col-md-12">
                    <br>
                    <b>Comments: (<span id="display_comments_count_3"></span>)</b>
                </div>
                <?php if (!is_null($_SESSION['user'])): ?>
                    <form method="POST" id="comment_form">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input id="comment_sender_name" type="hidden" name="comment_sender_name" value="<?php echo $_SESSION['user']->firstname . " " . $_SESSION['user']->lastname ?>" />
                                <input id="comment_sender_id" type="hidden" name="comment_sender_id" value="<?php echo $_SESSION['user']->id ?>" />
                                <input id="term_ilx" type="hidden" name="term_ilx" value="<?php echo $ilx ?>" />
                                <input id="comment_status" type="hidden" name="comment_status" value=1 />
                            </div>
                        </div>
                        <div class="col-md-11">
                            <div class="form-group">
                                <textarea id="comment_content" class="form-control" name="comment_content" placeholder="Write a comment..." rows="1"></textarea>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-froup">
                                <input type="hidden" name="comment_id" id="comment_id" value=0 />
                                <input id="submit" type="submit" name="submit" value="Submit" class="btn btn-info">
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="col-md-12">
                        <p><span style="color:red">*</span> Please login to post or reply a comment.</p>
                    </div>
                <?php endif ?>
                <div class="col-md-12">
                    <span id="comment_message"></span>
                </div>
                <br>
                <div class="col-md-12">
                    <span id="display_comment"></span>
                </div>
            </div>

            <?php include_once 'templates/term/term-vote-modal.html';?>
            <?php include_once 'templates/term/term-community-curate.html';?>

            <div id="add_notification_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
              	<div class="modal-dialog">
                		<div class="modal-content">
                        <?php if(isset($_SESSION['user'])): ?>
                      			<div class="modal-body">
                                <h3>Subscribe to term: {{ term.label }} (<?php echo $ilx ?>)</h3>
                                <a class="close dark less-right" style="color: red" data-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i></a>
                                <form method="POST" id="notification_add_form">
                                    <div class="form-group">
                                        <input id="user_id" type="hidden" name="user_id" value="<?php echo $_SESSION['user']->id ?>" />
                                        <input id="term_ilx" type="hidden" name="term_ilx" value="<?php echo $ilx ?>" />
                                        <input id="term_name" type="hidden" name="term_name" value="{{ term.label }}" />
                                        <input id="term_des" type="hidden" name="term_des" value="{{ term.definition }}" />
                                    </div>
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col-md-4">Notification Enabled</div>
                                            <div class="col-md-2">
                                                <input type="radio" id="term_notification" name="term_notification" value=1 checked />&nbsp;&nbsp;Yes
                                            </div>
                                            <div class="col-md-2">
                                                <input type="radio" id="term_notification" name="term_notification" value=0 />&nbsp;&nbsp;No
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">Notification Frequency</div>
                                            <div class="col-md-2">
                                                <input type="radio" id="term_update" name="term_update" value="daily" />&nbsp;&nbsp;Daily
                                            </div>
                                            <div class="col-md-2">
                                                <input type="radio" id="term_update" name="term_update" value="weekly" checked />&nbsp;&nbsp;Weekly
                                            </div>
                                            <div class="col-md-2">
                                                <input type="radio" id="term_update" name="term_update" value="monthly" />&nbsp;&nbsp;Monthly
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">Follow children</div>
                                            <div class="col-md-2">
                                                <input type="radio" id="term_follow_children" name="term_follow_children" value=1 />&nbsp;&nbsp;Yes
                                            </div>
                                            <div class="col-md-2">
                                                <input type="radio" id="term_follow_children" name="term_follow_children" value=0 checked />&nbsp;&nbsp;No
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-froup">
                                        <input id="submit" type="submit" name="submit" value="Subscribe" class="btn btn-info">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </form>
                      			</div>
                        <?php else: ?>
                            <div class="modal-body">
                                <p><span style="color:red">*</span> Please log in to subcribe the notification.</p>
                            </div>
                        <?php endif ?>
                		</div>
              	</div>
            </div>

            <div id="update_notification_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
              	<div class="modal-dialog">
                		<div class="modal-content">
                  			<div class="modal-body">
                            <form method="POST" id="notification_update_form">
                                <div class="form-group">
                                    <input id="user_id" type="hidden" name="user_id" value="<?php echo $_SESSION['user']->id ?>" />
                                    <input id="term_ilx" type="hidden" name="term_ilx" value="<?php echo $ilx ?>" />
                                    <input id="term_name" type="hidden" name="term_name" value="{{ term.label }}" />
                                    <input id="term_des" type="hidden" name="term_des" value="{{ term.definition }}" />
                                </div>
                                <div class="form-group" id="notification_detail"></div>
                                <div class="form-froup">
                                    <input id="submit" type="submit" name="submit" value="Update" class="btn btn-info">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                  			</div>
                		</div>
              	</div>
            </div>

            <div id="delete_notification_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
              	<div class="modal-dialog">
                		<div class="modal-content">
                  			<div class="modal-body">
                            <form method="POST" id="notification_delete_form">
                                <h3>Do you want to unsubscribe this notification?</h3>
                                <a class="close dark less-right" style="color: red" data-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i></a>
                                <div class="form-group">
                                  <input id="user_id" type="hidden" name="user_id" value="<?php echo $_SESSION['user']->id ?>" />
                                  <input id="term_ilx" type="hidden" name="term_ilx" value="<?php echo $ilx ?>" />
                                  <input id="term_name" type="hidden" name="term_name" value="{{ term.label }}" />
                                </div>
                                <div class="form-froup">
                                    <input id="submit" type="submit" name="submit" value="Unsubscribe" class="btn btn-info">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                  			</div>
                		</div>
              	</div>
            </div>
        </div>
    </div>

    <div id="reply_comment_Modal" class="modal fade bs-example-modal-sm" tabindex="-1">
      	<div class="modal-dialog">
        		<div class="modal-content">
          			<div class="modal-header">
          				    <a class="close dark less-right" style="color: red" data-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i></a>
          			</div>
          			<div class="modal-body">
                    <form method="POST" id="comment_reply_form">
                        <b>Reply:</b>
                        <div class="form-group">
                            <input id="comment_sender_name" type="hidden" name="comment_sender_name" value="<?php echo $_SESSION['user']->firstname . " " . $_SESSION['user']->lastname ?>" />
                            <input id="comment_sender_id" type="hidden" name="comment_sender_id" value="<?php echo $_SESSION['user']->id ?>" />
                            <input id="term_ilx" type="hidden" name="term_ilx" value="<?php echo $ilx ?>" />
                            <input id="comment_status" type="hidden" name="comment_status" value=1 />
                            <input id="comment_id" type="hidden" name="comment_id" value=0/>
                        </div>
                        <div class="form-group">
                            <textarea id="comment_content" class="form-control" name="comment_content" placeholder="Write a reply..." rows="1" onfocus="this.value=''"></textarea>
                        </div>
                        <div class="form-froup">
                            <input id="submit" type="submit" name="submit" value="Submit" class="btn btn-info">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        </div>
                    </form>
          			</div>
          			<!-- <div class="modal-footer">
          				    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          			</div> -->
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
                            <input id="term_ilx" type="hidden" name="term_ilx" value="<?php echo $ilx ?>" />
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
</div>

<script>

    $(document).ready(function(){
        $('#comment_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();
            var url = "/php/term/add-comment.php";
            process_comment_AJAX(form_data, url);
            // $.ajax({
            //     url:"/php/term/add-comment.php",
            //     method:"POST",
            //     data:form_data,
            //     dataType:"JSON",
            //     success:function(data){
            //         if(data.error != ''){
            //             $('#comment_form')[0].reset();
            //             $('#comment_message').html(data.error);
            //         }
            //         load_term();
            //     }
            // })
        });

        $('#comment_reply_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();
            var url = "/php/term/add-comment.php";
            process_comment_AJAX(form_data, url);
        });

        $('#comment_delete_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();
            var url = "/php/term/delete-comment.php";
            process_comment_AJAX(form_data, url);
        });

        $('#notification_add_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();
            $.ajax({
                url:"/php/term/add-notification.php",
                method:"POST",
                data:form_data,
                dataType:"JSON",
                success:function(data){
                    $('#add_notification_Modal').modal('hide');
                    load_term();
                }
            })
        });

        $('#notification_update_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();
            $.ajax({
                url:"/php/term/update-notification.php",
                method:"POST",
                data:form_data,
                dataType:"JSON",
                success:function(data){
                    $('#update_notification_Modal').modal('hide');
                    load_term();
                }
            })
        });

        $('#notification_delete_form').on('submit', function(event){
            event.preventDefault();
            var form_data = $(this).serialize();
            $.ajax({
                url:"/php/term/delete-notification.php",
                method:"POST",
                data:form_data,
                dataType:"JSON",
                success:function(data){
                    $('#delete_notification_Modal').modal('hide');
                    load_term();
                }
            })
        });

        load_term();

        function process_comment_AJAX(form_data, url) {
            $.ajax({
                url:url,
                method:"POST",
                data:form_data,
                dataType:"JSON",
                success:function(data){
                    if(data.error != ''){
                        $('#comment_form')[0].reset();
                        $('#reply_comment_Modal').modal('hide');
                        $('#delete_comment_Modal').modal('hide');
                        $('#comment_message').html(data.error);
                    }
                    load_term();
                }
            })
        }

        function load_term(){
            var ilx = "<?php echo $ilx ?>";
            var user_role = "<?php echo $_SESSION['user']->role ?>";
            var user_id = "<?php echo $_SESSION['user']->id ?>";
            $.ajax({
                url:"/php/term/fetch-comment.php",
                method:"POST",
                data:{"term_ilx":ilx, "user_role":user_role},
                success:function(data){
                    $('#display_comment').html(data);
                }
            });
            $.ajax({
                url:"/php/term/fetch-comments_count.php",
                method:"POST",
                data:{"term_ilx":ilx},
                success:function(data){
                    $('#display_comments_count_1').html(data);
                    $('#display_comments_count_2').html(data);
                    $('#display_comments_count_3').html(data);
                }
            });
            $.ajax({
                url:"/php/term/fetch-subscription.php",
                method:"POST",
                data:{"term_ilx":ilx, "uid":user_id},
                success:function(data){
                    $('#subscription').html(data);
                }
            });
        }
    });

    $(function () {
        $('#reply_comment_Modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var code = button.data('code'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#comment_id').val(code);
        });
    });

    $(function () {
        $('#delete_comment_Modal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var code = button.data('code'); // Extract info from data-* attributes
            var modal = $(this);
            modal.find('#comment_id').val(code);
        });
    });

    $(".hidden-default-toggle").click(function() {
        $(".hidden-default").slideToggle();
    });
</script>

<script>
    function fetchNotification(term_id) {
        event.preventDefault();
        $.ajax({
            url:"/php/term/fetch-notification.php",
            method:"POST",
            data:{"term_id":term_id},
            success:function(data){
                $('#notification_detail').html(data);
                $('#update_notification_Modal').modal('show');
            }
        });
    };

    function exportFile() {
      var url = document.getElementById("export_file").value;
      if (url != "") {
        var win = window.open(url, "_blank"); // open new tab
        win.focus();
      }
    }
</script>
