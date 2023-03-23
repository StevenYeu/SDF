<?php
    if (isset($_GET['errorID'])) {
        $errorID = new Error();
        $errorID->getByID(filter_var($_GET['errorID'], FILTER_SANITIZE_STRING));
        if(!$errorID->id){
            $errorID = false;
        }
    }
// can't find any code where $data["errorID"] is set ...
//    $errorID = $data["errorID"];
    $community = $data["community"];

    if(is_null($community) || $community->id == 0) {
        $create_account_link = "/register";
    } else {
        $create_account_link = "/" . $community->portalName . "/join";
    }

    $orcid_authorize_url = "https://orcid.org/oauth/authorize?client_id=" . ORCID_CLIENT_ID . "&response_type=code&scope=/authenticate&redirect_uri=" . PROTOCOL . "://" . FQDN . "/forms/login-orcid.php";
?>

<div class="container login-backing"
     style="<?php if($errorID && $errorID->type=='login-fail') echo 'display:block;'; else echo 'display:none;';?>position: fixed;left:0;top:0;width:100%;height:100%;z-index: 20000;background: rgba(0,0,0,.8)">
    <!--Reg Block-->
    <form method="post" action="/forms/login.php<?php if ($community->id) echo "?cid=" . $community->id; ?>">
        <div class="reg-block login-box">
            <div class="login-backing close dark">X</div>
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
            <span class="simple-toggle login-backing" modal=".forgot-password" style="cursor:pointer;color:#72c02c" title="Forgot Password">Forgot password?</span><br/>
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
<div class="back-hide forgot-password no-padding">
    <div class="close dark less-right">X</div>
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
