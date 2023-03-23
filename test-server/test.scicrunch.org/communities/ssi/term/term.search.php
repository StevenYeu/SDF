<?php
$searchTerm = isset($_GET['q']) ? $_GET['q'] : '';
$startSearch = isset($_GET['q']) ? 1 : 0;
if (preg_match('/^([\'])/m', $searchTerm)){
    $searchTerm = str_replace('\'', "\"", $searchTerm);
}
?>
<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<!-- <script src="/js/angular-1.7.9/angular-sanitize.js"></script> -->

<script src="/js/angular-chips/ui-bootstrap.js"></script>
<!-- <script src="/js/angular-chips/ui-bootstrap-tpls-0.14.3.js"></script> -->

<script src="/js/module-utilities.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-search.js"></script>


<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Search',array($home, 'Term Dashboard'),array('/'.$community->portalName, '/'.$community->portalName . '/interlex/dashboard'),'Term Search');
?>
<div class='container'>
<div style="padding-top:15px;" class="" ng-app="termSearchApp">
    <ng-include src="'/templates/term/term-messages.html'"></ng-include>

    <div  class="row col-md-12" ng-controller="termSearchCtrl" ng-cloak>
        <form class="term-form sky-form" role="form">
         <div class="input-group">

           <input type="hidden" name="q" id="q" value='<?php echo $searchTerm; ?>'></input>
           <input type="hidden" name="startSearch" id="startSearch" value='<?php echo $startSearch; ?>'></input>
           <input type="hidden" name="cid" id="cid" value='<?php echo $community->id; ?>'></input>
           <input type="text" class="form-control" style="height:34px" ng-model="searchTerm">
           <span class="input-group-btn">
               <button class="btn-u search-icon" type="submit" ng-click="startElasticSearch()"><i class="fa fa-search"></i></button>
           </span>

        </div>
        </form>

        <div id="resultContainer" style="margin-top: 15px">
<!--         <div id="resultContainer" ng-show="total < 1" class="text-danger">No result for {{ searchTerm }}</div> -->
        <div class="text-danger" ng-show="notFound == 1">
            No entry found for {{searchTerm}}! Please check the spelling. If you have not added terms to your commnunity, please add them.
        </div>

         <span class="pull-right">
            <i style="color:#009900;" class="fa fa-toggle-on fa-lg" ng-if="communitySelected == true" ng-click="changeCommunitySelection();"></i>
            <i class="fa fa-toggle-off fa-lg" ng-if="communitySelected  == false" ng-click="changeCommunitySelection();"></i>
            Search community terms only
        </span>
        <div ng-show="total > 0">
            <span class="text-danger">Search Term: {{ displayLabel }}</span> &nbsp;&nbsp;
            <span class="highlight ">Result {{ from + 1 }} through {{ to }} of {{ total }}.</span>

            <br>
            <ul uib-pagination
                ng-change="pageChanged()"
                total-items="pageTotal"
                ng-model="currentPage"
                max-size="5"
                num-pages="numPages"
                items-per-page="100"
                rotate="true"
                boundary-link-numbers="true"
                boundary-links="true"
                direction-links="true"
                force-ellipses="true"
                class="pagination-sm center"
                >
            </ul>
<!--             from:{{ from }} num-pages:{{ numPages }} -->
        </div>
        <br>

        <ul class="list-group">

            <li class="list-group-item" ng-repeat="m in matches" >
                <a href="/<?php echo $community->portalName?>/interlex/view/{{ m._id }}?searchTerm={{displayLabel}}"><strong>{{ m._source.label | parseHtml}}</strong>
                <i class="fa fa-external-link" aria-hidden="true"></i></a>
                <br>
                <strong>Preferred Id:</strong> {{ m.preferredId }}&nbsp;&nbsp;&nbsp;
                <strong>Score: </strong>{{ m._score }} &nbsp;&nbsp;&nbsp;
                <strong>Type: </strong> {{ m._source.type }}
                <p dd-text-collapse dd-text-collapse-max-length="100" dd-text-collapse-text="{{ m._source.definition }}"></p>
                <strong>Matches:</strong> <span ng-repeat="(key, data) in m.highlight">{{splitClass(key)}}{{$last ? '' : ', '}}</span>
            </li>

        </ul>

        </div>



    </div>
</div>
</div>
