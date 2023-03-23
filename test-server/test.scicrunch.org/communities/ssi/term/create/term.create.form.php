
<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><h3 class="panel-title md-title">Submit a new term</h3></div>

    <div>
    <form name="termForm" id="termForm" class="term-form sky-form" role="form" novalidate>
        <input id="cid" type="hidden" name="cid" value="<?= $community->id ?>" >
        <input id="uid" type="hidden" name="uid" value="<?= $_SESSION['user']->id ?>">
        <?php
        if (isset($_GET['referer'])) {
            echo '<input id="referer" type="hidden" name="referer" value="' . $_SERVER['HTTP_REFERER'] . '">';
        }
        if (isset($_GET['ttype'])) {
            echo '<input id="ttype" type="hidden" name="ttype" value="' . $_GET['ttype'] . '">';
        }
        if (isset($_GET['label']) && $_GET['label'] != "") {
            echo '<input id="label" type="hidden" name="label" value="' . $_GET['label'] . '">';
        } else {
            echo '<input id="label" type="hidden" name="label" value="">';
        }
        ?>

    <fieldset>
        <label style="padding-top:15px;" type="label"><span class="text-danger">*</span> Label</label>
        <label class="input">
            <input type="text" ng-class="{submitted:termForm.$submitted}" name="label" ng-model="label" ng-blur="matchTerm()" enter-directive required>
        </label>

        <label style="padding-top:15px;" type="label">Ilx Id</label>
        <label class="input">
            <input type="text" name="ilx" ng-model="ilx" placeholder="Will be generated automatically" disabled >
        </label>

        <label style="padding-top:15px;" type="label"><span class="text-danger">*</span> Type</label>
        <label class="input">
            <select name="type" ng-options="tt for tt in term_types" ng-model="type">
                <option value="{{tt}}">{{tt}}</option>
            </select>
            <span ng-show="type == 'annotation'">
            &nbsp;&nbsp;&nbsp;Annotation value type:
            <select  name="annotation_type" ng-options="at for at in annotation_types" ng-model="annotation_type">
                <option value="{{at}}">{{at}}</option>
            </select>
            </span>
        </label>

        <div ng-hide="hide_community">
        <label style="padding-top:15px;" type="label"><span class="text-danger">*</span> Community</label>
        <label class="input" ng-hide="hide_community">
            <select ng-model="community_id">
                <option value="">--Select Community--</option>
                <option ng-repeat="community in availableCommunities" ng-value="{{community.cid}}">{{community.portalName}}</option>
            </select>
        </label>
        </div>

        <label style="padding-top:15px;" type="label">isDefinedBy</label>
        <span style="padding-top:15px;float:right;text-align:right;font-style:italic"><a href="javascript:void(0)"  ng-click="openOntologyAddModal()">[Add new ontology entry]</a></span>
        <chips ng-model="formData.ontologies" render enter-directive>
            <chip-tmpl>
                <div class="default-chip">
                    {{chip.url}}
                    <span class="glyphicon glyphicon-remove" remove-chip></span>
                </div>
            </chip-tmpl>
            <label>
                <input ng-model-control ng-model="lastOntology"
                       uib-typeahead="ont as ont.url for ont in availableOntologies | filter:$viewValue"/>
            </label>
        </chips>
<!--         <chips defer ng-model="formData.ontologies" render="addOntology(data)" enter-directive> -->
<!--             <chip-tmpl> -->
<!--                 <div class="default-chip"> -->
<!--                     {{chip.isLoading ? chip.defer : chip.defer.url}} -->
<!--                     <span class="glyphicon glyphicon-remove" remove-chip="removeOntology(data)"></span> -->
<!--                     <div class="loader-container" ng-show="chip.isLoading"> -->
<!--                         <i class="fa fa-spinner fa-spin fa-lg loader"></i> -->
<!--                     </div> -->
<!--                 </div> -->
<!--             </chip-tmpl> -->
<!--             <input ng-model-control ng-model="lastOntology" uib-typeahead="ont as ont.url for ont in availableOntologies | filter:$viewValue"></input> -->
<!--         </chips>         -->


        <label style="padding-top:15px;" type="label">Definition</label>
        <label class="input">
            <textarea style="width:100%" name="definition" ng-model="definition" placeholder="" enter-directive></textarea>
        </label>

        <label style="padding-top:15px;" type="label"
            data-toggle="popover" title="How to add a synonym?"
            data-content="1. Type your synonym and press enter. <br>2. In the popup select whether it is 'abbreviation' or not.">
            Synonym(s) <span class="text-primary">[Details]</span></label>
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
            <input chip-control/>
        </chips>


        <label style="padding-top:15px;" type="label"
            data-toggle="popover" title="How to add an ID?"
            data-content="1. Type your ID (e.g. GO:123) and press enter. <br>2. In the popup select whether it is 'preferred' or not.">
            Existing Id(s) <span class="text-primary">[Details]</span><sub> e.g. GO:123 or ILX:234</sub></label>
        <span style="padding-top:15px;float:right;text-align:right;font-style:italic">
        <a href="javascript:void(0)"  ng-click="openCurieCatalogAddModal()">[Add new curie catalog entry]</a></span>
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
            <input chip-control/>
        </chips>

        <label style="padding-top:15px;" type="label" data-toggle="popover" title="How to add a Superclass?"
            data-content="1. Type the parent entities name. <br>2. Select parent entity from the list.">
            <span class="text-danger">*</span> Superclass <span class="text-primary">[Details]</span></label>
        <label class="input">
            <input enter-directive
                type="text"
                ng-model="formData.superclass"
                placeholder="Type for superclass list & click to select"
                uib-typeahead="sup as sup.label for sup in updateSuperclassList($viewValue)"
                typeahead-min-length="2"
                typeahead-wait-ms="0"
                typeahead-select-on-blur="true"
            />
        </label>

        <label style="padding-top:15px;" type="label">Comment <sub>e.g. SPARC: important note about term </sub></label>
        <label class="input">
            <textarea style="width:100%;" name="comment" ng-model="comment" placeholder="  Community notes denoted [Community: Notes]" enter-directive></textarea>
        </label>
    </fieldset>

    <footer>
        <button type="submit" class="btn-u btn-u-default" ng-click="addTerm($event)">Submit</button>
        <input type="reset" class="btn-u btn-u-default" ng-click="resetForm()"/>
    </footer>


    </form>

<!--     Synonyms:    {{ formData.synonyms }}<br> -->
<!--     Existing Ids: {{ formData.existing_ids }}<br> -->
<!--     Superclass: {{ formData.superclass }}<br> -->
<!--     Superclasses: {{ formData.superclasses }}<br> -->
<!--     Ontologies: {{ formData.ontologies }}<br> -->
    </div>
</div>
