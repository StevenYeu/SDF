<?php
    $errorID = $data["errorID"];
    $community = $data["community"];

    if(is_null($community) || $community->id == 0) {
        $create_account_link = "/register";
    } else {
        $create_account_link = "/" . $community->portalName . "/join";
    }

    $orcid_authorize_url = "https://orcid.org/oauth/authorize?client_id=" . ORCID_CLIENT_ID . "&response_type=code&scope=/authenticate&redirect_uri=" . PROTOCOL . "://" . FQDN . "/forms/login-orcid.php";
?>

<div class="container">
    <form method="post" action="/forms/login.php">
        <div class="login-box">
            <div class="reg-block-header">
                <h2>Log in</h2>
            </div>

            <?php if($errorID && $errorID->type=='login-fail'){?>
                <div class="alert alert-danger">
                    <?php
                    echo $errorID->message;
                    $errorID->setSeen();
                    ?>
                </div>
            <?php } ?>

            <div class="input-group margin-bottom-20">
                <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                <input type="text" class="form-control" name="email" placeholder="Email">
            </div>
            <div class="input-group margin-bottom-20">
                <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                <input type="password" class="form-control" name="password" placeholder="Password">
            </div>
            <a class="referer-link" href="<?php echo $create_account_link ?>">Create an account</a><br/>
            <span class="forgot-password-page-button" style="cursor:pointer;color:#72c02c" title="Forgot Password">Forgot password?</span><br/>
            <div class="forgot-password-page" style="display:none">
                <div id="sky-form4" class="sky-form" novalidate="novalidate">
                    <header>Forgot Password</header>
                    <fieldset>
                        <p>
                            If you have forgotten your password you can enter your email here and get a temporary password
                            sent to your email.
                            <div class="forgot-pw-container">
                                <form class="forgot-pw-form">
                                    <div class="input-group">
                                        <input type="text" class="form-control forgot-email" name="query" placeholder="Account Email"/>
                                        <span class="input-group-btn">
                                            <button class="btn-u" type="submit">Send</button>
                                        </span>
                                    </div>
                                </form>
                            </div>
                        </p>
                    </fieldset>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <button type="submit" class="btn-u btn-block">Log In</button>
                </div>
            </div>
        </div>
    </form>
    <!--End Reg Block-->
</div><!--/container-->
<script>
    $(function() {
        $(".forgot-password-page-button").on("click", function() {
            $(".forgot-password-page").toggle();
        });
    });
</script>
