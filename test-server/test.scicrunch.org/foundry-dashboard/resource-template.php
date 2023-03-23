<div class="profile container content" ng-controller="resourceDashBoard">
    <div class="row">
        <div class="col-md-12 ng-cloak">
            <!--Profile Body-->
            <!-- TODO Retrieve Resource information to populate the title and the description -->
            <h1> {{ resourceName }}
                <!-- TODO Add ResourceType, FormatType, TransportType, scheduleFreq x-->
                <a href="<?php echo $hostname."account/foundry-dashboard/logs/"?>{{scicrunchID}}">
                    <i class="fa fa-external-link" style="font-size: 16px"></i>
                </a>
                <a href="<?php echo $hostname."account/foundry-dashboard/logs/"?>{{scicrunchID}}" class="redirectButton">
                    <span class="label label-info pull-right"> View Logs </span>
                </a>
                <!-- TODO Replace the last part with current record's recordID-->
                <a href="https://github.com/SciCrunch/Foundry-Data/blob/master/SourceDescriptors/SCR_013869-Cellosaurus-RIN.yml" class="redirectButton" target="_blank">
                    <span class="label label-info pull-right"> View Source Descriptor </span>
                </a>
                <a href="https://github.com/SciCrunch/Foundry-Data/blob/master/Transformations/SCR_013869-Cellosaurus-RIN.trs" class="redirectButton" target="_blank">
                    <span class="label label-info pull-right"> View Transformation Script </span>
                </a>
            </h1>
            <body style="text-align: justify ">
			  <span ng-if="description === undefined">
				Currently not available 
			  </span>
			  <span ng-if="description != undefined">
				<p>{{ description }}</p>
			  </span>
            </body>
        </div>
        <!--End Profile Body-->
    </div>
    <!--/end row-->
</div>
<!--/container-->
<!--=== End Profile ===-->
<script type="text/javascript" src="http://0.0.0.0:8090/js/foundry-dashboard.js"></script>
