<?php

$atime = time();

$challengeset = new Component;
$set_holder = $challengeset->getByIcon3($thisComp->cid, $thisComp->component);

$component_data->color1_json = json_decode($thisComp->color1);

// Get information from Component_Data
$c_data = new Component_Data;
$c_data_array = $c_data->getByLink($thisComp->cid, $thisComp->text2);
list($a, $visibility) = explode(":", $c_data_array[0]->icon);
list($a, $rulesURL) = explode(":", $c_data_array[0]->color);

// put Component_Data into $component_data for form to use
$component_data->icon = 'visibility:' . $visibility;
$component_data->start = $c_data_array[0]->start;
$component_data->end = $c_data_array[0]->end;
$component_data->community = $thisComp->cid;
$component_data->component = $thisComp->component;

// Get RULES information 
$r_data = new Component;
$r_data->getPageByType($thisComp->cid, $rulesURL);

if ($_SESSION['user']->id) {
    $component_data->uid = $_SESSION['user']->id;

    $reg = new Challenge;
    $reg->checkRegistration($component_data->uid, $component_data->component);

    if ($reg->action == 'join') {
        $isRegistered = 'registered';

        // only get submissions for registered users
        $subs = new Challenge_Submission;
        $thereturn = $subs->getSubmissionByUser ($thisComp->cid, $component, $_SESSION['user']->id) ;
    } else
        $isRegistered = 'not registered';

} else 
    $isRegistered = "not registered";

$now = time();

$myrole = $_SESSION['user']->levels;


if (sizeof($set_holder)) {
    $set_holder = $set_holder['results'];
    foreach ($set_holder as $set) {

//var_dump($set);
//echo "<hr>\n";

        if (isset($set->did)) {
            if (isset($set->ext_id)) {
//              $set_assoc[$set->text1][$set->title]['file']['ext_id'] = $set->ext_id;
                        $set_assoc[$set->text1][$set->title]['file'][$set->ext_id]['filename'] = $set->file;
                        $set_assoc[$set->text1][$set->title]['file'][$set->ext_id]['name'] = $set->name;
                        $set_assoc[$set->text1][$set->title]['file'][$set->ext_id]['description'] = $set->ext_desc;
                    } else {
                        $set_assoc[$set->text1][$set->title]['file'][$set->did]['filename'] = $set->file;
                        $set_assoc[$set->text1][$set->title]['file'][$set->did]['name'] = $set->name;
                        $set_assoc[$set->text1][$set->title]['file'][$set->did]['description'] = $set->ext_desc;
                    }
        }                       

        $set_assoc[$set->text1][$set->title]['start'] = $set->start;
        $set_assoc[$set->text1][$set->title]['end'] = $set->end;
        $set_assoc[$set->text1][$set->title]['id'] = $set->did;
        $set_assoc[$set->text1]['content'] = $set->text3;
        $set_assoc[$set->text1]['comp_id'] = $set->comp_id;
    }   
}

// $vars['id'] might not be there if they just clicked on the challege, so dont' require it
// if no dataset in the link, just load overview data , but somehow lead them to click dataset ...
// that makes sense if there are multiple datasets, but if just one, can we streamline?

if (isset($vars['id'])) {
    if (!(in_array(strtolower($vars['id']), array_map('strtolower', array_keys($set_assoc))))) {
        echo "<strong>Invalid URL</strong>\n";
    }
}
?>


<style>
.flexbox-container-1, .flexbox-container-2, .flexbox-container-3 {
	display: -ms-flex;
	display: -webkit-flex;
	display: flex;
}

.stage1, .stage2 {
    -webkit-flex: 1;
    flex: 1;

}    

.list-group-item {
    font-weight: bold;
    padding-bottom: 10px;
}
</style>

<?php 
    // List the challenges that can use this new layout
if (in_array($vars['title'], array('grand-challenge-2', 'grand-challenge-2015', 'grand-challenge-3', 'challenge-pl-2016-1', 'sampl5', 'sampl6', 'dknet-nursa-challenge', 'grand-challenge-4'))): ?>

