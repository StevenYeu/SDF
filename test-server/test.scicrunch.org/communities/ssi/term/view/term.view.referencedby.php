<div class="tab-pane fade" id="referencedby">

<div class="col-md-12" style="background: #f8f8f8;padding: 15px 15px;border-bottom: 1px solid #dedede;">
    <div class="col-md-12" style="height: 250px;overflow: scroll;" ng-show="term.mappings.length == 0">
        <strong>Curation status: </strong>{{selectedOption}}
        <select ng-model="curation_status" ng-options="o  for o in statusOptions" ng-change="chooseCurationStatus(curation_status)">
        </select>
        <p >
            <strong>Total number of mappings for "{{term.label}}": 0</strong>
        </p>
    </div>

    <div class="col-md-12" ng-show="term.mappings.length > 0">
    <div class="panel panel-success" ng-repeat="mapping in term.mappings track by $index">

     <p style="margin-bottom:0;background-color:#d9edf7;border-color:#bce8f1;padding:10px 0 15px 15px;" ng-show="$index == 0">
     <strong>Concept: {{ mapping.concept }}</strong>  --
     <a target="_blank" href="{{ mapping.iri }}">
        {{ mapping.concept_id}}
        <i class="fa fa-external-link" aria-hidden="true"></i>
      </a>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <strong>Curation status: </strong>
      <select ng-model="curation_status" ng-options="o  for o in statusOptions" ng-change="chooseCurationStatus(curation_status)">
      </select>
      <span class="pull-right" style="padding-right:5px;">
      <span ng-show="term.mappings.showPrev==true">
      <button type="button" class="btn btn-info btn-xs" ng-click="getMappings(term.id, term.mappings.size, term.mappings.prevFrom, curation_status)">&lt; Previous</button> &nbsp;&nbsp;&nbsp;
      </span>
      <span>Showing {{ term.mappings.from + 1 }} through {{ term.mappings.from + term.mappings.length }} of {{ term.mappings.count }} mappings</span> &nbsp;&nbsp;&nbsp;
      <span ng-show="term.mappings.showNext==true">
      <button type="button" class="btn btn-info btn-xs" ng-click="getMappings(term.id, term.mappings.size, term.mappings.nextFrom, curation_status)">Next &gt;</button>
      </span>
      </span>
      </p>


        <div class="panel-heading">
            <span ng-show="term.mappings.length > 1">{{ term.mappings.nextFrom - term.mappings.size + $index + 1 }}.</span>
            <a ng-href="/resolver/{{ mapping.source_id }}" target="_blank">
            <i class="fa fa-files-o" aria-hidden="true"></i> {{ mapping.source }}
            <i class="fa fa-external-link" aria-hidden="true"></i></a>
            <br>
            <!-- <a href="/scicrunch/data/source/{{ mapping.view_id }}/search?q=*&filter[]={{ mapping.column_name }}" target="_blank">
            <?= $host ?>/scicrunch/data/source/{{ mapping.view_id }}/search?q=*&filter[]={{ mapping.column_name }}
            <i class="fa fa-external-link" aria-hidden="true"></i></a>
            <br> -->
            {{mapping.source}}, {{mapping.view_name}}, {{mapping.column_name}}, {{mapping.value}}
        </div>

        <div class="panel-body" style="padding-right:5px;">
            <div class="col-md-6">
                <strong>Method:</strong> {{ mapping.method }} &nbsp;&nbsp;
                <strong>Is whole?</strong> {{ mapping.is_whole }} &nbsp;&nbsp;
                <strong>Is ambiguous?</strong> {{ mapping.is_ambiguous }}<br>
                <strong>View:</strong> {{ mapping.view_name }} &nbsp;&nbsp;
                <strong>Column:</strong> {{ mapping.column_name }}
                <strong>Relation:</strong> {{ mapping.relation ? mapping.relation.length > 0 : 'N/A' }}<br>
                <strong>Matched value:</strong> {{ mapping.matched_value }}
                <p dd-text-collapse dd-text-collapse-max-length="80" dd-text-collapse-text="<strong>Value:</strong> {{ mapping.value }}"></p>
                <p dd-text-collapse dd-text-collapse-max-length="80" dd-text-collapse-text="<strong>Snippet:</strong> {{ mapping.snippet }}"></p>
            </div>
            <div class="col-md-6">
                <strong>Curation status:</strong> {{ mapping.curation_status }}<br>
                <strong>Curation history:</strong>
                <span class="pull-right" style="padding-right:30px"><a class="btn btn-success" target="_blank" href="/<?php echo $community->portalName?>/interlex/curate-mapping/tmid={{mapping.id}}">Curate this value</a></span><br>

                <div ng-repeat="ml in mapping.curation_logs" >
                    ------------------------<br>
                    <strong>Date:</strong> {{ml.time * 1000 | date:'MM/dd/yyyy'}} &nbsp;&nbsp;
                    <strong>Status:</strong> {{ ml.curation_status }} &nbsp;&nbsp;
                    <strong>User:</strong>
                    <?php if ($_SESSION["user"]): ?>
                    {{ ml.curator }}
                    <?php endif; ?>
                     &nbsp;&nbsp;
                    <strong>Notes:</strong> {{ ml.notes }}
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
</div>
