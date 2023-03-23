<script src="../js/resource-watch/resource-watch-submitForm.js"></script>

<?php

	if (!isset($_SESSION['user'])){
	    header('Location: /ResourceWatch');
	    exit();
	}

	$params = parse_url($_SERVER['REQUEST_URI'] , PHP_URL_QUERY);
	parse_str($params, $metadata);

	$rrid = $metadata['rrid'];
	$vendor = $metadata['vendor'];
	$catalogNumber = $metadata['catalogNumber'];
?>

<!DOCTYPE html>
<html ng-app="submissionApp">
	<title>Resource Watch | Submit Form</title>

	<body ng-controller="submissionCtrl">
		<!-- Breadcrumb & Header-->
		<?php
			echo Connection::createBreadCrumbs('Submission Form', array('Resource Watch'), array('/ResourceWatch'), 'Submission Form');
		?>
		<!-- Breadcrumb & Header-->

    <div class="container" style="margin-top:40px; margin-bottom:40px">
      <div class="row">
				<!-- Left side -->

        <div class="col-lg-2 col-md-10 col-md-offset-1" id="instructionsBlock"
						style="min-width:300px; margin-bottom:5%; text-align: justify; text-justify: inter-word">
          <h3 style="text-align:center">Submission Instructions</h3>
          <p id="instructions" style="text-align:center">
						For instructions, select a submission type.
          </p>
        </div>

        <!-- Left side -->

        <!-- Right side -->
        <div class="col-lg-7 col-md-5" style="margin-left:5%">
          <form id="submitForm" action="/resource-watch/submission.php" method="post" enctype="multipart/form-data">
						<input id="userID" type="hidden" name="userID" value="<?php echo $_SESSION['user']->id ?>">
            <div class="form-group row">
              <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <label for="">RRID:</label>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <input id="rrid" type="text" readonly class="form-control-plaintext" name="rrid" value="<?php echo $rrid?>"/>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <label for="">Vendor:</label>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <input id="vendor" type="text" readonly class="form-control-plaintext" name="vendor" value="<?php echo $vendor?>"/>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <label for="">Catalog Number:</label>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <input id="catalogNumber" type="text" readonly class="form-control-plaintext" name="catalogNumber" value="<?php echo $catalogNumber?>"/>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <label for="">Submission Type:<span style="color:red">*</span></label>
                </div>
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2" style="min-width:220px">
									<!-- onchange="selectionSubmissionType(this.value)" -->
                  <select id="submissionType" class="form-control" name="submissionType"
													ng-change="changeInstructions()" ng-model="submissionType" ng-init="submissionType='Validation'" required>
                    <option value="Validation">Provide Validation Info</option>
										<option value="Issue">{{ entity }} Has Issue</option>
                    <option value="Discontinued">{{ entity }} Discontinued</option>
										<option ng-if="prefix == 'CVCL'" value="Contaminated">{{ entity }} Contaminated</option>
										<option ng-if="prefix == 'CVCL'" value="Misidentified">{{ entity }} Misidentified</option>
                    <!-- <option ng-if="prefix == 'CVCL'" value="Other">Other</option> -->
                  </select>
                </div>
								<!-- <div id="otherRecordType" class="col-lg-6 col-md-1 col-sm-5 col-xs-7" style="display:none">
									<div style="display:flex">
										<div style="float:left; min-width:170px; max-width:170px">
											<input id="otherValue" type="text" class="form-control" name="otherType" placeholder="Other" required/>
										</div>
										<div>
											<a href="javascript:void(0);" style="float:left; margin-top:5px" id="SubTypes" data-html="true" data-toggle="popover"
												data-trigger="focus" title="Other Types" data-placement="right"
												data-content="Other values can be the following: <br/> <li>Testing</li>"
												style="min-width:200px">
												<img src="/images/question.png" style="width:18px; height:18px; margin-left:5px"></img>
											</a>
										</div>
									</div>
								</div> -->
              </div>
            </div>
            <div class="form-group row">
              <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <label for="">Description:<span style="color:red">*</span></label>
                </div>
                <div class="col-lg-6 col-md-9 col-sm-6 col-xs-12" id="descriptionField">
                  <textarea id="description" name="description" maxlength="3000" rows="8" cols="70" required placeholder=""></textarea>
									<p>
										<b>Note:</b> Description limited to 3000 characters
									</p>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-2 col-xs-2">
                  <label for="">Supporting Documents:<span style="color:red">*</span></label>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-2 col-xs-3" style="min-width:200px">
                  <select id="supportDocType" class="form-control" onchange="selectionSupportingDocs(this.value)" required>
                    <option selected value="pmid">PMID</option>
                    <option value="doi">DOI</option>
										<option value="url">URL</option>
                    <option value="pdf">PDF</option>
                    <option value="image">Image</option>
                  </select>
									<div id="noteSection">
										<p id="imageNote" style="display:none; min-width:300px; margin-top:20px">
											<b>Note:</b> Acceptable Formats: PNG, JPG, JPEG
										</p>
										<p id="pdfNote" style="display:none; min-width:300px; margin-top:20px">
											<b>Note:</b> Files are limited to 50MB in size
										</p>
									</div>
                </div>
								<div class="col-lg-2 col-md-2 col-sm-4 col-xs-7" style="display:flex">
									<div id="docInput">
										<div id="submitFileInput" style="display:none; min-width:300px">
											<input type="file" class="form-control form-control-lg" id="submitFile" required/>
										</div>
										<div id="submitIDInput" style="display:block; min-width:200px; max-width:200px">
											<input type="text" class="form-control" id="submitID" required/>
										</div>
									</div>
									<div class="col-lg-1 col-md-1">
										<button id="addDocBttn" type="button" class="btn btn-primary mb-2">Add Document</button>
									</div>
								</div>
              </div>
            </div>
						<div id="documentDisplay" class="form-group row" style="display:none">
							<div class="row">
								<div class="col-lg-10 col-lg-offset-2 col-md-10 col-md-offset-3 col-sm-offset-2">
									<label for=""><u>Current Submitted Documents:</u></label>
								</div>
							</div>
						</div>
						<div id="pmidDisplay" class="form-group row" style="display:none">
							<div class="row col-lg-offset-2 col-md-offset-3 col-sm-offset-2 col-xs-offset-1">
								<div class="col-md-1">
									<label for=""><u>PMIDs:</u></label>
								</div>
								<div class="col-md-10">
									<ul id="pmidList" style="display:inline-block">
									</ul>
								</div>
							</div>
						</div>
						<div id="doiDisplay" class="form-group row" style="display:none">
							<div class="row col-lg-offset-2 col-md-offset-3 col-sm-offset-2 col-xs-offset-1">
								<div class="col-md-1">
									<label for=""><u>DOIs:</u></label>
								</div>
								<div class="col-md-10">
									<ul id="doiList" style="display:inline-block">
									</ul>
								</div>
							</div>
						</div>
						<div id="urlDisplay" class="form-group row" style="display:none">
							<div class="row col-lg-offset-2 col-md-offset-3 col-sm-offset-2 col-xs-offset-1">
								<div class="col-md-1">
									<label for=""><u>URLs:</u></label>
								</div>
								<div class="col-md-10">
									<ul id="urlList" style="display:inline-block">
									</ul>
								</div>
							</div>
						</div>
						<div id="pdfDisplay" class="form-group row" style="display:none">
							<div class="row col-lg-offset-2 col-md-offset-3 col-sm-offset-2 col-xs-offset-1">
								<div class="col-md-1">
									<label for=""><u>PDFs:</u></label>
								</div>
								<div class="col-md-10">
									<ul id="pdfList" style="display:inline-block">
									</ul>
								</div>
							</div>
						</div>
						<div id="imageDisplay" class="form-group row" style="display:none">
							<div class="row col-lg-offset-2 col-md-offset-3 col-sm-offset-2 col-xs-offset-1">
								<div class="col-md-1">
									<label for=""><u>Images:</u></label>
								</div>
								<div class="col-md-10">
									<ul id="imageList" style="display:inline-block">
									</ul>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<div class="row">
								<div class="col-lg-5 col-lg-offset-2 col-md-3 col-md-offset-3 col-sm-2 col-sm-offset-2 col-xs-2" style="max-width:600px; min-width:610px">
									<button id="confirmSubmission" type="button" class="form-control btn btn-primary">Confirm Submission</button>
								</div>
							</div>
						</div>
          </form>
        </div>
        <!-- Right side -->
      </div>
    </div>

		<!-- Modal Section -->
		<div class="modal" id="confirmation" role="dialog">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-body">
						<div class="row" style="margin-left:2%; font-size:14px">
							<p><b><u>Please confirm that the following is correct before submitting:</u></b></p>
						</div>
						<form>
							<div class="form-group row">
								<div class="row">
									<div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-1 col-sm-3 col-sm-offset-1 col-xs-3 col-xs-offset-1">
										<label for="confirmRRID">RRID:</label>
									</div>
									<div class="col-lg-1">
										<input id="confirmRRID" name="rrid" type="text" readonly class="form-control-plaintext" value="{{ rrid }}"/>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<div class="row">
									<div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-1 col-sm-3 col-sm-offset-1 col-xs-3 col-xs-offset-1">
										<label for="confirmVendor">Vendor:</label>
									</div>
									<div class="col-lg-1">
										<input id="confirmVendor" name="vendor" type="text" readonly class="form-control-plaintext" value=""/>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<div class="row">
									<div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-1 col-sm-3 col-sm-offset-1 col-xs-3 col-xs-offset-1">
										<label for="confirmCatalogNumber">Catalog Number:</label>
									</div>
									<div class="col-lg-1">
										<input id="confirmCatalogNumber" name="catalogNumber" type="text" readonly class="form-control-plaintext" value=""/>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<div class="row">
									<div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-1style="min-width:200px" col-sm-3 col-sm-offset-1 col-xs-3 col-xs-offset-1">
										<label for="confirmSubmissionType">Submission Type:</label>
									</div>
									<div class="col-lg-1">
										<input id="confirmSubmissionType" name="SubmissionType" type="text" readonly class="form-control-plaintext" value=""/>
									</div>
								</div>
							</div>
							<div class="form-group row">
								<div class="row">
									<div class="col-lg-12 col-lg-offset-1 col-md-3 col-md-offset-1 col-sm-3 col-sm-offset-1 col-xs-3 col-xs-offset-1">
										<label for="confirmSupportingDocuments">Supporting Documents:</label>
										<ul id="confirmSupportingDocuments">
										</ul>
									</div>
								</div>
							</div>
							<div style="float:right; margin-bottom:5%; color:white">
								<button id="submitSubmission" class="btn btn-primary">Confirm</button>
								<button id="cancelSubmission" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<!-- Modal Section-->

	</body>
</html>
