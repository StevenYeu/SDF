<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

$flags = DatasetFlags::loadByDatasetAndUser($_GET['datasetid'], $_SESSION['user']->id);

// most data gotten using Angular, but lab name wasn't ...
$dataset = Dataset::loadBy(Array("id"), Array($_GET['datasetid']));

?>

<script type="text/javascript">
    $(window).on('load',function(){
        $('#goUpload').click(function(){
            $('#associated').modal("hide");
            window.location.href = "#upload";
            window.location.reload(true);
        });

    <?php
     if (in_array("associated files", $flags)): ?>
        $('#associated').modal('hide');
    <?php else: ?>
        $('#associated').modal('show');
    <?php 
        // don't show the modal window again
        DatasetFlags::createNewObj($_SESSION['user']->id, $_GET['datasetid'], "associated files");
        endif; 
    ?>
    });
</script>

<div id="single-dataset-app" ng-controller="singleDatasetController as ctrl" ng-cloak>
    <div class="modal fade" id="associated" tabindex="-1" role="dialog" aria-labelledby="switching-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="switching-title">Important associated files</h4>
                </div>
                <div class="modal-body">
                        <p>We highly encourage you to prepare and upload a Data Dictionary and Methodology.</p>

                        <p>Please note you will need to submit these for DOI and publication.</p>

                        <p>(This message is shown only the first time you visit this dataset page.)</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" name="goUpload" id="goUpload" onclick="javascript:window.location.href='#upload';">Go to Data Dictionary and Methodology Upload
                 </button>
                        <button class="btn btn-warning" data-dismiss="modal">Close</button>
                    </div>
            </div>
        </div>
    </div> <!-- end modal div-->

    <div ng-show="ctrl.initial_load">
        <h2>Loading...</h2>
    </div>
    <div ng-hide="ctrl.initial_load">
        <div class="row margin-bottom-20" ng-show="ctrl.dataset.can_edit">
            <div ng-show="ctrl.dataset.curation_status.substr(0, 11) == 'request-doi'">
                <span style="font-size:2em; color: red; font-weight: bold">
                DOI Requested: 
                <span ng-show="ctrl.dataset.is_locked"><i class="fa fa-lock"></i> Locked</span>
                <span ng-show="!ctrl.dataset.is_locked"><i class="fa fa-unlock"></i> Unlocked</span>
            </div>

            <div class="col-md-6">
                <h2>{{ ctrl.dataset.name }} - <a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $dataset->lab()->id ?>"><?php echo $dataset->lab()->name; ?></a></h2>
                <p>{{ ctrl.dataset.description }}</p>
                <p>Owner: {{ ctrl.dataset.owner }}, <?php echo $dataset->lab()->name; ?></p>
                <p>Last modified: {{ ctrl.dataset.last_updated_time * 1000 | date:'yyyy-MM-dd'}}</p>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td><span ng-style="{'color': ctrl.dataset.lab_status_color}">{{ ctrl.dataset.lab_status_pretty }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Size</strong></td>
                                    <td>{{ ctrl.dataset.total_records_count }} records / {{ ctrl.dataset.template.fields.length }} fields</td>
                                </tr>
                                <tr ng-class="{'text-warning': ctrl.getDefaultILXCount() > 0, 'text-success': ctrl.getDefaultILXCount() == 0}">
                                    <td><strong><span data-name="unmapped-cdes.html" class="help-tooltip-btn help-tooltip-text">Unmapped fields</span></strong></td>
                                    <td>{{ ctrl.getDefaultILXCount() }} <i class="fa fa-check" ng-show="ctrl.getDefaultILXCount() == 0"></i></td>
                                </tr>
                                <tr ng-show="ctrl.dataset.in_queue_count > 0">
                                    <td><strong><span data-name="dataset-upload-queue.html" class="help-tooltip-btn help-tooltip-text">Data in upload queue</span></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> <!-- end dataset info and stats div -->

        <div ng-hide="ctrl.dataset.can_edit">
            <h4>You do not have permission to edit this dataset</h4>
        </div>

        <div class="row margin-bottom-20" ng-show="ctrl.dataset.can_edit">
            <div class="row margin-bottom-10">
                <div class="col-md-8">
                    <strong>Downloads: </strong> 
                    <div class="btn-group">
                        <div style="cursor:pointer" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            Download options <span class="caret"></span>
                        </div>
                        <ul class="dropdown-menu">
                            <li>
                                <a ng-click="ctrl.csv_options.ilx = !ctrl.csv_options.ilx" href="javascript:void(0)">
                                    Include CDE ILX identifiers <span ng-show="ctrl.csv_options.ilx"><i class="fa fa-check"></i></span>
                                </a>
                            </li>
                        </ul>
                        &nbsp;
                        <a ng-href="{{ ctrl.downloadCSVUrl() }}" target="_self">
                            <div class="btn btn-primary">
                                <i class="fa fa-download"></i> Download CSV
                            </div>
                        </a>
                    </div>                
                    <button ng-click="ctrl.DownloadAssociatedFile(ctrl.dictionary)" class="btn btn-primary" ng-disabled="!ctrl.dictionary"><i class="fa fa-download"></i> Data Dictionary</button>
                    <button ng-click="ctrl.DownloadAssociatedFile(ctrl.methodology)" class="btn btn-primary" ng-disabled="!ctrl.methodology"><i class="fa fa-download"></i> Methodology</button>
                </div>
                <div class="col-md-4">
                    <span ng-show="!ctrl.dataset.is_locked">
                    <a target="_self" href="/<?php echo $community->portalName ?>/lab/datasetoverview?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}">
                        <button class="btn btn-primary"><i class="fa fa-edit"></i> Metadata Editor</button>
                    </a>
                    </span>
                    
                    <a target="_self" href="/<?php echo $community->portalName ?>/lab/doi_preview?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}">
                        <button class="btn btn-primary"> Metadata Preview</button>
                    </a>
                </div>
            </div>
            <div class="row margin-bottom-10" ng-show="ctrl.dataset.can_edit && !ctrl.dataset.is_locked">
                <div class="col-md-7">
                    <strong>Control: </strong> 
                    <a target="_self" href="/<?php echo $community->portalName ?>/lab/update-dataset?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}">
                        <button class="btn btn-primary"><i class="fa fa-upload"></i> Update Data</button>
                    </a>                
                    
                    <a href="javascript:void(0)" ng-click="ctrl.changeMode('associated-files')" ng-show="ctrl.mode != 'associated-files'">
                        <button class="btn btn-primary"><i class="fa fa-upload"></i> Upload Data Dictionary and Methodology</button>
                    </a>
                    <a href="javascript:void(0)" ng-click="ctrl.changeMode('edit')" ng-show="ctrl.mode != 'edit'">
                        <div class="btn btn-primary">
                            Edit dataset info
                        </div>
                    </a>
                    <a href="javascript:void(0)" ng-click="ctrl.changeMode('data')" ng-show="ctrl.mode != 'data'">
                        <div class="btn btn-primary">
                            Data mode
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <div ng-if="ctrl.dataset.in_queue_count">
                        <button formtarget="_self" class="btn btn-primary" ng-disabled=true>Share to Lab</button>
                        <button formtarget="_self" class="btn btn-danger" ng-disabled=true>Stop Sharing</button>
                    </div>
                    <div ng-if="ctrl.dataset.in_queue_count == 0">
                    <a href="javascript:void(0)" ng-click="ctrl.submitDataset()" ng-show="ctrl.dataset.lab_status == 'not-submitted' " >
                        <button formtarget="_self" class="btn btn-primary">Share to Lab</button>
                    </a> <a data-toggle="tooltip" title='Tutorial on "How to Share to Lab"' href="<?php echo $community->fullURL() ?>/about/tutorials?#share_release_publish" target="_blank" style="color: #428bca; background-color: white; font-size: 1.4em" ng-show="ctrl.dataset.lab_status == 'not-submitted' "><i class="fa fa-question-circle"></i></a>
                    <a href="javascript:void(0)" ng-click="ctrl.unsubmitDataset()" ng-hide="ctrl.dataset.lab_status == 'not-submitted'">
                        <button formtarget="_self" class="btn btn-danger">Stop Sharing</button>
                    </a>
                </div>
                    
                </div>
                <div class="col-md-3">
                    <a href="javascript:void(0)" ng-click="ctrl.deleteDataset()">
                        <button formtarget="_self" class="btn btn-danger">
                            Delete dataset
                        </button>
                    </a>
                    <a href="javascript:void(0)" ng-click="ctrl.deleteAllRecords()">
                        <button formtarget="_self" class="btn btn-danger">
                            Delete all records
                        </button>
                    </a>
                </div>
            </div>
        </div>

        <div class="row margin-bottom-20" ng-show="!ctrl.dataset.can_edit">
            <div class="col-md-7 margin-bottom-10">
                <strong>Downloads: </strong> 
                    <button formtarget="_self" ng-href="<?php echo $community->fullURL() ?>/lab/view-dataset?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}" class="btn btn-primary disabledd" ng-disabled="ctrl.dataset.in_queue_count">Download Options</button>
                <a target="_self" >
                    <button formtarget="_self" ng-href="<?php echo $community->fullURL() ?>/lab/view-dataset?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}" class="btn btn-primary" ng-disabled="ctrl.dataset.in_queue_count"><i class="fa fa-download"></i> Dataset</button>
                </a>
                <a target="_self" >
                    <button formtarget="_self" ng-href="<?php echo $community->fullURL() ?>/lab/view-dataset?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}" class="btn btn-primary" ng-disabled="!ctrl.dataset.in_queue_count"><i class="fa fa-download"></i> Data Dictionary</button>
                </a>
                <a target="_self" >
                    <button formtarget="_self" ng-href="<?php echo $community->fullURL() ?>/lab/view-dataset?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}" class="btn btn-primary" ng-disabled="!ctrl.dataset.in_queue_count"><i class="fa fa-download"></i> Methodology</button>
                </a>
            </div>
<!---            <div class="col-md-5">
                <button formtarget="_self" ng-href="/<?php echo $community->portalName ?>/lab/add-to-dataset?labid=<?php echo $lab->id ?>&datasetid={{ ctrl.dataset.id }}" class="btn btn-primary" ng-disabled="ctrl.dataset.in_queue_count">Follow Dataset</button>
            </div>
--->            
        </div>


        <div class="row margin-bottom-20" ng-show="ctrl.dataset.can_edit">
            <div class="col-md-12">
                <div class="row" ng-show="ctrl.mode == 'associated-files'">
                    <div ng-controller="AssociatedfilesController as ctrl2">
                        <div class="row">
                            <div class="col-md-12">
                                <h2>Upload Dataset Dictionary and Methodology</h2>
                                <div style="padding-left: 20px"><h3>Data Dictionary (.csv) (Required for dataset publication)</h3>
                                    <p><a data-toggle="tooltip" title='Tutorial on preparing a data dictionary' href="<?php echo $community->fullURL() ?>/about/tutorials?#dictionary" target="_blank" style="color: #428bca; background-color: white; font-size: 1.4em"><i class="fa fa-question-circle"></i></a> <a href="<?php echo $community->fullURL() ?>/about/tutorials?#dictionary" target="_blank" class="lab-link"> Click here for a more detailed tutorial on preparing a data dictionary.</a> </p>

                                    <ol>
                                        <li><a href="javascript:void(0)" ng-click="ctrl.showDictionaryRequirements()">
                                            <div class="lab-link">Review Data Dictionary Requirements</div></a></li>
                                        <li><a target="_blank" href="/upload/community-components/<?php echo strtoupper($community->portalName); ?>_Data_Dictionary_Template.csv" class="lab-link">Click to download the Data Dictionary template <img src="/images/csv-file-format-extension.png" height="24px"></a></li>

                                            <div class="row">
                                                <div class="form-group">
                                                    <div class="col-md-2"><li>
                                                        <label>Data Dictionary File (.csv)</label></li>
                                                    </div>
                                                    <div class="col-md-10">
                                                        <label for="fileChoose_dictionary" style="cursor: pointer;" class="lab-button lab-small-button">Select .csv File</label>
                                                            <input id="fileChoose_dictionary" class="hidden" type="file" custom-on-change="dictionaryFileSelect" />
                                                        </label>
                                                        <label style="cursor: pointer;" class="lab-button lab-small-button" ng-click="fileReset('dictionary')" ng-show="filename">Clear</label>

                                                        <label style="display: inline;" ng-show="filename">
                                                            Selected file: {{filename | limitTo:30}}{{filename.length > 30 ? '&hellip;' : ''}}
                                                        </label>
                                                        <br />
                                                        <div ng-show="fatal && filename">
                                                            <label style="display: inline; color: red;" ng-show="response != 'File has been validated.'" ><i class="fa fa-ban"></i> Error: </label><p ng-bind-html="response"></p>
                                                        </div>
                                                        <div ng-show="!fatal && filename">
                                                            <label style="display: inline; color: orange;" ng-if="response"><i class="fa fa-exclamation-triangle"></i> Warning: </label><p ng-bind-html="response"></p>
                                                            <label style="display: inline; color: green;"> <i class="fa fa-check-circle"></i> File has been validated</label>
                                                            <br />
                                                            <a href="javascript:void(0)">
                                                                <button ng-click="upload_associated_files('dictionary')" class="btn btn-success">Upload file</button>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                    </ol>
                                </div>
                                <div style="padding-left:20px"><h3>Methodology (.doc or .pdf) (Optional)</h3>
                                    <p><a data-toggle="tooltip" title='Tutorial on preparing a methodology document' href="<?php echo $community->fullURL() ?>/about/help#method_guide" target="_blank" style="color: #428bca; background-color: white; font-size: 1.4em"><i class="fa fa-question-circle"></i></a> <a href="<?php echo $community->fullURL() ?>/about/help#method_guide" target="_blank" class="lab-link"> Click here for guidelines on preparing a Methodology document.</a> </p>

                                    <ol>
                                        <div class="row">
                                            <div class="form-group">
                                                <div class="col-md-3"><li>
                                                    <label>Methodology File (.doc or .pdf) (Optional)</label></li>
                                                </div>
                                                <div class="col-md-9">
                                                    <input type="file" id="fileChoose_methodology" class="hidden" custom-on-change="methodologyFileSelect" />
                                                    <label for="fileChoose_methodology" style="cursor: pointer;" class="lab-button lab-small-button">Select .doc or .pdf File</label>
                                                    <label style="cursor: pointer;" class="lab-button lab-small-button" ng-click="fileReset('methodology')" ng-show="filename2">Clear</label>

                                                    <label style="display: inline;" ng-show="filename2">
                                                        Selected file: {{filename2 | limitTo:30}}{{filename2.length > 30 ? '&hellip;' : ''}}
                                                    </label>
                                                    <br />
                                                    <label style="display: inline; color: red;" ng-show="filename2 && response2 != 'File format has been validated.'" ><i class="fa fa-ban"></i> Error: <p ng-bind-html="response2"></p></label>
                                                    <div ng-show="response2 == 'File format has been validated.'">
                                                        <label style="display: inline; color: green;" ><i class="fa fa-check-circle"></i> {{ response2 }}</label>
                                                        <br />
                                                        <a href="javascript:void(0)">
                                                            <button ng-click="upload_associated_files('methodology')" class="btn btn-success">Upload file</button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- end row -->
                                    </ol>
                                </div>
                                <div>
                                    <a href="javascript:void(0)" ng-click="ctrl.changeMode('data')" ng-show="ctrl.mode != 'data'">
                                        <div class="btn btn-primary">Back to Dataset</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" ng-show="ctrl.mode == 'edit'">
                <div class="col-md-4">
                    <h4>Update dataset fields</h4>
                    <form class="form" ng-submit="ctrl.updateDatasetFields()">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" ng-model="ctrl.edit.name" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" ng-model="ctrl.edit.description" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Publications</label>
                            <input type="text" ng-model="ctrl.edit.publications" class="form-control" />
                        </div>
                        <input type="submit" class="btn btn-success" value="Update" />
                        <div class="pull-right">                            
                            <a href="javascript:void(0)" ng-click="ctrl.changeMode('data')" ng-show="ctrl.mode != 'data'">
                                <div class="btn btn-primary">
                                    Back to Dataset
                                </div>
                            </a>
                        </div>

                        <span ng-show="ctrl.updating_info">
                            Updating <i class="fa fa-spinner fa-spin"></i>
                        </span>
                        <p ng-show="ctrl.error_info" class="text-danger">
                            There was an error updating the dataset info.
                        </p>
                    </form>
                    
                </div>
            </div>
        </div>
        <div class="row" ng-show="ctrl.mode == 'data'">
            <input ng-model="ctrl.query" />
            <button class="btn btn-success" ng-click="ctrl.searchQuery()">Search</button>
            <h4 ng-show="ctrl.dataset.total_records_count == 0 && ctrl.dataset.in_queue_count > 0">
                We are still processing your dataset. Please check back in a few minutes ...
            </h4>
            <div id="dataset-table-scroll-top" style="overflow-x: scroll; overflow-y: hidden; width: 100%; height: 20px">
                <div>&nbsp;</div>
            </div>
            <div id="dataset-table-wrapper" style="overflow-x: scroll;width: 100%">
                <table class="table">
                    <thead>
                        <tr>
                            <th><span class="text-danger" ng-show="!ctrl.dataset.is_locked">Delete record</span></th>
                            <th ng-repeat="field in ctrl.dataset.template.fields">
                                {{ field.name }}
                            </th>
                        </tr>
                        <tr>
                            <th></th>
                            <th ng-repeat="field in ctrl.dataset.template.fields">
                                <small>
                                    <span ng-show="!ctrl.dataset.is_locked">
                                        <a ng-class="{'text-warning': field.termid.ilx == ctrl.defaultILX(), 'text-primary': field.termid.ilx != ctrl.defaultILX()}" href="javascript:void(0)" ng-click="ctrl.changeILX(field)">
                                            <span>{{ field.termid.label }} {{ field.termid.ilx }}</span>
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    </span>
                                    <span ng-show="ctrl.dataset.is_locked">{{ field.termid.label }} {{ field.termid.ilx }}</span>
                                </small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="datum in ctrl.dataset.data.records">
                            <td><span ng-show="!ctrl.dataset.is_locked"><a href="javascript:void(0)" class="text-danger" ng-click="ctrl.deleteRecord(datum)"><i class="fa fa-times"></i></a></span></td>
                            <td ng-repeat="field in ctrl.dataset.template.fields">
                                {{ datum[field.name] }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <ul uib-pagination
                ng-hide="!ctrl.dataset.data.count"
                total-items="ctrl.total_count"
                items-per-page="ctrl.per_page"
                ng-model="ctrl.page"
                ng-change="ctrl.changeDataPage()"
                max-size="5"
                boundary-links="true"
            ></ul>

        </div>
    </div>
</div>