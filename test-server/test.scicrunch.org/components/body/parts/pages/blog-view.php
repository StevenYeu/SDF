<?php
$holder = new Tag();
$popular = $holder->getPopularTags($thisComp->component, $community->id, 0, 15);
if($thisComp->icon1 == "blog1" && $community->id != 0 && $thisComp->include_scicrunch == 1) {
    $holder = new Component;
    $blogs = $holder->getByIcon1(0, "blog1");
    $holder = new Component_Data();
    foreach($blogs as $blog) {
        if($blog->text2 != "blog") continue;
        $blog_return = $holder->getByComponentNewest($blog->component, $blog->cid, ($page - 1) * 10, 10, $tag_filter);
        $datas = array_merge($datas, $blog_return);
        $blog_count = $holder->getCount($blog->component, $blog->cid);
        $count = max($count, $blog_count);
    }
    usort($datas, function($a, $b) {
        if($a->time > $b->time) return -1;
        if($a->time < $b->time) return 1;
        return 0;
    });
}

$pageBaseURL = "/" . $community->portalName . "/about/" . $thisComp->text2;
$tagURL = $pageBaseURL . "?tag=";
?>
<div class="container content">
<div class="row blog-page">
<!-- Left Sidebar -->
<div class="col-md-9 md-margin-bottom-40 <?php if ($vars['editmode']) echo 'editmode' ?>">

    <?php if($tag_filter): ?>
        <div class="col-md-8 col-md-offset-4">
            Tag filter:
            <a href="<?php echo $pageBaseURL ?>">
                <span class="label label-info"><?php echo $tag_filter ?> <span style="color:#F33"><i class="fa fa-times"></i></span></span>
            </a>
        </div>
    <?php endif ?>

    <?php
    $data = NULL;
    foreach ($datas as $data) {
        if (!$data->image)
            $image = null;
        else
            $image = '/upload/community-components/' . $data->image;
        ?>
        <div class="row blog blog-medium margin-bottom-40">
            <div class="col-md-4" style="text-align: center">
                <?php if($data->image) { ?>
                    <img src="<?php echo '/upload/community-components/' . $data->image ?>" class="img-responsive" alt="">
                <?php } ?>
            </div>
            <div class="col-md-8">
                <h2><a href="<?php echo $baseURL . $thisComp->text2 . '/' . $data->id ?>"><?php echo Component::macroExpand($data->title, Array("community" => $community)) ?></a>
                </h2>
                <ul class="list-unstyled list-inline blog-info">
                    <li><i class="fa fa-calendar"></i> <?php echo date('h:ia F j, Y', $data->time) ?></li>
                    <li><i class="fa fa-eye"></i> <a
                            href="<?php echo $baseURL . $thisComp->text2 . '/' . $data->id ?>"><?php echo $data->views ?>
                            views</a></li>
                    <li><i class="fa fa-tags"></i>
                        <?php
                        $tags = $data->getTags();
                        $html = array();
                        foreach ($tags as $tag) {
                            $html[] = '<a href="' . $tagURL . $tag->tag . '">' . $tag->tag . '</a>';
                        }
                        echo join(', ', $html);
                        ?>
                    </li>
                </ul>
                <?php if($data->description): ?>
                    <p><?php echo Component::macroExpand($data->description, Array("community" => $community)) ?></p>
                <?php else: ?>
                    <p><?php echo substr(strip_tags(Component::macroExpand($data->content, Array("community" => $community))), 0, 200) ?>...</p>
                <?php endif ?>

                <p>
                    <a class="btn-u btn-u-sm" href="<?php echo $baseURL . $thisComp->text2 . '/' . $data->id ?>">
                        Read More <i class="fa fa-angle-double-right margin-left-5"></i>
                    </a>
                </p>
            </div>
        </div>
        <!--End Blog Post-->

        <hr class="margin-bottom-40">
    <?php
    }
    ?>
    <!--End Blog Post-->

    <!--Pagination-->
    <div class="text-center">
        <ul class="pagination">
            <?php
            $page = $vars['page'];
            $max = ceil($count / 10);
            if ($page > 1)
                echo '<li><a href="/browse/resources/page/' . ($page - 1) . '?' . $params . '">«</a></li>';
            else
                echo '<li><a href="javascript:void(0)">«</a></li>';

            if ($page - 3 > 0) {
                $start = $page - 3;
            } else
                $start = 1;
            if ($page + 3 < $max) {
                $end = $page + 3;
            } else
                $end = $max;

            if ($start > 2) {
                echo '<li><a href="' . $baseURL . $thisComp->text2 . '/page/1">1</a></li>';
                echo '<li><a href="' . $baseURL . $thisComp->text2 . '/page/2">2</a></li>';
                echo '<li><a href="javascript:void(0)">..</a></li>';
            }

            for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) {
                    echo '<li class="active"><a href="javascript:void(0)">' . number_format($i) . '</a></li>';
                } else {
                    echo '<li><a href="' . $baseURL . $thisComp->text2 . '/page/' . $i . '">' . number_format($i) . '</a></li>';
                }
            }

            if ($end < $max - 3) {
                echo '<li><a href="javascript:void(0)">..</a></li>';
                echo '<li><a href="' . $baseURL . $thisComp->text2 . '/page/' . ($max - 1) . '">' . number_format($max - 1) . '</a></li>';
                echo '<li><a href="' . $baseURL . $thisComp->text2 . '/page/' . $max . '">' . number_format($max) . '</a></li>';
            }

            if ($page < $max)
                echo '<li><a href="' . $baseURL . $thisComp->text2 . '/page/' . ($page + 1) . '">»</a></li>';
            else
                echo '<li><a href="javascript:void(0)">»</a></li>';
            ?>
        </ul>
    </div>
    <!--End Pagination-->
    <?php
    if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>Container Options</h3>';
        echo '<div class="pull-right">';
        echo '<button class="btn-u btn-u simple-toggle" modal=".add-content-box" title="Add New ' . $thisComp->text1 . '"><i class="fa fa-plus"></i></button>
          <a title="Manage the data under this container" href="/' . $community->portalName . '/account/communities/' . $community->portalName . '/view/' . $thisComp->component . '" class="btn-u btn-u-blue"><i class="fa fa-pencil-square-o"></i></a>
          <button class="btn-u btn-u-default simple-toggle" modal=".custom-form" title="Edit Container"><i class="fa fa-cogs"></i></button>
          <a href="javascript:void(0)" componentID="' . $thisComp->component . '" community="' . $community->id . '" class="btn-u btn-u-red article-delete-btn"><i class="fa fa-times"></i><span class="button-text"> Delete</span></a></div>';
        echo '</div>';

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
            <form method="post"
                  action="/forms/component-forms/container-component-edit.php?cid=<?php echo $community->id ?>&id=<?php echo $thisComp->id ?>"
                  id="header-component-form" class="sky-form" enctype="multipart/form-data">
                <?php echo $thisComp->bodyComponentHTML(0, 0, false, array()); ?>
                <footer>
                    <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
                </footer>
            </form>
        </div>
        <div class="large-modal back-hide add-content-box no-padding">
            <div class="close dark less-right">X</div>
            <form method="post"
                  action="/forms/component-forms/component-insert.php?id=<?php echo $thisComp->component ?>&cid=<?php echo $community->id ?>"
                  id="header-component-form" class="sky-form" enctype="multipart/form-data">

                <header><h2>Add <?php echo $thisComp->text1 ?></h2></header>
                <div class="row margin-bottom-10">
                    <?php
                    $holder = new Component_Data();
                    echo $holder->getContainerDataForm($thisComp->icon1, '');
                    ?>
                </div>

                <footer>
                    <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
                </footer>
            </form>
        </div>
        <div class="article-delete back-hide">
            <div class="close dark">X</div>
            <h2 style="margin-bottom: 40px">Are you sure you want to delete this Container and all data added to
                it?</h2>
            <a href="javascript:void(0)" class="btn-u close-btn">No</a>
            <a href="/forms/component-forms/container-component-delete.php?cid=<?php echo $community->id ?>&id=<?php echo $thisComp->id ?>"
               class="btn-u btn-u-default" style="">Yes</a>

        </div>
    <?php
    }?>
