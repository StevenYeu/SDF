<?php

if (isset($run_counter))
    $run_counter++;
else
    $run_counter = 1;
        
if (!isset($checkuser)) {
// this script is run for every "community_components.component lab_block. To only run this
// once, we check a $checkuser flag that is set at the end of the script

    $main_lab = Lab::getUserMainLab($_SESSION["user"],$community->id);
    $lab_members = LabMembership::loadArrayBy(Array("labid"), Array($main_lab->id));

    if ($main_lab) {
        $open_data_url = $community->fullURL() . "/data/public?labid=" . $main_lab->id;        
        $dashboard_url = $community->fullURL() . "/community-labs/dashboard?labid=" . $main_lab->id;
        $join_lab_url = $community->fullURL() . "/community-labs/list";
    } else {
        $open_data_url = $community->fullURL() . "/community-labs/datasets";
        $dashboard_url = $community->fullURL() . "/community-labs/dashboard";
        $join_lab_url = $community->fullURL() . "/community-labs/list";
    }

    if (!($_SESSION['user']))
        $whoami = 'no_account';
    else {
        if ($community->isMember($_SESSION["user"])) {
            if ($main_lab->id) {
                $whoami = 'lab_member';
            } else
                $whoami = 'general_member';
        } else
            $whoami = 'registered_user';
    }

    if($community->id != 501 || $main_lab) { // Special setting for	ODC-TBI	no join	for now

        if ($community->isMember($_SESSION["user"])) {
            $show_join_now = false;

            if($community->id == 97 && is_null($main_lab)) {
                $open_data_url = $community->fullURL() . "/data/public";
            }

        } else {
            $show_join_now = true;

            if($community->id == 97) {
                $open_data_url = $community->fullURL() . "/data/public";
            } else {
                $open_data_url = $community->fullURL() . "/community-labs/main";
            }
        }

    } else {
        $show_join_now = true;
        $open_data_url = $community->fullURL() . "/community-labs/main";
    } // End Special Setting ODC-TBI

// special odc-tbi setting, while figuring out if logic above works for 501 ...
    if($community->id == 501)
        $open_data_url = $community->fullURL() . "/data/public";

    if(!is_null($main_lab)) {
        // send to admin page if moderator/pi
        if ($main_lab->isModerator($_SESSION['user']))
            $my_lab_url = $community->fullURL() . "/lab/admin?labid=" . $main_lab->id;
        else
            $my_lab_url = $community->fullURL() . "/lab?labid=" . $main_lab->id;

        $add_data_url = $community->fullURL() . "/lab/add-data?labid=" . $main_lab->id;
        $lab_data_url = $community->fullURL() . "/lab/all-datasets?labid=" . $main_lab->id;
    } else {
        $add_data_url = $community->fullURL() . "/community-labs/main";
        $my_lab_url = $community->fullURL() . "/community-labs/main";
        $lab_data_url = $community->fullURL() . "/community-labs/main";
    }

    $number_of_labs = Lab::getCommunityCount($community);
    $number_of_datasets = Dataset::getCommunityCount($community);


    $add_component = '';
    $add_buttons = Array(); 
    
?>

<style>
.btn-short {
  background-color: #3f57a6;
  border: none;
  color: white;
  padding: 12px 10px;
  font-size: 16px;
  cursor: pointer;
  height: 60px;
  font-size: 20px;
  border-radius: 10px;
  text-align: center;
  margin: 10px;
  width: 240px;
}

.labs-block-button {
    text-align: center;
    width: 200px;
    height: 200px;
    background-color: #3f57a6;
    display: inline-block;
    border-radius: 10px;
    font-size: 20px;
    margin: 20px;
    color: white;
    padding-top: 30px;
}

.labs-block-button-disabled {
    text-align: center;
    width: 200px;
    height: 200px;
    background-color: #D3D3D3;
    display: inline-block;
    border-radius: 10px;
    font-size: 20px;
    margin: 20px;
    color: white;
    padding-top: 30px;
}

.labs-block-counter {
    color: #3f57a6;
    text-align: center;
}

.labs-block-icon {
    font-size: 65px;
}

.labs-block-icon2 {
    font-size: 65px;
    padding-right: 10px;
    color: #207ab3;
}
</style>

<div class="no-bottom-space container content <?php if($vars["editmode"]) echo 'editmode' ?>" style="padding-top: 10px">
    <div class="row">
        <div class="col-md-12">
            <h3 class="labs-block-counter" >
                <?php echo $community->name ?> currently has <strong><?php echo $number_of_labs ?> labs</strong> and <strong><?php echo $number_of_datasets ?> datasets</strong>.
            </h3>
        </div>
    </div>

    <div class="row">
        <div class = "col-md-12" style="text-align: center">
            <div id="add_buttons"></div>
        </div>
    </div>
    <br />
    <div id="add_component"></div>

<?php 
    
    $add_buttons['lab_member'] = <<<EOT
            <!-- Lab-block buttons -->
            <a href='$dashboard_url'>
                <button class='btn-short'><i class='fa fa-th-list' aria-hidden='true'></i> My Dashboard</button>
            </a>
            <a href='$my_lab_url'>
                <button class='btn-short'><i class='fa fa-flask' aria-hidden='true'></i> My Lab</button>
            </a>
              <a href='$open_data_url'>
                <button class='btn-short'><i class='fa fa-cloud-upload' aria-hidden='true'></i> Explore Public Data</button>
            </a>
EOT;

    $add_buttons['general_member'] = <<<EOT
            <!-- Lab-block buttons -->
            <a href='$dashboard_url'>
                <button class='btn-short'><i class='fa fa-th-list' aria-hidden='true'></i> My Dashboard</button>
            </a>
            <a href='$join_lab_url'>
                <button class='btn-short'><i class='fa fa-flask' aria-hidden='true'></i> Join a Lab</button>
            </a>
              <a href='$open_data_url'>
                <button class='btn-short'><i class='fa fa-cloud-upload' aria-hidden='true'></i> Explore Public Data</button>
            </a>
EOT;

    $add_buttons['registered_user'] = <<<EOT
            <!-- Lab-block buttons -->
            <a href='' data-toggle='modal' data-target='#joinModal'>
                <button class='btn-short'><i class='fa fa-database' aria-hidden='true'></i> Join ODC Community</button>
            </a>
            <button class='btn-short' style='background-color: #bbb'><i class='fa fa-flask' aria-hidden='true'></i> Join a Lab</button>
            <a href='$open_data_url'>
                <button class='btn-short'><i class='fa fa-cloud-upload' aria-hidden='true'></i> Explore Public Data</button>
            </a>
EOT;

    $add_buttons['no_account'] = <<<EOT
        <!-- Lab-block buttons -->
        <a href='#' class='btn-login'>
            <button class='btn-short'><i class='fa fa-flask' aria-hidden='true'></i> Login</button>
        </a>
        <a class='referer-link' href='/$community->portalName/join'>
            <button class='btn-short'><i class='fa fa-database' aria-hidden='true'></i> Create Account</button>
        </a>
            <button class='btn-short' style='background-color: #bbb'><i class='fa fa-th-list' aria-hidden='true'></i> My Dashboard</button>
        <a href='$open_data_url'>
            <button class='btn-short'><i class='fa fa-cloud-upload' aria-hidden='true'></i> Explore Public Data</button>
        </a>
EOT;
?>
    </div>

<?php
    // end first block that only gets included once
}

