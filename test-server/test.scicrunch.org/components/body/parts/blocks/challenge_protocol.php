<?php 
$chset = new Challenge;
?>

<div id='protocols' class='tab-pane fade'>
    <?php printDataSetandTimeframe($component_data->title, 'Protocols', $component_data->start, $component_data->end);
            if ($isRegistered !== 'registered'):
                echo 'Please join the challenge and <a class="btn-login" href="#">Login</a>.';
            else:
        ?>          
                <style>
                     #protocolss td { font-size: .9em; }
                </style>

        <?php
                foreach ($set_assoc[$component_data->title] as $stage=>$stagevalue) {
                    if (($stage == 'content') || ($stage == 'comp_id'))
                        continue;

                    $show_manage_data = 0;
                    $show_uploader = 0;

                    echo "<div class='well'><h2>" . $stage . "</h2>\n";

                    $stage_component = $set_assoc[$component_data->title][$stage]['id'];
                    $chset->getChallengeSetByID($stage_component);
                    $stage_parameters = json_decode($chset->icon);

                    $special_people_array = explode(",", $stage_parameters->special_access);
                    if (in_array($_SESSION['user']->id, $special_people_array)) {
                        $show_manage_data = 1;
                        $show_uploader = 1;
                    }

                    // check the time
                    // future start date
                    if (strtotime($stage_parameters->open_submissions) > $now) {
                        echo "Protocol uploads for this challenge/stage starts on " . $stage_parameters->open_submissions;
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
                        $thereturn = $subs->getSubmissionByUser ($thisComp->cid, $stage_component, $_SESSION['user']->id) ;
                                    $proto = new Challenge_Submission;
                                    $p_array = array('pp'=>'PosePredictionProtocol.txt', 'ls'=>'LigandScoringProtocol.txt', 'fe'=>'FreeEnergyProtocol.txt');

$thereturn[] = 0;
               ?>
                    <div id="home-1" >
                        <div class=" margin-bottom-20 well well-sm">
                        <h3>Manage Your Protocol Files</h3>
                        <?php if (sizeof($thereturn) == 0) 
                            echo "No protocol files yet";
                        else {
                        ?>
                        <table id='protocolss'>
                        <thead>
                            <tr>
                                <th width="10%">Protocol ID</th>
                                <th width="30%">Name</th>
                                <th width="15%">Type</th>
                                <th width="20%">Date</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                                    $results = $proto->GetProtocolsByUID($_SESSION['user']->id);
                                    foreach ($results as $ff ) {
                                        if ($ff['component'] !== $stage_component)
                                            continue;

                                        echo "<tr>\n";
                                        echo "<td class='showing'>" . $ff['protocol_id'] . "</td>\n";
                                        echo "<td class='showing'><a href='/php/d3r/gc4/combined/spreadsheets/my_protocols.php?component=" . $component . "&receipt=" . $ff['protocol_id'] . "' target='_blank'>" . $ff['filename'] . "</a></td>\n";
                                        echo "<td class='showing'>" . $p_array[substr($ff['protocol_id'], -2)] . "</td>\n";
                                        echo "<td class='showing'>" . date("Y-m-d H:i", $ff['add_date']) . "</td>\n";
                                        if ($ff['submissions'])
                                            echo "<td class='showing'>* In use</td>\n";
                                        else
                                            echo '<td class="showing"><a href="/php/d3r_upload.php?action=protocolDelete&component=' . $component . '&protocol_id=' . $ff['protocol_id'] .'" class="receipt-delete" data-confirm="Are you sure you to delete this item?">Delete</a></td>' . "\n";
                                        echo "</tr>\n";
                                    }
                        ?>
                        <tr><td colspan="5" align="right">* "In use" protocols are linked to a submission(s). <br />
                        If you want to delete the protocol, you must first delete the linked submission file.</td></tr>
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
//$show_uploader=1000;
                if ($show_uploader): ?>
                            <div id="filelist00" style="display:none"><?php echo $stage_component; ?></div>

                            <div class="well well-sm">
                                <h2>Upload Your Protocol File</h2>
                                <form method="post" name="protoform" id="protoform">
                                <div id="example2">
                                    <strong>Prediction Categories</strong>
                                    <div style="padding-left: 20px;">
                                        Please select a protocol type:<br />
                                        <div style="padding-left: 20px; padding-bottom: 20px;">

<?php
$cat_checked = 1;

// If free energy or pose, then need to allow PosePredictionProtocol
if (isset($stage_parameters->predictioncategory->pose) || isset($stage_parameters->predictioncategory->freeenergy1))
    echo '<input type="radio" name="proto-type" value="pose" id="proto-type-pose" ' . $cat_checked . "/> Pose Prediction Protocol<br />\n";

if (isset($stage_parameters->predictioncategory->scoreligand))
echo '<input type="radio" name="proto-type" value="scoreligand" id="proto-type-scoreligand" ' . $cat_checked . "/> Ligand Scoring Protocol<br />\n";

if (isset($stage_parameters->predictioncategory->freeenergy1))
echo '<input type="radio" name="proto-type" value="freeenergy1" id="proto-type-freeenergy" ' . $cat_checked . "/> Free Energy Protocol (FEP, TI, MBAR, etc ...)<br />\n";
?>                                            
                                        </div>
                                        
                                    </div>

                                    
                                    <input type='hidden' id='protocol_type_required' name='protocol_type_required' value='1' />
                                    <div id="protocoluploader">
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
