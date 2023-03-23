<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><h3 class="panel-title md-title">Term Information</h3></div>

    <form class="term-form sky-form">
    <fieldset ng-show="term.id > 0">
        <span style="text-decoration: underline;">Term:</span> <br><br>
        <a href="/<?php echo $community->portalName?>/interlex/view/{{ term.ilx }}" target="_blank">
        <strong>{{ term.label }}</strong> <i class="fa fa-external-link" aria-hidden="true"></i></a>
        <br>{{curiefyIlx(term.ilx)}}&nbsp;&nbsp;&nbsp;Type: {{term.type}}
        &nbsp;&nbsp;&nbsp;Version: {{term.version}}
        <p dd-text-collapse dd-text-collapse-max-length="100" dd-text-collapse-text="{{ term.definition }}"></p>
    </fieldset>

    <fieldset ng-show="annotation.id > 0">
        <span style="text-decoration: underline;">Annotation term:</span> <br><br>
        <a href="/<?php echo $community->portalName?>/interlex/view/{{ annotation.ilx }}" target="_blank">
        <strong>{{ annotation.label }}</strong> <i class="fa fa-external-link" aria-hidden="true"></i></a>
        <br>{{curiefyIlx(annotation.ilx)}}&nbsp;&nbsp;&nbsp;Type: {{annotation.type}}
        &nbsp;&nbsp;&nbsp;Version: {{annotation.version}}
        <p dd-text-collapse dd-text-collapse-max-length="100" dd-text-collapse-text="{{ annotation.definition }}"></p>
    </fieldset>
    </form>
</div>
