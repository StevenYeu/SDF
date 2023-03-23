<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php echo GACODE ?>', 'auto');
    ga('send', 'pageview');

</script>
<style>
    .header-v1 .navbar-default .navbar-nav > li > a:hover, .header-v1 .navbar-default .navbar-nav > .active > a, .header-v1 .navbar-default .navbar-nav > li:hover > a {
        color: #E74C3C;
        background: #E74C3C !important;
    }

    .header-v1 .navbar-default .navbar-nav > li > a:focus, .header-v1 .navbar-default .navbar-nav > .active > a, .header-v1 .navbar-default .navbar-nav > li:focus > a {
        color: #E74C3C !important;
        background: #E74C3C !important;
    }

    .header .navbar-default .navbar-nav > .open > a {
        color: #E74C3C !important;
        background: #E74C3C !important;
    }

    .header .dropdown-menu {
        border-top: solid 2px #E74C3C !important;
    }

    .topbar-link:hover {
        color: #E74C3C !important;
    }

    .header .navbar-default .navbar-nav > li > a:hover, .header .navbar-default .navbar-nav > .active > a {
        border-bottom: solid 2px #E74C3C !important;
    }

    .header-v1 .navbar .nav > li > .search:hover {
        background: #E74C3C !important;
    }

    .header .navbar .nav > li > .search:hover {
        color: #E74C3C;
        border-bottom-color: #E74C3C !important;
    }

    .top-v1-data .btn-group.open .dropdown-menu {
        display: block;
        text-align: left;
    }

    .top-v1-data .btn-group.open .dropdown-menu li {
        display: block;
        padding: 0px;
    }

    .header-v1 .navbar-default .navbar-nav > li > a {
        padding: 12px 30px 9px 20px;
    }

    .header-v1 .navbar .nav > li > .search {
        padding: 12px 10px;
    }

    .header .navbar-brand {
        top: 10px;
    }

    .header-v1 .dropdown > a:after {
        top: 13px;
    }
</style>
<link rel="stylesheet" href="../../css/resource-watch/resource-watch.css">

<!--Keep track of selected pages -->
<?php
	$tab = 0;
	$pages = array('About' => 'About', 'RRID' => 'What are RRIDs?', 'CatalogNumber' => "What are Catalog Numbers?");
	$currentPage = explode('/', $_SERVER['REQUEST_URI'])[2]; // Retrieve redirect page
	switch ($currentPage) {
		case "About":
			$tab = 2;
			break;
		case "RRIDs":
			$tab = 3;
			break;
		case "CatalogNumbers":
			$tab = 4;
			break;
		case "": // Home
			$tab = 1;
			break;
	}
?>
<!-- -->

<div class="header header-v1" style="z-index:99">
    <!-- Topbar -->
    <div
        class="topbar-v1 margin-bottom-20">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                </div>

                <div class="col-md-6">
                    <ul class="list-unstyled top-v1-data" style="float:right">
                        <?php if (!isset($_SESSION['user'])) { ?>
                            <li><a href="#" class="topbar-link btn-login">Login</a></li>
                            <li><a class="topbar-link referer-link" href="/register">Register</a></li>
                        <?php
                        } else {
                            if ($_SESSION['user']->role > 0) {
                                $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                                $splits = explode('&', $actual_link);
                                if (count($splits) > 1) {
                                    $base = str_replace('&editmode=true', '', $actual_link);
                                    $url = str_replace('&editmode=true', '', $actual_link) . '&';
                                } else {
                                    $base = str_replace('?editmode=true', '', $actual_link);
                                    $url = '?';
                                }
                            }
                            if (count($_SESSION['user']->levels) > 0) {
                                ?>
                            <?php } ?>
							Welcome back, <?php echo $_SESSION['user']->firstname; ?>
                            <li><a class="topbar-link" href="/forms/logout.php">Logout</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Topbar -->

    <!-- Navbar -->
    <div class="navbar navbar-default" role="navigation" style="margin-bottom: 20px;">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="fa fa-bars"></span>
                </button>
                <div class="row" style="display:flex">
                  <a class="navbar-brand" href="/ResourceWatch">
                      <span style="font-size: 36px">Resource Watch</span>
                  </a>
                  <div class="alphaTesting" style="margin-top:-15px">
                    <img src="../images/BetaTest64x54.png" alt="Alpha Testing">
                  </div>
                </div>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse navbar-responsive-collapse">
                <ul class="nav navbar-nav">
                    <!-- Home -->
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="drop-down-toggle">Information</a>
						<ul class="dropdown-menu">
							<li class="<?php if ($tab == 1) echo 'active'?>"><a href="/ResourceWatch">Resource Watch</a></li>
							<li class="<?php if ($tab == 2) echo 'active'?>"><a href="/ResourceWatch/About">About</a></li>
							<li class="<?php if ($tab == 3) echo 'active'?>"><a href="/ResourceWatch/No_Results_Found?q=rrid">What is a RRID?</a></li>
						</ul>
                    </li>
                    <!-- End Home -->

                </ul>
            </div>
            <!--/navbar-collapse-->
        </div>
    </div>
    <!-- End Navbar -->

</div>
<?php echo \helper\htmlElement("login-form", Array("errorID" => $errorID, "community" => NULL)); ?>
