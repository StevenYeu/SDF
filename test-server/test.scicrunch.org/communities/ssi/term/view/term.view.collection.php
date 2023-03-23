<div ng-show="term.type=='TermSet'" class="tab-pane fade" id="collection">
    <div class="col-md-12" style="background: #f8f8f8;padding: 15px 15px;border-bottom: 1px solid #dedede;" >
        <div class="col-md-12" style="height: 500px;overflow: scroll;">
              <treecontrol class="tree-light"
                tree-model="collectionTreeModel"
                on-node-toggle="fetchCollectionNodes(node, expanded)"
                options="collectionTreeOptions">
                {{node.label}} ({{node.preferred_id}})
                <span ng-show="node.type == 'TermSet'">[{{node.children_count}}]</span>
                <span title="{{node.definition}}"><i class="fa fa-info-circle"></i></span>
                <a target="_blank" ng-href="/<?php echo $community->portalName?>/interlex/view/{{node.ilx}}"><i class='fa fa-external-link'></i></a>
              </treecontrol>
        </div>
    </div>
</div>
