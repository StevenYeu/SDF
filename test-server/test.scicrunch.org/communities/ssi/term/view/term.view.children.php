<div class="tab-pane fade" id="children">
<div class="col-md-12" style="background: #f8f8f8;padding: 15px 15px;border-bottom: 1px solid #dedede;" >

<div class="col-md-12" style="height: 250px;overflow: scroll;">
<span ng-show="term.type=='term'"><strong>OWL Equivalent:</strong>  owl:Class</span>
<span ng-show="term.type=='cde'"><strong>OWL Equivalent:</strong>  owl:Class</span>
<span ng-show="term.type=='relationship'"><strong>OWL Equivalent:</strong>  owl:ObjectProperty</span>
<span ng-show="term.type=='annotation'"><strong>OWL Equivalent:</strong>  owl:AnnotationProperty</span>
<span ng-show="term.type=='data'"><strong>OWL Equivalent:</strong>  owl:DataProperty</span>
<span ng-show="term.type=='token'"><strong>OWL Equivalent:</strong>  owl:NamedIndividual</span>
<br><br>
<strong>Total number of first generation children of "{{term.label}}":</strong> {{childCount}}
      <treecontrol class="tree-light"
        tree-model="treeModel"
        on-node-toggle="fetchChildNodes(node, expanded)"
        options="treeOptions">
        {{node.label}} [<a target="_blank" ng-href="/<?php echo $community->portalName?>/interlex/view/{{node.ilx}}">View</a>]
      </treecontrol>
</div>



</div>
</div>
