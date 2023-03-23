<h4>
    An account is required to to use the lab and data sharing features.  Please sign in or create an account below.
</h4>
<?php 
    $community = $data["community"];
?>
<?php echo \helper\htmlElement("login-page", Array("community" => $community)) ?>
