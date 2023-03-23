<?php
    if(isset($_GET["query"]) && $_GET["query"] !="") {
        $rrid = filter_var($_GET['query'], FILTER_SANITIZE_STRING);
        $rrid = str_replace('/', '$U+002F;', $rrid);
        $rrid_url = "/resolver/" . $rrid;
        header("location: " . $rrid_url);
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>SciCrunch | Research Resource Resolver</title>

        <!-- Meta -->
        <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="description" content="">
            <meta name="author" content="">
            <meta name="google-site-verification" content="vhe7FXQ5uQHNwM10raiS4rO23GgbFW6-iyRfapxGPJc" />

            <!-- Favicon -->
            <link rel="shortcut icon" href="/favicon.ico">

            <!-- CSS Global Compulsory -->
            <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
            <link rel="stylesheet" href="/assets/css/style.css">
            <link rel="stylesheet" href="/css/main.css">
            <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">

            <!-- CSS Implementing Plugins -->
            <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
            <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">

            <!-- CSS Page Style -->
            <link rel="stylesheet" href="/assets/css/pages/page_search_inner.css">
            <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
            <link rel="stylesheet" href="/assets/css/shop/shop.blocks.css">

            <!-- CSS Theme -->
            <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">

            <!-- CSS Customization -->
            <link rel="stylesheet" href="/assets/css/custom.css">
            <link rel="stylesheet" href="/js/Highcharts-6.0.7/code/css/highcharts.css">

            <!-- JS Global Compulsory -->
            <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
            <script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
            <script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>

            <script type="text/javascript" src="/assets/plugins/gmap/gmap.js"></script>
            <script type="text/javascript" src="/js/main.js"></script>
            <script type="text/javascript" src="/js/jquery.truncate.js"></script>
            <!-- JS Implementing Plugins -->
            <script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
            <!-- JS Page Level -->
            <script type="text/javascript" src="/assets/js/app.js"></script>
            <script src='https://www.google.com/recaptcha/api.js'></script>
            <!-- angular -->
            <script src="/js/angular-1.7.9/angular.min.js"></script>
            <script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
            <script src="/js/angular-1.7.9/angular-sanitize.js"></script>
            <script src="/js/module-error.js"></script>
            <script src="/js/resolver.js"></script>

            <script src="/js/module-resource.js"></script>
            <script type="text/javascript" src="/js/Highcharts-6.0.7/code/js/highcharts.js"></script>
            <script type="text/javascript" src="/js/Highcharts-6.0.7/code/js/modules/series-label.js"></script>

    </head>
    <body>
        <?php echo \helper\topPageHTML(); ?>

        <div class="wrapper">
            <!-- Brand and toggle get grouped for better mobile display -->
            <?php include 'ssi/header.php' ?>
            <!--=== Breadcrumbs v3 ===-->
            <?php
                echo Connection::createBreadCrumbs('RRID Search', array('Home'), array('/scicrunch/'), 'Resolver ID Search');
            ?>
            <!--=== End Breadcrumbs v3 ===-->

            <!--=== Search Block Version 2 ===-->
            <div class="search-block-v2" style="padding:30px 0 38px">
                <div class="container">
                    <div class="col-md-6 col-md-offset-3">
                        <h2>Search Resolver ID</h2>

                        <form method="get" action="/resolver">
                            <div class="input-group">
                                <input type="text" class="form-control" name="query"
                                       placeholder="Please input resolver ID#" value="<?php echo $query ?>">
                        <span class="input-group-btn">
                            <button class="btn-u" type="search"><i class="fa fa-search"></i></button>
                        </span>
                            </div>
                        </form>
                        <p><br></p>
                        <div class="container well">
                            <h4>Resolver ID Example:</h4>
                            <ul>
                                <li>RRID:SCR_003070</li>
                                <li>SCR_003070</li>
                                <li>DOI:10.17504/protocols.io.c4ryv5</li>
                                <li>10.17504/protocols.io.c4ryv5</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!--/container-->
            <!--=== End Search Block Version 2 ===-->
        </div>

        <?php
            $holder = new Component();
            $components = $holder->getByCommunity(0);
            echo Component::footerHTML($components);
        ?>
    </body>
</html>
