<?php

$community = $data["community"];
$text = $data["text"];

?>

<?php if((is_null($community->mailchimp_api_key)) || (is_null($community->mailchimp_default_list))): ?>
    <a href="/forms/login.php?join=true&cid=<?php echo $community->id ?>"><?php echo $text ?></a>
<?php elseif(in_array($community->shortName, array("D3R"))): ?>
    <a href="/forms/login.php?join=true&mailchimp=true&cid=<?php echo $community->id ?>"><?php echo $text ?></a>
<?php else: ?>
    <a href data-toggle="modal" data-target="#joinModal"><?php echo $text ?></a>
<?php endif ?>
