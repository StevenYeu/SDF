<?php 

require_once __DIR__ . "/../../../api-classes/labs.php";

$lab = $data["lab"];
$community = $data["community"];
$user = $data["user"];
$user_labs = $data['user_labs'];
$main_lab = $data['main_lab'];

$dashboard_url = $community->fullURL() . "/community-labs/dashboard";
if ($lab->id)
    $dashboard_url .= "?labid=" . $lab->id;
else {
    if ($main_lab) {
        $lab = $main_lab;
    }
}

?>
		
<div id="main-content" class="main">
    <div id="sidebarr">
        <nav id="navList" class="devsite-section-nav devsite-nav nocontent" style="left: auto; max-height: 643px; position: relative; top: 0px;">    
        <ul  id="odc" class="devsite-nav-list devsite-nav-expandable">
            <li class="devsite-nav-item devsite-nav-item-heading1">
                <span class="devsite-nav-title devsite-nav-title-no-path" track-type="leftNav" track-name="expandNavSectionNoLink" track-metadata-position="0">
                    <span id=""><strong>My Dashboard</strong></span>
                </span>
            </li>
            <li class="devsite-nav-item devsite-nav-item">
                <a class="devsite-nav-title lab-link" href="<?php echo $dashboard_url; ?>">
                    <span>My Dashboard</span>
                </a>
            </li>

<?php 
    // if no main_lab, then must be a general_member. so, remove all links
    if ($main_lab): ?>
            <li class="devsite-nav-item devsite-nav-item-heading">
                <span class="devsite-nav-title devsite-nav-title-no-path lab-link" track-type="leftNav" track-name="expandNavSectionNoLink" track-metadata-position="0">
                    <span><strong>My Data</strong></span>
                </span>
            </li>
              <li class="devsite-nav-item"><a class="devsite-nav-title lab-link" href="<?php echo $data['community']->fullURL() ?>/lab/my-datasets?labid=<?php echo $data['lab']->id; ?>&datasetid=<?php echo $data['dataset']; ?>">My Datasets</a>
                <span style="padding-left: 10px;">(<?php echo $lab->name; ?></span>)</li>

              <li class="devsite-nav-item"><a class="devsite-nav-title lab-link" href="<?php echo $data['community']->fullURL() ?>/lab/create-dataset?labid=<?php echo $data['lab']->id; ?>">Upload New Dataset</a></li>

              <li class="devsite-nav-item"><a class="devsite-nav-title lab-link"  data-toggle="tooltip" title="Coming soon">
                <span style="color: #999">Follow Datasets</span></a></li>

              <li class="devsite-nav-item"><a href="#" data-toggle="tooltip" title="Coming soon">
                <span style="color: #999">Share Datasets</span></a></li>

<?php // no main lab, so gray out
    else: ?>
            <li class="devsite-nav-item devsite-nav-item-heading">
                <strong>My Data</strong>
            </li>
            <a href="#" data-toggle="tooltip" title="Must be a lab member">
              <li class="devsite-nav-item-disabled">My Datasets</li>
              <li class="devsite-nav-item-disabled">Upload New Dataset</li>
              <li class="devsite-nav-item-disabled">Publish Datasets</li>
              <li class="devsite-nav-item-disabled">Follow Datasets</li>
              <li class="devsite-nav-item-disabled">Share Datasets</li>
              </a>
