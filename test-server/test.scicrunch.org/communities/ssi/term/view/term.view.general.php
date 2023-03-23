<div class="tab-pane fade in active" id="general">
<div class="col-md-12" style="background: #f8f8f8;padding: 15px 15px;border-bottom: 1px solid #dedede;">

    <div class="col-md-12" style="padding:15px 0px;">

    <div style="" class="col-md-2">
        <strong>Superclasses:</strong><br>
        <a ng-show="term.parentListDisplay=='short' && term.parents.length>0" href="#" ng-click="toggleParentList('long')">
            <i class="fa fa-expand" aria-hidden="true"></i> Expand list</a>
        <a ng-show="term.parentListDisplay=='long' && term.parents.length>0" href="#" ng-click="toggleParentList('short')">
            <i class="fa fa-compress"></i> Collapse list</a>
        <div style="height: 250px;overflow: scroll;">
        <table ng-show="term.allParents.length>0" class="table table-striped table-condensed" style="word-warp:break-word;width:98%;">
            <tr>
                <td>
                {{ term.label }}
                <br>
                <i class="fa fa-arrow-down" aria-hidden="true"></i>
                </td>
            </tr>
            <tr ng-repeat="p in term.parents track by $index">
                <td data-toggle="popover" data-content="{{p.parent_definition}}" title="{{curiefyIlx(p.parent_ilx)}}">
                <a ng-href="/<?php echo $community->portalName?>/interlex/view/{{ p.parent_ilx }}" target="_blank">{{ p.parent_label }}</a>
                <br>
                <span ng-show="$index !=term.parents.length-1"><i class="fa fa-arrow-down" aria-hidden="true"></i></span>
                </td>
            </tr>
        </table>
        </div>
    </div>

    <div style="" class="col-md-5">
        <strong>Synonyms:</strong>
        <div style="height: 250px;overflow: scroll;">
        <table class="table table-striped table-condensed" style="width:98%">
            <tr><th>Synonym</th><th style="width:20%;">Type</th></tr>
            <tr ng-repeat="s in term.synonyms">
                <td>{{ s.literal }}</td>
                <td>{{ s.type }}</td>
            </tr>
        </table>
        </div>
    </div>
    <div style="" class="col-md-5">
        <strong>Existing Ids:</strong>
        <div style="height: 250px;overflow: scroll;">
        <table class="table table-striped table-condensed" style="word-warp:break-word;width:98%;">
            <tr><th style="width:10%">Preferred</th>
            <th style="width:30%;">CURIE</th>
            <th>IRI</th></tr>
            <tr ng-repeat="s in term.existing_ids">
                <td><span ng-show="s.preferred=='1'"><i class="fa fa-check" aria-hidden="true"></i></span></td>
                <td><span>{{ s.curie }}</span></td>
                <td><a href="{{ s.iri }}" target="_blank"><span>{{ s.iri | replace: '/base/':'/' }}</span></a></td>
            </tr>
        </table>
        </div>
    </div>

    </div>
</div>
</div>
