<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

if (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 0) {
    header('location:/' . $community->portalName);
    exit();
}

$search = new Search();
$search->community = $community;

$components = $community->components;

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title><?php echo $community->shortName ?> | Welcome...</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo \helper\metaDescription($community->description) ?>">
    <meta name="author" content="">
    <meta name="google-site-verification" content="vhe7FXQ5uQHNwM10raiS4rO23GgbFW6-iyRfapxGPJc" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- CSS Global Compulsory -->
    <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/css/community-search.css">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/plugins/flexslider/flexslider.css">
    <link rel="stylesheet" href="/assets/plugins/parallax-slider/css/parallax-slider.css">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v1.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/css/main.css"/>

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">
    <link rel="stylesheet" href="/assets/css/custom.css">

    <script type="text/javascript" src="/assets/plugins/jquery-1.10.2.min.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<div class="wrapper" <?php if ($vars['editmode']) echo 'style="margin-top:32px;"' ?>>
    <!--=== Header ===-->

    <?php
    //Header
    echo \helper\htmlElement("components/header", Array(
        "community" => $community,
        "component" => $components["header"][0],
        "vars" => $vars,
        "tab" => $tab,
        "hl_sub" => $hl_sub,
        "ol_sub" => $ol_sub,
    ));

    //Body

    if (count($components['breadcrumbs']) > 0 && !$community->rinStyle()) {
        $component = $components['breadcrumbs'][0];
        if (!$component->disabled) {
            ob_start();
            include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/breadcrumbs.php';
            $breadcrumb_component = ob_get_clean();
        }
    }?>

    <?php ob_start(); ?>
    <div class="container content">
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6 text-center">
                <p class="grey-boxed">Already have an account? <a href="#" class="btn-login">Login</a></p>
                <form class="reg-page sky-form captcha-form" method="post" action="/forms/register.php?join=true&cid=<?php echo $community->id ?>">
                    <input type="hidden" name="referer" value="<?php echo urlencode(\helper\sanitizeHTMLString($_GET["referer"])) ?>" />
                    <div class="reg-header">
                        <h2>Register a new account and Join</h2>
                    </div>

                    <?php if(isset($_GET["altcaptchaerror"])): ?>
                        <div class="alert alert-danger">
                            The entered text did not match the image below
                        </div>
                    <?php endif ?>

<?php if (strtolower(substr($community->shortName, 0, 3)) == "odc"): ?>
                    <section>
                        <label class="label">ORCID</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
    <?php if (isset($_SESSION['orcid']['id'])): ?>
                            <input type="text" name="orcid_id" value="<?php echo $_SESSION['orcid']['id']; ?>" readonly >
    <?php else: ?>
                        <a href="https://orcid.org/oauth/authorize?client_id=<?php echo ORCID_CLIENT_ID; ?>&response_type=code&scope=/authenticate&redirect_uri=<?php echo PROTOCOL; ?>://<?php echo \helper\httpHost() ?>/forms/associate-orcid.php?cid=<?php echo $community->id ?>/join"><button class="btn btn-primary" style="float: left">Associate ORCID iD</button></a>
    <?php endif; ?>
<?php endif; ?>
                            <b class="tooltip tooltip-top-right">ORCID</b>
                        </label>
                        <br style="clear: both;" />
                    </section>

                    <section>
                        <label class="label">First Name <span class="color-red">*</span></label>
                        <label class="input">
                            <input type="text" required="required" name="firstname" value="<?php if (isset($_SESSION['orcid']['firstname'])) echo $_SESSION['orcid']['firstname']['value']; ?>">
                        </label>
                    </section>

                    <section>
                        <label class="label">Last Name <span class="color-red">*</span></label>
                        <label class="input">
                            <input type="text" required="required" name="lastname" value="<?php if (isset($_SESSION['orcid']['lastname'])) echo $_SESSION['orcid']['lastname']['value']; ?>">
                        </label>
                    </section>

                    <section>
                        <label class="label">Email <span class="color-red">*</span></label>
                        <label class="input">
                            <input type="text" class="sign-up" required="required" name="email">
                        </label>
                    </section>

                    <div class="row">
                        <div class="col-sm-12">
                            <label style="float:left;">Password <span class="color-red">*</span></label>
                            <input type="password" name="password" class="sign-up-password form-control margin-bottom-20" required>
                        </div>
                        <div class="col-sm-12">
                            <label style="float:left;">Confirm Password <span class="color-red">*</span></label>
                            <input type="password" name="password2" class="sign-up-password form-control margin-bottom-20" required>
                        </div>
                    </div>

