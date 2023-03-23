<?php echo Connection:: createBreadCrumbs("Labs", Array("Home", "Account"), Array($profileBase, $profileBase . "account"), "Labs") ?>
<div class="profile container content">
    <div class="row">
        <?php include $GLOBALS["DOCUMENT_ROOT"] . "/profile/left-column.php"; ?>
        <div class="col-md-9">
            <?php
                echo \helper\htmlElement("lab-list", Array(
                    "community" => $community,
                    "user" => $_SESSION["user"],
                ));
            ?>
        </div>
    </div>
</div>
