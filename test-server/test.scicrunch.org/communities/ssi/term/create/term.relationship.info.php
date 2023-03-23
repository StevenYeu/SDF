<div ng-show="error === true">
<pre style="font-family:Verdana, Geneva, sans-serif" class="alert alert-danger" >{{ feedback }}</pre>
</div>

<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><h3 class="panel-title md-title">Term Information</h3></div>

    <form class="term-form sky-form">
    <fieldset ng-show="term1.id > 0">
        <span style="text-decoration: underline;">First term:</span> <br><br>
        <a href="/<?php echo $community->portalName?>/interlex/view/{{ term1.ilx }}" target="_blank">
        <strong>{{ term1.label }}</strong> <i class="fa fa-external-link" aria-hidden="true"></i></a>
        <br>{{curiefyIlx(term1.ilx)}}&nbsp;&nbsp;&nbsp;Type: {{term1.type}}
        &nbsp;&nbsp;&nbsp;Version: {{term1.version}}
        <p dd-text-collapse dd-text-collapse-max-length="100" dd-text-collapse-text="{{ term1.definition }}"></p>
    </fieldset>

    <fieldset ng-show="term2.id > 0">
        <span style="text-decoration: underline;">Second term:</span> <br><br>
        <a href="/<?php echo $community->portalName?>/interlex/view/{{ term2.ilx }}" target="_blank">
        <strong>{{ term2.label }}</strong> <i class="fa fa-external-link" aria-hidden="true"></i></a>
        <br>{{curiefyIlx(term2.ilx)}}&nbsp;&nbsp;&nbsp;Type: {{term2.type}}
        &nbsp;&nbsp;&nbsp;Version: {{term2.version}}
        <p dd-text-collapse dd-text-collapse-max-length="100" dd-text-collapse-text="{{ term2.definition }}"></p>
    </fieldset>

    <fieldset ng-show="relationship.id > 0">
        <span style="text-decoration: underline;">Relationship term:</span> <br><br>
        <a href="/<?php echo $community->portalName?>/interlex/view/{{ relationship.ilx }}" target="_blank">
        <strong>{{ relationship.label }}</strong> <i class="fa fa-external-link" aria-hidden="true"></i></a>
        <br>{{curiefyIlx(relationship.ilx)}}&nbsp;&nbsp;&nbsp;Type: {{relationship.type}}
        &nbsp;&nbsp;&nbsp;Version: {{relationship.version}}
        <p dd-text-collapse dd-text-collapse-max-length="100" dd-text-collapse-text="{{ relationship.definition }}"></p>
    </fieldset>
    </form>
</div>
