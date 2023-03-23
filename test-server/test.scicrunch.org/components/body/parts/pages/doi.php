<div class="container content">
<?php

            if (!(isset($_SESSION['user']))) {  // if not a user, give register / login links
                ?>
                <div id="join1">Create new <a href='/<?php echo $community->portalName; ?>/join'><?php echo $community->shortName; ?> Account</a> and/or <a
                        class="btn-login"
                        href="#">log in</a> to download the file.
                </div>
<?php
} else {
    $filename = $thisComp->text2 . ".zip";

    echo "<table >";
    echo "  <tr>";
    echo "    <th width=800><H1>" . strtoupper($community->portalName) . " Public Dataset</H1></th>";
    echo "    <th>";
    echo "<div><a href='/php/file-download.php?type=doi&doi=" . $thisComp->text2 . "'><img src='/images/csv-file-format-extension.png'></a><br />";
    echo "    </th>";
    echo "    <th>";
    echo "    </th>";
    echo "    <th>";
    echo "<div><a href='/php/file-download.php?type=dict&doi=" . $thisComp->text2 . "'><img src='/images/csv-file-format-extension.png'></a><br />";
    echo "    </th>";
    echo "  </tr>";
    echo "  <tr>";
    echo "    <th>&nbsp;</th>";
    echo "    <th><div align=center>Data File</div></th>";
    echo "    <th>&nbsp;</th>";
    echo "    <th><div align=center>Data Dictionary</div></th>";
    echo "  </tr>";
    echo " </table>";


}
?>
</div>
<div class="container content <?php if($vars['editmode']) echo 'editmode' ?>">
    <div class="row-fluid privacy">
        <?php echo $thisComp->text3 ?>
    </div>
    <?php
    if ($vars['editmode']) {
    echo '<div class="body-overlay"><h3>Container Options</h3>';
    echo '<div class="pull-right">';
    echo '<button class="btn-u btn-u-default edit-body-btn" componentType="data" componentID="' . $thisComp->component . '"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button><a href="javascript:void(0)" componentID="' . $thisComp->component . '" community="' . $community->id . '" class="btn-u btn-u-red article-delete-btn"><i class="fa fa-times"></i><span class="button-text"> Delete</span></a></div>';
    echo '</div>';
    if (count($tags) > 0) {
        foreach ($tags as $tag) {
            $tagText[] = $tag->tag;
        }
        $tt = join(', ', $tagText);
    } else {
        $tt = '';
    }

    ?>


    <div class="custom-form back-hide no-padding">
        <div class="close light less-right">X</div>
        <style>
            .servive-block-default {
                cursor: pointer;
            }

            .panel-dark .panel-heading {
                background: #555;
                color: #fff;
            }
        </style>
        <form method="post" action="/forms/component-forms/container-component-edit.php?cid=<?php echo $community->id ?>&id=<?php echo $thisComp->id ?>" id="header-component-form" class="sky-form" enctype="multipart/form-data">
            <?php echo $thisComp->bodyComponentHTML(0, 0, false, array()); ?>
            <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
        </form>
    </div>
    <div class="article-delete back-hide">
        <div class="close dark">X</div>
        <h2 style="margin-bottom: 40px">Are you sure you want to delete this page?</h2>
        <a href="javascript:void(0)" class="btn-u close-btn">No</a>
        <a href="/forms/component-forms/container-component-delete.php?cid=<?php echo $community->id ?>&id=<?php echo $thisComp->id ?>"
           class="btn-u btn-u-default" style="">Yes</a>

    </div>
<?php
}?>
</div>
