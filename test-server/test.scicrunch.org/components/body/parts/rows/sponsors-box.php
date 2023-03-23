<?php
$holder = new Component_Data();
$datas = $holder->getByComponent($component->component, $community->id, 0, 20);
?>
<style>
    a:hover.sponsors-link {
        color: #<?php echo Component::getColorStatic($component, $community, 1); ?>;
    }
</style>

<script>
    $(function(){
        $("#sponsors-text").owlCarousel({
            loop:true,
            autoplay:true,
            autoplayTimeout: 2000,
            lazyload: true,
            smartSpeed: 700
        });
    });
</script>

<div class="container content <?php if ($vars['editmode']) echo 'editmode' ?>">
    <div class="headline">
        <h2><?php echo $component->text1 ?></h2>
    </div>
    <div class="owl-carousel" id="sponsors-text">
        <?php foreach($datas as $datum): ?>
            <div class="item">
                <a class="sponsors-link" href="<?php echo $datum->link ?>">
                    <img class="center-block sponsor-img img-responsive" src="/upload/community-components/<?php echo $datum->image ?>" />
                    <h3 class="text-center"><?php echo $datum->title ?></h3>
                </a>
            </div>
        <?php endforeach ?>
    </div>

    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        if ($componentCount > 0)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=up"><i class="fa fa-angle-up"></i><span class="button-text"> Shift Up</span></a>';
        if ($componentCount != $componentTotal - 1)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=down"><i class="fa fa-angle-down"></i><span class="button-text"> Shift Down</span></a>';
        echo '<button class="btn-u add-data-btn" componentType="body" componentID="' . $component->component . '" cid="' . $community->id . '"><i class="fa fa-plus"></i><span class="button-text"> Edit</span></button>';

        if($community->id==0)                                                                                                                                                                               
            echo '<a class="btn-u btn-u-purple" href="/account/scicrunch/dynamic/'.$component->component.'"><i class="fa fa-list-alt"></i></a>';
        else
            echo '<a class="btn-u btn-u-purple" href="/'.$community->portalName.'/account/communities/'.$community->portalName.'/dynamic/'.$component->component.'"><i class="fa fa-list-alt"></i></a>';

        echo '<button class="btn-u btn-u-default edit-body-btn" componentType="body" componentID="' . $component->id . '"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button><a href="javascript:void(0)" componentID="' . $component->id . '" community="' . $community->id . '" class="btn-u btn-u-red component-delete-btn"><i class="fa fa-times"></i><span class="button-text"> Delete</span></a></div>';
        echo '</div>';
    } ?>
</div>
