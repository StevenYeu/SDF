<div class="tab-pane fade" id="relationships">
<div class="col-md-12" style="background: #f8f8f8;padding: 15px 15px;border-bottom: 1px solid #dedede;">

    <div class="col-md-12" style="padding-bottom:20px;">
        <strong>OWL Equivalent:</strong> owl:ObjectProperty
        <span class="pull-right">
            <a target="_blank" href="/<?php echo $community->portalName?>/interlex/create-relationship?id={{term.id}}" class="text-success">
                <i class="fa fa-plus" aria-hidden="true"></i> <strong>Add new relationship for this term</strong>
            </a>
            <span ng-show="childCount > 0"><br><strong>To view subClass, click "Children" tab.</strong></span>
        </span>
    </div>

<!--     <div class="col-md-12" style="height: 250px;overflow: scroll;">  -->
    <div class="col-md-12">

        <div ng-show="term.allRelationships.length>0">
            <a ng-show="term.relationshipDisplay=='short'" href="#" ng-click="toggleRelationships('long')">
            <i class="fa fa-eye" aria-hidden="true"></i> Show withdrawn relationships</a>
            <a ng-show="term.relationshipDisplay=='long'" href="#" ng-click="toggleRelationships('short')">
            <i class="fa fa-eye-slash" aria-hidden="true"></i> Hide withdrawn relationships</a>
        </div>

        <table class="table table-striped table-condensed" style="width:100%" ng-show="term.relationships.length > 0">
            <tr>
                <th style="width:2%">Edit</th>
                <th style="width:3%">Withdrawn</th>
                <th style="width:30%;">
                    <a href="javascript:void(0)" class="hidden-default-toggle-1">Term 1</a>&nbsp;&nbsp;
                    <a href="javascript:void(0)"  style="color:#408dc9" ng-click="sortFilteredRelationships(filtered_relationships, 'up', 0)" title="Sort Ascending"><i class="fa fa-sort-asc"></i></a>&nbsp;&nbsp;
                    <a href="javascript:void(0)"  style="color:#408dc9" ng-click="sortFilteredRelationships(filtered_relationships, 'down', 0)" title="Sort Descending"><i class="fa fa-sort-desc"></i></a>
                    <div class="hidden-default-1">
                        <input type="text" ng-model="input1">
                        <button ng-click="getFilteredRelationships(term.relationships, input1, 0)">search</button>
                    </div>
                </th>
                <th style="width:30%;">
                    <a href="javascript:void(0)" class="hidden-default-toggle-2">Relationship type</a>&nbsp;&nbsp;
                    <a href="javascript:void(0)"  style="color:#408dc9" ng-click="sortFilteredRelationships(filtered_relationships, 'up', 1)" title="Sort Ascending"><i class="fa fa-sort-asc"></i></a>&nbsp;&nbsp;
                    <a href="javascript:void(0)"  style="color:#408dc9" ng-click="sortFilteredRelationships(filtered_relationships, 'down', 1)" title="Sort Descending"><i class="fa fa-sort-desc"></i></a>
                    <div class="hidden-default-2">
                        <input type="text" ng-model="input2">
                        <button ng-click="getFilteredRelationships(term.relationships, input2, 1)">search</button>
                    </div>
                </th>
                <th style="width:25%;">
                    <a href="javascript:void(0)" class="hidden-default-toggle-3">Term 2</a>&nbsp;&nbsp;
                    <a href="javascript:void(0)"  style="color:#408dc9" ng-click="sortFilteredRelationships(filtered_relationships, 'up', 2)" title="Sort Ascending"><i class="fa fa-sort-asc"></i></a>&nbsp;&nbsp;
                    <a href="javascript:void(0)"  style="color:#408dc9" ng-click="sortFilteredRelationships(filtered_relationships, 'down', 2)" title="Sort Descending"><i class="fa fa-sort-desc"></i></a>
                    <!-- <a href=""  style="color:black" ng-click="sortFilteredRelationships(filtered_relationships, 'up', 2)" title="Sort Ascending"><i class="fa fa-caret-up"></i></a>&nbsp;&nbsp;
                    <a href=""  style="color:black" ng-click="sortFilteredRelationships(filtered_relationships, 'down', 2)" title="Sort Descending"><i class="fa fa-caret-down"></i></a> -->
                    <div class="hidden-default-3">
                        <input type="text" ng-model="input3">
                        <button ng-click="getFilteredRelationships(term.relationships, input3, 2)">search</button>
                    </div>
                </th>
                <th style="width:10%;">Votes</th>
            </tr>
            <tr ng-repeat="r in term.relationships">
                <td style="">
                    <span ng-show="r.id > 0">
                        <a target="_blank" href="/<?php echo $community->portalName?>/interlex/edit-relationship/{{ r.id }}">
                        <i class="glyphicon glyphicon-pencil" data-toggle="popover" data-content="Edit this relationship"></i></a>
                    </span>
                </td>

                <td style="">
                    <i ng-show="r.withdrawn == 1" class="fa fa-check" data-toggle="popover" data-content="This relationship is withdrawn"></i>
                </td>

                <td>
                    <span ng-show="r.term1_id != term.id">
                        <a data-toggle="popover" data-content="{{r.term1_definition}}" title="{{r.term1_curie}}" target="_blank" href="/<?php echo $community->portalName?>/interlex/view/{{ r.term1_ilx }}">{{r.term1_label}}</a>
                    </span>
                    <span data-toggle="popover" data-content="This term<br>({{term.curie}})" class="" ng-show="r.term1_id == term.id">
                        <i class="fa fa-info-circle" aria-hidden="true" style="color:#31708f"></i> {{term.label}}
                    </span>
                </td>

                <td>
                    <span ng-show="r.relationship_tid != term.id">
                        <span ng-show="r.relationship_tid > 0">
                            <a data-toggle="popover" data-content="{{r.relationship_term_definition}}" title="{{r.relationship_term_curie}}" target="_blank"
                            href="/<?php echo $community->portalName?>/interlex/view/{{ r.relationship_term_ilx }}">{{r.relationship_term_label}}</a>
                        </span>
                        <span ng-show="r.relationship_tid == 0">
                            {{r.relationship_term_label}}
                        </span>
                    </span>
                    <span data-toggle="popover" data-content="This term<br>({{term.curie}})" class="" ng-show="r.relationship_tid == term.id">
                        <i class="fa fa-info-circle" aria-hidden="true" style="color:#31708f"></i> {{term.label}}
                    </span>
                </td>

                <td>
                    <span ng-show="r.term2_id != term.id">
                        <a data-toggle="popover" data-content="{{r.term2_definition}}" title="{{r.term2_curie}}" target="_blank" href="/<?php echo $community->portalName?>/interlex/view/{{ r.term2_ilx }}">{{r.term2_label}}</a>
                    </span>
                    <span data-toggle="popover" data-content="This term<br>({{term.curie}})" class="" ng-show="r.term2_id == term.id">
                        <i class="fa fa-info-circle" aria-hidden="true" style="color:#31708f"></i> {{term.label}}
                    </span>
                </td>
                <td>
                    <span ng-show="r.relationship_tid > 0">
                    <?php if(isset($_SESSION['user'])): ?>
        <!-- <span class="fa-stack fa-3x">
           <i class="fa fa-thumbs-o-up fa-stack-1x"></i>
          <span class="fa-stack-1x fa-text" style="">{{ r.upvote }}</span>
          </span> -->
                        + {{ r.upvote }}
                        <i class="fa fa-thumbs-o-up" aria-hidden="true" style="color:#009900;" data-toggle="popover" data-content="Upvote" ng-click="vote('upvote', 'term_relationships', r)"></i>
                        &nbsp;
                        <i class="fa fa-thumbs-o-down" aria-hidden="true" style="color:#990000;" data-toggle="popover" data-content="Downvote" ng-click="vote('downvote', 'term_relationships', r)"></i>
                        - {{ r.downvote }}
                    <?php else: ?>
                        + {{ r.upvote }}
                        <i class="fa fa-thumbs-o-up" aria-hidden="true" style="color:#009900;" data-toggle="popover" data-content="Upvote"></i>
                        &nbsp;
                        <i class="fa fa-thumbs-o-down" aria-hidden="true" style="color:#990000;" data-toggle="popover" data-content="Downvote"></i>
                        - {{ r.downvote }}
                    <?php endif ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

</div>
</div>

<style>
    .hidden-default-1 {
        display: none;
    }
    .hidden-default-2 {
        display: none;
    }
    .hidden-default-3 {
        display: none;
    }
</style>

<script>
$(function() {
    $(".hidden-default-toggle-1").click(function() {
        $(".hidden-default-1").toggle("slide", { direction: "left" }, 0);
        $(".hidden-default-2").hide("slide", { direction: "left" }, 0);
        $(".hidden-default-3").hide("slide", { direction: "left" }, 0);
    });

    $(".hidden-default-toggle-2").click(function() {
        $(".hidden-default-2").toggle("slide", { direction: "left" }, 0);
        $(".hidden-default-1").hide("slide", { direction: "left" }, 0);
        $(".hidden-default-3").hide("slide", { direction: "left" }, 0);
    });

    $(".hidden-default-toggle-3").click(function() {
        $(".hidden-default-3").toggle("slide", { direction: "left" }, 0);
        $(".hidden-default-1").hide("slide", { direction: "left" }, 0);
        $(".hidden-default-2").hide("slide", { direction: "left" }, 0);
    })
});
</script>
