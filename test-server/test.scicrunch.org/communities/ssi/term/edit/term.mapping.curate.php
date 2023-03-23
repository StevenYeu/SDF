<?php
if(!isset($_SESSION["user"])){
    echo \helper\loginForm("You must be logged in to curate a term mapping");
    return;
}
$uid = $_SESSION["user"]->id;
$protocol = $_SERVER['SERVER_PORT']  == 443 ?'https':'http';
$host = $protocol . "://" . $_SERVER['HTTP_HOST'];

$uri = array_pop(explode("/", $_SERVER[REQUEST_URI]));
parse_str($uri, $params);
// print_r($params);

$dbObj = new DbObj();

$term = new Term($dbObj);
$term->getMappings($params['tmid']);

?>

<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/js/term/bootstrap.min.js"></script>
<script src="/js/term/angular.min.js"></script>
<script src="/js/term/angular-modal-service.js"></script>

<!-- <script src="/js/angular-chips/angular-sanitize.js"></script> -->
<script src="/js/module-error.js"></script>
<script src="/js/angular-chips/ui-bootstrap.js"></script>
<!-- <script src="/js/angular-chips/ui-bootstrap-tpls-0.14.3.js"></script> -->
<script src="/js/term/term.js"></script>
<script src="/js/term/term-mapping-curate.js"></script>

<!-- <script src="/js/angular-1.7.9/angular.min.js"></script> -->
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>

<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Curate Mapping',array($home, 'Term Dashboard', 'Mapping Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard', '/'.$community->portalName.'/interlex/dashboard-mappings'),'Curate Mapping');
?>

<?php if(count($term->mappings) == 0): ?>
    <div class="container content">
        <h3>This term mapping doesn't exist, please check <a href="/<?php echo $community->portalName?>/interlex/dashboard-mappings">Mapping Dashboard</a>.</h3>
    </div>
