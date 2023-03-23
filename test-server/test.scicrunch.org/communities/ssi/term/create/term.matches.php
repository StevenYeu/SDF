<div ng-show="error === true">
<pre style="font-family:Verdana, Geneva, sans-serif" class="alert alert-danger" >{{ feedback }}</pre>
</div>

<div class="panel-success col-nopad" >
    <div class="panel-heading"><span class="panel-title">Potential Matches</span>
    <span class="form-group pull-right">
        <label class="checkbox-inline" ng-class="{submitted:termForm.$submitted && (!no_match || no_match == undefined)}">
        <input type="checkbox" style="" name="no_match" ng-model-options="{allowInvalid: false}" ng-model="no_match" required> No match!
        </label>
    </span>
    </div>

    <ul class="list-group">

        <li class="list-group-item" ng-repeat="m in matches">
            <div ng-show="{{m._id.length > 0}}">
                <a href="/<?php echo $community->portalName?>/interlex/view/{{ m._source.ilx }}" target="_blank"><strong>{{ m._source.label | parseHtml }}</strong> <i class="fa fa-external-link" aria-hidden="true"></i></a>
                {{m._source.id}}
                <br>
                <p dd-text-collapse dd-text-collapse-max-length="100" dd-text-collapse-text="{{ m._source.definition }}"></p>
            </div>
        </li>

    </ul>

</div>
