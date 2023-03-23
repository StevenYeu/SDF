<link rel="stylesheet" href="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.css">
<link rel="stylesheet" href="/assets/plugins/owl-carousel/owl-carousel/owl.theme.css">
<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script type="text/javascript" src="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.js"></script>
<script src="/js/term/highcharts5.0.9.js" type="text/javascript"></script>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<!-- <script src="/js/angular-1.7.9/angular-sanitize.js"></script> -->

<script src="/js/angular-chips/ui-bootstrap.js"></script>
<!-- <script src="/js/angular-chips/ui-bootstrap-tpls-0.14.3.js"></script> -->

<script src="/js/term/term.js"></script>
<script src="/js/term/term-dashboard.js"></script>

<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/api-classes/term/term_affiliates.php";
$affiliates = getTermAffiliates($_SESSION['user']);
//print_r($affiliates);
?>


<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Dashboard',array($home),array("/".$community->portalName),'Term Dashboard');
?>

<div class="search-block-v2" style="">
    <div class="container">
        <div class="col-md-3">
            <?php if($community->portalName != "scicrunch"): ?>
                <div class="row" style="width: 200px">
                    <img class="<?php echo $header_img_class ?>" src="https://scicrunch.org/upload/community-logo/<?php echo $community->logo ?>"/ style="width:100px"><br>
                    <?php echo $community->name ?>
                </div>
            <?php endif ?>
        </div>
        <div class="col-md-6">
            <h2 style="text-transform:uppercase;text-align:center;">Search for Terms</h2>
            <form method="get" action="/<?php echo $community->portalName?>/interlex/search">
                <div class="input-group">
                    <input class="form-control" name="q" placeholder="Search for terms" value="" type="text">
                    <span class="input-group-btn">
                        <button class="btn-u" type="search">
                        <i class="fa fa-search"></i>
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
<hr>