<div class="container content">
<?php
// only show if "public" or "private" with the right role
if (($component_data->icon == 'visibility:public') ||
(($component_data->icon == 'visibility:private') && ($myrole[$thisComp->cid] >= 3))):
?>
    <!-- Left Sidebar -->
    <div class="col-md-2">
        <ul id="sidebar-nav-1" class="list-group sidebar-nav-v1 margin-bottom-40" style="padding-top: 30px;">
            <?php
                if (!(isset($vars['id'])))
                    $setactive = 'active';
                else
                    $setactive = '';

                echo '<li class="list-group-item ' . $setactive . '"><a href="/D3R/about/' . $vars['title'] . '"><i class="fa fa-home"></i> <strong><span style="font-size:1em;">' . $thisComp->text1 . '</span></strong></a></li>' . "\n";

                foreach ($component_data->color1_json->verticaltabs as $title=>$foo) {
                    if (strtolower($component_data->color1_json->verticaltabs->$title) == strtolower($vars['id'])){
                        $setactive = 'active';
                    } else
                        $setactive = '';

                    if ($component_data->color1_json->verticaltabs->$title == '') {
                        echo '<li class="list-group-item" style="padding: 10px; background-color: #c9ddfc"><i class="fa fa-folder"></i> ' . $title . '</li>' . "\n";
                    } else {
                        echo '<li style="padding-left: 20px" class="list-group-item ' . $setactive . '"><a href="/D3R/about/' . $vars['title'] . '/' . strtolower($component_data->color1_json->verticaltabs->$title) . '" >' . $title . ' </a></li>' . "\n";
                    }

//                    echo '<li class="list-group-item ' . $setactive . '"><a href="/D3R/about/' . $vars['title'] . '/' . strtolower($display) . '" ><i class="fa fa-folder-o"></i> ' . $title . ' </a></li>' . "\n";
                }
            ?>
        </ul>
    </div> <!-- end left sidebar div -->

    <div class="col-md-10">
        <div class="tab-v1">

<?php 
    // if no $vars['id'] then we are on main challenge page, so show main challenge related tabs
    if (!(isset($vars['id']))): ?>
            <ul class="nav nav-tabs margin-bottom-20 tut-nav">
                <li class="page1-tab active tut-overview"><a href="#overview" data-toggle="tab"><i class="fa fa-info-circle"></i> Overview</a></li>
                <li class="page2-tab tut-join"><a href="#join" data-toggle="tab"><i class="fa fa-group"></i> Join the Challenge</a></li>
            </ul>

<?php 
    // since we're not on main challenge page, show dataset related tabs
    else: ?>

            <ul class="nav nav-tabs margin-bottom-20 tut-nav">
                <li class="page1-tab active tut-overview"><a href="#overview" data-toggle="tab">Overview</a></li>

                <li class="page2-tab tut-download"><a href="#data-download" data-toggle="tab"><i class="fa fa-floppy-o"></i> Data Download</a></li>
            <?php
                if ($component_data->color1_json->protocols): ?>
                    <li class="page2-tab tut-protocols"><a href="#protocols" data-toggle="tab"><i class="fas fa-bars"></i> Protocols</a></li>
            <?php
                endif; ?>
                    
                <li class="page2-tab tut-submissions"><a href="#submissions" data-toggle="tab"><i class="fa fa-upload"></i> Submissions</a></li>

                <li class="final-tab tut-results"><a href="#evaluation-results" data-toggle="tab"><i class="fa fa-bar-chart-o"></i> Evaluation Results</a></li>
            </ul>
<?php 
    endif; 

    // horizontal tabs completed, still in 'tab-v1'
    ?>


            <?php 

