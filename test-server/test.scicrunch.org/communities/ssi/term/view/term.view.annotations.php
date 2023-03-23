<div class="tab-pane fade" id="annotations">
<div class="col-md-12" style="background: #f8f8f8;padding: 15px 15px;border-bottom: 1px solid #dedede;">

    <div class="col-md-12" style="padding-bottom:20px;">
    <strong>OWL Equivalent:</strong>  owl:AnnotationProperty
    <span class="pull-right">
        <a target="_blank" href="/<?php echo $community->portalName?>/interlex/create-annotation?id={{term.id}}" class="text-success">
            <i class="fa fa-plus" aria-hidden="true"></i> <strong>Add new annotation for this term</strong>
        </a>
    </span>
    </div>

<!--     <div class="col-md-12" style="height: 250px;overflow: scroll;">  -->
    <div class="col-md-12">

    <div ng-show="term.allAnnotations.length>0">
        <a ng-show="term.annotationDisplay=='short'" href="#" ng-click="toggleAnnotations('long')">
        <i class="fa fa-eye" aria-hidden="true"></i> Show withdrawn annotations</a>
        <a ng-show="term.annotationDisplay=='long'" href="#" ng-click="toggleAnnotations('short')">
        <i class="fa fa-eye-slash" aria-hidden="true"></i> Hide withdrawn annotations</a>
    </div>

    <table class="table table-striped table-condensed" style="width:100%" ng-show="term.annotations.length > 0">
        <tr>
            <th style="width:2%">Edit</th>
            <th style="width:3%">Withdrawn</th>
            <th style="width:25%;">Term</th>
            <th style="width:15%;">Annotation type</th>
            <th style="width:45%;">Value</th>
            <th style="width:10%;">Votes</th>
        </tr>
        <tr ng-repeat="a in term.annotations">
            <td style="">
                <span ng-show="a.id > 0">
                    <a target="_blank" href="/<?php echo $community->portalName?>/interlex/edit-annotation/{{ a.id }}">
                    <i class="glyphicon glyphicon-pencil" data-toggle="popover" data-content="Edit this annotation"></i>
                    </a>
                </span>
            </td>
            <td style="">
                <i ng-show="a.withdrawn == 1" class="fa fa-check" data-toggle="popover" data-content="This annotation is withdrawn"></i>
            </td>


            <td>
            <span ng-show="a.tid != term.id">
                <a data-toggle="popover" title="{{a.term_curie}}" target="_blank" data-content="{{a.term_definition}}" href="/<?php echo $community->portalName?>/interlex/view/{{ a.term_ilx }}">{{a.term_label}}</a>
            </span>
            <span data-toggle="popover" data-content="This term<br>({{term.curie}})" class="" ng-show="a.tid == term.id">
                <i class="fa fa-info-circle" aria-hidden="true" style="color:#31708f"></i> {{term.label}}
            </span>
            </td>

            <td>
            <span ng-show="a.annotation_tid != term.id">
                <span ng-show="a.annotation_tid > 0">
                    <a data-toggle="popover" data-content="{{a.annotation_term_definition}}" title="{{a.annotation_term_curie}}" target="_blank"
                    href="/<?php echo $community->portalName?>/interlex/view/{{ a.annotation_term_ilx }}">{{a.annotation_term_label}}</a>
                </span>
                <span ng-show="a.annotation_tid == 0">
                    {{a.annotation_term_label}}
                </span>
            </span>
            <span ng-show="a.annotation_tid == term.id" data-toggle="popover" data-content="This term<br>({{term.curie}})" class="">
                <i class="fa fa-info-circle" aria-hidden="true" style="color:#31708f"></i> {{term.label}}
            </span>
            </td>

            <td>{{a.value}}</td>

            <td>
            <span ng-show="a.annotation_tid > 0">
            <?php if(isset($_SESSION['user'])): ?>
                + {{ a.upvote }}
                <i class="fa fa-thumbs-o-up" aria-hidden="true" style="color:#009900;" data-toggle="popover" data-content="Upvote" ng-click="vote('upvote', 'term_annotations', a)"></i>
                &nbsp;
                <i class="fa fa-thumbs-o-down" aria-hidden="true" style="color:#990000;" data-toggle="popover" data-content="Downvote" ng-click="vote('downvote', 'term_annotations', a)"></i>
                - {{ a.downvote }}
            <?php else: ?>
                + {{ a.upvote }}
                <i class="fa fa-thumbs-o-up" aria-hidden="true" style="color:#009900;" data-toggle="popover" data-content="Upvote"></i>
                &nbsp;
                <i class="fa fa-thumbs-o-down" aria-hidden="true" style="color:#990000;" data-toggle="popover" data-content="Downvote"></i>
                - {{ a.downvote }}
            <?php endif ?>
            </span>
            </td>
        </tr>
    </table>
    </div>

</div>
</div>
