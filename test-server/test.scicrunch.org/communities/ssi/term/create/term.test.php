<link rel="stylesheet" type="text/css" href="/css/ng-tags-input.min.css" />
<link rel="stylesheet" type="text/css" href="/css/angular-chips.css" />
<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/ng-tags-input.min.js"></script>
<script src="/js/module-error.js"></script>
<script src="/js/angular-chips/ui-bootstrap.js"></script>
<script src="/js/angular-chips/angular-chips.min.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-test.js"></script>

<?php
if (isset($_GET['ilx'])) {
    //echo $_GET['ilx'];
}
?>

<div class="container content" ng-app="termTestApp" ng-cloak>

    <div class="row" ng-controller="termTestCtrl" >

    <form name="termTestForm" id="termTestForm" class="term-form sky-form" role="form" novalidate>

    <fieldset>

    <button type="button" class="btn btn-default" ng-click="openTermForm()">Open term form</button>
    <br><br>

   <label>Tags</label>
   <tags-input ng-model="tags">
       <auto-complete source="loadTags($query)"></auto-complete>
   </tags-input>


    <label>Synonyms</label>
    <chips defer ng-model="synonyms" render="addSynonym(data)" enter-directive>
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
    <br>

    <label>Existing Ids</label>
    <chips defer ng-model="existing_ids" render="addEid(data)" enter-directive>
        <chip-tmpl>
            <div class="default-chip">
                {{chip.isLoading ? chip.defer : chip.defer.curie}}
                <span ng-hide="chip.isLoading">({{chip.defer.iri}})</span>
                <span class="glyphicon glyphicon-remove" remove-chip="removeEid(data)"></span>
                <div class="loader-container" ng-show="chip.isLoading">
                    <i class="fa fa-spinner fa-spin fa-lg loader"></i>
                </div>
            </div>
        </chip-tmpl>
        <input chip-control></input>
    </chips>
    <br>

    <label>Superclasses</label>
        <chips ng-model="superclasses" enter-directive>
            <chip-tmpl>
                <div class="default-chip">
                    {{chip.label}}
                    <span class="glyphicon glyphicon-remove" remove-chip></span>
                </div>
            </chip-tmpl>
            <input ng-model-control ng-model="lastSuperclass" uib-typeahead="sup as sup.label for sup in availableSuperclasses | filter:$viewValue"></input>
        </chips>
        <br>


       <label>Superclass</label>
       <input enter-directive style="width:100%;"
            type="text"
            ng-model="superclass"
            placeholder="select a superclass"
            uib-typeahead="sup as sup.label for sup in availableSuperclasses | filter:$viewValue | limitTo:80"
            typeahead-min-length="2"
            typeahead-on-select="addSuperclass2=($label)"
            typeahead-wait-ms="0"
            typeahead-select-on-blur="true"
        />
        <br>
        <br>

     <label>Chips typeahead example with small list to choose from</label>
     <chips ng-model="companies">
            <chip-tmpl>
                <div class="default-chip">
                    {{chip}}
                    <span class="glyphicon glyphicon-remove" remove-chip></span>
                </div>
            </chip-tmpl>
            <input ng-model-control ng-model="typeaheadmodel" uib-typeahead="company for company in availableCompanies | filter:$viewValue"></input>
    </chips>

    </fieldset>
    <footer>
        <button type="submit" class="btn-u btn-u-default" ng-click="runTest()">Submit</button>
        <input type="reset" class="btn-u btn-u-default" ng-click="resetForm()"></input>
    </footer>


    </form>
    Synonyms:    {{ synonyms }}<br>
    Existing Ids: {{ existing_ids }}<br>
    Superclasses: {{ superclasses }}<br>
    Superclass: {{ superclass }}
    </div>

    <?php include_once 'templates/term/term-synonym-modal.html';?>
    <?php include_once 'templates/term/term-curie-modal.html';?>
</div>
