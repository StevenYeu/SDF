<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";?>
<?php
    $headerNames = ["id1", "reltype", "id2"];
?>
<style>
#upload-table {
    margin-top: 20px;
    overflow-x: auto;
    max-width: 100%;
    max-height: 600px;
}
#upload-table td {
    min-width: 175px;
}
th {
    background: #72c02c;
    color: white;
}
.btn-choose-file {
    transition: background 0.5s ease;
    background: #ccc;
}
.btn-choose-file:hover {
    background: #a6a6a6;
}
.btn-choose-file, .btn-choose-file:hover, .btn-table, .btn-table:hover, .btn-table:focus {
    color: white;
}
.btn-table {
    transition: background 0.5s ease;
    background: #72c02c;
}
.btn-table:hover {
    background: #599722 !important;
}
[ng\:cloak], [ng-cloak], .ng-cloak {
    display: none;
}
</style>

<div class="panel">
    <div class="panel-heading">
        <h1>Bulk Resource Relationships Uploader</h1>
        <p><b>Instructions</b></p>
        <ul>
            <li><a href="/php/bulk-resource-relationships-upload-csv-template.php" style="color:#72c02c;">Download Resource Relationships Upload Template File</a>
            <li>Open file in Microsoft Excel or Google Sheets</li>
            <li>Specify action (adding resource, skipping resource) in the <b>Action</b> column with <b>add</b>, <b>skip</b> respectively.</li>
            <li>Required columns to <b>add resource</b>: id1, reltype, id2</li>
            <li>Choose the file you want to upload. You should then see a preview table of the file contents.</li>
            <li>Make sure the file you are uploading is a <b>.csv</b> file. You can edit the file via Google Sheets or Microsoft Excel and then Save As as a <b>.csv</b> file.</li>
            <li>Click the <b>Upload</b> button. This may take a few minutes depending on the number of items. </li>
            <li><b>Do not navigate off the page until all items are uploaded</b></li>
            <li>After Uploading the file, you should see the <b>status</b> of the action on the last column of the preview table along with "Added".</li>
            <li>After Uploading the file, change all the <b>Action</b> columns to <b>skip</b> to prevent adding duplicates the next time.</li>
        </ul>
    </div>
    <div class="panel-body">
        <div class="container" ng-app="bulkResourceRelationshipsUploadApp" ng-cloak>
            <div ng-controller="uploadController as uc" ng-show="logged_in">
                <div class="inline-block">
                    <label class="btn btn-file btn-choose-file">
                        Choose File<input style="display: none;" type="file" id="csv-file" /> <span style="color:red">{{ upload.errmsg }}</span>
                    </label>
                    <button class="btn btn-table" ng-show="upload.resources.length > 0" ng-click="upload.uploadResources(upload.resources)">Upload</button>
                </div>
                <div class="table-search-v2 margin-bottom-20" ng-show="upload.resources.length > 0">
                    <div class="table-responsive" id="upload-table">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <?php
                                    foreach($headerNames as $tableHeaders) {
                                        echo "<th>$tableHeaders</th>";
                                    }
                                    ?>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="resource in upload.resources | startFrom:uc.per_page*(uc.current_page-1) | limitTo:uc.per_page">
                                    <td ng-repeat="header in upload.header">{{ resource.data[header] }}</td>
                                    <td><span ng-show="resource.message_status"><i class="fa {{ uc.resourceStatusClass(resource.message_status) }}" ng-style="{color: uc.resourceStatusColor(resource.message_status)}"></i> {{ resource.message }}</span></td>
                                    <td><span ng-show="resource.new_id"> {{ resource.new_id }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <ul uib-pagination
                    ng-show="upload.resources.length > 0"
                    total-items="upload.resources.length"
                    items-per-page="uc.per_page"
                    ng-model="uc.current_page"
                    max-size="7"
                    boundary-links="true"
                ></ul>
            </div>
            <div ng-hide="logged_in">
                <p>You must be logged in to use this tool</p>
            </div>
        </div>
    </div>
</div>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/curator-bulk-resource-relationships-upload.js"></script>
<script>
$(".btn-table").click(function() {
    $(".btn-table").css("background", "#A7D87B");
})
$(".btn-choose-file").change(function() {
    $(".btn-table").css("background", "#72c02c");
})
</script>
