<!-- <script src="https://unpkg.com/@ungap/custom-elements-builtin"></script>
<script type="module" src="https://unpkg.com/x-frame-bypass"></script> -->
<!-- CSS Global Compulsory -->
<link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/css/main.css">
<link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
<!-- angular -->
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

<?php if($rrid_url != ""): ?>
    <div class="row" style="background:#fcf8e3">
        <br>
        <div class="col-md-12">
            <div class="col-md-12">
                <h4 style="color:#8a6d3b"><b>You are being redirected to the external resource authority website. If you haven't been redirected in 10 seconds, please click <a target="_blank" href="<?php echo $rrid_url ?>"><?php echo $rrid_url ?></a>.</b></h4>
            </div>
        </div>
        <div class="col-md-12">
            <div class="col-md-10">
                <h4 style="color:#8a6d3b"><b>For additional information about this resource, such as mentions, alerts, rating and validation information, please view our Resource Report (shown below).</b></h4>
            </div>
            <div class="col-md-2">
                <a target="_blank" href="/resolver/<?php echo $rrid ?>?nodirect=true" class="btn btn-success">View Resource Report</a></p>
            </div>
        </div>
    </div>
    <div class="row" style="background:#585f69"><p><br></p></div>
    <!-- <div class="row">
        <div class="col-md-12">
            <iframe is="x-frame-bypass" src="<?php echo $rrid_url ?>" style="height:90%;width:100%;border:none;"></iframe>
        </div>
    </div> -->
    <div class="row">
        <div class="col-md-12">
          <?php
          if($rrid) {

              $community = new Community();
              $community->portalName = "scicrunch";

              echo \helper\htmlElement("rin/search-single-item", Array(
                  "view" => $view,
                  "rrid" => $rrid,
                  "tab" => $tab,
                  "community" => $community,
              ));
          }
          ?>
        </div>
    </div>
    <meta http-equiv="refresh" content="10;url=<?php echo $rrid_url ?>" />
<?php else: ?>
    <div class="row" style="background:#fcf8e3">
        <div class="col-md-12">
            <br><p style="color:#8a6d3b">Warning: No external resource.</p>
        </div>
    </div>
<?php endif ?>
