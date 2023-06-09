
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php echo GACODE ?>', 'auto');
    ga('send', 'pageview');

</script>
<style xmlns="http://www.w3.org/1999/html">
    <?php if($component2->color1){ ?>
    .header .navbar-default .navbar-nav > li > a:hover,
    .header .navbar-default .navbar-nav > .active > a {
        color: <?php echo '#'. $component2->color1?>;
        border-bottom: solid 2px <?php echo '#'. $component2->color1?>;
    }

    .header .dropdown-menu {
        border-top: solid 2px <?php echo '#'. $component2->color1?>;
    }

    .header .navbar .nav > li > .search:hover {
        color: <?php echo '#'. $component2->color1?>;
        background: #f7f7f7;
        border-bottom-color: <?php echo '#'. $component2->color1?>;
    }

    .topbar .loginbar li a:hover {
        color: <?php echo '#'. $component2->color1?>;
    }

    .header .btn-u {
        background: <?php echo '#'. $component2->color1?>;
    }

    .header .btn-u:hover {
        background: <?php echo '#'. $component2->color1?>;
    }

    <?php } ?>
    .header .container > .navbar-header, .header .container-fluid > .navbar-header, .header .container > .navbar-collapse, .header .container-fluid > .navbar-collapse {
        margin-bottom: -30px;
    }
</style>
<div class="header">
    <!-- Topbar -->
    <div class="topbar">
        <div class="container">
            <!-- Topbar Navigation -->
            <ul class="loginbar pull-right">
                <?php if (!isset($_SESSION['user'])) { ?>
                    <li><a href="#" class="btn-login">Login</a></li>
                    <li class="topbar-devider"></li>
                    <li><a href="/register" class="referer-link">Register</a></li>
                <?php } else { ?>
                    <li><a href="/forms/logout.php">Logout</a></li>
                <?php } ?>
            </ul>
            <!-- End Topbar Navigation -->
        </div>
    </div>
    <!-- End Topbar -->

    <!-- Navbar -->
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="fa fa-bars"></span>
                </button>
                <a class="navbar-brand" href="/">
                    <span style="font-size: 36px">SciCrunch</span>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse mega-menu navbar-responsive-collapse" aria-expanded="false">
                <ul class="nav navbar-nav">
                    <!-- Home -->
                    <li class="<?php if ($tab == 0) echo 'active' ?> dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Information</a>
                        <ul class="dropdown-menu">
                            <li class="<?php if($tab==0 && $hl_sub==0) echo 'active'?>"><a href="/">Home</a></li>
                            <?php
                            $pages = $components['page'];
                            foreach($pages as $pag){
                                if($tab==0 && $hl_sub==$pag->position+1)
                                    echo '<li class="active"><a href="/page/'.$pag->text2.'">'.$pag->text1.'</a></li>';
                                else
                                    echo '<li><a href="/page/'.$pag->text2.'">'.$pag->text1.'</a></li>';
                            }

                            ?>
                            <li class="<?php if($tab==0 && $hl_sub == -1) echo 'active'?>"><a href="/versions">Release Notes</a></li>
                            <li class="<?php if($tab==0 && $hl_sub == -2) echo 'active'?>"><a href="/news">News</a></li>
                            <li class="<?php if($tab==0 && $hl_sub == -3) echo 'active'?>"><a href="/faq">FAQs</a></li>
                        </ul>
                    </li>
                    <!-- End Home -->

                    <!-- Job Pages -->
                    <li class="<?php if ($tab == 1) echo 'active' ?> dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Browse</a>
                        <ul class="dropdown-menu">
                            <li class="<?php if($tab==1 && $hl_sub==0) echo 'active'?>"><a href="/browse/communities">Communities</a></li>
                            <li class="<?php if($tab==1 && $hl_sub==3) echo 'active'?>"><a href="/browse/resources">Resources</a></li>
                            <li class="<?php if($tab==1 && $hl_sub==2) echo 'active'?>"><a href="/browse/content">Content</a></li>
                        </ul>
                    </li>
                    <!-- End Job Pages -->


                    <li class="<?php if ($tab == 2) echo 'active' ?> dropdown">
                        <a href="javascript:void(0);">Create</a>
                        <ul class="dropdown-menu">
                            <li><a href="/create/community">New Community</a></li>
                            <li><a href="/create/resource">New Resource</a></li>
                        </ul>
                    </li>

                    <?php if(isset($_SESSION['user'])){?>
                    <li class="<?php if ($tab == 3) echo 'active' ?> dropdown tut-myaccount">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">My Account</a>
                        <ul class="dropdown-menu">
                            <li class="<?php if($tab==3 && $hl_sub==0) echo 'active'?>"><a href="/account">Home</a></li>
                            <li class="<?php if($tab==3 && $hl_sub==1) echo 'active'?>"><a href="/account/communities">Communities</a></li>
                            <li class="<?php if($tab==3 && $hl_sub==2) echo 'active'?>"><a href="/account/resources">Resources</a></li>
                            <li<?php if ($hl_sub == 9 && $tab == 3) echo ' class="active"' ?>><a href="/scicrunch/account/datasets-overview">Datasets</a></li>
                            <li class="<?php if ($tab == 3 && $hl_sub == 3) echo 'active' ?>"><a
                                    href="/account/saved">Saved Searches</a></li>
                            <li class="<?php if ($tab == 3 && $hl_sub == 5) echo 'active' ?>"><a
                                    href="/account/collections">My Collections</a></li>
                            <li class="<?php if ($tab == 3 && $hl_sub == 7) echo 'active' ?>"><a
                                    href="/account/developer">API Keys</a></li>
                            <?php if($_SESSION['user']->role>0){?>
                                <li class="<?php if ($tab == 3 && $hl_sub == 4) echo 'active' ?>"><a
                                        href="/account/scicrunch">Edit SciCrunch</a></li>
                                <li class="<?php if ($tab == 3 && $hl_sub == 6) echo 'active' ?>"><a
                                        href="/account/uptime">Uptime Dashboard</a></li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>


                    <!-- Search Block -->
                    <li>
                        <i class="search fa fa-search search-btn"></i>

                        <div class="search-open">
                            <form method="get" action="/browse/content">
                            <div class="input-group animated fadeInDown">
                                <input type="text" class="form-control" name="query" placeholder="Search">
                                    <span class="input-group-btn">
                                        <button class="btn-u" type="submit">Go</button>
                                    </span>
                            </div>
                            </form>
                        </div>
                    </li>
                    <!-- End Search Block -->
                </ul>
            </div>
            <!--/navbar-collapse-->
        </div>
    </div>
    <!-- End Navbar -->
</div>
<!--=== End Header ===-->
<?php echo \helper\htmlElement("login-form", Array("errorID" => $errorID, "community" => NULL)); ?>
