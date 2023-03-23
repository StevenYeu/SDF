<?php 
$chset = new Challenge;

?>

<div id='submissions' class='tab-pane fade'>
    <?php printDataSetandTimeframe($component_data->title, 'Submissions', $component_data->start, $component_data->end);
            if ($isRegistered !== 'registered'):
                echo 'Please join the challenge and <a class="btn-login" href="#">Login</a>.';
            else:
        ?>          
                <style>
                     #submissions td { font-size: .9em; }
                </style>

        <?php
                foreach ($set_assoc[$component_data->title] as $stage=>$stagevalue) {
                    if (($stage == 'content') || ($stage == 'comp_id'))
                        continue;

                    $show_manage_data = 0;
                    $show_uploader = 0;

                    echo "<div class='well'><h2>" . $stage . "</h2>\n";

                    $chset->getChallengeSetByID($set_assoc[$component_data->title][$stage]['id']);
                    $stage_parameters = json_decode($chset->icon);

                    $special_people_array = explode(",", $stage_parameters->special_access);
                    if (in_array($_SESSION['user']->id, $special_people_array)) {
                        $show_manage_data = 1;
                        $show_uploader = 1;
                    }

                    // check the time
                    // future start date
                    if (strtotime($stage_parameters->open_submissions) > $now) {
                        echo "Submissions for this challenge/stage starts on " . $stage_parameters->open_submissions;
                    } 
                    // start date has passed, close date has passed
                    elseif (strtotime($stage_parameters->close_submissions) < $now) { 
                        $show_manage_data = 1;
                    } 
                    else { 
                    // start date has passed, future close date, so still open
                        $show_manage_data = 1;
                        $show_uploader = 1;
                    }

                    if ($show_manage_data):
                        // only get submissions for registered users
                        $subs = new Challenge_Submission;
                        $thereturn = $subs->getSubmissionByUser ($thisComp->cid, $set_assoc[$component_data->title][$stage]['id'], $_SESSION['user']->id) ;

               ?>
                    <div id="home-2" >
                        <div class=" margin-bottom-20 well well-sm">
                        <h3>Manage Your Data</h3>
                        <?php if (sizeof($thereturn) == 0) 
                            echo "No submissions yet";
                        else {
                        ?>
                        <table id='submissions'>
                        <thead>
                            <tr>
                                <th width="10%">Receipt ID</th>
                                <th width="30%">Filename</th>
                                <th width="15%">Type</th>
                                <th width="20%">Submission Date</th>
                                <th width="12%">Anonymous</th>
                                <th width="15%">Action</th>
                                <th width="15%">Protocols</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php 
                                foreach ($thereturn as $row) {
                                    echo "<tr>\n";
                                    echo "<td>" . $row['receipt_id'] . "</td>\n";
                                    echo "<td><a href='/php/file-download.php?type=datasubmission&receipt=" . $row['receipt_id'] ."' target='_blank'>" . $row['filename'] . "</a></td>\n";
                                    echo "<td>" . $row['type'] . "</td>\n";
                                    echo "<td>" . date("Y-m-d H:i", $row['submit_date']) . "</td>\n";
                                    echo "<td>" . anonymousPublicPrivate($row['isAnonymous']) . "</td>\n";
                                    if (strtotime($stage_parameters->close_submissions) > $atime) {
                                        echo '<td><a href="/php/d3r_upload.php?action=receiptDelete&component=' . $row['component'] . '&receipt=' . $row['receipt_id'] .'" class="receipt-delete" data-confirm="Are you sure you to delete this item?">Delete</a></td>' . "\n";
                                    } elseif ($stage_parameters->check_your_results) {
                                        echo '<td><a href="/php/file-download.php?type=mychallengeresults&component=' . $row['component'] . '&receipt=' . $row['receipt_id'] .'" target="_blank">Check your results</a></td>' . "\n";
                                        echo "</tr>\n";
                                    }
                                    $bl = $subs->getProtocolsFromSubmissionID($row['id']);
                                    echo "<td>";
                                    foreach ($bl as $proto) {
                                        echo "<a href='/php/d3r/gc4/combined/spreadsheets/my_protocols.php?component=" . $proto['component'] . "&receipt=" . $proto['protocol_id'] . "' target='_blank'>" . $proto['protocol_id'] . "</a>\n";
                                    }
                                    echo "</td>\n";
                                }
                            ?>
                        </tbody>
                        </table>

                        <script type="text/javascript">
                        var deleteLinks = document.querySelectorAll('.receipt-delete');

                        for (var i = 0; i < deleteLinks.length; i++) {
                          deleteLinks[i].addEventListener('click', function(event) {
                              event.preventDefault();

                              var choice = confirm(this.getAttribute('data-confirm'));

                              if (choice) {
                                window.location.href = this.getAttribute('href');
                              }
                          });
                        }
                        </script>
                        <?php } ?>
                        </div>  
                    </div>
                <?php endif; // show manage data 

                if ($show_uploader): ?>
                            <div id="filelist0" style="display:none"><?php echo $set_assoc[$component_data->title][$stage]['id']; ?></div>

                            <div class="well well-sm">
                                <h2>Submit Your Data</h2>
                                <form method="post" name="subform" id="subform">
                                <div id="example">
                                    <strong>Prediction Categories</strong>
                                        <div style="padding-left: 20px;">
                                            Please select a prediction category:<br />
                                    <?php
                                    if (count((array)$stage_parameters->predictioncategory) == 1)
                                        $cat_checked = ' checked';
                                    else
                                        $cat_checked = '';

                                            foreach ($stage_parameters->predictioncategory as $cat=>$dog) {
                                                switch ($cat) {
/* Hardcode remove for Stage 2 
                                                    case 'pose':
                                                        echo '<input type="radio" name="predictioncategory" value="pose" id="predictioncategory-pose" ' . $cat_checked . " onclick='show_pp();' /> Pose Prediction<br />\n";
                                                        break;
*/
                                                    case 'scoreligand':
                                                        echo '<input type="radio" name="predictioncategory" value="scoreligand" id="predictioncategory-scoreligand" ' . $cat_checked . " onclick='show_ls();' /> Score - Ligand based<br />\n";
                                                        break;

                                                    case 'scorestructure':
                                                        echo '<input type="radio" name="predictioncategory" value="scorestructure" id="predictioncategory-structure" ' . $cat_checked . " onclick='show_ss();' /> Score - Structure based<br />\n";
                                                        break;
/* traditional freeenergy1 */
/* if non-traditional freeenergy , like -fe, -lb, -sb exist, then don't show freeenergy1 */
                                                    case 'freeenergy1':
                                                        $skip = 0;
                                                        if (isset($stage_parameters->predictioncategory)) {
                                                            foreach ($stage_parameters->predictioncategory as $pred=>$preval) {
                                                                if ($pred == 'freeenergy1-fe')
                                                                    $skip = 1;
                                                            }
                                                        }
                                                        
                                                        if (!($skip))
                                                            echo '<input type="radio" name="predictioncategory" value="freeenergy1" id="predictioncategory-freeenergy1" ' . $cat_checked . " onclick='show_fe();' /> Free Energy Set 1<br />\n";
                                                        break;

                                                    case 'freeenergy1-fe':
                                                            echo '<input type="radio" name="predictioncategory" value="freeenergy1-fe" id="predictioncategory-freeenergy1-fe" ' . $cat_checked . " onclick='show_fe_fe();' /> Free Energy Set 1 by Free Energy Methods<br />\n";
                                                        break;

                                                    case 'freeenergy1-sb':
                                                        echo '<input type="radio" name="predictioncategory" value="freeenergy1-sb" id="predictioncategory-freeenergy1-sb" ' . $cat_checked . " onclick='show_fe_sb();' /> Free Energy Set 1 by Structure-Based Scoring<br />\n";
                                                        break;

                                                    case 'freeenergy1-lb':
                                                        echo '<input type="radio" name="predictioncategory" value="freeenergy1-lb" id="predictioncategory-freeenergy1-lb" ' . $cat_checked . " onclick='show_fe_lb();' /> Free Energy Set 1 by Ligand-Based Scoring<br />\n";
                                                        break;

                                                    case 'freeenergy2':
                                                        echo '<input type="radio" name="predictioncategory" value="freeenergy2" id="predictioncategory-freeenergy2" ' . $cat_checked . "/> Free Energy Set 2<br />\n";
                                                        break;

                                                    case 'host-guest':
                                                        echo '<input type="radio" name="predictioncategory" value="host-guest" id="predictioncategory-freeenergy2" ' . $cat_checked . "/> Host Guest<br />\n";
                                                        break;

                                                    case 'physical-properties':
                                                        echo '<input type="radio" name="predictioncategory" value="physical-properties" id="predictioncategory-freeenergy2" ' . $cat_checked . "/> Physical Properties<br />\n";
                                                        break;

                                                }
                                            }

                                    ?>
                                        </div>
                               
                                <?php if ($component_data->color1_json->protocols): ?>
         
                                    <strong>Protocol Files</strong><br />
                                    <div style="padding-left: 20px;">
                                    <?php
                                    $proto = new Challenge_Submission;
                                    $p_array = array('pp'=>'PosePredictionProtocol.txt', 'ls'=>'LigandScoringProtocol.txt', 'fe'=>'FreeEnergyProtocol.txt');
                                    foreach (array_keys($p_array) as $twoletter) {
                                        $ff = $proto->GetProtocolsByUID($_SESSION['user']->id);
                                        echo "<div id='protocoldiv-" . $twoletter . "' class='hide'>\n";
                                        echo $p_array[$twoletter] . " <select id='menu-" . $twoletter . "' name='menu-" . $twoletter. "'>\n";
                                        echo "<option value='0'>Choose a protocol file</option>\n";
                                        foreach ($ff as $file) {
                                            if (substr($file['protocol_id'], -2) == $twoletter)
                                                echo "<option value='" . $file['id'] . "'>" . $file['protocol_id'] . " " . $file['filename']. "</option>\n";
                                        }
                                        echo "</select>\n";
                                        echo "</div>";
                                    }

                                    ?>
                                    <script type="text/javascript">
                                        show_ls();

                                        function show_pp(){
                                        $('#protocoldiv-pp').attr('class','show');
                                          $('#protocoldiv-ls').attr('class','hide');
                                          $('#protocoldiv-fe').attr('class','hide');
                                        }
                                        function show_ls(){
                                          $('#protocoldiv-ls').attr('class','show');
                                        $('#protocoldiv-pp').attr('class','hide');
                                          $('#protocoldiv-fe').attr('class','hide');
                                        }
                                        function show_ss(){
                                        $('#protocoldiv-pp').attr('class','show');
                                          $('#protocoldiv-ls').attr('class','show');
                                          $('#protocoldiv-fe').attr('class','hide');
                                        }

                                        function show_fe(){
                                        $('#protocoldiv-pp').attr('class','show');
                                          $('#protocoldiv-fe').attr('class','show');
                                          $('#protocoldiv-ls').attr('class','hide');
                                        }

                                        function show_fe_fe(){
                                        $('#protocoldiv-pp').attr('class','show');
                                          $('#protocoldiv-fe').attr('class','show');
                                          $('#protocoldiv-ls').attr('class','hide');
                                        }

                                      function show_fe_sb(){
                                        $('#protocoldiv-pp').attr('class','show');
                                          $('#protocoldiv-ls').attr('class','show');
                                          $('#protocoldiv-fe').attr('class','hide');
                                        }

                                        function show_fe_lb(){
                                          $('#protocoldiv-ls').attr('class','show');
                                        $('#protocoldiv-pp').attr('class','hide');
                                          $('#protocoldiv-fe').attr('class','hide');
                                        }
                                    </script>
                                    </div>
                        <?php
                            endif;

                            // Show/Hide Anonymous checkbox
                            if ($component_data->color1_json->allow_anonymous):
                        ?>
                                    <strong>Anonymous Submissions</strong>
                                        <div style="padding-left: 20px;">
                                            <input type="checkbox" name="anonymous" value="1" /> I would like this submission to be anonymous.<br />
                                        </div>
                                            <br />
                        <?php endif; ?>
                                    <input type='hidden' id='category_required' name='category_required' value='1' />
                                    <div id="uploader">
                                        <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
                                    </div>
                                    <div class="the-return"></div>
                                </div> <!-- example -->
                                </form>
                            </div> <!-- well well-sm -->

        <?php
                endif; // show_uploader
                    

                    echo "</div>\n";
                }   // foreach
            endif; ?>

</div> <!-- close id='submissions' -->
