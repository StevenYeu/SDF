<?php /* ################################################## RESOURCE REPORTS ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/rin/rrids">
    <span class="fa-stack fa-md">
        <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
        <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
    </span>
    Resource Reports
</a>
<?php $cell1_title = ob_get_clean(); ?>
<?php ob_start(); ?>
<span style="font-size:20px">Is my antibody specific? Who else is using my software tools? Answer these questions and more using Research Resource Identifiers (RRIDs) and Digital Object Identifier (DOIs).</span>
<?php $cell1_body1 = ob_get_clean(); ?>
<?php ob_start(); /* TO DO find out why $community->fullURL() doesn't work properly here */ ?>
<a href="<?php echo $community->fullURL() ?>/data/source/nlx_144509-1/search">Tools</a> |
<a href="<?php echo $community->fullURL() ?>/data/source/SCR_013869-1/search">Cell lines</a> |
<a href="<?php echo $community->fullURL() ?>/data/source/nif-0000-07730-1/search">Antibodies</a> |
<a href="<?php echo $community->fullURL() ?>/data/source/nlx_154697-1/search">Organisms</a> |
<a href="<?php echo $community->fullURL() ?>/data/source/nif-0000-11872-1/search">Plasmids</a> |
<a href="<?php echo $community->fullURL() ?>/data/source/nlx_143929-1/search">Biosamples</a> |
<a href="<?php echo $community->fullURL() ?>/data/source/protocol/search">Protocols</a>
<?php $cell1_body2 = ob_get_clean(); ?>

<?php /* ################################################## DISCOVERY PORTAL ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/search">
    <span class="fa-stack fa-md">
        <i class="fa fa-circle fa-stack-2x" style="color:#76BD43"></i>
        <i class="fa fa-search fa-stack-1x fa-inverse"></i>
    </span>
    Discovery Portal
</a>
<?php $cell2_title = ob_get_clean(); ?>
<?php ob_start(); ?>
<span style="font-size:20px">Search across 100s of biomedical databases for...</span>
<?php $cell2_body1 = ob_get_clean(); ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/search#category-filter=Category:Grants&category-filter=Output%20Type:Funding">Funding</a> |
<a href="<?php echo $community->fullURL() ?>/data/search#category-filter=Category:Images">Images</a> |
<a href="<?php echo $community->fullURL() ?>/data/search#category-filter=Category:Phenotype">Phenotypes</a> |
<a href="<?php echo $community->fullURL() ?>/literature/search?q=%2A&l=">Literature</a> |
<a href="<?php echo $community->fullURL() ?>/data/search#all">and more</a>
<br>
<!-- <?php if ($_SESSION['user']->role == 2): ?>
    <img src="/images/BetaTest64x54.png"> <a href="<?php echo $community->fullURL() ?>/discovery/source/rin,ks/search">test elastic search discovery</a>
<?php endif ?> -->
<?php $cell2_body2 = ob_get_clean(); ?>

<?php /* ################################################## RIGOR REPRODUCIBILITY ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/rin/rigor-reproducibility-about">
    <span class="fa-stack fa-md">
        <i class="fa fa-circle fa-stack-2x" style="color:#F57F29"></i>
        <i class="fa fa-gears fa-stack-1x fa-inverse"></i>
    </span>
    Authentication Reports &amp; FAIR Data
</a>
<?php $cell3_title = ob_get_clean(); ?>
<?php ob_start(); ?>
<span style="font-size:20px">View resources on how to comply with NIH's new policies on authentication of key biological resources, using our authentication reports, and making data FAIR.</span>
<?php $cell3_body1 = ob_get_clean(); ?>
<?php ob_start(); ?>
    <a href="<?php echo $community->fullURL() ?>/rin/rrid-report">Authentication reports</a> |
    <a href="<?php echo $community->fullURL() ?>/rin/research-data-management">Research data management</a> |
    <a href="<?php echo $community->fullURL() ?>/rin/suggested-data-repositories">Suggested data repositories</a>
    <!-- <a href="<?php echo $community->fullURL() ?>/rin/suggested-data-repositories?p1=SCR_001606&p2=SCR_011446,SCR_012895&reltype=3,14">Suggested data repositories</a> -->
    <!-- <a href="https://dknet.org/about/Suggested-data-repositories-niddk">Suggested data repositories</a> -->
<?php $cell3_body2 = ob_get_clean(); ?>

<?php /* ################################################## SPP ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/about/hypothesis_center">
    <span class="fa-stack fa-md">
        <i class="fa fa-circle fa-stack-2x" style="color:#CCCCCC"></i>
    </span>
    <img src="/images/SigPath_curved.png" style="position: absolute; width: 65px; left: 10px; top: 2px" />
    <!--changed "Signaling Pathways Project" to "Hypothesis Center" - Vicky-2018-11-21 -->
    Hypothesis Center
</a>
<?php $cell4_title = ob_get_clean(); ?>
<?php ob_start(); ?>
<span style="font-size:20px">Analyze diverse ‘omics data to generate or test research hypotheses – powered by the Signaling Pathways Project.</span>
<?php $cell4_body2 = ob_get_clean(); ?>

<div class="rrid-comp-root <?php if($vars["editmode"]) echo 'editmode' ?>">

    <?php echo \helper\htmlElement("rin-style-page", Array(
        "title" => "dkNET: Connecting Researchers to Resources",
        "title-center" => true,
        "rows" => Array(
            Array(
                /* Resource Reports */
                Array(
                    "title" => $cell1_title,
                    "body" => Array(
                        Array("p" => $cell1_body1),
                        Array("p" => $cell1_body2),
                    )
                ),

                /* Discovery Portal */
                Array(
                    "title" => $cell2_title,
                    "body" => Array(
                        Array("p" => $cell2_body1),
                        Array("p" => $cell2_body2),
                    )
                ),
            ),
            Array(
                /* Rigor Reporducibility */
                Array(
                    "title" => $cell3_title,
                    "body" => Array(
                        Array("p" => $cell3_body1),
                        Array("p" => $cell3_body2),
                    )
                ),

                /* SPP */
                Array(
                    "title" => $cell4_title,
                    "body" => Array(
                        Array("p" => $cell4_body1),
                        Array("p" => $cell4_body2),
                    )
                ),
            ),
        ),
    )) ?>


    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        if ($componentCount > 0)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=up"><i class="fa fa-angle-up"></i><span class="button-text"> Shift Up</span></a>';
        if ($componentCount != $componentTotal - 1)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=down"><i class="fa fa-angle-down"></i><span class="button-text"> Shift Down</span></a>';
        echo '<button class="btn-u btn-u-default edit-body-btn" componentType="body" componentID="'.$component->id.'"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button><a href="javascript:void(0)" componentID="'.$component->id.'" community="'.$community->id.'" class="btn-u btn-u-red component-delete-btn"><i class="fa fa-times"></i><span class="button-text"> Delete</span></a></div>';
        echo '</div>';
    } ?>
</div>