// Each user type component text gets added to $add_component, and then displayed one time.
// jQuery function should hide the blocks not related to the logged in user ...
    $add_component .= <<<EOT
    <div class='row checkuser'>
        <div class = 'col-md-12'>
            <h1>$component->text1</h1>
            <h3>$component->text3</h3>
        </div>
    </div>
EOT;



?>

<?php
$checkuser = 1; 

// since we have 4 user types, when we hit run_counter == 4, then run some scripts ...
if ($run_counter == 4): ?>
    <script>
            $(document).ready(function() {
                $("#add_buttons").html("<?php echo str_replace("\n", "\\n", $add_buttons[$whoami]); ?>");
                $("#add_component").html("<?php echo str_replace("\n", "\\n", $add_component); ?>");

                $(".checkuser").each(function(index) {
                    $(this).hide();
                    <?php 
                    switch ($whoami) {
                        case "no_account": ?>
                            if ($(this).find('.no_account').text().length)
                                $(this).show();
                        <?php
                            break;

                        case 'registered_user': ?>
                            if ($(this).find('.registered_user').text().length)
                                $(this).show();
                        <?php
                            break;

                        case 'general_member': ?>
                            if ($(this).find('.general_member').text().length) {
                                $(this).show();
                            }
                        <?php
                            break;

                        case 'lab_member': ?>
                            if ($(this).find('.lab_member').text().length)
                                $(this).show();
                        <?php
                            break;
                    }
                    ?>
                });

                $(".one-page").each(function(index) {
                    $(this).hide();

                    if ($(this).find(".<?php echo $whoami; ?>").text().length) {
                        $(this).show();
                    }
                });

                $("h1").each(function(index) {
                    if ($(this).find(".<?php echo $whoami; ?>").text().length) {
                        $(this).hide();
                    }
                });
            });
        </script>
<?php endif; ?>

