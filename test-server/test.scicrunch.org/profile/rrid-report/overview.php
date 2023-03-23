<?php
$rrid_reports = RRIDReport::loadArrayBy(Array("uid"), Array($_SESSION["user"]->id));
$base_uri = explode("?", $_SERVER["REQUEST_URI"])[0];
?>

<?php
echo Connection::createBreadCrumbs(
    "Authentication reports",
    Array("Home", "Account"),
    Array($profileBase, $profileBase."account"),
    "Authentication reports"
);
?>
<div class="profile container content">
    <div class="row">
        <?php if(isset($_SESSION["user"])): ?>
            <?php include $_SERVER["DOCUMENT_ROOT"] . "/profile/left-column.php"; ?>
        <?php else: ?>
            <div class="col-md-3">
                <?php echo \helper\htmlElement("login-page", Array("community" => $community)) ?>
            </div>
        <?php endif ?>
        <div class="col-md-9">
            <div class="profile-body">
                <?php echo \helper\htmlElement("rrid-report-overview", Array(
                    "user" => $_SESSION["user"],
                    "community" => $community,
                    "error" => $_GET["error"],
                )) ?>
            </div>
        </div>
    </div>
</div>

<div class="new-rrid-report html-modal back-hide no-padding">
    <div class="close dark less-right">X</div>
    <form method="post" id="name-form" action="/forms/rrid-report-forms/new-rrid-report.php" class="sky-form" enctype="multipart/form-data">
        <header>Create new authentication report</header>
        <fieldset>
            <section>
                <label class="label">Report name</label>
                <label class="input">
                    <input type="text" name="name" required>
                </label>
            </section>
            <section>
                <label class="label">Description</label>
                <label class="input">
                    <textarea name="description" style="width:100%" required></textarea>
                </label>
            </section>
        </fieldset>

        <footer>
            <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
        </footer>
    </form>
</div>
