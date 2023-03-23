<?php
/*
    I should be checking community status too!

    although D3R is a private community, there's nothing stopping people from seeing stuff ...
*/
?>

<?php
//var_dump($community->portalName);
//var_dump($data);
$challenge = new Challenge();
//var_dump($challenge->showYourResultsBlock($thisComp->component));


/*
 * showYourResultsBlock($thisComp->component)
 *
 */
?>

        <!-- Right Sidebar -->
        <div class="col-sm-3 ">
            <?php
                if (trim($visibility) == 'public'):
                    // if after end date, show links to evaluation results if available ...
                    if ($now > $data->end) {
                        if ($data->color1_json->evaluations_ready) {
                            echo "<div class='headline headline-md'><h2>Evaluation Results</h2></div>\n";
                            echo "  <div>\n";

                            if (!(isset($_SESSION['user']))) {
                                echo 'Please <a class="btn-login" href="#">Log in</a> to see your results.';
                            } else {
                                echo '<a href="/' . $community->portalName . "/" . $data->color1_json->evaluations_link . '">View evaluation data</a>' . "\n";
                            }
                            echo "</div>\n";
                        }
                    } else {
                        ?>
                        <div class="headline headline-md"><h2>Join the Challenge</h2>
                            <div id="joinchallenge">
                                <?php
                                if ($isRegistered == 'registered') {
                                    echo "You registered on " . date("m/d/Y", $reg->update_time);
                                    if ($now <= $data->end) {
                                        echo '<br /><span style="padding-left: 20px;"><a href data-toggle="modal" data-target="#myModal3">Leave challenge</a></span>';
                                        if ($data->color1_json->allow_anonymous)
                                            echo '<br /><span style="padding-left: 20px;">Anonymous status: ' . $reg->anonymouslabel . ' <a href data-toggle="modal" data-target="#myModal4"><img src="/assets/img/icons/flat/settings.png" height="16" /></a></span>';
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
                                ?>
                            </div><!-- id="joinchallenge" -->
                        </div> <!-- "Join the Challenge" -->
                        <?php
                    }
                    ?>
             
             <div class="headline headline-md"><h2><?php echo $challenge_settings->download_the_data_label; ?></h2>
             <?php
                foreach (array_keys($set_assoc) as $set) {
                    echo "<div style='padding-top: 10px;'><strong>" . $set . "</strong></div>\n";
                    
                    $stage_count = sizeof(array_keys($set_assoc[$set]));
                    foreach (array_keys($set_assoc[$set]) as $title) {
                        if ($stage_count > 1)
                            echo "<div style='padding-left: 20px;'><strong>" . $title . "</strong> (" . date('m/d/Y', $set_assoc[$set][$title]['start']) . " to " . date('m/d/Y', $set_assoc[$set][$title]['end']) . ")</div>\n";
                        else
                            echo "<div style='padding-left: 20px;'>" . date('m/d/Y', $set_assoc[$set][$title]['start']) . " to " . date('m/d/Y', $set_assoc[$set][$title]['end']) . "</div>\n";

                        // if before start date, state when data will be available
                        if ($now <= $set_assoc[$set][$title]['start']) {
                            echo "<div style='padding-left: 40px; color: #777; '>Available on " . date('m/d/Y', $set_assoc[$set][$title]['start']) . "</div>\n";
                                                
                        // if after end date, anyone is allowed access w/o registration. 
                        } elseif ($now >= $set_assoc[$set][$title]['end']) {
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
                            
                            // now, just need to be d3r user to get data
                            elseif ((isset($_SESSION['user']->levels[$community->id])) && ($_SESSION['user']->levels[$community->id] > 0)) {

                                if ($now >= $set_assoc[$set][$title]['end'])
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
             ?>
             </div>

             <div class="headline headline-md"><h2>Rules</h2>
                 <div>Read the <a href data-toggle="modal" data-target="#myModal">Rules and Procedures</a></div>
             </div>

             <div id="filelist0" style="display:none"><?php echo $vars['id']; ?></div>

             <!--</div> -->
            <?php
            if ($isRegistered == 'registered') {
                if (($now > $data->end) || ($challenge->showYourResultsBlock($thisComp->component))):
                     echo "<div class='headline headline-md'><h2>Your Results</h2><br />\n";
                     $subcount = new Challenge_Submission;
                     $sub = new Challenge;

                     foreach (array_keys($set_assoc) as $set) {
                         echo "<div style='padding-top: 10px;'><strong>" . $set . "</strong></div>\n";
                         $stage_count = sizeof(array_keys($set_assoc[$set]));
                         foreach (array_keys($set_assoc[$set]) as $title) {
                             if ($now >= $set_assoc[$set][$title]['start']) {
                                $url_split = explode($vars['title'], $_SERVER['REQUEST_URI']);
                                if ($sub->showStageLabel($set_assoc[$set][$title]['id'])) 
                                    $stage_title = $title . " - ";
                                else
                                    $stage_title = '';

                                echo "<div style='padding-left: 20px;'>" . $stage_title . "<a href='" . $url_split[0] . $vars['title'] . "/" . $set_assoc[$set][$title]['id'] . "'>" . $subcount->countSubmissionsPerStage($set_assoc[$set][$title]['id'], $_SESSION['user']->id) . " submissions</a></div>\n";
                             }
                         }
                     }
                     echo "</div>\n";

                 endif; // show your results block check

                 if ($now > $data->end) {
                     echo "<div class='headline headline-md'><h2>Download the Answers</h2><br />\n";

                     if ($data->component == 261) {
                         echo "<div style='padding-top: 10px;'><strong>HSP90</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/gc2015_submissions/HSP90_AFFINITY.csv'>HSP90_AFFINITY.csv</a></div>\n";
                         echo "<div style='padding-top: 10px;'><strong>MAP4K4</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/gc2015_submissions/MAP4K4_AFFINITY.csv'>MAP4K4_AFFINITY.csv</a></div>\n";
                     } elseif ($data->component == 263) {
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 host-guest</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/SAMPL5HG_exp_data02.xlsx'>SAMPL5HG_exp_data02.xlsx</a></div>\n";
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 distribution coefficients</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/logD_final.txt' target='_blank'>logD_final.txt</a></div>\n";
                     } elseif ($data->component == 285) {
                         echo "<div style='padding-top: 10px;'><strong>PL-2016-1</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/d3r/pl-2016-1/PL-2016-1-answers-corrections-7-25-16.tgz'>PL-2016-1-answers-corrections-7-25-16.tgz</a></div>\n";
                     } elseif ($data->component == 315) {
                        echo "<div style='padding-top: 10px;'><strong>FXR</strong></div>\n";
                         echo '<div style="padding-left: 20px;">' . "\n";
                         echo "<div style='padding-top: 10px;'><strong>Stage 1</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='https://drugdesigndata.org/php/file-download.php?type=extended&id=108'>FXR_Stage1_REVISED_20170111.tar.gz</a></div>\n";
                         echo "<div style='padding-top: 10px;'><strong>Stage 2</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/gc2_submissions/FXR_affinities_all_20170210.csv.gz'>FXR_affinities_all_20170210.csv.gz</a></div>\n";
                        echo "</div>\n";

                     }
                     echo "</div>\n";

                     // D3R SAMPL5 specific results
                     if ($data->component == 263) {
                         echo "<div class='headline headline-md'><h2>Download the Analysis</h2><br />\n";
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 host-guest</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/SAMPL5-HG-Analysis.zip'>SAMPL5-HG-Analysis.zip</a></div>\n";
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 distribution coefficients</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/SAMPL5-DC-Analysis.zip' target='_blank'>SAMPL5-DC-Analysis.zip</a></div>\n";
                         echo "</div>\n";

                         echo "<div class='headline headline-md'><h2>Standard Calculated Results</h2><br />\n";
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 host-guest</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/SAMPL5-HG-Standard.xlsx'>SAMPL5-HG-Standard.xlsx</a></div>\n";
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 distribution coefficients</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/SAMPL5-DC-Standard.txt' target='_blank'>SAMPL5-DC-Standard.txt</a></div>\n";
                         echo "</div>\n";

                         echo "<div class='headline headline-md'><h2>Download the Submissions</h2><br />\n";
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 host-guest</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/SAMPL5-HG-submissions.tar.gz'>SAMPL5-HG-submissions.tar.gz</a></div>\n";
                         echo "<div style='padding-top: 10px;'><strong>SAMPL5 distribution coefficients</strong></div>\n";
                         echo "<div style='padding-left: 20px;'><a href='/upload/community-components/sampl5_submissions/SAMPL5-DC-submissions.tar.gz' target='_blank'>SAMPL5-DC-submissions.tar.gz</a></div>\n";
                         echo "</div>\n";
                     }
                 } // if ($now > $data->end)
            } // isRegistered  ... Your Results

            endif;  // isRegistered ... Manage Your Data
        //endif;
            ?>
            <!-- End Join the Challenge -->
        </div>
        <!-- End Right Sidebar -->

    <!-- Modal -->
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

<?php
    if ($now <= $data->end):
?>  
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
                <input type="hidden" id="community" name="community" value="<?php echo $data->community; ?>" />
                <input type="hidden" id="component" name="component" value="<?php echo $data->component; ?>" />
                <input type="hidden" id="uid" name="uid" value="<?php echo $data->uid; ?>" />
                <input type="hidden" id="action" name="action" value="join" />
            <?php if (!is_null($data->color1_json->mailchimp_list_id)): ?>
                <input type="checkbox" id='mailchimp' name='mailchimp' value='<?php echo $data->color1_json->mailchimp_list_id; ?>' checked /> Add me to the <?php echo $data->title; ?> mailing list<br />
            <?php endif; ?>
                <button class="btn btn-default" id="simple-post">Join Challenge</button>
            
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="myModal3" tabindex="-3" role="dialog" aria-labelledby="myModalLabel3">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel3">Leave challenge</h4>
          </div>
          <div class="modal-footer">
                <form class="form-horizontal well" data-async data-target="#rating-modal" action="/forms/component-forms/challenges.php" method="POST">
                <input type="hidden" id="community" name="community" value="<?php echo $data->community; ?>" />
                <input type="hidden" id="component" name="component" value="<?php echo $data->component; ?>" />
                <input type="hidden" id="uid" name="uid" value="<?php echo $data->uid; ?>" />
                <input type="hidden" id="action" name="action" value="leave" />
            <?php if (!is_null($data->color1_json->mailchimp_list_id)): ?>
                <input type="checkbox" id='mailchimp' name='mailchimp' value='<?php echo $data->color1_json->mailchimp_list_id; ?>' checked /> Remove me from the <?php echo $data->title; ?> mailing list<br />
            <?php endif; ?>
                <button class="btn btn-default" id="simple-post">Leave Challenge</button>
            
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </form>
          </div>
        </div>
      </div>
    </div>

        <?php
        if ($data->color1_json->allow_anonymous) {
        ?>

    <div class="modal fade" id="myModal4" tabindex="-4" role="dialog" aria-labelledby="myModalLabel4">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel4">Change Anonymous Status</h4>
          </div>
        <div class="modal-body">
            Your current status is 
            <?php   if ($reg->isAnonymous == 1)
                        echo "anonymous.";
                    else
                        echo "public.";
                    
                    echo " Do you want to change your status?\n";
            ?>  
          </div>
          <div class="modal-footer">
                <form class="form-horizontal well" data-async data-target="#rating-modal" action="/forms/component-forms/challenges.php" method="POST">
                <input type="hidden" id="id" name="id" value="<?php echo $reg->id; ?>" />
                <input type="hidden" id="anonymous" name="anonymous" value="<?php echo $reg->isAnonymous; ?>" />
                <input type="hidden" id="action" name="action" value="switchanonymous" />
                <button class="btn btn-default" id="simple-post">Change status to
                <?php   if ($reg->isAnonymous == 1)
                        echo "public";
                    else
                        echo "anonymous";
                ?>
                </button>
            
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </form>
          </div>
        </div>
      </div>
    </div>
<?php } // only show #myModal4 if "allow_anonymous" is true ?>
<?php
    endif;
?>
    <!--=== End Modal Part ===-->        
