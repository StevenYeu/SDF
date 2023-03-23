<?php
$tid = array_pop(explode("/", $_SERVER[REQUEST_URI]));
?>
<div class="panel panel-success" >
    <div class="panel-heading"><span class="panel-title">Previous Versions</span></div>

    <div class="panel-body right-panel" ng-show="versions.length > 0">
        <label>Select Prior Version:
            <span  class="nullable">
            <select ng-model="selectedVersion" ng-options="v.version for v in versions" ng-change="changeVersion()">
                <option value="">-- choose prior version --</option>
            </select>
            </span>
        </label>

        <fieldset>
            <h5>{{versionInfo.label}}</h5>
            <p><strong>CURIE:</strong> {{curiefyIlx(versionInfo.ilx)}} &nbsp;&nbsp;
                <strong>Version:</strong> {{selectedVersion.version}} &nbsp;&nbsp;
                <strong>Type:</strong> {{versionInfo.type}} &nbsp;&nbsp;
                <strong>Time:</strong> {{selectedVersion.time | epochToDateTime | date:'yyyy-MM-dd HH:mm'}} &nbsp;&nbsp;
                <strong>Modified By:</strong> {{selectedVersion.modify_user }}
                <span ng-show="versionInfo.type == 'annotation'">
                    &nbsp;&nbsp;&nbsp;<strong>Annotation value type:</strong> {{ versionInfo.annotation_type }}
                </span>
            </p>
            <!-- <p><strong>Ontology URLs:</strong><br>
                <span ng-repeat="o in versionInfo.ontologies">
                    <a href="{{ o.url }}" target="_blank">{{ o.url }}</a> &nbsp;&nbsp;&nbsp;
                </span></p> -->
            <p ng-show="versionInfo.definition.length>0"><strong>Definition:</strong> {{versionInfo.definition}}</p>
            <p ng-show="versionInfo.comment.length>0"><strong>Comment:</strong> {{versionInfo.comment}}</p>
            <p>
                <strong>Status:</strong> {{versionInfo.status}} &nbsp;&nbsp;
                <strong>Display Superclass:</strong> {{versionInfo.display_superclass}}
            </p>

            <div class=""><strong>Synonyms</strong>
            <table class="table table-striped table-condensed">
                <tr><th>Synonym</th><th>Type</th></tr>
                <tr ng-repeat="s in versionInfo.synonyms">
                    <td>{{s.literal}}</td><td>{{s.type}}</td>
                </tr>
            </table>
            </div>
            <br>
            <div class=""><strong>Existing IDs</strong>
            <table class="table table-striped table-condensed">
                <tr><th style="width:10%">Preferred</th><th style="width:30%;">Curie</th><th>IRI</th></tr>
                <tr ng-repeat="x in versionInfo.existing_ids">
                    <td><span ng-show="x.preferred=='1'">&check;</span></td>
                    <td>{{ x.curie }}</td>
                    <td>{{ x.iri | replace:"/base/":"/" }}</td>
                </tr>
            </table>
            </div>
            <br>
            <div class=""><strong>Superclass</strong>
            <table class="table table-striped table-condensed">
                <tr><th>Label</th><th>ILX</th></tr>
                <tr ng-repeat="s in versionInfo.superclasses">
                    <td>{{s.label}}</td><td>{{curiefyIlx(s.ilx)}}</td>
                </tr>
            </table>
            </div>
        </fieldset>
    </div>
</div>
