<?php

$community = $data["community"];
if(!$community) {
    return;
}

?>

<div>
    <h2>Lab not found</h2>
    <h4>To upload, share, release, and publish your data or explore non-published data, you must be a member of a verified lab. To become a lab member, you can <a href="<?php echo $data['community']->fullURL() . '/community-labs/list?labid=' . $lab->id; ?>">create or join a lab</a>.</h4>
    <a href='<?php echo $data['community']->fullURL(); ?>/community-labs/list'><span class='lab-button lab-small-button'>Join/Register</span></a>
</div>
