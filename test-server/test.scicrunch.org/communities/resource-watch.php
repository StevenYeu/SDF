<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- CSS Global Compulsory -->
		<link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="/assets/css/style.css">

		<!-- CSS Implementing Plugins -->
		<link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
		<link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="/assets/plugins/flexslider/flexslider.css">
		<link rel="stylesheet" href="/assets/plugins/parallax-slider/css/parallax-slider.css">

		<!-- CSS Theme -->
		<link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
		<link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
		<link rel="stylesheet" href="/assets/plugins/scrollbar/src/perfect-scrollbar.css">
		<link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
		<!--[if lt IE 9]>
		<link rel="stylesheet" href="assets/plugins/sky-forms/version-2.0.1/css/sky-forms-ie8.css">-->

		<!-- CSS Page Style -->
		<link rel="stylesheet" href="/assets/css/pages/profile.css">

		<!-- CSS Theme -->
		<link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">

		<!-- CSS Customization -->
		<link rel="stylesheet" href="/assets/css/custom.css">
		<link rel="stylesheet" href="/css/main.css">
		<link rel="stylesheet" href="/css/community-search.css">
		<link rel="stylesheet" href="/css/joyride-2.0.3.css">
		<link rel="stylesheet" href="/assets/plugins/summernote/summernote.css"/>
	</head>

	<body>
		<script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>

		<!-- JS Global Compulsory -->
		<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/jquery-steps/build/jquery.steps.js"></script>
		<script src="/assets/plugins/summernote/summernote.js"></script>
		<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
		<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
		<script type="text/javascript" src="/js/main.js"></script>
		<!-- JS Implementing Plugins -->
		<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
		<script type="text/javascript" src="/assets/plugins/flexslider/jquery.flexslider-min.js"></script>
		<script type="text/javascript" src="/assets/plugins/parallax-slider/js/modernizr.js"></script>
		<script type="text/javascript" src="/assets/plugins/parallax-slider/js/jquery.cslider.js"></script>
		<script type="text/javascript" src="/assets/plugins/counter/waypoints.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/counter/jquery.counterup.min.js"></script>
		<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>
		<script type="text/javascript" src="/js/jquery.truncate.js"></script>

		<script type="text/javascript" src="/assets/plugins/gmap/gmap.js"></script>
		<script type="text/javascript" src="/assets/js/app.js"></script>
		<!-- JS Page Level -->
		<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
		<script type="text/javascript" src="/assets/plugins/masonry/jquery.masonry.min.js"></script>
		<!-- JS Implementing Plugins -->
		<script type="text/javascript" src="/assets/js/pages/blog-masonry.js"></script>
		<script src='https://www.google.com/recaptcha/api.js'></script>

		<!-- Angular Files-->
		<script src="/js/angular-1.7.9/angular.min.js"></script>
		<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
		<script src="/js/angular-1.7.9/angular-sanitize.js"></script>

		<?php echo \helper\topPageHTML(); ?>

		<div class="wrapper">
			<?php
				include $_SERVER['DOCUMENT_ROOT'] . '/ssi/header/header-resourcewatch.php';

				if ($vars['type'] === 'resource-watch') {
					switch ($vars['title']) {
						case "No_Results_Found":
							include $_SERVER['DOCUMENT_ROOT'] . '/resource-watch/noResultsFound.php';
							break;
						case "Search":
							include $_SERVER['DOCUMENT_ROOT'] . '/resource-watch/search.php';
							break;
						case "About":
							include $_SERVER['DOCUMENT_ROOT'] . '/resource-watch/about.php';
							break;
						case "Submit":
							include $_SERVER['DOCUMENT_ROOT'] . '/resource-watch/submit.php';
							break;
						case "Confirmation":
							include $_SERVER['DOCUMENT_ROOT'] . '/resource-watch/confirmation.php';
							break;
						case "":
							include $_SERVER['DOCUMENT_ROOT'] . '/resource-watch/home.php';
							break;
						default:
							include $_SERVER['DOCUMENT_ROOT'] . '/resource-watch/errorPage.php';
							break;
					}
				}
				include $_SERVER['DOCUMENT_ROOT'] . '/ssi/footer/footer-resourcewatch.php';
			?>
		</div>

		<!-- Overwrite Certain General CSS -->
		<link rel="stylesheet" href="../css/resource-watch/resource-watch.css">

	</body>

</html>