if (!(isset($vars['id']))) {
    echo "<div class='tab-content'>\n";
    echo "    <div id='overview' class='tab-pane fade in active'>";
    $overview = new Component();
    $overview_content = $overview->getByID($component_data->color1_json->overview);
    echo $overview->text3;
    echo "    </div>\n";
    echo "    <div id='join' class='well tab-pane fade'><div class='headline headline-md'><h2>Join the Challenge</h2></div>\n";

        if ($isRegistered == 'registered') {
            echo "You registered on " . date("m/d/Y", $reg->update_time);
            if ($now <= $component_data->end) {
                echo '<br /><span style="padding-left: 20px;"><a href data-toggle="modal" data-target="#myModal3">Leave challenge</a></span>';
            }
        } else { // not registered for challenge, so see if at least is a user
            if (!(isset($_SESSION['user']))) {  // if not a user, give register / login links
                ?>
                <div id="join1">Step 1: <a href='/<?php echo $community->portalName; ?>/join'>Create
                        New <?php echo $community->shortName; ?> Account</a> and/or <a
                        class="btn-login"
                        href="#">log in</a>
                </div>
                <?php
            } elseif ((isset($_SESSION['user']->levels[$community->id])) && ($_SESSION['user']->levels[$community->id] > 0)) {

                ?>
                <div id="join2">Step 2: Read the <a href data-toggle="modal"
                                                    data-target="#myModal">Rules
                        and Procedures</a></div>
                <div id="join2">Step 3:</div>

                <div style="padding-left: 50px;">
                    <button class="btn btn-info" data-toggle="modal" data-target="#myModal2">
                        Join Challenge
                    </button>

                </div>
                <?php

            } else {
                ?>
                <div id="join2">Step 1:</div>
                <div style="padding-left: 50px;">
                   <a class="btn-u btn-u-orange" href="/forms/login.php?join=true&cid=<?php echo $community->id; ?>">Join <?php echo $community->shortName; ?></a>
                </div>
                <div id="join2">Step 2: Read the <a href data-toggle="modal"
                                                    data-target="#myModal">Rules
                        and Procedures</a></div>
                <div id="join2">Step 3: Join Challenge</div>
                <?php
            }
        }
                            
//                        <!-- </div>  "Join the Challenge" ---- taking this out -->

    echo "    </div>\n"; // join tab
    echo "</div>\n"; // tab-content
} else {
            foreach (array_keys($set_assoc) as $title) {
                

                // if on the right dataset, build tab content
                if (strtolower($title) == strtolower($vars['id'])) {
                    $component_data->title = $title;

                    echo "<div class='tab-content'>\n";
                    
                    // overview tab
                    echo "  <div id='overview' class='tab-pane fade in active'>";
                    printDataSetandTimeframe($component_data->title, 'Overview', $component_data->start, $component_data->end);
                    
                    if (($set_assoc[$title]['content'] == '<p><br></p>') || ($set_assoc[$title]['content'] == '<p><br /></p>')) {
                        echo "very empty!";
                    } else
                        echo $set_assoc[$title]['content'];

                    echo "</div>\n"; // overview tab

                    // data-download tab
                    echo "  <div id='data-download' class='tab-pane fade'>\n";
                    printDataSetandTimeframe($component_data->title, 'Data Download', $component_data->start, $component_data->end);

                        foreach (array_keys($set_assoc) as $set) {
                            if ($set !== $title)
                                continue;

                            $stage_count = sizeof(array_keys($set_assoc[$set]));
                            foreach (array_keys($set_assoc[$set]) as $title) {
                                if (($title == 'content') || ($title == 'comp_id'))
                                    continue;

                                if ($stage_count > 1)
                                    echo "<div style='padding-left: 10px;'><strong>" . $title . "</strong> (" . date('m/d/Y', $set_assoc[$set][$title]['start']) . " to " . date('m/d/Y', $set_assoc[$set][$title]['end']) . ")</div>\n";
                                else
                                    echo "<div style='padding-left: 10px;'>" . date('m/d/Y', $set_assoc[$set][$title]['start']) . " to " . date('m/d/Y', $set_assoc[$set][$title]['end']) . "</div>\n";
                                // if before start date, state when data will be available
                                if ($atime <= $set_assoc[$set][$title]['start']) {
                                    echo "<div style='padding-left: 40px; color: #777; '>Available on " . date('m/d/Y', $set_assoc[$set][$title]['start']) . "</div>\n";
                                                        
                                // if after end date, anyone is allowed access w/o registration. 
                                } elseif ($atime >= $set_assoc[$set][$title]['end']) {
                                    //still require login though.
                                    if (!(isset($_SESSION['user'])))
                                        echo "<div style='padding-left: 40px; color: #777; '>Please <a class='btn-login' href='#'>log in</a> first</div>\n";
                                    else {
                                        if (sizeof($set_assoc[$set][$title]['file'])) {
                                        
                                            foreach (array_keys($set_assoc[$set][$title]['file']) as $id) {

                                                echo "<div style='padding-left: 40px;'><a href='/php/file-download.php?type=extended&id=" . $id ."'>" . $set_assoc[$set][$title]['file'][$id]['filename'] . "</a> (" . getFilesize($set_assoc[$set][$title]['file'][$id]['filename']) . ")</div>\n";


                                                echo "<div style='padding-left: 40px;'>" . $set_assoc[$set][$title]['file'][$id]['description'] . "</div>\n";

                                            }
                                        }
                                    }

                                // if here, then challenge is currently live
                                } else {
                                    // if live and user is not logged in, show login link
                                    if (!(isset($_SESSION['user'])))
                                        echo "<div style='padding-left: 40px; color: #777; '>Please <a class='btn-login' href='#'>log in</a> first</div>\n";
                                    
                                    elseif ($isRegistered == 'not registered') {
                                        echo "<div style='padding-left: 40px; color: #777; '>To ensure that users receive notifications about the data, we are now requiring users to <a href='../" . $vars['title'] . "#join'>Join the Challenge</a>. You may leave the challenge at any time.</div>\n";    
                                    }
                                    // atime, just need to be d3r user to get data
                                    elseif ((isset($_SESSION['user']->levels[$community->id])) && ($_SESSION['user']->levels[$community->id] > 0)) {

                                        if ($atime >= $set_assoc[$set][$title]['end'])
                                            echo "<div style='padding-left: 40px;'>Stage closed, but data is still available</div>\n";
                                        
                                        if (sizeof($set_assoc[$set][$title]['file'])) {
                                        
                                            foreach (array_keys($set_assoc[$set][$title]['file']) as $id) {
                                                echo "<div style='padding-left: 40px;'><a href='/php/file-download.php?type=extended&id=" . $id ."'>" . $set_assoc[$set][$title]['file'][$id]['filename'] . "</a> (" . getFilesize($set_assoc[$set][$title]['file'][$id]['filename']) . ")</div>\n";
                                                echo "<div style='padding-left: 40px;'>" . $set_assoc[$set][$title]['file'][$id]['description'] . "</div>\n";
                                            }
                                        } else {
                                            echo "<div style='padding-left: 40px; color: #777'>No files available yet</div>\n";
                                        }

                                    // if live and logged in, but not D3R member, offer "Join D3R" link
                                    } else {
                                        ?>
                                        <div style="padding-left: 50px;">
                                            <a class="btn-u btn-u-purple" href="/forms/login.php?join=true&cid=<?php echo $community->cid; ?>">Join <?php echo $community->shortName; ?> to download the data</a>
                                        </div>
                                        <?php    
                                    }
                                }
                            }
                        }
                    echo "</div>\n"; // data download tab

                    // protocols tab
                    if ($component_data->color1_json->protocols)
                        include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/challenge_protocol.php';

                    // submissions tab
                    include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/challenge_submission.php';

                    // evaluation-results tab
                    echo "<div id='evaluation-results' class='tab-pane fade'>\n";
                    printDataSetandTimeframe($component_data->title, 'Evaluation Results', $component_data->start, $component_data->end);

                    echo "<h2>Evaluation Results</h2>\n";
                    if ($component_data->color1_json->evaluations_ready) {
                        $evals = new Component();
                        $eval_content = $evals->getByID($component_data->color1_json->evaluations_ready);
                        echo $evals->text3;
                    } else {
                        echo "Evaluation results are not available yet.";
                    }
                    echo "</div>\n"; // evaluation results tab


                }
            }
        }?>
            </div> <!-- tab-content -->
           
        </div> <!-- end tab-v1 -->
    </div> <!-- end col-md-10 -->
