
<div class="panel panel-success col-nopad" >
    <div class="panel-heading"><h3 class="panel-title md-title">Upload Term File</h3></div>


    <fieldset ng-show="error === true" style="padding:10px 0px">
        <pre class="alert alert-danger" >{{ feedback }}</pre>
    </fieldset>

    <form name="termForm" id="termForm" class="term-form sky-form" role="form" novalidate>
        <input id="cid" type="hidden" name="cid" value="<?= $community->id ?>" >


    <fieldset class="col-md-12" style="margin: 10px 0px">
        <input type="file" accept=".json" class="filestyle"  data-classButton="btn btn-primary"
            ngf-select ng-model="tsvFile" name="file"
            accept=".tsv,.txt" ngf-max-size="2MB"
            ngf-model-invalid="errorFiles"
            required enter-directive/>
        <i ng-show="termForm.file.$error.required" class="text-danger">*required</i><br>
        <i ng-show="termForm.file.$error.maxSize" class="text-danger">File too large
           {{errorFiles[0].size / 1000000|number:1}}MB: max 2M</i>
    </fieldset>
    <footer>
        <button class="btn btn-default" ng-click="uploadTsv(tsvFile)">Submit</button>
        <button class="btn btn-default" type="reset" ng-click="reset()">Cancel</button>
    </footer>

    </form>

    <fieldset style="padding:10px">
        <span ng-show="tsvFile.result">File uploaded successfully. Please wait while it is parsed and inserted into the database.</span>
        <span class="error" ng-show="errorMsg">{{errorMsg}}</span>
    </fieldset>

    <fieldset class="pull-left" style="padding:10px">
        <a href="#" ng-model="collapsed" ng-click="collapsed=!collapsed">Toggle example format</a><br><br>
        <p style="white-space: nowrap; border:1px solid #990000; padding:5px;" ng-show="collapsed">
[{
    "label": "",
    "definition": "",
    "type": "",
    "comment": "",
    "synonyms": [
        {
            "literal": "",
            "type": ""
        }
    ],
    "existing_ids": [
        {
            "curie": "",
            "iri": "",
            "preferred": ""
        }
    ],
    "superclass": {
        "label": ""
    },
    "ontologies": [
        {
            "url": ""
        }
    ]
}]<br>
        <span class="bg-danger">label:</span> required, must be unique and not already in the database.<br>
        <span class="bg-danger">type:</span> values can be only one of these four: "term", "relationship", "annotation", or "cde".<br>
        <span class="bg-danger">synonyms:</span> multiple, type in this field can be either blank or "abbrev".<br>
        <span class="bg-danger">existing_ids:</span> multiple, must already have added the namespace and prefix in curie catalog.<br>
        <span class="bg-danger">superclass:</span> must already be loaded and exact spelling with a term label loaded before.<br>
        <span class="bg-danger">ontologies:</span> multiple, must already be in the database.<br><br>

        <a href="/upload/term/term-upload-template.json" download>Download template file <i class="fa fa-download" aria-hidden="true"></i></a></p>
    </fieldset>
</div>

