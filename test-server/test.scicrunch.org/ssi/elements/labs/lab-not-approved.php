<?php

$lab = $data["lab"];
$submitted = $data["submitted"];

?>

<div>
    <?php if($submitted): ?>
        <h4>Your request to create "<?php echo $lab->name; ?>" has been received.</h4>
        <h4>The approval may take some time. We will evaluate your request and may email you for additional information. Please contact us at info@odc-sci.org if you do not hear back within a few days.</h4>
    <?php else: ?>
        <h4>
            During this beta testing phase, the community owner will need to approve the lab before you are able to start contributing data.  An email will be sent to you after the owner has reviewed your lab.
        <h4>
    <?php endif ?>
</div>
