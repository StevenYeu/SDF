<div class="tab-pane fade <?php if ($section == 'content') echo 'in active' ?>" id="content">

    <div class="pull-right margin-bottom-20">
        <a class="btn-u btn-u-purple" href="/faq/tutorials/45">View Tutorial</a>
        <button type="button" class="btn-u container-add">Add New Container</button>
    </div>

    <?php $start = true; ?>
    <?php foreach ($components['page'] as $container): ?>
        <?php
            if ($container->image == 'static') {
                $static[] = $container;
                continue;
            }
        ?>

        <!--if ($start) {-->
        <div class="row margin-bottom-20">
            <div class="col-sm-12">
                <div class="panel panel-profile no-bg">
                    <div class="content-heading overflow-h" style="overflow: visible;height:45px;">
                        <h2 class="panel-title heading-sm pull-left">
                            <i class="fa fa-file-archive-o"></i>
                            <a href="<?php echo $container->buildURL($community)?>" style="text-align: center;margin-right: 10px;"> <?php echo $container->text1 ?></a>
                        </h2>

                        <div class="btn-group pull-right" style="margin-top:-4px;">
                            <a href="<?php echo $profileBase ?>account/communities/<?php echo $community->portalName ?>/view/<?php echo $container->component ?>" class="btn-u btn-u-default" style="text-align: center;margin-right: 10px;">See All</a>
                            <button type="button" class="btn-u btn-default dropdown-toggle" data-toggle="dropdown">Action
                                <i class="fa fa-angle-down"></i>
                            </button>

                            <ul class="dropdown-menu" role="menu">

                                <!--check if current icon1 is listable, if so, show add content button-->
                                <?php if($container->isListable()): ?>
                                    <li>
                                        <a href="<?php echo $profileBase ?>account/communities/<?php echo $community->portalName ?>/component/insert/<?php echo  $container->component ?>">
                                            <i class="fa fa-plus-circle"></i> Add Content
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php if($container->position > 0): ?>
                                    <li><a href="/forms/component-forms/container-component-shift.php?component=<?php echo $container->id ?>&cid=<?php echo $community->id ?>&direction=up"><i class="fa fa-angle-up"></i> Move Up</a></li>
                                <?php endif; ?>

                                <?php if($container->position < count($components['page'])-1): ?>
                                    <li><a href="/forms/component-forms/container-component-shift.php?component=<?php echo $container->id ?>&cid=<?php echo $community->id ?>&direction=down"><i class="fa fa-angle-down"></i> Move Down</a></li>
                                <?php endif; ?>

                                <li><a href="javascript:void(0)" class="edit-container" container="<?php echo $container->id ?>" community="<?php echo $community->id ?>"><i class="fa fa-wrench"></i> Edit Container</a></li>
                                <li><a href="/forms/component-forms/container-component-delete.php?cid=<?php echo $community->id ?>&id=<?php echo $container->id ?>"><i class="fa fa-times"></i> Delete</a></li>
                            </ul>

                            <!--if listable, drop down button shows-->
                            <?php if($container->isListable()): ?>
                                <span style="display: block; min-width: 200px;">
                                    <i class="fa fa-angle-down collapse-arrow" data-toggle="collapse" href="#panel-<?php echo $container->position ?>"></i>
                                </span>
                            <?php else: ?>
                                <!--blank space to replace down arrow-->
                                <span style="display: block; min-width: 200px;"><i></i></span>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!--if listable, allow dropdown functionality-->
                    <?php if ($container->isListable()): ?>
                        <div class="panel-body contentHolder panel-collapse collapse" style="width:100%" id="panel-<?php echo $container->position ?>">

                            <?php
                                $holder = new Component_Data();
                                $datas = $holder->getByComponentNewest($container->component, $community->id, 0, 10);
                            ?>

                            <?php if (count($datas) == 0): ?>
                                <h3 class="heading-xs">This container has no content yet. Click the action button to add content.</h3>
                            <?php endif; ?>

                            <?php foreach ($datas as $data): ?>
                                <div class="profile-event" style="clear:both">

                                    <?php if($community->id==0): ?>
                                        <?php echo $data->dropdown($profileBase.'account/scicrunch',$container->icon1) ?>
                                    <?php endif; ?>

                                    <?php echo $data->dropdown($profileBase.'account/communities/'.$community->portalName,$container->icon1) ?>
                                    <div class="overflow-h" style="display: inline-block">
                                        <!--URL navigates to specific entry-->
                                        <h3 class="heading-xs"><a href="<?php echo $data->makeURL($data) ?>" style="text-align: center;margin-right: 10px;"><?php echo $data->title ?></a></h3>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div> 
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endforeach; ?>  

    <?php if(count($static) > 0): ?>

        <div class="row margin-bottom-20">
            <div class="col-sm-6 md-margin-bottom-20">
                <div class="panel panel-profile no-bg">
                    <div class="panel-heading overflow-h" style="overflow: visible;height:45px;">
                        <h2 class="panel-title heading-sm pull-left"><i class="fa fa-file-archive-o"></i>Static Pages</h2>
                    </div>
                    <div id="scrollbar2" class="panel-body contentHolder" style="width:100%">
                        <?php foreach ($static as $data): ?>
                            <div class="profile-event">
                                <div class="btn-group pull-right" style="margin-top:-4px;">
                                    <button type="button" class="btn-u btn-default dropdown-toggle" data-toggle="dropdown">Action
                                        <i class="fa fa-angle-down"></i>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="javascript:void(0)" class="edit-container" container="<?php echo $container->id ?>" community="<?php echo $community->id ?>"><i class="fa fa-wrench"></i> Edit Container</a></li>
                                        <li><a href="/forms/component-forms/container-component-delete.php?cid=<?php echo $community->id ?>&id=<?php echo $data->id ?>"><i class="fa fa-times"></i> Delete</a></li>
                                    </ul>
                                </div>
                                <div class="overflow-h" style="display: inline-block">
                                    <h3 class="heading-xs"><a href="#"><?php echo $data->text1 ?></a></h3>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <hr/>
    <?php endif; ?>




    <div class="row margin-bottom-20" style="margin-top:20px;">
        <div class="col-sm-6 md-margin-bottom-20">
            <div class="panel panel-profile no-bg">
                <div class="panel-heading overflow-h" style="color: #fff;background: #585f69;">
                    <h2 class="panel-title heading-sm pull-left"><i class="fa fa-file-archive-o"></i>Questions</h2>
                    <a href="<?php echo $profileBase?>account/communities/<?php echo $community->portalName?>/component/insert/104"><i class="fa fa-plus-circle pull-right" style="color: white;"></i></a>
                </div>
                <div id="scrollbar2" class="panel-body contentHolder">
                    <?php foreach ($questions as $data): ?>
                        <div class="profile-event">
                            <?php if($community->id==0): ?>
                                <?php echo $data->dropdown($profileBase.'account/scicrunch','questions'); ?>
                            <?php else: ?>
                                <?php echo $data->dropdown($profileBase.'account/communities/'.$community->portalName,'questions'); ?>
                            <?php endif; ?>
                            <div class="overflow-h" style="display: inline-block">
                                <h3 class="heading-xs"><a href="#"><?php echo $data->title ?></a></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    
        <div class="col-sm-6 md-margin-bottom-20">
            <div class="panel panel-profile no-bg">
                <div class="panel-heading overflow-h" style="color: #fff;background: #585f69;">
                    <h2 class="panel-title heading-sm pull-left"><i class="fa fa-file-archive-o"></i>Tutorials</h2>
                    <a href="<?php echo $profileBase?>account/communities/<?php echo $community->portalName ?>/component/insert/105"><i class="fa fa-plus-circle pull-right" style="color: white;"></i></a>
                </div>
                <div id="scrollbar2" class="panel-body contentHolder">
                    <?php foreach ($tutorials as $data): ?>
                        <div class="profile-event">
                            <?php if ($community->id==0): ?>
                                <?php echo $data->dropdown($profileBase.'account/scicrunch','tutorials'); ?>
                            <?php else: ?>
                                <?php echo  $data->dropdown($profileBase.'account/communities/'.$community->portalName,'tutorials'); ?>
                            <?php endif; ?>
    
                            <div class="overflow-h" style="display: inline-block">
                                <h3 class="heading-xs"><a href="#"><?php echo $data->title ?></a></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="<?php echo $profileBase?>account/communities/<?php echo $community->portalName?>/view/105" class="btn-u btn-u-default" style="width:100%;text-align: center;margin-top: 10px;">SEE ALL</a>
            </div>
        </div>
    </div>
</div>
<div class="cont-select-container back-hide">
    <div class="close dark">X</div>
    <div class="selection">
        <h2 align="center">Select a Container to Add</h2>

        <div class="components-select">
            <?php 
            $holder = new Component();
            echo $holder->getContainerSelectHTML($community->id);
            ?>
        </div>
    </div>
</div>
<div class="container-add-load back-hide"></div>
