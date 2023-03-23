<?php

$community = $data["community"];

if(!$community) {
    return;
}

$lab_create_page = "lab-create.php";
if (isset($_GET['labid']))
    $lab_create_page = "lab-create.php?labid=" . $_GET['labid'];

?>


<div class="container">
    <div class="col-md-6 col-md-offset-3">
        <div class="text-center">
            <h2>Register a lab</h2>
        </div>
        <form class="sky-form margin-bottom-20" action="/forms/community-forms/<?php echo $lab_create_page; ?>" method="post">
            <fieldset>
                <section>
                    <label class="label">Name</label>
                    <label class="input"><input name="name" type="text" class="required" required /></label>
                </section>
                <section>
                    <label class="label">Private description</label>
                    <label class="input"><textarea name="private_description" class="required" style="width: 100%" rows="3" required /></textarea></label>
                </section>
                <section>
                    <label class="label">Public description</label>
                    <label class="input"><textarea name="public_description" class="required" style="width: 100%" rows="3" required /></textarea></label>
                </section>
            </fieldset>
            <input type="hidden" name="portal_name" value="<?php echo $community->portalName ?>" />
            <div style="margin-left: 30px; margin-bottom: 10px">
                <button class="btn btn-success">Create</button>
                <?php if(isset($_GET["error"])): ?>
                    <span style="color: red">Could not create lab</span>
                <?php endif ?>
            </div>
        </form>
    </div>
</div>
