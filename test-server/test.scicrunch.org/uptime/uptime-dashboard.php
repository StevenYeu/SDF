<script type="text/javascript" src="../assets/plugins/jquery-3.4.1.min.js"></script>
<script src="../js/highcharts/js/highcharts.js" type="text/javascript"></script>
<script src="../js/highcharts/js/highcharts-more.js" type="text/javascript"></script>
<script src="../js/highcharts/js/modules/solid-gauge.js" type="text/javascript"></script>
<script type="text/javascript" src="../js/uptime.js"></script>
<?php
echo Connection::createBreadCrumbs('Uptime Dashboard',array('Home','Account'),array($profileBase,$profileBase.'account'),'Uptime Dashboard');
?>

<div class="hide">
<?php
$CONFIG = include_once($_SERVER['DOCUMENT_ROOT'] . "/uptime/config.php");
// print "<pre>";
// print_r($CONFIG['UR_IDS']);
// print "</pre>";
foreach ( $CONFIG['UR_IDS'] as $host => $id ) {
  //print($name . ": " . $value['max'] . " " . $value['min'] . "\n");

  print '<input type="hidden" id="'.$id.'" value="'.$host.'" class="ur-id"/>';
}
  ?>
</div>

<div id='details-modal' class='modal fade'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type="button" class="close popover-hide" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class='modal-title' id='details-modal-title'></h4>
            </div>
            <div class='modal-body' id='details-modal-body'>

            </div>
        </div>
    </div>
</div>

<div id="ratio-modal" class="modal fade" style="min-width: 420px;">
    <div class='modal-header'>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class='modal-title text-center'>Uptime Ratios</h4>
    </div>
    <div class="modal-body" id="ratio-modal-body" style="max-width: 430px;">

    </div>
</div>

<div class="profile container content">
    <div class="row">
        <!--Left Sidebar-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>
        <!--End Left Sidebar-->


        <div class="col-md-9">
            <!--Profile Body-->
            <div class="profile-body">
                <!--Service Block v3-->

                    <div id="alert" class="alert alert-info">Data is retrieved from Uptime Robot depending on your connection it might take a few seconds to load.</div>

                    <div class="table-responsive">

                        <table class="table table-hover" id="hosts">
                            <thead>
                            <tr>
                                <th>Host</th>
                                <th>Status</th>
                                <th>% Uptime [Days: %]</th>
                                <th>Stats</th>
                            </tr>
                            </thead>

                            <tbody>
                            <tr><td colspan="4" style="background-color:#d9edf7;border-color:#bce8f1;"><h5>Data Services</h5></td></tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['DATA_SCR'].'?host=nif-services.neuinfo.org'?>"></span>&nbsp; <span class="hostname"> nif-services.neuinfo.org</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['DATA_SCR'].'?host=nif-apps1.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> nif-apps1.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['DATA_SCR'].'?host=nif-apps2.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> nif-apps2.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>

                            <tr><td colspan="4" style="background-color:#d9edf7;border-color:#bce8f1;"><h5>Ontology Services</h5></td></tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['ONTO_SCR'].'?host=cypher.neuinfo.org'?>"></span>&nbsp; <span class="hostname"> cypher.neuinfo.org</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['ONTO_SCR'].'?host=trinity.neuinfo.org'?>"></span>&nbsp; <span class="hostname"> trinity.neuinfo.org</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['ONTO_SCR'].'?host=matrix.neuinfo.org'?>"></span>&nbsp; <span class="hostname"> matrix.neuinfo.org</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>

                            <tr><td colspan="4" style="background-color:#d9edf7;border-color:#bce8f1;"><h5>Solr Services</h5></td></tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['SOLR_SCR'].'?host=tatoo1.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> tatoo1.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['SOLR_SCR'].'?host=tatoo2.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> tatoo2.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['SOLR_SCR'].'?host=starburst.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> starburst.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['SOLR_SCR'].'?host=vivaldi.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> vivaldi.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>

                            <tr><td colspan="4" style="background-color:#d9edf7;border-color:#bce8f1;"><h5>MySQL</h5></td></tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['MS_SCR'].'?host=nif-mysql.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> nif-mysql.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['MS_SCR'].'?host=mysql5-stage.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> mysql5-stage.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['MS_SCR'].'?host=dev-db.crbs.ucsd.edu'?>"></span>&nbsp; <span class="hostname"> dev-db.crbs.ucsd.edu</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>

                            <tr><td colspan="4" style="background-color:#d9edf7;border-color:#bce8f1;"><h5>PostgreSQL</h5></td></tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['PG_SCR'].'?host=postgres.neuinfo.org'?>"></span>&nbsp; <span class="hostname"> postgres.neuinfo.org</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>
                            <tr>
                                <td class="service"><span data-toggle="popover" data-content="Click for details" class="glyphicon glyphicon-play-circle glyphicon-info" data-url="<?php echo $CONFIG['BASE_URL'].'/'.$CONFIG['PG_SCR'].'?host=postgres-stage.neuinfo.org'?>"></span>&nbsp; <span class="hostname"> postgres-stage.neuinfo.org</span></td>
                                <td class="status"></td>
                                <td class="percents" data-container="body"></td>
                                <td class="uptimes"></td>
                            </tr>

                            </tbody>
                        </table>
                    </div>


            </div>
        </div>
        <!--End Profile Body-->
    </div>
    <!--/end row-->
</div>
<!--/container-->
<!--=== End Profile ===-->
