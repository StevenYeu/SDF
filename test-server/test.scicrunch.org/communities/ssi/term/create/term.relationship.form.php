
<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><h3 class="panel-title md-title">Add a new term relationship</h3></div>

    <div>
    <form name="termRelationshipForm" id="termRelationshipForm" class="term-form sky-form" role="form" novalidate>

    <fieldset>
        <label>Please choose first term</label>
        <label class="input">
            <input
                type="text"
                ng-model="term1"
                placeholder="select first term"
                uib-typeahead="term1 as term1.label for term1 in updateTermList($viewValue)"
                typeahead-min-length="2"
                typeahead-wait-ms="0"
                typeahead-select-on-blur="true"
            />
        </label><br>

        <label>Please add relationship term</label>
        <label class="input">
            <input
                type="text"
                ng-model="relationship"
                placeholder="select second term"
                uib-typeahead="relationship as relationship.label for relationship in updateRelationshipList($viewValue)"
                typeahead-min-length="2"
                typeahead-on-select="selectTerm($label)"
                typeahead-wait-ms="0"
                typeahead-select-on-blur="true"
            />
        </label>

        <label>Please choose second term</label>
        <label class="input">
            <input
                type="text"
                ng-model="term2"
                placeholder="select second term"
                uib-typeahead="term2 as term2.label for term2 in updateTermList($viewValue)"
                typeahead-min-length="2"
                typeahead-wait-ms="0"
                typeahead-select-on-blur="true"
            />
        </label><br>

    </fieldset>

    <footer>
        <button type="submit" class="btn-u btn-u-default" ng-click="addTermRelationship()">Submit</button>
        <input type="reset" class="btn-u btn-u-default" ng-click="reset()"></input>
    </footer>


    </form>
    </div>
</div>
