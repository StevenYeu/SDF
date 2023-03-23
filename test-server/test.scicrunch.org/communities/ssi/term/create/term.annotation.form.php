
<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><h3 class="panel-title md-title">Add a new term annotation</h3></div>

    <div>
    <form name="termAnnotationForm" id="termAnnotationForm" class="term-form sky-form" role="form" novalidate>

    <fieldset>
        <label>Please choose term</label>
        <label class="input">
            <input
                type="text"
                ng-model="term"
                placeholder="select term"
                uib-typeahead="term as term.label for term in terms | filter:$viewValue | filter:limitTo:100"
                typeahead-min-length="2"
                typeahead-on-select="selectTerm($label)"
                typeahead-wait-ms="250"
                typeahead-select-on-blur="true"
            />
        </label><br>

        <label>Please choose annotation term</label>
        <label class="input">
<!--            <input-->
<!--                type="text"-->
<!--                ng-model="annotation"-->
<!--                placeholder="select annotation term"-->
<!--                typeahead="annotation as annotation.label for annotation in annotations | filter:$viewValue | filter:limitTo:100"-->
<!--                typeahead-min-length="2"-->
<!--                typeahead-on-select="selectAnnotationTerm(annotation)"-->
<!--                typeahead-wait-ms="250"-->
<!--                typeahead-select-on-blur="true"-->
<!--            />-->
            <input
                    type="text"
                    ng-model="annotation"
                    placeholder="select annotation term"
                    uib-typeahead="anno as anno.label for anno in updateAnnotationList($viewValue)"
                    typeahead-min-length="2"
                    typeahead-wait-ms="0"
                    typeahead-select-on-blur="true"
            />
        </label><br>

        <label type="label">Value</label>
<!--            <div ng-show="annotation.annotation_type == 'range'">-->
<!--                From: <input class="small_num" name="range_start" ng-model="range_start" type="number" enter-directive/>&nbsp;&nbsp;-->
<!--                To: <input class="small_num" name="range_end" ng-model="range_end" type="number" enter-directive/>&nbsp;&nbsp;-->
<!--                Step: <input class="small_num" name="range_step" ng-model="range_step" type="number" enter-directive/>&nbsp;&nbsp;-->
<!--            </div>-->
<!--            <div ng-show="annotation.annotation_type == 'text'">-->
                <textarea style="width:100%" name="value" ng-model="value" placeholder="" enter-directive></textarea>
<!--            </div>-->
        <br>

        <label type="label">Comment</label>
        <label class="input">
            <textarea style="width:100%" name="comment" ng-model="comment" placeholder="" enter-directive></textarea>
        </label>
    </fieldset>

    <footer>
        <button type="submit" class="btn-u btn-u-default" ng-click="addTermAnnotation()">Submit</button>
        <input type="reset" class="btn-u btn-u-default" ng-click="reset()"></input>
    </footer>


    </form>
    </div>
</div>

