<script src="../js/resource-watch/resource-watch-search.js"></script>

<?php

	$query = htmlspecialchars($_GET['q']);
	/** Redirects to No Results Found page if no query is given**/
	if ($query != '') {
	} else {
		echo '<script language="javascript">window.location.href="./No_Results_Found?q="' . $query . '</script>';
	}

	function uppercasePrefix($rrid) {
		$prefix = strtoupper(explode("_", $rrid)[0]);
		$base = explode("_", $rrid)[1];
		return $prefix . "_" . $base;
	}
?>

<!DOCTYPE html>
<html ng-app="searchResultsApp">
	<title>Resource Watch | Search</title>

	<body ng-controller="searchResultsCtrl">
		<!-- Breadcrumb & Header-->
		<?php
			$query = "RRID:" . str_replace("RRID:", "", $query); 	// Includes RRID prefix regardless if the query had it or not
			echo Connection::createBreadCrumbs(uppercasePrefix($query), array('Resource Watch'), array('/ResourceWatch'), uppercasePrefix($query));
		?>

		<!-- Breadcrumb & Header-->

		<div class="container" style="margin-top:40px; margin-bottom:40px">
			<div class="row" style="margin-left:5%; margin-right:5%">

				<!-- Start of Left Side -->
				<div class="col-lg-5 col-md-12 ng-cloak" id="vendorBlock">
					<div id="nameLoader" class="loader"></div>
					<h1 ng-style="{ 'font-size' : (entityName.length > 20) ? '35px' : '40px', 'line-height' : (entityName.length > 20) ? '45px' : '65px' }">
						{{ entityName }}
					</h1>
					<div style="margin-left:4px; margin-top:20px; font-size:18px; min-width:400px">
						<p class="row">
							<span ng-if="rrid != query" style="margin-left:2%">
								<a id="resolved" style="color:black" title="Resolved RRID &nbsp;&nbsp;<a style='cursor:pointer; float:right' id='closePopover' onclick='closePopover()' role='button'>&#10005;</a>"
										data-toggle="popover" data-html="true"
										data-content="<span>RRID:{{ query }} <br/> was resolved to the following</span>">
										<b>RRID:{{ rrid }}</b>
								</a>
							</span>
							<span ng-if="rrid == query" style="margin-left:2%">
								<b>RRID:{{ rrid }}</b>
							</span>
							<span style="float:right"><b>Number of Vendors: ({{ numVendors }})</b></span>
						</p>
					</div>
					<ul class="nav nav-tabs">
						<li ng-repeat="vendor in vendors" ng-class="{active: $index == 0}">
							<a id="tabHeader-{{ $index + 1 }}" href="#tab-{{ $index + 1 }}" data-toggle="tab" ng-click="sortByActive(vendor.vendorName)" ng-if="vendor.vendorName.length > 0">
								{{ vendor.vendorName }}
							</a>
						</li>
					</ul>
					<div class="tab-content" style="margin-top:10px; font-size:15px">
						<div id="tab-{{ $index + 1 }}" class="tab-pane fade in" ng-class="{active: $index == 0}" ng-repeat="vendor in vendors">
							<p ng-if="vendor.vendorName.length > 0">
								<b>URL:</b><a href="{{ url }}" target="_blank"> {{ url }}</a>
								<br/>
								<span ng-if="(vendor.uri != '') && vendor.uri != undefined">
									<b>Vendor:</b><a href="{{ vendor.uri }}" target="_blank"> {{ vendor.vendorName }}</a>
								</span>
								<span ng-if="(vendor.uri === undefined) || (vendor.uri == '')">
									<b>Vendor:</b> {{ vendor.vendorName }}
								</span>
								<br/>
								<b>Catalog Number:</b>
								<span ng-if="vendor.catNums.length > 1">
									<select id ="{{ vendor.vendorName }}-{{ $index + 1 }}">
										<option ng-repeat="catNum in vendor.catNums" value="{{ catNum }}">{{ catNum }}</option>
									</select>
								</span>
								<span ng-if="vendor.catNums.length == 1">
									<span id="{{ vendor.vendorName.replaceAll(' ', '_') }}-catnum" ng-repeat="catNum in vendor.catNums" value="{{ catNum }}">{{ catNum }}</span>
								</span>
								<br/>
								<span style="text-align:justify; text-justify:inter-word; word-wrap:break-word">
									<b>Description:</b> {{ vendor.description }}
								</span>
							</p>
						</div>
						<div>
							<p ng-if="vendors.length == 0">
								<b>URL:</b><a href="{{ url }}" target="_blank"> {{ url }}</a>
								<br/>
								<span style="text-align:justify; text-justify:inter-word; word-wrap:break-word">
									<b>Description:</b> {{ description }}
								</span>
							</p>
						</div>
						<div style="margin:70px 20px 70px; text-align:center; border-style:solid; border-width:1px; border-color:grey">
							<h1 style="margin-top:10px; font-size:24px">Report Issue/Validation Information</h1>
							<!-- <p>By adding validation information for a given entity, the science community ...</p> -->

							<?php if (isset($_SESSION['user'])): ?>
								<button id="submitInfo" type="button" class="btn redirectBttn confirmBttn" data-toggle="modal" data-target="#confirmation"
									style="margin-bottom:20px; color:white">
									Make A Submission About The Above Product
							  </button>
							<?php endif ?>

							<?php if (!isset($_SESSION['user'])): ?>
								<button type="button" class="btn btn-login redirectBttn confirmBttn" style="margin-bottom:20px; color:white">
									Login In Order to Make A Submission
								</button>
							<?php endif ?>

						</div>
					</div>
					<!-- Start of Search Block -->
					<div class="row">
							<p style="font-size:15px; text-align:center; min-width:500px"><b>Search For More Issue/Validation Information by RRID</b></p>
					</div>
					<div class="row col-lg-6 col-lg-offset-1 col-md-6 col-md-offset-2">
							<form method="get" action="/ResourceWatch/Search">
								<div class="form-group row" style="margin:0% 25% 20%; display:flex">
									<a href="javascript:void(0);" style="float:right" data-html="true" data-toggle="popover" data-trigger="focus" title="What is a RRID?" data-placement="left"
										data-content="RRIDs are persistent and unique identifiers for referencing a research resource. <a href='/ResourceWatch/No_Results_Found?q=rrid' target='_blank'>[Learn More]</a>">
										<img src="/images/question.png" style="width:18px; height:18px; margin-right:5px; margin-top:9px"></img>
									</a>
									<label for="searchBar" class="col-form-label" style="font-size:22px; margin-right:5px; float:right">RRID:</label>
									<div>
										<input id="searchBar" class="form-control" style="width:195px; float:right" name="q" placeholder="Ex: AB_2341236" value="" type="text"/>
									</div>
									<span style="float:right">
										<button class="btn-u" type="search"><i class="fa fa-search"></i></button>
									</span>
								</div>
							</form>
					</div>
					<!-- End of Search Block -->
				</div>
				<!-- End of Left Side -->

				<!-- Start of Right Side -->
				<div class="col-lg-6 col-lg-offset-1 col-sm-12">
					<div id="rwDBLoader" class="loader"></div>
					<div class="ng-cloak" id="statementBlock" ng-if="validationInfo.length > 0 || issueInfo.length > 0">
						<!-- Start of Validation Info Block -->
						<div class="col-mod-6 row" style="border-bottom:solid 1px">
							<h2 style="display:inline-block">Validation Information</h2>
							<span style="float:right; font-size:15px; margin-top:2%">
								<b>Number of Validation Statements: ({{ validationInfo.length }})</b>
							</span>
						</div>
						<div ng-if="validationInfo.length > 0">
							<div style="font-size:15px; margin-bottom:40px" ng-repeat="valid in validationInfo | limitTo: validationLimit">
								<div class="row">
									<p>
										<span class="col-sm-5"><b>Vendor:</b> {{ valid.vendor}} </span>
										<span class="col-sm-5"><b>Catalog Number:</b> {{ valid.catalogNumber}} </span>
									</p>
								</div>
								<p>
									<b>Information Source:</b> <span ng-bind-html="valid.source"></span>
									<br/>
									<b>Comment:</b> {{ valid.displayMessage }}
									<br/>
									<span ng-if="valid.url != ''">
										<b>URL:</b> <a href="{{ valid.url }}" target="_blank">{{ valid.url }}</a>
										<br/>
									</span>
								</p>
								<hr class="dotted"></hr>
								<div ng-if="($index == (validationLimit - 1)) && ((validationInfo.length - 1) > 1)">
									<span ng-if="($index > 1) && ($index >= $middle)">
										<a ng-click="decrementLimit('validation')" style="cursor:pointer">[Show less]</a>
									</span>
									<a ng-click="incrementLimit('validation')" style="cursor:pointer; display:inline-block">[Show more]</a>
								</div>
								<div ng-if="($index == (validationInfo.length - 1)) && ((validationInfo.length - 1) > 1)">
									<a ng-click="decrementLimit('validation')" style="cursor:pointer">[Show less]</a>
								</div>
							</div>
						</div>
						<div ng-if="validationInfo.length === 0">
							<div class="row">
								<p class="col-sm-10" style="font-size:15px">No Validation Information was found</p>
							</div>
						</div>
						<!-- End of Validation Info Block -->

						<!-- Start of Issue Info Block -->
						<div class="row" style="border-bottom:solid 1px">
							<h2 style="display:inline-block">Issue Information</h2>
							<span style="float:right; font-size:15px; margin-top:2%">
								<b>Number of Issue Statements: ({{ issueInfo.length }})</b>
							</span>
						</div>
						<div ng-if="issueInfo.length > 0">
							<div style="font-size:15px; margin-bottom:40px" ng-repeat="issue in issueInfo | limitTo: issueLimit">
								<div class="row">
									<p ng-if="issue.vendor == null && issue.catalogNumber == null">
										<span class="col-sm-10"><b>Vendor:</b> This issue applies to all vendors </span>
									</p>
									<p ng-if="issue.vendor != null && issue.catalogNumber != null">
										<span class="col-sm-5"><b>Vendor:</b> {{ issue.vendor}} </span>
										<span class="col-sm-7"><b>Catalog Number:</b> {{ issue.catalogNumber}} </span>
									</p>
								</div>
								<p>
									<b>Information Source:</b> <span ng-bind-html="issue.source"></span>
									<br/>
									<b>Issue:</b>
									<span ng-if="issue.notificationType == 'warning' || issue.notificationType == 'alert'"
									      ng-style="{'color' : (issue.notificationType == 'warning') ? 'darkorange' : 'red'}">
										{{ issue.display }}
										<svg width="1.0625em" height="1em" viewBox="0 0 17 16" class="bi bi-exclamation-triangle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
										  <path fill-rule="evenodd" d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5a.905.905 0 0 0-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 5zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
										</svg>
									</span>
									<span ng-if="issue.notificationType == 'notice'"
										  ng-style="{'font-style' : (issue.notificationType == 'notice') ? 'italic' : 'normal', 'color' : 'black'}">
										{{ issue.display }}
									</span>
									<br/>
									<b>Comment:</b> <span ng-bind-html="issue.displayMessage"></span>
									<br/>
									<span ng-if="issue.url != ''">
										<b>URL:</b> <a href="{{ issue.url }}" target="_blank">{{ issue.url }}</a>
										<br/>
									</span>
								</p>
								<hr class="dotted"></hr>
								<div ng-if="($index == (issueLimit - 1)) && ((issueInfo.length - 1) > 1)">
									<span ng-if="($index > 1) && ($index >= $middle) && ($index != (issueInfo.length - 1))">
										<a ng-click="decrementLimit('issue')" style="cursor:pointer">[Show less]</a>
									</span>
									<span ng-if="$index != (issueInfo.length - 1)">
										<a ng-click="incrementLimit('issue')" style="cursor:pointer; display:inline-block">[Show more]</a>
									</span>
								</div>
								<div ng-if="($index == (issueInfo.length - 1)) && ((issueInfo.length - 1) > 1)">
									<a ng-click="decrementLimit('issue')" style="cursor:pointer">[Show less]</a>
								</div>
							</div>
						</div>
						<div ng-if="issueInfo.length === 0">
							<div class="row">
								<p class="col-sm-10" style="font-size:15px">No Issue Information was found</p>
							</div>
						</div>
						<!-- End of Issue Info Block -->
					</div>

					<div id="noResults" class="ng-cloak" style="visibility:hidden">
						<h1 style="text-align:center">No Issue/Validation Information for RRID:{{ rrid }} </h1>
						<div class="row" style="margin-left:10%; margin-right:10%">

							<?php if (isset($_SESSION['user'])): ?>
								<button type="button" class="btn btn-outline-light redirectBttn confirmBttn" data-toggle="modal" data-target="#confirmation"
									style="margin-bottom:20px; color:white; width:100%">
									Contribute Information To The Selected Product
							  </button>
							<?php endif ?>

							<?php if (!isset($_SESSION['user'])): ?>
								<button type="button" class="btn btn-login redirectBttn confirmBttn" style="margin-bottom:20px; color:white; width:100%">
									Login In Order to Make A Submission
								</button>
							<?php endif ?>

						</div>
					</div>
				</div>
				<!-- End of Right Side -->
			</div>
		</div>

		<!-- Modal Section -->
		<div class="modal" id="confirmation" role="dialog">
		  <div class="modal-dialog modal-dialog-centered" role="document">
		    <div class="modal-content">
		      <div class="modal-body">
						<div class="row" style="margin-left:2%; font-size:14px">
							<p><b><u>Please confirm that the following is what you would like to make a submission on:</u></b></p>
						</div>
						<form id="submitForm" action="/ResourceWatch/Submit" method="get" novalidate>
	            <div class="form-group row">
	              <div class="row">
	                <div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2">
	                  <label for="confirmRRID">RRID:</label>
	                </div>
	                <div class="col-lg-1">
	                  <input id="confirmRRID" name="rrid" type="text" readonly class="form-control-plaintext" value="{{ rrid }}"/>
	                </div>
	              </div>
	            </div>
	            <div class="form-group row">
	              <div class="row">
	                <div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2">
	                  <label for="confirmVendor">Vendor:</label>
	                </div>
	                <div class="col-lg-1">
	                  <input id="confirmVendor" name="vendor" type="text" readonly class="form-control-plaintext" value=""/>
	                </div>
	              </div>
	            </div>
	            <div class="form-group row">
	              <div class="row">
	                <div class="col-lg-3 col-lg-offset-1 col-md-3 col-md-offset-2 col-sm-3 col-sm-offset-2 col-xs-3 col-xs-offset-2">
	                  <label for="confirmCatalogNumber">Catalog Number:</label>
	                </div>
	                <div class="col-lg-1">
	                  <input id="confirmCatalogNumber" name="catalogNumber" type="text" readonly class="form-control-plaintext" value=""/>
	                </div>
	              </div>
	            </div>
							<div style="float:right; margin-bottom:5%; color:white">
								<button type="submit" class="btn btn-primary redirectBttn confirmBttn">Confirm</button>
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							</div>
	          </form>
		      </div>
		    </div>
		  </div>
		</div>
		<!-- Modal Section-->

	</body>

</html>