</div>
<!-- End Left Sidebar -->

<!-- Right Sidebar -->
<div class="col-md-3 magazine-page">

    <a class="btn-u" style="width:100%;text-align: center" href="<?php echo $baseURL . $thisComp->text2 . '/rss' ?>">RSS
        Feed</a>
    <hr/>
    <!-- Search Bar -->
    <div class="headline headline-md"><h2>Search</h2></div>
    <form method="get" action="<?php echo $searchURL ?>">
        <div class="input-group margin-bottom-40">
            <input type="text" class="form-control" name="query" placeholder="Search">
            <input type="hidden" name="content" value="<?php echo $vars['title'] ?>"/>
                    <span class="input-group-btn">
                        <button class="btn-u" type="submit">Go</button>
                    </span>
        </div>
    </form>
    <!-- End Search Bar -->

    <!-- Posts -->
    <div class="posts margin-bottom-40">
        <div class="headline headline-md"><h2>
                Recent Posts</h2></div>
        <?php
        $datas = Array();
        if(!is_null($data)) $datas = $data->getByComponentNewest($thisComp->component, $community->id, 0, 3);
        foreach ($datas as $compData) {
            echo '<dl class="dl-horizontal">';
            if ($compData->image)
                echo '<dt><a href="#"><img src="/upload/community-components/' . $compData->image . '" class="img-responsive" alt="" /></a></dt>';
            else
                echo '<dt></dt>';
            echo '<dd>
                    <p>
                        <a href="' . $baseURL . $thisComp->text2 . '/' . $compData->id . '">
                            <p>' . Component::macroExpand($compData->title, Array("community" => $community)) . '</p>
                            <small>(' . date('h:ia F j, Y', $compData->time) . ')</small>
                        </a>
                    </p>
                </dd>
            </dl>';
        }
        ?>

    </div>
    <!--/posts-->
    <!-- End Posts -->

    <!-- Tabs Widget -->


    <!-- Blog Tags -->
    <div class="headline headline-md"><h2><?php echo $thisComp->text1 ?> Tags</h2>
    </div>
    <ul class="list-unstyled blog-tags margin-bottom-30">
        <?php foreach ($popular as $tag) {
            echo '<li><a href="' . $tagURL . $tag->tag . '"><i class="fa fa-tags"></i> ' . $tag->tag . '</a></li>';
        }?>
    </ul>
    <!-- End Blog Tags -->


    <!-- End Blog Latest Tweets -->
</div>
<!-- End Right Sidebar -->
</div>
<!--/row-->
</div><!--/container-->
