<?php

$fields = Array(
    Array(
        "name" => "Resource_Suggestion_Name",
        "view-name" => "Resource Name",
        "required" => true,
    ),
    Array(
        "name" => "Resource_URL",
        "view-name" => "Resource URL",
        "required" => false,
    ),
    Array(
        "name" => "Description",
        "view-name" => "Description",
        "required" => false,
    ),
    Array(
        "name" => "Defining_Citation",
        "view-name" => "Defining Citation",
        "required" => false,
    ),
);

if(isset($_GET["rel"])) {
    $relationship = new Form_Relationship();
    $relationship->getByID($_GET["rel"]);
    if(!$relationship->id) {
        $typeid = 1;
    } else {
        $typeid = $relationship->rid;
    }
} else {
    $typeid = 1;
}

$logged_in = isset($_SESSION["user"]);

?>

<?php if(!isset($community)): ?>
    <div class="breadcrumbs-v3">
        <div class="container">
            <ul class="pull-left breadcrumb">
                <li><a href="/">Home</a></li>
                <li class="active">Resource Suggestion</li>
            </ul>
            <h1 class="pull-right">Resource Suggestion</h1>
        </div>
    </div>
<?php endif ?>

<style>
.required-color {
    color: #bb0000;
}
.required-label::after {
    content: " *";
    color: #bb0000;
}
</style>

<div class="container content">
    <section>
        <h4>
            Suggest a resource for the SciCrunch registry.  Your suggestion will be manually verified by a curator.  If it is approved, it will be given an RRID.
        </h4>
    </section>
    <form method="post" action="/forms/resource-forms/resource-suggestion.php" class="sky-form resource-form-validate captcha-form">
        <input type="hidden" value="<?php echo isset($community) ? $community->id : 0 ?>" name="cid" />
        <input type="hidden" value="<?php echo $typeid ?>" name="typeid" />
        <fieldset>
            <section>
                <?php if(!$logged_in): ?>
                    <label type="label" class="required-label">Email</label>
                    <label class="input"><input type="text" class="resource-field" name="email" required="required" /></label>
                <?php endif ?>
                <?php foreach($fields as $field): ?>
                    <label type="label"<?php if($field["required"]) echo ' class="required-label"'?>>
                        <?php echo $field["view-name"] ?>
                    </label>
                    <label class="input"><input type="text" class="resource-field <?php echo $field["name"] ?>" name="<?php echo $field["name"] ?>" <?php if($field["required"]) echo 'required="required"' ?> /></label>
                <?php endforeach ?>
            </section>
            <?php if(!$logged_in): ?>
                <section>
                    <div class="g-recaptcha" data-sitekey="<?php echo CAPTCHA_KEY ?>"></div>
                </section>
            <?php endif ?>
            <button class="btn btn-success" type="submit">Submit</button>
        </fieldset>
    </form>
</div>
