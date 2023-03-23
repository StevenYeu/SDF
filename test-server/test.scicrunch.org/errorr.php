<?php
$tab = 2;

if ($type == 'private' && !is_null($data)) {
    $community = new Community();
    $community->getByPortalName($data);

    if ($community->private != 1 || (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 0)) {
        header('location:/' . $data);
        exit();
    }

    $car = CommunityAccessRequest::loadBy(Array("uid", "cid"), Array($_SESSION['user']->id, $community->id));
    if ($car && $car->_get_status() === CommunityAccessRequest::STATUS_PENDING) {
        $type = "pending";
    }
}

$feedback_submitted = isset($_GET["feedback"]);

switch ($type) {
    case 'private':
        $title = 'Private Community';
        $header_text = "Private";
        $body_text = "<b>" . $community->name . "</b> is a private community. Only current members may enter at this time. If you are a current member log in and you will be directed there.";
        $status_code = 403;
        break;
    case 'pending':
        $title = 'Pending Approval';
        $header_text = $title;
        $body_text = "Your request for <b>" . $community->name . "</b> has been submitted and is pending approval. We will notify you once the owner has approved your request.";
        $status_code = 403;
        break;
    case '404':
        $title = 'Community Not Found';
        $header_text = "404";
        $body_text = "Sorry, there does not exist a community with the url <b>/" . $data . "</b>.";
        $status_code = 404;
        break;
    case 'nopmid':
        $title = 'PMID Not Found';
        $header_text = "404";
        $body_text = "Sorry, there does not exist a PMID <b>" . $data . "</b>.";
        $status_code = 404;
        break;
    case 'noresource':
        $title = "Resource not found";
        $header_text = "404";
        $body_text = "Sorry, there doesn't seem to be a resource with that ID.";
        $status_code = 404;
        break;
    case 'drupal':
        $title = "Not supported";
        $header_text = "405";
        $body_text = "The commands you have submitted are not supported by this site";
        $status_code = 405;
        break;
    case "archived":
        $title = "Archived";
        $header_text = "410";
        $body_text = "The community you requested has been archived";
        $status_code = 410;
        break;
    case "rrid-report":
        $title = "Not found";
        $header_text = "404";
        $body_text = "Authentication report not found";
        $status_code = 404;
        break;
    case "source-id":
        $title = "Not found";
        $header_text = "404";
        $body_text = "Data source ID is not existed.";
        $status_code = 404;
        break;
    case "source-config":
        $title = "Not found";
        $header_text = "404";
        $body_text = "Data source configuration is not existed.";
        $status_code = 404;
        break;
    case "400":
        $title = "400";
        $header_text = "Error";
        $body_text = "An error has occured";
        $status_code = 400;
        break;
    case "404-generic":
    default:
        $title = "404";
        $header_text = "Not found";
        $body_text = "The page you requested doesn't exist";
        $status_code = 404;
        break;
}

header("X-PHP-Response-Code: " . $status_code, true, $status_code);

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title><?php /* Manu */ echo $community->shortName ?> | <?php echo $title ?></title>

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
    <link rel="stylesheet" href="/css/main.css" />
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css" />

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>

    <style>
        .error-page-modal {
            display: none;
            text-align: left;
        }
    </style>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>

<!--=== Error V3 ===-->
<div class="container content">
    <!-- Error Block -->
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="error-v3">
                <h2><?php echo $header_text ?></h2>
                <p><?php echo $body_text ?></p>
            </div>
        </div>
    </div>
    <!-- End Error Block -->

    <!-- Begin Service Block V2 -->
    <div class="row service-block-v2">

<!-- Manu
        <div class="col-md-4">
            <div class="service-block-in service-or">
                <div class="service-bg"></div>
                <i class="icon-bulb"></i>
                <h4>Find New Communities</h4>

                <p>
                    Browse around and visit other communities. We have communities covering all scientific areas. Or you can create your own community.
                </p>
                <a class="btn-u btn-brd btn-u-light" href="<?php echo PROTOCOL . "://" . FQDN ?>/browse/communities"> Discover More</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="service-block-in service-or">
                <div class="service-bg"></div>
                <i class="icon-directions"></i>
                <h4>Browse Resources</h4>

                <p>
                    Search our extensive collection of scientific resources. Let our curated list of thousands of resources aid your research.
                </p>
                <a class="btn-u btn-brd btn-u-light" href="<?php echo PROTOCOL . "://" . FQDN ?>/browse/resourcedashboard"> See Resources</a>
            </div>
        </div>
