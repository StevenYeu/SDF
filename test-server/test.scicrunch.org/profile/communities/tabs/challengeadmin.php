<div class="tab-pane fade <?php if ($section == 'challengeadmin') echo 'in active' ?>" id="challengeadmin">

<?php
// just to make extra sure non admins can't get to admin data ...
if ($_SESSION['user']->levels[$community->id] < 3) {
        echo "</div>\n";
        exit;
}

     /*
<h3>Please select a Challenge</h3>
<p>Note to myself: maybe provide summary information here and then send them to /php pages for more in depth admin ... </p>

<p>Probably don't want to do too much here since everything loads in the background regardless of what you're doing</p>

<p>Scicrunch interface gets very crowded and doesn't have things organized, so maybe I'll just have some elements here ...</p>
*/
?>
    <?php
    $chall = new Challenge();
    foreach (array('upcoming', 'active', 'completed') as $timeperiod) {
        echo "<div class='panel panel-profile'>\n";
        echo "    <div class='panel-heading overflow-h'>\n";
        echo "        <h2 class='panel-title heading-sm pull-left'>" . ucfirst($timeperiod) . " Challenges</h2>\n";
        echo "    </div>\n";

        echo "    <div class='panel-body margin-bottom-20'>\n";
        if (!($list = $chall->getChallengesByTimePeriod($timeperiod, $community->id)))
            echo "There are no " . $timeperiod . " challenges.\n";
        else {
            echo "        <div class='togglewrapper'>\n";
            foreach ($list as $challenge) {
                $joined = new Challenge();
                $joined_array = $joined->getJoined($challenge['component']);

                $submitted = new Challenge();
                $submitted_array = $submitted->getSubmissionByChallengeComponent($challenge['component']);

                echo "            <h3><i class='fa fa-caret-right' aria-hidden='true'></i>" . $challenge['text1'] . " (" . sizeof($joined_array) . " participants)</h3>\n";
                echo "        <div>\n";

                if (sizeof($joined_array)) {
                    echo "<table>\n";
                    echo "<thead>\n";
                    echo "<th width='20'></th><th width='150px'>First Name</th><th width='200px'>Last Name</th><th width='250px'>Email</th><th width='450px'>Organization</th>\n";
                    echo "</thead>\n<tbody>\n";
                    $cnt = 0;
                    foreach ($joined_array as $person) {
                        if ($cnt++ % 2)
                            echo "<tr>\n";
                        else
                            echo "<tr class='zebra'>\n";

                        $found = 0;
                        foreach ($submitted_array as $submitter) {
                            if ($submitter['uid'] == $person['uid']) {
                                echo "<td><i class='fa fa-check' aria-hidden='true'></i></td>\n";
                                $found = 1;
                            }
                        }

                        if (!$found)
                            echo "<td>&nbsp;</td>\n";

                        echo "    <td>" . $person['firstname'] . "</td>\n";
                        echo "    <td>" . $person['lastname'] . "</td>\n";
                        echo "    <td>" . $person['email'] . "</td>\n";
                        echo "    <td>" . $person['organization'] . "</td>\n";
                        echo "</tr>\n";
                    }
                    echo "</tbody>\n</table>\n";
                } else {
                    echo "No participants found\n";
                }
                
                echo "</div>\n";

                 //. "More challenge stuff here" . "</div>\n";
            }
            echo "</div>\n";
        }
        echo "</div> <!-- panel-body -->\n";
echo "</div> <!-- panel-profile -->\n";
    }
?>
</div>