<?php if (strtolower(substr($community->shortName, 0, 3)) == "odc"): ?>
                    <section>
                        <label class="label">Organization <span class="color-red">*</span></label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="organization" required="required" value="<?php if (isset($_SESSION['orcid']['organization'])) echo $_SESSION['orcid']['organization']; ?>" >
                            <b class="tooltip tooltip-top-right">The organization you are affiliated with (ie UCSD, Scripps, etc) <a href="URL">How to submit</a>ddd</b>
                        </label>
                    </section>
    <?php if (isset($_SESSION['orcid']['id']) && sizeof($_SESSION['orcid']['works_count'])): ?>
                    <section>
                        <label class="label">Lab Website</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="website">
                            <b class="tooltip tooltip-top-right">Lab Website</b>
                        </label>
                    </section>
    <?php else: ?>
                    <section>
                        <label class="label">Lab Website <span class="color-red">*</span></label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="website" required="required">
                            <b class="tooltip tooltip-top-right">Lab Website</b>
                        </label>
                    </section>
    <?php endif; ?>
<?php else: ?>

                    <section>
                        <label class="label">Organization</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="organization">
                            <b class="tooltip tooltip-top-right">The organization you are affiliated with (ie UCSD, Scripps, etc)  <a href="URL">How to submit</a></b>
                        </label>
                    </section>

<?php endif; ?>
                <?php
                    // if api_key AND default list, use mailchimp join
                    if ($community->mailchimp_api_key && $community->mailchimp_default_list):
                ?>
                    <section>
                        <label class="label"><?php echo $community->name; ?> provides channels for you to stay up to date. </label>
                        <span style="float: left;" ><input type="checkbox" value="<?php echo $community->id; ?>" id="mailchimp" name="mailchimp" checked /> Add me to the mailing list.</span><br />
                    </section>
                <?php endif; ?>

<!-- Manu 
                    <section>
                        <div id="g-recaptcha"
                             data-sitekey="<?php echo CAPTCHA_KEY ?>"></div>
                        <div id="recaptcha-error" style="text-align:left">
                            <img id="captcha-alt" src="/php/captcha-alt-image.php" alt="CAPTCHA Image" />
                            <label class="label">
                                Please enter the text in the image above
                                <?php if(isset($_GET["altcaptchaerror"])): ?>
                                    <div class="text-danger">
                                        The entered text did not match the image
                                    </div>
                                <?php endif ?>
                            </label>
                            <label class="input">
                                <input type="text" name="captcha-alt" size="10" maxlength="6" />
                                <a href="#" onclick="document.getElementById('captcha-alt').src = '/php/captcha-alt-image.php?' + Math.random(); return false;">[ Different Image ]</a>
                            </label>
                        </div>
                    </section>
Manu -->
                    <hr>

                    <div class="row">
                        <div class="col-lg-6">
                            <label class="checkbox">
                                <input class="sign-up-checkbox" type="checkbox" name="terms" required style="top: 3px; left: 30px;">
                                I have read the <a href="/page/terms" class="color-green" target="_blank">Terms and Conditions</a> and <a href="/page/privacy">Privacy Policy</a>.
                            </label>
                        </div>
                        <div class="col-lg-6 text-right">
                            <button class="btn-u" type="submit">Register</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--/container-->
    <?php

    $html_body = ob_get_clean();
    if($community->rinStyle()) {
        echo \helper\htmlElement("rin-style-page", Array(
            "title" => "Join " . $community->shortName,
            "breadcrumbs" => Array(
                Array("text" => "Home", "url" => $community->fullURL()),
                Array("text" => "Join " . $community->shortName, "active" => true),
            ),
            "html-body" => $html_body,
        ));
    } else {
        echo $breadcrumb_component;
        echo $html_body;
    }

    ?>

    <?php
    if (!isset($vars['stripped']) || $vars['stripped'] != 'true') {
        if (count($components['footer']) == 1) {
            $component = $components['footer'][0];
            if ($component->component == 92)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-normal.php';
            elseif ($component->component == 91)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-light.php';
            elseif ($component->component == 90)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-dark.php';
        } else
            include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-normal.php';
    } else echo '<div style="background:#fff;height:20px"></div>';
    ?>
    <div class="background"></div>
    <div class="component-add-load back-hide"></div>
    <div class="component-delete back-hide">
        <div class="close dark">X</div>
        <form method="post"
              id="component-delete-form" class="sky-form" enctype="multipart/form-data">
            <section>
                <p style="font-size: 18px;padding:40px">Are you sure you want to delete that component?</p>
            </section>
            <footer>
                <a href="javascript:void(0)" class="btn-u close-btn">No</a>
                <button type="submit" class="btn-u btn-u-default" style="">Yes</button>
            </footer>
        </form>
    </div>

</div>

<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<script src="/assets/plugins/scrollbar/src/jquery.mousewheel.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>
<script>
    function captchaCallback() {
        $("#recaptcha-error").hide();
        grecaptcha.render('g-recaptcha', {sitekey: '<?php echo CAPTCHA_KEY ?>'});
    }
</script>
<script src='https://www.google.com/recaptcha/api.js?onload=captchaCallback&render=explicit'></script>
<!-- JS Implementing Plugins -->

<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
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
        GATiming("community-join");
    });
</script>
</body>
</html>