</div> <!-- end container content -->

<?php endif; ?>


<!-- Modal -->
<!-- Rules Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel"><?php echo $r_data->text1; ?></h4>
          </div>
          <div class="modal-body">
            <?php echo $r_data->text3; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

<!-- Leave Modal -->
    <div class="modal fade" id="myModal3" tabindex="-3" role="dialog" aria-labelledby="myModalLabel3">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel3">Leave challenge</h4>
          </div>
          <div class="modal-footer">
                <form class="form-horizontal well" data-async data-target="#rating-modal" action="/forms/component-forms/challenges.php" method="POST">
                <input type="hidden" id="community" name="community" value="<?php echo $component_data->community; ?>" />
                <input type="hidden" id="component" name="component" value="<?php echo $component_data->component; ?>" />
                <input type="hidden" id="uid" name="uid" value="<?php echo $component_data->uid; ?>" />
                <input type="hidden" id="action" name="action" value="leave" />
            <?php if (!is_null($component_data->color1_json->mailchimp_list_id)): ?>
                <input type="checkbox" id='mailchimp' name='mailchimp' value='<?php echo $component_data->color1_json->mailchimp_list_id; ?>' checked /> Remove me from the <?php echo $component_data->title; ?> mailing list<br />
            <?php endif; ?>
                <button class="btn btn-default" id="simple-post">Leave Challenge</button>
            
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </form>
          </div>
        </div>
      </div>
    </div>

