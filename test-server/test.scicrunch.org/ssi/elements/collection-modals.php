<?php

    $user = isset($data["user"]) ? $data["user"] : NULL;
    $community = $data["community"];
    $uuids = isset($data["uuids"]) ? $data["uuids"] : NULL;
    $views = isset($data["views"]) ? $data["views"] : NULL;

?>

<?php if (!is_null($user)): ?>
    <div class="new-collection back-hide no-padding">
        <div class="close dark less-right">X</div>
        <form method="post" id="name-form"
              action="/forms/collection-forms/create-collection.php" class="sky-form" enctype="multipart/form-data">
            <header>Create New Collection</header>
            <fieldset>
                <section>
                    <label class="label">Collection Name</label>
                    <label class="input">
                        <i class="icon-prepend fa fa-asterisk"></i>
                        <i class="icon-append fa fa-question-circle"></i>
                        <input type="text" name="name" placeholder="Focus to view the tooltip" required>
                        <b class="tooltip tooltip-top-right">The name of your collection</b>
                    </label>
                </section>
                <section>
                    <label class="label">Transfer Records from Default Collection?</label>
                    <label class="select">
                        <i class="icon-append fa fa-question-circle"></i>
                        <select name="transfer">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <b class="tooltip tooltip-top-right">The name of your collection</b>
                    </label>
                </section>
            </fieldset>

            <footer>
                <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
            </footer>
        </form>
    </div>
    <div class="new-collection-ajax back-hide no-padding">
        <div class="close dark less-right">X</div>
        <form method="get" id="new-collection-ajax" class="sky-form" enctype="multipart/form-data">
            <header>Create New Collection</header>
            <fieldset>
                <section>
                    <label class="label">Collection Name</label>
                    <label class="input">
                        <i class="icon-prepend fa fa-asterisk"></i>
                        <i class="icon-append fa fa-question-circle"></i>
                        <input type="text" class="ajax-name" name="name" placeholder="Focus to view the tooltip" required>
                        <b class="tooltip tooltip-top-right">The name of your collection</b>
                    </label>
                </section>
                <section>
                    <label class="label">Transfer Records from Default Collection?</label>
                    <label class="select">
                        <i class="icon-append fa fa-question-circle"></i>
                        <select class="ajax-transfer" name="transfer">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                        <b class="tooltip tooltip-top-right">The name of your collection</b>
                    </label>
                </section>
            </fieldset>

            <footer>
                <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
            </footer>
        </form>
    </div>
    <div class="rrid-report-item-update large-modal back-hide no-padding no-propagation">
        <a class="close dark less-right" style="color: red"><i class="fa fa-window-close" aria-hidden="true"></i> Close</a>
        <div id="rrid-report-item-update-app" ng-controller="rridItemController">
            <div class="container">
                <div style="min-height:100px; margin:10px" ng-show="active">
                    <div class="row">
                        <h3>{{ name }}</h3>
                        <h3>RRID:<span ng-bind-html="rrid"></span></h3>
                    </div>
                    <div class="row" ng-show="nosubtypes">
                        <div ng-show="!!items">
                            <p>Are you sure you want to remove this item from Authentication Report?</p>
                            <button ng-click="deleteItem('')" class="btn btn-danger">Remove</button>
                            <a target="_blank" ng-href="/rin/rrid-report/{{ report_id }}" class="btn btn-primary">View Authentication Report</a>
                        </div>
                        <div ng-hide="!!items">
                            <p>Do you want to add this resource to your authentication report?</p>
                            <button ng-click="addItem('')" class="btn btn-success">Add</button>
                            <a target="_blank" ng-href="/rin/rrid-report/{{ report_id }}" class="btn btn-primary">View Authentication Report</a>
                        </div>
                    </div>
                    <div class="row" ng-hide="nosubtypes">
                        <table style="border-spacing:5px 5px;border-collapse:separate">
                            <tbody ng-repeat="subtype in fmt_subtypes">
                                <tr>
                                    <td ng-show="subtype.used"><span class="label label-primary">Added</span></td>
                                    <td ng-hide="subtype.used"></td>
                                    <td>{{subtype.name}}</td>
                                    <td ng-show="subtype.used"><button ng-click="deleteItem(subtype.name)" class="btn btn-danger">Remove</button></td>
                                    <td ng-hide="subtype.used"><button ng-click="addItem(subtype.name)" class="btn btn-success">Add</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div ng-hide="active">
                    <h3>An error occured when loading the RRID.  Please try again later.</h3>
                </div>
            </div>
        </div>
    </div>
    <?php if(!is_null($uuids) && !is_null($views)): ?>
        <div class="add-all back-hide no-padding">
            <a class="close dark less-right" style="color: red"><i class="fa fa-window-close" aria-hidden="true"></i> Close</a>
            <form method="post" id="name-form"
                  action="/forms/collection-forms/add-all-items.php"
                  id="header-component-form" class="sky-form" enctype="multipart/form-data">
                <header>Add All Records on Page to a Collection</header>
                <input type="hidden" name="community" value="<?php echo $community->id ?>"/>
                <input type="hidden" name="items" value="<?php echo join(',', $uuids) ?>"/>
                <input type="hidden" name="views" value="<?php echo join(',', $views) ?>"/>
                <fieldset>
                    <section>
                        <label class="label">Which Collection</label>
                        <label class="select">
                            <i class="icon-append fa fa-question-circle"></i>
                            <select name="collection">
                                <?php
                                foreach ($user->collections as $id => $collection) {
                                    echo '<option value="' . $id . '">' . $collection->name . '</option>';
                                }
                                ?>
                            </select>
                            <b class="tooltip tooltip-top-right">The name of your collection</b>
                        </label>
                    </section>
                </fieldset>

                <footer>
                    <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
                </footer>
            </form>
        </div>
    <?php endif ?>
    <!--/container-->
<?php endif ?>

<script src="/js/module-rrid-report-item-update.js"></script>
