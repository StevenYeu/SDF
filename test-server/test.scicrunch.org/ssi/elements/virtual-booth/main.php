<?php
    $community = $data['community'];
?>

<style>
    p, li, span {
        color: white;
        font-family: Helvetica, sans-serif;
        font-size: 18px;
    }

    h1 {
        font-family: Helvetica, sans-serif;
        font-size: 40px;
    }

    h2 {
        color: white;
        font-family: Helvetica, sans-serif;
        font-size: 28px;
    }

    div.desktop-wrapper {
        position: relative;
        padding-top: 25px;
        padding-bottom: 67.5%;
        height: 0;
    }
    div.desktop-wrapper iframe {
        box-sizing: border-box;
        background: url(https://scicrunch.org/upload/community-components/imac_7f6ffaa6bb0b4080.png) center center no-repeat;
        background-size: contain;
        padding: 5% 10.8% 18.6%;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
</style>

<div style="text-align: center">
    <h1><a target="_blank" href="https://dknet.org/" style="color: #1c2d5c;"><i style="font-size: 40px"><b>Your Research Starts Here</b></i></a></h1>
</div>

<div style="background: #408DC9">
    <div class="row">
        <div class="col-md-6">
            <div class="row" style="margin: 40px 0 0 60px">
                <div class="row">
                    <div class="col-md-6">
                        <p><span><b>Resource Information</b></span></p>
                        <ul>
                            <li><b>Cell lines</li></b>
                            <li><b>Antibodies</li></b>
                            <li><b>Software tools</li></b>
                            <li><b>Organisms & more</li></b>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <p><span><b>Free Reports</b></span></p>
                        <ul>
                            <li><b>Rigor + Reproducibility</li></b>
                            <li><b>Resource Summaries</li></b>
                            <li><b>Resource Usage</li></b>
                            <li><b>FAIR data plan & more</li></b>
                        </ul>
                    </div>
                </div>
                <!-- <div class="row"> -->
                <p><br></p>
                <div class="row">
                    <div class="col-md-1">
                        <h2><img src="https://scicrunch.org/upload/community-components/email_76dc611d6ebaafc6.png" style="width: 40px"></h2>
                    </div>
                    <div class="col-md-11">
                        <h2><a target="_blank" href="https://dknet.org/about/maillist" style="color: white;"><b>Sign up for Newsletter</b></a></h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-1">
                        <h2><img src="https://scicrunch.org/upload/community-components/file_65b9eea6e1cc6bb9.png" style="width: 40px"></h2>
                    </div>
                    <div class="col-md-11">
                        <h2><a target="_blank" href="<?php echo $community->fullURL() ?>/virtual-booth/resources" style="color: white;"><b>Resources/Brochure</b></a></h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-1">
                        <h2><img src="https://scicrunch.org/upload/community-components/laptop_3636638817772e42.png" style="width: 40px"></h2>
                    </div>
                    <div class="col-md-11">
                        <h2><a target="_blank" href="https://dknet.org/about/webinar" style="color: white;"><b>Webinar</b></a></h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-1">
                        <h2><img src="https://scicrunch.org/upload/community-components/info_9fc3d7152ba9336a.png" style="width: 45px"></h2>
                    </div>
                    <div class="col-md-11">
                        <h2><a target="_blank" href="https://cdmcd.co/gQajbL" style="color: white;"><b>Video chat with us @EB2021</b></a></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div style="margin: 20px 20px 0 0">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="desktop-wrapper">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/-6QVHWnL_F8" frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="text-center">
                <a target="_blank" href="https://dknet.org/"><button class="btn btn-primary btn-lg" style="background:#1c2d5c;white-space: normal; font-size: 32px">&nbsp;&nbsp;&nbsp;Visit dkNET.org Now!&nbsp;&nbsp;&nbsp;</button></a>
            </div>
        </div>
        <div class="col-md-3">
            <a target="_blank" href="https://twitter.com/dkNET_Info"><img src="https://scicrunch.org/upload/community-components/square-twitter_a3c65c2974270fd0.png" style="width: 50px"></a>&nbsp;&nbsp;&nbsp;
            <a target="_blank" href="https://www.youtube.com/channel/UCwukSrB8L61Fhwjv3x20lOQ"><img src="https://scicrunch.org/upload/community-components/youtube_a4a042cf4fd6bfb4.png" style="width: 50px"></a>&nbsp;&nbsp;
            <a target="_blank" href="https://www.facebook.com/dkNET.org/"><img src="https://scicrunch.org/upload/community-components/facebook_6974ce5ac660610b.png" style="width: 50px"></a>&nbsp;&nbsp;
            <a target="_blank" href="mailto:info@dknet.org"><img src="https://scicrunch.org/upload/community-components/email_82aa4b0af34c2313.png" style="width: 55px"></a>
        </div>
    </div>
    <p><br><br></p>
</div>
