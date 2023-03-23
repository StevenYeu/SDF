<?php
$tid = array_pop(explode("/", $_SERVER[REQUEST_URI]));
?>

<div class="panel col-nopad panel-success">
    <div class="panel-heading"><span class="panel-title">Edit Term</span>
        <span class="pull-right" style="padding-right:10px">
        <a href="/<?php echo $community->portalName?>/interlex/view/{{ formData.ilx }}" target="_blank">View details
            <i class="fa fa-external-link" aria-hidden="true"></i>
        </a>
        &nbsp;&nbsp;&nbsp;
        <a target="_blank" href="/<?php echo $community->portalName?>/interlex/create-relationship?id=<?php echo $tid ?>">Add new relationship
            <i class="fa fa-plus" aria-hidden="true"></i>
        </a>
        </span>
    </div>

    <div>
    <form id="termForm" class="term-form sky-form" role="form" novalidate>
		<input id="cid" type="hidden" name="cid" value="<?= $community->id ?>">
		<input id="tid" ng-model="tid" type="hidden" name="tid" ng-value="<?= $tid ?>" value="<?= $tid ?>">

		<fieldset>

		<?php if ($_SESSION['user']->role > 1): ?>
		<div>
		    <label>Status:</label>&nbsp;&nbsp;&nbsp;
		    <label>
		        <input type="radio" ng-model="formData.status" value="0"> Active (0)</input>&nbsp;&nbsp;&nbsp;
		        <input type="radio" ng-model="formData.status" value="-1"> Inactive (-1)</input>&nbsp;&nbsp;&nbsp;
		        <input type="radio" ng-model="formData.status" value="-2"> Delete (-2)</input>&nbsp;&nbsp;&nbsp;
		    </label>
		</div>
		<br>
		<div>
		    <label>Display Superclass:</label>&nbsp;&nbsp;&nbsp;
		    <label>
		        <input type="radio" ng-model="formData.display_superclass" value="1"> Yes (1)</input>&nbsp;&nbsp;&nbsp;
		        <input type="radio" ng-model="formData.display_superclass" value="0"> No (0)</input>&nbsp;&nbsp;&nbsp;
		    </label>
		</div>
		<br>
		<?php else: ?>
		<div>
		<label>Status:</label><label>&nbsp;&nbsp;&nbsp; {{formData.status}} </label>
		<br>
		0: active, -1: inactive, -2: deleted<br>
		Note: You should have a role of above curator to edit status.
		</div>
		<br>
		<div>
		<label>Display Superclass:</label><label>&nbsp;&nbsp;&nbsp; {{formData.display_superclass}} </label>
		<br>
		1: yes, 0: no<br>
		Note: You should have a role of above curator to edit display superclass.
		</div>
		<br>
		<?php endif; ?>
			<label type="label">Label <span style="color: #bb0000">*</span></label>
			<label class="input pad-bottom">
			    <input type="text" name="label" ng-model="formData.label" ng-class="{submitted:submitted===true}" enter-directive required >
			</label>

			<label type="label">Ilx </label> <label class="input">
			    <input type="text" name="ilx" ng-model="formData.ilx" disabled>
			</label>

    		<label type="label">Type</label>
            <label class="input">
                <select name="type" ng-options="tt for tt in term_types" ng-model="formData.type" disabled>
                    <option value="{{tt}}">{{tt}}</option>
                </select>
                <span ng-show="formData.type == 'annotation'">
                &nbsp;&nbsp;&nbsp;Annotation value type:
                <select  name="annotation_type" ng-options="at for at in annotation_types track by at" ng-model="formData.annotation_type">
                    <option value="{{at}}" ng-selected="formData.annotation_type == at">{{at}}</option>
                </select>
                </span>
            </label>

    		<label type="label">isDefinedBy</label>
    		<span style="float:right;text-align:right;font-style:italic"><a href="javascript:void(0)"  ng-click="openOntologyAddModal()">[Add new ontology entry]</a></span>
            <chips ng-model="formData.ontologies" render enter-directive>
                <chip-tmpl>
                    <div class="default-chip">
                        {{chip.url}}
                        <span class="glyphicon glyphicon-remove" remove-chip></span>
                    </div>
                </chip-tmpl>
                <input ng-model-control ng-model="lastOntology" uib-typeahead="ont as ont.url for ont in availableOntologies | filter:$viewValue"></input>
            </chips>

            <label type="label">Definition</label>
            <label class="input">
                <textarea style="width:100%" rows="3" name="definition" ng-model="formData.definition" enter-directive placeholder="" ></textarea>
            </label>

            <label type="label"
                data-toggle="popover" title="How to add a synonym?"
                data-content="1. Type your synonym and press enter. 2. In the popup select whether it is 'abbreviation' or not.">
                Synonym(s) </label>
            <chips defer ng-model="formData.synonyms" render="addSynonym(data)" enter-directive>
            <chip-tmpl>
                <div class="default-chip">
                    {{chip.isLoading ? chip.defer : chip.defer.literal}}
                    <span ng-hide="chip.isLoading || chip.defer.type !== 'abbrev'">({{chip.defer.type}})</span>
                    <span class="glyphicon glyphicon-remove" remove-chip="removeSynonym(data)"></span>
                    <div class="loader-container" ng-show="chip.isLoading">
                        <i class="fa fa-spinner fa-spin fa-lg loader"></i>
                    </div>
                </div>
            </chip-tmpl>
            <input chip-control></input>
            </chips>

			<label type="label"
			    data-toggle="popover" title="How to add an ID?"
			    data-content="1. Type your ID (e.g. GO:123) and press enter. 2. In the popup select whether it is 'preferred' or not.">
			    Existing Ids </label>
			<span style="float:right;text-align:right;font-style:italic"><a href="javascript:void(0)"  ng-click="openCurieCatalogAddModal()">[Add new curie catalog entry]</a></span>
            <chips defer ng-model="formData.existing_ids" render="addEid(data)" enter-directive>
            <chip-tmpl>
                <div class="default-chip">
                    {{chip.isLoading ? chip.defer : chip.defer.curie}}
                    <span ng-hide="chip.isLoading || chip.defer.preferred != '1'">(preferred)</span>
                    <span class="glyphicon glyphicon-remove" remove-chip="removeEid(data)"></span>
                    <div class="loader-container" ng-show="chip.isLoading">
                        <i class="fa fa-spinner fa-spin fa-lg loader"></i>
                    </div>
                </div>
            </chip-tmpl>
            <input chip-control></input>
            </chips>

            <label type="label">Superclass</label>
