<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', '<?php echo GACODE ?>', 'auto');
    <?php if(!is_null($community->ga_code)): ?>ga('create', '<?php echo $community->ga_code ?>', 'auto', 'community'); <?php endif ?>
    ga('send', 'pageview');
    ga('community.send', 'pageview');

</script>

<?php if($community->portalName == "odc-sci" || $community->portalName == "odc-tbi"): ?>
    <script>
        (function (u, s, e, r, g) {
            u[r] = u[r] || [];
            u[r].push({
              'ug.start': new Date().getTime(), event: 'embed.js',
            });
            var f = s.getElementsByTagName(e)[0],
                j = s.createElement(e);
            j.async = true;
            j.src = 'https://static.userguiding.com/media/user-guiding-'
             + g + '-embedded.js';
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'userGuidingLayer', '72143837ID');
    </script>
<?php endif ?>

<?php if (!isset($_SESSION['user'])) { ?>
    <!-- Modified Steven replaced register and login buttons with CILogon -->
    <li><a href="<?php echo "https://cilogon.org/authorize?response_type=code&client_id=cilogon:/client_id/7cb0c6760f8bc24d65671e14e7a0071b&redirect_uri=https://sdf.sdsc.edu/auth/cilogon&scope=openid+profile+email+org.cilogon.userinfo+edu.uiuc.ncsa.myproxy.getcert" ?>" class="btn-login">Login with CILogon</a></li>
<?php } else { ?>
    <?php
    if ($_SESSION['user']->levels[$community->id] > 1) {
        $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $splits = explode('&', $actual_link);
        if (count($splits) > 1) {
            $base = str_replace('&editmode=true', '', $actual_link);
            $url = str_replace('&editmode=true', '', $actual_link) . '&';
        } else {
            $base = str_replace('?editmode=true', '', $actual_link);
            $url = '?';
        }
        if ($vars['editmode']) {
            if ($tab <= 0 && $hl_sub <= 0)
                echo '<li><a href="javascript:void(0)" class="component-add"><i class="fa fa-plus"></i> Add Component</a></li>';
            echo '<li><a href="' . $url . '"><i class="fa fa-times"></i> Exit Edit Mode</a></li>';
        } else
            echo '<li><a href="' . $url . 'editmode=true">Edit Mode</a></li>';
    }
    if ($_SESSION['user']->levels[$community->id] < 1) {
        echo "<li>";
        echo \helper\htmlElement("join-community", Array(
            "community" => $community,
            "text" => "Join Community",
        ));
        echo "</li>";
    } else {
        echo '<li><a href="' . Community::fullURLStatic($community) . '/account">Welcome back, ' . $_SESSION["user"]->firstname . '</a></li>';
    }
    ?>
    <li><a href="/forms/logout.php">Logout</a></li>
<?php } ?>
