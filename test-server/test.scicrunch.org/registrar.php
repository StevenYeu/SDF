<?php
$tab = 0;
$hl_sub = 0;
include 'vars/overview.php';
$holder = new Component();
$components = $holder->getByCommunity(0);

$vars['editmode'] = filter_var($_GET['editmode'],FILTER_SANITIZE_STRING);
if($vars['editmode']){
    if(!isset($_SESSION['user']) || $_SESSION['user']->levels[$community->id]<2)
        $vars['editmode'] = false;
}

$vars['errorID'] = filter_var($_GET['errorID'],FILTER_SANITIZE_NUMBER_INT);
if($vars['errorID']){
    $errorID = new Error();
    $errorID->getByID($vars['errorID']);
    if(!$errorID->id){
        $errorID = false;
    }
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
    <title>SciCrunch | Register an Account</title>

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

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<div class="wrapper">
    <!--=== Header ===-->

    <?php include 'ssi/header.php'; ?>
    <div class="breadcrumbs-v3">
        <div class="container">
            <ul class="pull-left breadcrumb">
                <li><a href="/">Home</a></li>
                <li class="active">Registration</li>
            </ul>
            <h1 class="pull-right">Registration</h1>
        </div>
    </div>

    <div class="container content">
        <div class="row">
            <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
                <form class="reg-page sky-form captcha-form" method="post" action="/forms/register.php">
                    <input type="hidden" name="referer" value="<?php echo urlencode(\helper\sanitizeHTMLString($_GET["referer"])) ?>" />
                    <div class="reg-header">
                        <h2>Register a new account</h2>
                        <p>Already registered? <a href="javascript:void(0)" class="color-green btn-login" style="text-decoration: underline">Log in</a> your account.</p>
                    </div>

                    <?php if(isset($_GET["altcaptchaerror"])): ?>
                        <div class="alert alert-danger">
                            The entered text did not match the image below
                        </div>
                    <?php endif ?>

                    <section>
                        <label class="label">First Name <span class="color-red">*</span></label>
                        <label class="input">
                            <input type="text" required="required" name="firstname">
                        </label>
                    </section>

                    <section>
                        <label class="label">Last Name <span class="color-red">*</span></label>
                        <label class="input">
                            <input type="text" required="required" name="lastname">
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
                            <label>Password <span class="color-red">*</span></label>
                            <input type="password" name="password" class="sign-up-password form-control margin-bottom-20" required>
                        </div>
                        <div class="col-sm-12">
                            <label>Confirm Password <span class="color-red">*</span></label>
                            <input type="password" name="password2" class="sign-up-password form-control margin-bottom-20" required>
                        </div>
                    </div>

                   <section>
                       <label class="label">Organization</label>
                        <label class="input">                           <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" placeholder="Focus to view the tooltip" name="organization">
                            <b class="tooltip tooltip-top-right">The organization you are affiliated with (ie UCSD, Scripps, etc)</b>
                       </label>
                    </section>

<!-- Manu
                    <section>
                        <div id="g-recaptcha"
                             data-sitekey="<?php echo CAPTCHA_KEY ?>"></div>
                        <div id="recaptcha-error">
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
-->
                    <hr>

                    <div class="row">
                        <div class="col-lg-6">
                            <label class="checkbox">
                                <input type="checkbox" name="terms" class="sign-up-checkbox" required style="top: 3px; left: 30px;">
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
    </div><!--/container-->

    <?php
        $holder = new Component();
        $components = $holder->getByCommunity(0);
        echo Component::footerHTML($components);
    ?>

</div>
<script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<script src="/assets/plugins/scrollbar/src/jquery.mousewheel.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>

<!-- Manu
<script>
    function captchaCallback() {
        $("#recaptcha-error").hide();
        grecaptcha.render('g-recaptcha', {sitekey: '<?php echo CAPTCHA_KEY ?>'});
    }
</script>
<script src='https://www.google.com/recaptcha/api.js?onload=captchaCallback&render=explicit'></script>
Manu -->

<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("registrar");

    });
</script>

</body>
</html>
