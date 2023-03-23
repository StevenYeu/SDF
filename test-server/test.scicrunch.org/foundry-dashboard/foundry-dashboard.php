<!--
<script src="../js/highcharts/js/highcharts.js" type="text/javascript"></script>
<script src="../js/highcharts/js/highcharts-more.js" type="text/javascript"></script>
<script src="../js/highcharts/js/modules/solid-gauge.js" type="text/javascript"></script>
-->
<?php
	// Global Variable
  $hostname = $GLOBAL["config"]["protocol"] . "//".$_SERVER["HTTP_HOST"]."/";
?>

<html lang="en">
	<head>
		<script src="/js/angular-1.7.9/angular.min.js"></script>
		<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
		<script type="text/javascript" src="/assets/plugins/jquery-1.10.2.min.js"></script>
		<link rel="stylesheet" href="/css/foundry-dashboard/foundry-dashboard.css">
	</head>

	<body>
		<!-- FIXME May need to adjust when pushed to production -->
		<?php
			switch ($vars['arg1']) {
				case "All_Resources":
					echo Connection::createBreadCrumbs('All Resources', array('Home','Account', 'Foundry Dashboard'),
														array($profileBase, $profileBase.'account', $profileBase.'account/foundry-dashboard'), 'All Resources');
					break;
				default:
					echo Connection::createBreadCrumbs('Foundry Dashboard', array('Home','Account'), array($profileBase,$profileBase.'account'), 'Foundry Dashboard');
			}
		?>

		<div class="profile container content">
			<div class="row">
				<!--Left Sidebar-->
				<?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>
				<!--End Left Sidebar-->

				<!-- Dashboard -->
				<div class="col-md-9" ng-app="dashboard">
					<div class="alphaTesting" style="margin-bottom:25px; float:right; margin-left:53%; margin-right:47%;">
						<img src="/images/BetaTest100x87.png" alt="Alpha Testing">
					</div>

					<div class="profile-body">
						<table class="table" id="Views">
							<tr>
								<!-- TODO Add links-->
								<th class="col-sm-2" style="text-align:center"><a href="/account/foundry-dashboard">Main View</a></th>
								<th class="col-sm-2" style="text-align:center"><a href="/account/foundry-dashboard/all_resources">All Resources View</a></th>
								<th class="col-sm-2" style="text-align:center"><a href="">Actions View</a></th>
								<th class="col-sm-2" style="text-align:center"><a href="">Log View</a></th>
							</tr>

						</table>

						<?php
							$page = (isset($vars['arg1'])) ? $vars['arg1'] : "" ;
							switch ($page) {
								case "all_resources":
									include $_SERVER['DOCUMENT_ROOT'] . '/foundry-dashboard/allResourcesView.php';
									break;
								case "resource":
									include $_SERVER['DOCUMENT_ROOT'] . '/foundry-dashboard/resource-template.php';
									break;
								case "logs":
									include $_SERVER['DOCUMENT_ROOT'] . '/foundry-dashboard/resource-logInfo-template.php';
									break;
								case "":
									include $_SERVER['DOCUMENT_ROOT'] . '/foundry-dashboard/dashboard.php';
							}
						?>
					</div>
				</div>
				<!-- End of  Dashboard -->

			</div>
			<!--/end row-->
		</div>
		<!--/container-->
		<!--=== End Profile ===-->
		<!-- <script src="https://code.angularjs.org/1.6.7/angular.js"></script> -->
		<script type="text/javascript" src="/js/foundry-dashboard.js"></script>
	</body>
</html>
