<?php
    if(!$footer_component && !empty($components["footer"])) {
        $footer_component = $components["footer"][0];
    }
?>
<style>
    /* footer */
    .footer-v2 .copyright a, .footer-v2 .copyright a:hover,.footer a {
        color: <?php echo '#'.Component::getColorStatic($footer_component, $community, 1); ?>;
    }
    .footer .headline h2, .footer .headline h3, .footer .headline h4 {
        border-bottom: 2px solid <?php echo '#'.Component::getColorStatic($footer_component, $community, 1); ?>;
    }
    .footer-v2 .footer .dl-horizontal a:hover {
        color: <?php echo '#'.Component::getColorStatic($footer_component, $community, 1); ?> !important;
    }
    .posts .dl-horizontal:hover dt img,.posts .dl-horizontal:hover dd a {
        color: <?php echo '#'.Component::getColorStatic($footer_component, $community, 1); ?> !important;
        border-color: <?php echo '#'.Component::getColorStatic($footer_component, $community, 1); ?> !important;
    }
</style>
<div class="footer-v2 <?php if ($vars['editmode']) echo 'editmode' ?>">
    <div class="footer">
        <div class="container">
            <div class="row">
                <!-- About -->
                <div class="col-md-3 md-margin-bottom-40">
                    <a href="index.html"><?php echo $community->shortName?></a>
                    <p class="margin-bottom-20"><?php echo $community->description?></p>
                </div><!---->

                <!-- Link List -->
                <div class="col-md-3 md-margin-bottom-40">
                    <div class="headline"><h2>Useful Links</h2></div>
                    <ul class="list-unstyled link-list">
                <?php
                if ($component->text3 && $component->text3 != '' && $component->text2 && $component->text2 != '') {
                    $splits = explode(',', $component->text3);
                    $link = trim($link);
                    $splits2 = explode(',', $component->text2);
                    foreach($splits as $key=>$link){
                        echo ' <li><a href="' . $link . '"> ' . $splits2[$key]. '</a><i class="fa fa-angle-right"></i></li>';
                    }
                }?>
                    </ul>
                </div><!--/col-md-3-->
                <!-- End Link List -->

                <?php
                $social = array('facebook.com', 'twitter.com', 'plus.google.com', 'youtube.com', 'linkedin.com', 'dropbox.com', 'blogspot.com');
                $html = array('data-original-title="Facebook" class="social_facebook"', 'data-original-title="Twitter" class="social_twitter"', 'data-original-title="Google Plus" class="social_googleplus"', 'data-original-title="Youtube" class="social_youtube"');
                if ($component->text1 && $component->text1 != '') {
                    $split = explode(',', $component->text1);
                    $tag = trim($tag);
                    foreach ($split as $tag) {
                        foreach ($social as $i => $part) {
                            $split2 = explode($part, $tag);
                            if (count($split2) > 1) {
                                $media[] = ' <li><a href="' . $tag . '" ' . $html[$i] . '></a></li>';
                            }
                        }
                    }
                }
                ?>
                <!-- Latest New Entries -->
                <div class="col-md-3 md-margin-bottom-40">
                    <div class="posts">
                        <div class="headline"><h2>Latest Posts</h2></div>
                        <?php
                        $holder = new Component_Data();
                        $datas = $holder->searchAllFromComm('', $community->id, 0, 3);
                        foreach ($datas['results'] as $data) {
                            $comp = new Component();
                            if ($data->component == 104)
                                $url = '/'.$community->portalName . '/about/faq/questions/'.$data->id;
                            elseif ($data->component == 105)
                                $url = '/'.$community->portalName . '/about/faq/tutorials/'.$data->id;
                            else {
                                $comp->getByType($community->id, $data->component);
                                $url = '/'.$community->portalName.'/about/'.$comp->text2.'/'.$data->id;
                            }
                            echo '<dl class="dl-horizontal">';
                            if ($data->image)
                                echo '<dt><a href="' . $url . '"><img src="/upload/community-components/' . $data->image . '" alt=""></a></dt>';
                            else
                                echo '<dt><a href="' . $url . '"><img src="/upload/community-logo/' . $community->logo . '" alt=""></a></dt>';
                            echo '<dd><p><a href="' . $url . '">' . $data->title . '</a></p></dd>';
                            echo '</dl>';
                        }
                        ?>
                    </div>
                </div>
                <!-- End Latest Tweets -->

                <!-- Address -->
                <div class="col-md-3 md-margin-bottom-40">
                    <div class="headline"><h2 class="heading-sm">Contact Us</h2></div>
                    <address class="md-margin-bottom-40">
                        <?php echo $community->address?>
                    </address>

                    <!-- Social Links -->
                    <!-- Social Links -->

                    <?php
                    if (count($media) > 0) {
                        ?>
                        <div class="headline"><h2>Stay Connected</h2></div>
                        <ul class="social-icons">
                            <?php echo join('', $media); ?>
                        </ul>
                    <?php } ?>
                    <!-- End Social Links -->
                </div><!--/col-md-3-->
                <!-- End Address -->
            </div>
        </div>
    </div><!--/footer-->

    <div class="copyright">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>
                        <a target="_blank" href="/page/scicrunch">About SciCrunch</a> | <a href="/page/privacy">Privacy
                            Policy</a> | <a href="/page/terms">Terms of Service</a>
                    </p>
                </div>
                <div class="col-md-6">
                    <a href="https://scicrunch.org/" class="pull-right">
                        <h3 class="pull-right" style="display: inline-block;color:#fff">SciCrunch</h3>
                        <img class="pull-right" style="height:30px" src="/images/scicrunch.png" alt="">
                    </a>
                </div>
            </div>
        </div>
    </div><!--/copyright-->
    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        echo '<button class="btn-u btn-u-default edit-body-btn" componentType="other" componentID="' . $component->id . '"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button></div>';
        echo '</div>';
    } ?>
</div>

<div class="notifications">
</div>

<div class="note-load" style="display: none"></div>
<script type="text/javascript">
    window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
        d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
        _.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute('charset','utf-8');
        $.src='//v2.zopim.com/?2CsONjIPURBMECjYLIRkz9JVf7erv9vw';z.t=+new Date;$.
            type='text/javascript';e.parentNode.insertBefore($,e)})(document,'script');
    $zopim(function(){
        $zopim.livechat.setGreetings({
            "online":"Contact help desk",
            "offline":"Contact help desk"
        });
        <?php if(isset($_SESSION["user"])): ?>
            $zopim.livechat.setName("<?php echo $_SESSION["user"]->getFullName() ?>");
            $zopim.livechat.setEmail("<?php echo $_SESSION["user"]->email ?>");
        <?php endif ?>
        $zopim.livechat.window.setTitle("Contact help desk");
        $zopim.livechat.offlineForm.setGreetings("Please leave a description of the issue and, if applicable, a URL where the issue occurs.");
    });
</script>
