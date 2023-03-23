<?php foreach ($views as $i => $view): ?>

    <?php if ($i % 3 == 0): ?>
        <div class="row">
    <?php endif ?>
    <div class="col-sm-4 ">
        <div class="tag-box tag-box-v2 box-shadow shadow-effect-1">
            <div class="grid-boxes-caption ">
                <div class="the-title">
                    <h3 style="display: inline-block">
                        <?php echo $view->getTitle() ?>
                    </h3>
                    <?php if (count($colors[$view->nif]) > 0): ?>
                        <div class="circle-container body-hide"><div class="circle" style="display:inline-block;margin-left:10px;vertical-align:middle" id="circle-<?php echo $view->nif ?>" num="<?php echo  count($colors[$view->nif]) ?>" colors="<?php echo join(',', $colors[$view->nif]) ?>"></div>
                        <div class="who-container no-propagation shadow-effect-1"><h3 align="center" style="margin:0;text-decoration: underline">Used in</h3>
                        <?php foreach ($colors[$view->nif] as $j => $color): ?>
                            <?php if ($who[$view->nif][$j]->id == $community->id): ?>
                                <div><i class="fa fa-square" style="color:<?php echo $color ?>"></i> <?php echo $who[$view->nif][$j]->name ?></div>
                            <?php else: ?>
                                <div><a target="_blank" href="/<?php echo $who[$view->nif][$j]->portalName ?>"><i class="fa fa-square" style="color:<?php echo $color ?>"></i> <?php echo $who[$view->nif][$j]->name ?></a></div>
                            <?php endif ?>
                        <?php endforeach ?>
                        </div></div>
                    <?php endif ?>
                </div>

                <p><?php echo $view->description ?></p>

                <ul class="list-unstyled specifies-list" style="margin-top:10px">
                    <li style="margin-bottom:10px">
                        <i class="fa fa-caret-right"></i> <b><u>Records:</u></b> <a target="_self" style="color:#72c02c" href="/<?php echo $community->portalName?>/data/source/<?php echo $view->nif?>/search"><?php echo number_format($view->data) ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <?php if ($i % 3 == 2): ?>
        </div>
    <?php endif ?>
<?php endforeach ?>
<?php if ($i % 3 != 2): ?>
    </div>
<?php endif ?>
