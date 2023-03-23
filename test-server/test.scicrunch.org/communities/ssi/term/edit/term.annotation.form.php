<?php
$aid = array_pop(explode("/", $_SERVER[REQUEST_URI]));
?>
<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><span class="panel-title">Edit term annotation</span></div>

    <div>
    <form name="termAnnotationForm" id="termAnnotationForm" class="term-form sky-form" role="form" novalidate>
        <input type="hidden" name="aid" id="aid" value="<?= $aid ?>"></input>
        <input type="hidden" name="uid" id="uid" value="<?= $_SESSION['user']->id ?>"></input>
        <input type="hidden" name="role" id="role" value="<?= $_SESSION['user']->role ?>"></input>

    <fieldset>
    <div ng-show="canWithdraw == 'yes'">
        <div ng-show="withdrawn == 0">
        <label>Withdraw?</label>&nbsp;&nbsp;&nbsp;
        <label>
            <input type="checkbox" ng-model="withdraw">
        </label>
        </div>
        <div ng-show="withdrawn == 1">
        <span class="text-danger">You have withdrawn this relationship</span><br>
        <label>Remove withdrawal?</label>&nbsp;&nbsp;&nbsp;
        <label>
            <input type="checkbox" ng-model="remove_withdrawal">
        </label>
        </div>
        <br>
    </div>

    <?php if ($_SESSION['user']->role >= 2): ?>
    <div>
    <label>Curator status:</label>&nbsp;&nbsp;&nbsp;
    <label>
        <input type="radio" ng-model="curator_status" value="-1"> -1</input>&nbsp;&nbsp;&nbsp;
        <input type="radio" ng-model="curator_status" value="0"> 0</input>&nbsp;&nbsp;&nbsp;
        <input type="radio" ng-model="curator_status" value="+1"> +1</input>&nbsp;&nbsp;&nbsp;
    </label>
    </div>
    <br>
    <?php else: ?>
    <div>
        <label>Curator status:</label><label>&nbsp;&nbsp;&nbsp; {{curator_status}}</label>
    </div>
    <br>
    <?php endif; ?>

        <label>Upvotes:</label>
        <label>&nbsp;&nbsp;&nbsp; {{upvote}}</label><br>

        <label>Downvotes:</label>
        <label>&nbsp;&nbsp;&nbsp; {{downvote}}</label><br><br>

        <label>Please choose term</label>
        <label class="input">
            <input
                type="text"
                ng-model="term"
                placeholder=""
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
<!--                placeholder=""-->
<!--                typeahead="annotation as annotation.label for annotation in annotations | filter:$viewValue | filter:limitTo:100"-->
<!--                typeahead-min-length="2"-->
<!--                typeahead-on-select="selectTerm($label)"-->
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
<!--        <div ng-show="annotation.annotation_type == 'range'">-->
<!--            From: <input class="small_num" name="range_start" ng-model="range_start" type="number" enter-directive/>&nbsp;&nbsp;-->
<!--            To: <input class="small_num" name="range_end" ng-model="range_end" type="number" enter-directive/>&nbsp;&nbsp;-->
<!--            Step: <input class="small_num" name="range_step" ng-model="range_step" type="number" enter-directive/>&nbsp;&nbsp;-->
<!--        </div>-->
<!--        <div ng-show="annotation.annotation_type == 'text'">-->
            <textarea style="width:100%" name="value" ng-model="value" placeholder="" enter-directive></textarea>
<!--        </div>-->
        <br>

        <label type="label">Comment</label>
        <label class="input">
            <textarea style="width:100%" name="comment" ng-model="comment" placeholder="" enter-directive></textarea>
        </label>
    </fieldset>

    <footer>
        <button type="submit" class="btn-u btn-u-default" ng-click="editTermAnnotation()">Update</button>
        <input type="reset" class="btn-u btn-u-default" ng-click="reset()"></input>
    </footer>


    </form>
    </div>
</div>
