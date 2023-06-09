<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);
include '../classes/classes.php';
\helper\scicrunch_session_start();

$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

$component = new Component();
$component->getByID($id);
$components = $component->getByCommunity($cid);
?>

<div class="close light">X</div>
<form method="post" action="/forms/component-forms/body-components.php?cid=<?php echo $component->cid ?>"
      id="header-component-form" class="sky-form" enctype="multipart/form-data">
    <?php echo $component->bodyComponentHTML(0, 0, false,$components['body']); ?>
    <footer>
        <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
    </footer>
</form>



<script type="text/javascript">
    Validation.initValidation();
    jQuery.validator.addMethod("accept", function(value, element, param) {
        return value.match(new RegExp(param));
    }, $.format("Acceptable characters are: A-F, a-f, and 0-9"));
    jQuery.validator.addClassRules('color-input', {
        maxlength: 6,
        minlength:6,
        required: false,
        accept: "[0-9a-fA-F]*"
    });
    $('.summer-text').summernote({
        height: 200,                 // set editor height

        minHeight: null,             // set minimum height of editor
        maxHeight: null,             // set maximum height of editor

        focus: false,                 // set focus to editable area after initializing summernote
        toolbar: [
            //[groupname, [button list]]

            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['insert', ['link', 'hr', 'picture']],
            ['misc',['fullscreen','codeview']],
            ['help',['help']]
        ]
    });
</script>