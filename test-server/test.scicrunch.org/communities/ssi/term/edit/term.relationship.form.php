<?php
$rid = array_pop(explode("/", $_SERVER[REQUEST_URI]));
?>

<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><span class="panel-title">Edit term relationship</span></div>

    <div>
    <form name="termRelationshipForm" id="termRelationshipForm" class="term-form sky-form" role="form" novalidate>
        <input type="hidden" name="rid" id="rid" value="<?= $rid ?>"></input>
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

        <label>Please choose first term</label>
        <label class="input">
            <input
                type="text"
                ng-model="term1"
                placeholder=""
                uib-typeahead="term1 as term1.label for term1 in updateTermList($viewValue)"
                typeahead-min-length="2"
                typeahead-on-select="selectTerm($label)"
                typeahead-wait-ms="0"
                typeahead-select-on-blur="true"
            />
        </label><br>

        <label>Please choose relationship term</label>
        <label class="input">
            <input
                type="text"
                ng-model="relationship"
                placeholder=""
                uib-typeahead="relationship as relationship.label for relationship in updateRelationshipList($viewValue)"
                typeahead-min-length="2"
                typeahead-on-select="selectTerm($label)"
                typeahead-wait-ms="0"
                typeahead-select-on-blur="true"
            />
        </label><br>

        <label>Please choose second term</label>
        <label class="input">
            <input
                type="text"
                ng-model="term2"
                placeholder=""
                uib-typeahead="term2 as term2.label for term2 in updateTermList($viewValue)"
                typeahead-min-length="2"
                typeahead-wait-ms="0"
                typeahead-select-on-blur="true"
            />
        </label><br>

        <label>Comment</label>
        <label class="input">
            <textarea rows="3" style="width:100%" ng-model="comment"></textarea>
        </label>
    </fieldset>

    <footer>
        <button type="submit" class="btn-u btn-u-default" ng-click="editTermRelationship()">Update</button>
        <input type="reset" class="btn-u btn-u-default" ng-click="reset()"></input>
    </footer>


    </form>
    </div>
</div>