-->

	<!-- Manu  added the below line instead of this div class="col-md-4" -->

        <div class="col-md-offset-4 col-md-4">
            <div class="service-block-in service-or">
                <?php if(!isset($_SESSION['user'])): ?>
                    <?php
                        // if($type == "private") {
                        //     $register_link = "/" . $community->portalName . "/join";
                        // } else {
                        //     $register_link = "/register";
                        // }
                        $cilogon_link = "https://cilogon.org/authorize?response_type=code&client_id=cilogon:/client_id/7cb0c6760f8bc24d65671e14e7a0071b&redirect_uri=https://sdf.sdsc.edu/auth/cilogon&scope=openid+profile+email+org.cilogon.userinfo+edu.uiuc.ncsa.myproxy.getcert";
                    ?>
                    <h4>Sign In with CILogon</h4>
                    <a href="<?php echo $cilogon_link ?>" class="btn-u btn-brd btn-u-light">CILogon</a>
                    <!-- <h4>Don't have an account?</h4>
                    <a href="" class="btn-u btn-brd btn-u-light">Register</a>
                    <br/><br/>
                    <h4>Already have an account?</h4>
                    <a href="javascript:void(0)" class="btn-login btn-u btn-brd btn-u-light">Login</a> -->
                <?php elseif($type == "private"): ?>
                <?php elseif($type == "private"): ?>
                    <div class="service-bg"></div>
                    <i class="icon-users"></i>
                    <h4>Request to join</h4>
                    <p>Send a request to the community owner to join</p>
                    <a id="join-request-button" class="btn-u btn-brd btn-u-light" href="javascript:void(0)">Request</a>
                    <div id="join-request-modal" class="modal fade error-page-modal">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h2 class="modal-title">Request to join <?php echo $community->name ?></h2>
                                </div>
                                <div class="modal-body">
                                    <form class="sky-form" action="/forms/community-forms/user-request.php" method="POST">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>
                                                    Please describe your interest in this community
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <textarea name="message" rows="5"></textarea>
                                            </div>
                                        </div>
                                        <input type="hidden" name="cid" value="<?php echo $community->id ?>" />
                                        <div class="row">
                                            <div class="col-md-6 col-md-offset-6">
                                                <input type="submit" class="btn btn-primary" value="Submit" />
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        $("#join-request-button").on("click", function() {
                            $("#join-request-modal").modal("show");
                        })
                    </script>
                <?php else: ?>
                    <div class="service-bg"></div>
                    <i class="icon-pie-chart"></i>
                    <h4>Search Data</h4>
                    <p>Search through data collected from over 100 data sources.</p>
                    <a class="btn-u btn-brd btn-u-light" href="<?php echo PROTOCOL . "://" . FQDN ?>/browse/datadashboard"> Search Data</a>
                <?php endif ?>
            </div>
        </div>
    </div>
    <!-- End Service Block V2 -->
</div>

<!-- Manu
<div class="container content">
    <div class="row service-block-v2">
        <div class="col-md-offset-4 col-md-4">
            <div class="service-block-in service-or">
                <p>If you believe you reached this page in error, please contact us so our support team can help you solve the problem.</p>
                <a href="javascript:void(0)" id="feedback-error-modal-btn" class="btn-u btn-brd btn-u-light">Leave feedback</a>
            </div>
        </div>
    </div>
</div>

-->

<!-- feedback modals -->
<div id="feedback-error-modal" class="modal fade error-page-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Feedback</h2>
            </div>
            <div class="modal-body">
                <form action="/forms/scicrunch-forms/submit-feedback.php" method="POST">
                    <?php if(!isset($_SESSION["user"])): ?>
                        <div class="form-group">
                            <input class="form-control" type="text" name="name" placeholder="Name"/>
                        </div>
                    <?php endif ?>
                    <div class="form-group">
                        <textarea required class="form-control" name="feedback" placeholder="Feedback"></textarea>
                    </div>
                    <input type="submit" value="Submit" class="btn btn-primary" />
                </form>
            </div>
        </div>
    </div>
</div>
<div id="feedback-submitted-modal" class="modal fade error-page-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p>
                    Thank you for your feedback
                </p>
            </div>
        </div>
    </div>
</div>
<!-- /feedback modals -->


<?php echo \helper\htmlElement("login-form", Array("errorID" => $errorID, "community" => $community)); ?>
<!--=== End Error-V3 ===-->

<!--=== Sticky Footer ===-->
<!--=== End Sticky-Footer ===-->

<!-- JS Global Compulsory -->
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

    $("#feedback-error-modal-btn").on("click", function() {
        $("#feedback-error-modal").modal("show");
    })

    <?php if($feedback_submitted): ?>
        $("#feedback-submitted-modal").modal("show");
    <?php endif ?>
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("errorr");
    });
</script>
<script type="text/javascript" src="/js/main.js"></script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->

</body>
</html>