<?php else: ?>
<div class="container content" ng-app="mappingCurateApp" ng-cloak>
    <ng-include src="'/templates/term/term-messages.html'"></ng-include>

    <div class="" ng-controller="mappingCurateCtrl">
       <div class="col-md-12 text-danger" ng-show="message.length > 0" style="font-size:larger;color:blue">{{message}}</div>
       <!-- <hr style="margin: 20px 0;"> -->

       <div>
          <div class="panel panel-success">
              <div class="panel-heading">
                  <div class="row">
                      <div class="col-md-1 pull-left" ng-show="previous_id > 0">
                          <a href="/<?php echo $community->portalName?>/interlex/curate-mapping/tmid={{ previous_id }}">
                              <i class="fa fa-chevron-circle-left"></i>
                          </a>
                      </div>
                      <div class="col-md-3">
                          <b>{{ source }}</b>
                      </div>
                      <div class="col-md-4">
                          <b>Column:&nbsp;&nbsp;</b> {{ column_name }}
                      </div>
                      <div class="col-md-3">
                          <b>View:&nbsp;&nbsp;</b> {{ view_name }}
                      </div>
                      <div class="col-md-1" ng-show="next_id > 0">
                          <div class="pull-right">
                            <a href="/<?php echo $community->portalName?>/interlex/curate-mapping/tmid={{ next_id }}">
                                <i class="fa fa-chevron-circle-right"></i>
                            </a>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="row">
              <div class="col-md-9">
                  <form name="mappingCurateForm" id="mappingCurateForm"  class="term-form sky-form" role="form">
                      <input id="uid" type="hidden" name="uid" value="<?= $uid ?>">
                      <input id="tmid" type="hidden" name="tmid" value="<?= $params['tmid'] ?>">

                      <div class="container">
                          <div class="row">
                              <b>Mapped Term:&nbsp;&nbsp;</b>
                              <span ng_show="tid != null && tid > 0">
                                {{ concept }}&nbsp;&nbsp;
                                ({{ concept_id }})&nbsp;&nbsp;<a href="/<?php echo $community->portalName?>/interlex/view/{{ ilx }}" target="_blank"><i class="fa fa-external-link" title="View term details"></i></a>
                              </span>
                          </div>
                          <div class="row">
                              <div class="col-md-6" style="padding:0 0;">
                                  <b>Source Value:&nbsp;&nbsp;</b> {{ selectedRecord.matched_value }}
                              </div>
                          </div>
                          <div class="row">
                              <b>Original Value:&nbsp;&nbsp;</b> {{ selectedRecord.value }}
                              &nbsp;&nbsp;&nbsp;&nbsp;
                              <select ng-model="is_ambiguous" ng-change="">
                                  <option value="true">Ambiguous</option>
                                  <option value="false">Not Ambiguous</option>
                              </select>
                              &nbsp;&nbsp;&nbsp;&nbsp;
                              <select ng-model="is_whole" ng-change="">
                                  <option value="true">Whole</option>
                                  <option value="false">Not Whole</option>
                              </select>
                          </div>
                          <div class="row">
                              <b>Snippet:&nbsp;&nbsp;</b> {{ selectedRecord.snippet }}
                          </div>
                          <div class="row">
                              <div class="col-md-6" style="padding:0 0;">
                                  <b>Relation:&nbsp;&nbsp;</b>
                                  <select ng-model="relation" ng-options="o  for o in relationOptions"></select>
                                  &nbsp;&nbsp;&nbsp;&nbsp;
                                  <b>Status:&nbsp;&nbsp;</b>
                                  <select ng-model="selectedStatus" ng-options="o  for o in statusOptions" ng-change=""></select>
                              </div>

                              <div class="col-md-12" style="padding:0 0;">
                                  <strong>Notes: </strong>
                              </div>

                              <div class="col-md-12" style="padding:0 0;">
                                  <textarea name="notes" rows="2" cols="100" ng-model="notes"></textarea>
                              </div>

                              <div class="col-md-12" style="padding:0 0;">
                                  <div class="pull-right">
                                      <button class="btn btn-sm btn-primary" ng-click="mappingCurateForm.notes.$valid && changeStatus()">Update</button>
                                      <button class="btn btn-sm btn-danger" ng-click="mappingCurateForm.notes.$valid && deleteRecord()"><i class="fa fa-trash-o" title="Delete value"></i></button>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <br>
                      <div class="container" style="background-color:#d9edf7;border-color:#d9edf7;">
                          <br>
                          <span ng_show="tid != null && tid > 0">
                              <b>{{ concept }}&nbsp;&nbsp;({{ concept_id}})</b><br>
                              <b>Proferred ID:</b>&nbsp;&nbsp;{{ preferredId }}&nbsp;&nbsp;&nbsp;&nbsp;
                              <b>Type:</b>&nbsp;&nbsp;{{ type }}&nbsp;&nbsp;&nbsp;&nbsp;
                              <b>Version:</b>&nbsp;&nbsp;{{ version }}<br>
                              <b>Synonyms:</b>&nbsp;&nbsp;{{ synonyms }}<br>
                              <b>Description:</b>&nbsp;&nbsp;{{ description }}<br>
                          </span>
                          <span ng_show="tid == null || tid == 0"><b> No description.</b><br></span>
                          <br>
                      </div>
                      <div class="container" style="background-color:#bdd8eb;border-color:#bdd8eb;">
                          <br>
                          <div class="scrollbox" style="max-height: 250px;overflow: auto">
                              <div class="col-md-12" style="padding:10px 0;">
                                  <strong>Curation history:</strong><br>
                              </div>
                              <div style="padding:10px 0;" class="col-md-12" ng-show="selectedRecord.curation_logs.length==0">
                                  <div class="col-md-12">
                                    <b>None.</b>
                                  </div>
                              </div>
                              <div style="padding:10px 0;" class="col-md-12" ng-repeat="l in selectedRecord.curation_logs">

                                  <div class="col-md-12">
                                    ------------------------
                                  </div>
                                  <div class="col-md-3">
                                      <strong>Date:</strong> {{l.time * 1000 | date:'MM/dd/yyyy'}}
                                  </div>
                                  <div class="col-md-3">
                                      <strong>User:</strong> {{ l.curator }}
                                  </div>
                                  <div class="col-md-3">
                                      <strong>Status:</strong> {{ l.curation_status }}
                                  </div>
                                  <div class="col-md-3">
                                      <strong>Relation:</strong> {{ l.relation }}
                                  </div>
                                  <div class="col-md-6">
                                      <strong>Mapped Concept:</strong> {{ l.concept }} - {{ l.concept_id }}
                                  </div>
                                  <div class="col-md-6">
                                      <strong>Notes:</strong> {{ l.notes }}
                                  </div>
                              </div>
                          </div>
                          <br>
                      </div>
                  </form>
              </div>
              <div class="col-md-3">
                  <div class="container" style="background-color:#fff3b8;border-color:#fff3b8;">
                      <br>
                      <b style="font-size:16px">Suggested Matches</b>
                      <div ng-show="suggested_matches.length > 0">
                          <div class="row" ng-repeat="record in suggested_matches">
                              <p style="height:1px"></p>
                              <div class="col-md-10">
                                  <span uib-popover="{{ record.info }}" popover-trigger="'mouseenter'">{{ record.name }} <i class="fa fa-info-circle"></i></span>
                                  <br>
                                  {{ record.source }}
                              </div>
                              <div class="col-md-2">
                                  <span ng-show="record.ilx != ilx"><a href="javascript:void(0)" ng-click="changeMappedTerm(record)"><i class="fa fa-exchange"></i></a></span>
                                  <span ng-show="record.ilx == ilx"><i class="fa fa-check"></i></span>
                                  <span ng-show="record.ilx != ilx"><a href="/<?php echo $community->portalName?>/interlex/view/{{ record.ilx }}" target="_blank"><i class="fa fa-external-link" title="View term details"></i></a></span>
                              </div>
                          </div>
                      </div>
                      <div class="row" ng-hide="suggested_matches.length > 0">
                          <div class="col-md-12">
                              <p style="height:1px"></p>
                          </div>
                          <div class="col-md-12">No matches found.</div>
                      </div>
                      <hr style="border-top: 1px solid grey;">
                      <div>
                          <b style="font-size:16px">InterLex Matches</b>
                          <div class="row">
                              <div class="col-md-9">
                                  <input type="text" style="width:100%" ng-model="keywords">
                              </div>
                              <div class="col-md-3">
                                  <a href="javascript:void(0)" ng-click="searchInterlexMatches()"><button class="btn btn-sm btn-success"><i class="fa fa-search"></i></button></a>
                              </div>
                          </div>
                          <div class="row" ng-repeat="result in interlex_matches">
                              <div class="col-md-12">
                                  <p style="height:1px"></p>
                              </div>
                              <div class="col-md-10">
                                  <span uib-popover="{{result.description}}" popover-trigger="'mouseenter'">{{ result.name }} <i class="fa fa-info-circle"></i></span><br>
                                  {{ result.preferredID }}
                              </div>
                              <div class="col-md-2">
                                  <span ng-show="result.ilx != ilx"><a href="javascript:void(0)" ng-click="changeMappedTerm(result)"><i class="fa fa-exchange"></i></a></span>
                                  <span ng-show="result.ilx == ilx"><i class="fa fa-check"></i></span>
                                  <a href="/<?php echo $community->portalName?>/interlex/view/{{ result.ilx }}" target="_blank"><i class="fa fa-external-link"></i></a>
                              </div>
                          </div>
                          <div class="row" ng-hide="interlex_matches.length > 0">
                              <div class="col-md-12">
                                  <p style="height:1px"></p>
                              </div>
                              <div class="col-md-12">No matches found.</div>
                          </div>
                          <br>
                      </div>
                  </div>
              </div>
          </div>
          <br>
          <?php include_once 'templates/term/term-mapping-delete-modal.html';?>
       </div>
    </div>
</div>
<?php endif ?>
