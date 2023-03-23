<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/module-error.js"></script>
<script src="/js/resource-mention-upload.js"></script>

<div class="container" ng-app="mentionUploadApp" ng-cloak>
    <h2>Resource mention upload tool</h2>
    <p>To use, choose a .csv file (comma separated values) and click upload.  Any user can upload resource mentions, but only the owner of a resource can update a resource mention.  The comma separated file must have the following columns:</p>
    <dl class="dl-horizontal">
        <dt>Resource ID</dt><dd>SciCrunch ID (e.g. SCR_001905)</dd>
        <dt>Publication ID</dt><dd>The PMID to upload or change (e.g. PMID:123456)</dd>
        <dt>Snippet</dt><dd>Text from the publication that shows the mention of the resource.  One or two sentences is usually enough.</dd>
        <dt>Verified</dt><dd>Should be either 'good' or 'bad'.  Marks the mention as either good or bad.  Only the owners of a resource are able to set this value.</dd>
        <dt>Upload</dt><dd>If set to true, then the resource mention will be added or updated.  Otherwise it will be skipped.</dd>
    </dl>
    <hr/>

    <a href="/php/resource-mention-csv-example.php"><div class="btn btn-info">Download template file</div></a>
    <div ng-controller="uploadController as uc" ng-show="logged_in">
        <input type="file" id="csv-file" /> <span style="color:red">{{ upload.errmsg }}</span><br/>
        <button class="btn btn-success" ng-show="upload.mentions.length > 0" ng-click="upload.uploadMentions(upload.mentions)">Upload</button>

        <div class="table-search-v2 margin-bottom-20" ng-show="upload.mentions.length > 0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th ng-repeat="header in upload.header">{{ header }}</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="mention in upload.mentions | startFrom:uc.per_page*(uc.current_page-1) | limitTo:uc.per_page">
                            <td ng-repeat="header in upload.header">{{ mention.data[header] }}</td>
                            <td><span ng-show="mention.message_status"><i class="fa {{ uc.mentionStatusClass(mention.message_status) }}" ng-style="{color: uc.mentionStatusColor(mention.message_status)}"></i> {{ mention.message }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <pagination
            ng-show="upload.mentions.length > 0"
            total-items="upload.mentions.length"
            items-per-page="uc.per_page"
            ng-model="uc.current_page"
            max-size="7"
            boundary-links="true"
        ></pagination>
    </div>
    <div ng-hide="logged_in">
        <p>You must be logged in to use this tool</p>
    </div>
</div>