<!--             <chips ng-model="formData.superclasses" enter-directive> -->
<!--                 <chip-tmpl> -->
<!--                     <div class="default-chip"> -->
<!--                         {{chip.label}} -->
<!--                         <span class="glyphicon glyphicon-remove" remove-chip></span> -->
<!--                     </div> -->
<!--                 </chip-tmpl> -->
<!--                 <input ng-model-control ng-model="lastSuperclass" uib-typeahead="sup as sup.label for sup in availableSuperclasses | filter:$viewValue"></input> -->
<!--             </chips> -->
            <label class="input">
                <input enter-directive
                    type="text"
                    ng-model="formData.superclass"
                    placeholder="select a superclass"
                    uib-typeahead="sup as sup.label for sup in updateSuperclassList($viewValue)"
                    typeahead-min-length="2"
                    typeahead-wait-ms="0"
                    typeahead-select-on-blur="true"
                />
            </label>

            <label type="label">Comment</label>
            <label class="input">
                <textarea style="width:100%" class="resource-field" name="comment" ng-model="formData.comment" placeholder="" enter-directive></textarea>
            </label>

    		<label type="label">Version: {{formData.version }} </label>
		</fieldset>


    <footer>
			<button type="submit" class="btn-u btn-u-default" ng-click="editTerm()">Submit</button>
      <a target='_self' href="/<?php echo $community->portalName?>/interlex/view/{{ formData.ilx }}?searchTerm=<?php echo $_GET['searchTerm'] ?>" class='btn-u btn-u-default'>Cancel</a>
			<button type="reset" class="btn-u btn-u-default">Reset</button>
		</footer>


	</form>

<!-- 	Synonyms:    {{ formData.synonyms }}<br> -->
<!--     Existing Ids: {{ formData.existing_ids }}<br> -->
<!--     Superclass: {{ formData.superclass }}<br> -->
<!--     Ontologies: {{ formData.ontologies }}<br> -->
	</div>

</div>
