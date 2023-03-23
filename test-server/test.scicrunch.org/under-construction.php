<?php
$tab = 2;
include 'classes/classes.php';
\helper\scicrunch_session_start();
$_SESSION["web"] = true;

$type = filter_var($_GET['type'], FILTER_SANITIZE_STRING);

$portalName = filter_var($_GET['name'], FILTER_SANITIZE_STRING);

if ($type != '404') {
    $community = new Community();
    $community->getByPortalName($portalName);

    if ($community->private != 1 || (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 0)) {
        header('location:/' . $portalName);
        exit();
    }
}

switch ($type) {
    case 'private':
        $title = 'Private Community';
        break;
    case '404':
        $title = 'Community Not Found';
        break;
}

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title>SciCrunch | <?php echo $title ?></title>

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

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">

    <!-- CSS Page Style -->
    <link rel="stylesheet" href="/assets/css/pages/page_error3_404.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
    <!--[if lt IE 9]>
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/sky-forms-ie8.css">
    -->
</head>

<body>
<?php echo \helper\topPageHTML(); ?>

<!--=== Error V3 ===-->
<div class="container content">
    <!-- Error Block -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="error-v3">

                <p>Sorry, SciCrunch is not available right now.</p>
            </div>
        </div>
    </div>
    <!-- End Error Block -->
</div>
<!--=== End Error-V3 ===-->

<!--=== Sticky Footer ===-->
<!--=== End Sticky-Footer ===-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<script type="text/javascript" src="/assets/plugins/backstretch/jquery.backstretch.min.js"></script>
<script type="text/javascript">
    $.backstretch([
        "/assets/img/blur/img1.jpg"
    ])
</script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>
    });
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("under-construction");
    });
</script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->

</body>
</html>