<?php endif; ?>

            <li class="devsite-nav-item devsite-nav-item-heading">
                <span class="devsite-nav-title devsite-nav-title-no-path" track-type="leftNav" track-name="expandNavSectionNoLink" track-metadata-position="0">
                    <span><strong>My Labs</strong></span>
                </span>
            </li>
        <?php if ($main_lab): ?>
              <li class="devsite-nav-item">
                <?php
                    if ($lab->isModerator($user))
                        $lab_link = '/lab/admin?labid=' . $lab->id;
                    else
                        $lab_link = '/lab?labid=' . $lab->id;
                ?>
                <span><?php echo '<a class="lab-link" href="' . $community->fullURL() . $lab_link . '">'; ?>Current Lab Space</a></span><br />
                <span style="padding-left: 10px;">(<?php echo $lab->name; ?></span>)</li>

              <li class="devsite-nav-item"><a href="#" class="devsite-nav-title lab-link">
                <span>Switch Lab</span></a>
                <ul class="listTab">
                  <?php foreach($user_labs as $ulab) {
                    if ($ulab->id != $_GET['labid']) {
                        if ($ulab->isModerator($user))
                            $lab_link = '/lab/admin?labid=' . $ulab->id;
                        else
                            $lab_link = '/lab?labid=' . $ulab->id;
                        echo '<li><a href="' . $community->fullURL() . $lab_link . '">' . $ulab->name . "</a></li>\n";
                    }
                } ?>
                </ul>
                </li>
              <li class="devsite-nav-item"><a class="devsite-nav-title lab-link" href="<?php echo $data['community']->fullURL() ?>/community-labs/list?labid=<?php echo $_GET['labid']; ?>">
                <span>Register/Join a Lab</span></a></li>
        <?php else: ?>
              <li class="devsite-nav-item"><a class="devsite-nav-title lab-link" href="<?php echo $data['community']->fullURL() ?>/community-labs/list">
                <span>Register/Join a Lab</span></a></li>
        <?php endif; ?>
                             

            <li class="devsite-nav-item devsite-nav-item-heading">
                <span class="devsite-nav-title devsite-nav-title-no-path" track-type="leftNav" track-name="expandNavSectionNoLink" track-metadata-position="0">
                    <span><strong>Commons</strong></span>
                </span>
            </li>
              <li class="devsite-nav-item"><a class="devsite-nav-title lab-link" href="<?php echo $data['community']->fullURL() ?>/data/public?labid=<?php echo $_GET['labid']; ?>">
                <span>Published Datasets</span></a></li>
              <li class="devsite-nav-item"><a class="devsite-nav-title lab-link" href="<?php echo $data['community']->fullURL() ?>/community-labs/datasets<?php if(isset($_GET['labid'])) echo "?labid=" . $lab->id; ?>">
                <span>Commons Data</span></a></li>
<!--
            <li class="devsite-nav-item devsite-nav-item-heading">
                <span class="devsite-nav-title devsite-nav-title-no-path" track-type="leftNav" track-name="expandNavSectionNoLink" track-metadata-position="0">
                    <span><strong>Preferences</strong></span>
                </span>
            </li>
              <li class="devsite-nav-item"><a href="#" class="devsite-nav-title" >
                <span>General Settings</span></a></li>
-->
            <li class="devsite-nav-item devsite-nav-item-heading">
                <span class="devsite-nav-title devsite-nav-title-no-path" track-type="leftNav" track-name="expandNavSectionNoLink" track-metadata-position="0">
                    <span><strong>Help</strong></span>
                </span>
            </li>
              <li class="devsite-nav-item"><a class="lab-link" href="<?php echo $data['community']->fullURL() ?>/about/help" class="devsite-nav-title" target="_blank">
                <span>General Help/FAQ</span></a></li>
              <li class="devsite-nav-item"><a class="lab-link" href="<?php echo $data['community']->fullURL() ?>/about/tutorials" class="devsite-nav-title" target="_blank">
                <span>Tutorials</span></a></li>
              <li class="devsite-nav-item"> <a href="#" data-toggle="tooltip" title="Coming soon">
                <span style="color: #999">For Developers</span></a></li>

            </ul>
          </nav>

    </div>
    <div id="contentt" class="contentt">
        <div class="row">
            <div class="col-md-12">
                <ul id="odc_breadcrumb" class="breadcrumb">
                <?php echo $data['crumb']; ?>
                </ul>
            </div>
        </div>
        
        <!-- Content goes here -->
            <!-- main content -->