<div class="container content" ng-app="termDashboardApp" style="padding-top:10px">
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/templates/term/term-messages.html';?>

    <div class="row" ng-controller="termDashboardCtrl" ng-cloak>

        <div class="col-md-3 hidden-xs s-results related-search">
            <div class="col-md-12 vertical-line" style="padding-left:0px;margin-bottom:20px">
                <h3 style="font-size: 16px">CREATE YOUR OWN TERM</h3>
                <p>
                You can contribute to the SciCrunch term registry by submitting your own term.
                </p>
                <a class="btn btn-success" href="/<?php echo $community->portalName?>/interlex/create">Create a term</a>
            </div>

            <!-- <div class="col-md-12 vertical-line" style="padding-left:0px;margin-top:20px">
            <h3 style="font-size: 16px">UPLOAD YOUR TERMS</h3>
                <p>
                You can upload your terms written in a JSON formatted file into the SciCrunch term registry.
                </p>
                <a class="btn btn-success" href="/<?php echo $community->portalName?>/interlex/upload">Upload Terms</a>
            </div> -->

            <div class="col-md-12 vertical-line" style="padding-left:0px;margin-bottom:20px">
                <h3 style="font-size: 16px">TERM RELEASE NOTES</h3>
                <p>

                </p>
                <a class="btn btn-success" href="/<?php echo $community->portalName?>/interlex/release-notes">View release notes</a>
            </div>

            <div class="col-md-12 vertical-line" style="padding-left:0px;margin-bottom:20px">
                <h3 style="font-size: 16px">TERM ACTIVITY</h3>
                <p>

                </p>
                <a class="btn btn-success" href="/<?php echo $community->portalName?>/interlex/dashboard-history?origCid=&page=1&sort=desc">View term activity</a>
            </div>
            <?php if($_SESSION['user']->role > 0): ?>
                <div class="col-md-12 vertical-line" style="padding-left:0px;margin-bottom:20px">
                    <h3 style="font-size: 16px">TERM MAPPINGS</h3>
                    <p>

                    </p>
                    <a class="btn btn-success" href="/<?php echo $community->portalName?>/interlex/dashboard-mappings">View term mappings</a>
                </div>

                <div class="col-md-12 vertical-line" style="padding-left:0px;margin-bottom:20px">
                    <h3 style="font-size: 16px">TERM COMMENTS</h3>
                    <p>

                    </p>
                    <a class="btn btn-success" href="/<?php echo $community->portalName?>/interlex/dashboard-comments">View term comments</a>
                </div>

                <div class="col-md-12 vertical-line" style="padding-left:0px;margin-bottom:20px">
                    <h3 style="font-size: 16px">TERM LISTINGS</h3>
                    <p>

                    </p>
                    <a class="btn btn-success" href="/<?php echo $community->portalName?>/interlex/search-index">Browse terms</a>
                </div>
            <?php endif ?>
        </div>

        <div class="col-md-9">
        <div class="col-md-6">
        <p>
        The InterLex project - a core component of SciCrunch and supported by projects such as the Neuroscience Information Framework project (NIF),
        the NIDDK Information Network (dkNET), and the Open Data Commons for Spinal Cord Injury - is a dynamic lexicon of biomedical  terms.
        Unlike an encyclopedia, a lexicon provides the meaning of a term, and not all there is to know about it.
        InterLex is being constructed to help improve the way that biomedical scientists communicate about their data, so that information systems like
        NIF and dkNET can find data more easily and provide more powerful means of integrating that data across distributed resources.
        One of the big roadblocks to data integration in the biomedical sciences is the inconsistent use of terminology in databases and other
        resources such as the literature. When we use the same terms to mean different things, we cannot easily ask questions that span across
        multiple resources. For example, if three databases have information about what genes are expressed in cortex, but they all use different
        definitions of cerebral cortex, then it is hard to compare them. InterLex allows for the association of data values
        (i.e. the value of a field or text within a field) to terminologies enabling the crowdsourcing of data-terminology mappings.
        </p>

        <p>
        InterLex was built on the foundation of NeuroLex (see Larson and Martone 2013 <a href="https://www.ncbi.nlm.nih.gov/pmc/articles/PMC3757470/" target="_blank">
        Neurolex: An online framework for neuroscience knowledge</a>.
        Frontiers in Neuroinformatics, 7:18) and contains all of the existing NeuroLex terms. The initial entries in NeuroLex were built from the
        NIFSTD ontologies. NIFSTD currently has about 60,000 concepts (includes both classes and synonyms) that span gross anatomy, cells,
        subcellular structures, diseases, functions and techniques. InterLex models terms using primitives of the Web Ontology Language (OWL) and
        can export directly to a variety of standard ontology formats.
        </p>

        <p>
        A primary goal of interlex is to provide a stable layer on top of the many other existing terminologies, lexicons, and ontologies
        (i.e. provide a way to federate ontologies for data applications) and to provide a set of inter-lexical and inter-data-lexical mappings.
        In the future, InterLex will support user specific namespaces so that users can customize the exact definitions or ontologies they source from,
        as well as the relationships on those terms. Importantly, however, InterLex enforces a simple rule which is that terms which represent the same
        concept under the same superclass will maintain the same identifier fragment (i.e.  'ilx_1234567').
        However, each user will be able to 'fork' a term into their own namespace (e.g. http://uri.interlex.org/user/ilx_1234567).
        This enables the various perspectives on a term or concept to have equal space so that the full diversity of views on a term can be seen and expressed.
        Sign-up for updates to get notified about updates to InterLex and when new features are available.
        </p>

        <p class="bg-warning">
            Final content review from NeuroLex is still ongoing. We welcome your feedback.
        </p>
        </div>

        <div class="col-md-6">
            <div>
                <img src="/images/neurolex-logo.png" style="width:15%;display:inline-block"/>
                <img src="/images/neurolex-subtitle.png" style="width:80%;display:inline-block" />
            </div>
            <div>
                <p>
                    <strong>
                        NeuroLex is now accessible via InterLex and is once again accepting contributions.
                        InterLex is a new tool that builds on the successes of NeuroLex, and which is intended to bridge the gap between community lexicons and more formal ontologies.
                        We are currently in a public beta and are continuing to work on ensuring that all content from NeuroLex has been transitioned.
                        If you have any questions or feedback please contact us via the help desk below.
                    </strong>
                </p>
            </div>
            <div id="graph-term" style="width:100%; height:400px;"></div>
            <!-- <div id="graph-cde" style="width:100%; height:400px;"></div> -->
        </div>

       <div class="col-md-12 headline">
       <h3 style="text-transform:uppercase;font-size:16px">Affiliates</h3></div>
       <div class="owl-carousel col-md-12" id="affiliates" style="opacity: 1; display: block;">
            <?php foreach($affiliates as $affiliate): ?>
                <div class="item">
                    <a href="<?php echo $affiliate['url'] ?>" target="_blank">
                        <img class="sponsor-img img-responsive" src="<?php echo $affiliate['logo'] ?>" alt="<?php echo $affiliate["name"] ?>" />
                    </a>
                </div>
            <?php endforeach ?>

        </div>
        </div>

    </div>
</div>