<!-- Join Modal -->
    <div class="modal fade" id="myModal2" tabindex="-2" role="dialog" aria-labelledby="myModalLabel2">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel2"><?php echo $r_data->text1; ?></h4>
          </div>
          <div class="modal-body">
            <?php echo $r_data->text3; ?>
          </div>
          <div class="modal-footer">
      
    <!--        <form method="post" action="/forms/component-forms/challenges.php" id="myForm_" > -->
        <form class="form-horizontal well" data-async data-target="#rating-modal" action="/forms/component-forms/challenges.php" method="POST">
                <input type="hidden" id="community" name="community" value="<?php echo $component_data->community; ?>" />
                <input type="hidden" id="component" name="component" value="<?php echo $component_data->component; ?>" />
                <input type="hidden" id="uid" name="uid" value="<?php echo $component_data->uid; ?>" />
                <input type="hidden" id="action" name="action" value="join" />
            <?php if (!is_null($component_data->color1_json->mailchimp_list_id)): ?>
                <input type="checkbox" id='mailchimp' name='mailchimp' value='<?php echo $component_data->color1_json->mailchimp_list_id; ?>' checked /> Add me to the <?php echo $component_data->title; ?> mailing list<br />
            <?php endif; ?>
                <button class="btn btn-default" id="simple-post">Join Challenge</button>
            
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </form>
          </div>
        </div>
      </div>
<?php endif; ?>	
    </div>
<?php

    function getFilesize($file) {
        //$file_path = $_SERVER['DOCUMENT_ROOT'] . "/upload/extended-data/" . $file;
        $file_path = $_SERVER['DOCUMENT_ROOT'] . "/upload/extended-data/" . $file;

        if (!(is_file($file_path))) 
            return "file not found ";

        else {
            $filesize = filesize($file_path);

            if (($filesize / 1000) < 999)
                return round($filesize/1000, 1) . "k";
            
            if (($filesize / 1000000) < 999)
                return round($filesize/1000000, 1) . "M";

    //return 0;
            return $filesize;

        }
    }

    function printDataSetandTimeframe($title, $tab, $start, $end) {
        // hard code date end for CatS since need separate dates to show
//        if ($title == 'Cathepsin_S')
//            $end = 1513411199;

	    echo "<div id='bigdiv'>\n";
        echo "    <div style='float: left; width:500px;'><h1>" . $title . ' - ' . $tab . "</h1></div><div style='float: left;'><h3>Challenge timeframe: " . date("M d, Y", $start) . " to " . date("M d, Y", $end) . "</h3></div>\n";
        echo "</div>\n"; // end bigdiv
        echo "<br clear='all'>";
    }

?>
