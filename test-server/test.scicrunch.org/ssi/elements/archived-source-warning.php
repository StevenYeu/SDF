<?php if($data["always-show"] || in_array($data["viewid"], Search::$archivedViews)): ?>
    <i style="color:orange" class="fa fa-warning" data-toggle="popover" data-content="This source has been archived.  We are no longer crawling it or it is no longer updating with new data." data-trigger="hover"></i>
<?php endif ?>
