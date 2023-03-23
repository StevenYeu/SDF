<?php
    require_once $_SERVER[DOCUMENT_ROOT] . '/classes/schemas/schemas.class.php';
    include 'process-elastic-search.php';

    $src = new Sources();
    $sources = $src->getAllSources();
    if(isset($sources[$vars['nif']])){
        $source = $sources[$vars['nif']];
        $portalName = $community->portalName;
        $dataFeedSchema = SchemaGeneratorSources::generateDataFeed($source, $portalName);
    }

    $categories = $sources[$vars["nif"]]->categories;
    usort($categories, function($a, $b) {
        return strcmp($a["category"], $b["category"]);
    });
?>

<?php if($search->page >= Search::MAX_PAGE) echo \helper\htmlElement("too-many-pages", Array("max_page" => Search::MAX_PAGE)) ?>
<style>

body {
    float: left;
    min-width: 100%;
}
.table-fixed th, .table-fixed td {
    min-width: 180px;
    background-color: white;
}
.table-fixed td {

    /* These are technically the same, but use both */
    overflow-wrap: break-word;
    word-wrap: break-word;

    word-break: break-word;

    /* Adds a hyphen where the word breaks, if supported (No Blink) */
    -ms-hyphens: auto;
    -moz-hyphens: auto;
    -webkit-hyphens: auto;
    hyphens: auto;
}

body {
    float: left;
    min-width: 100%;
}
.table-fixed > thead > tr > th, .table-fixed > thead > tr > td {
    min-width: 180px;
    background-color: white;
}
.table-fixed td {

    /* These are technically the same, but use both */
    overflow-wrap: break-word;
    word-wrap: break-word;

    word-break: break-word;

    /* Adds a hyphen where the word breaks, if supported (No Blink) */
    -ms-hyphens: auto;
    -moz-hyphens: auto;
    -webkit-hyphens: auto;
    hyphens: auto;
}
.grey-option {
    background-color: rgb(149, 165, 166);
}
</style>


<?php if (count($results["facets"])) { ?>
<link rel="stylesheet" href="/css/facets-wordcloud.css">
<!-- Facet world cloud modal -->
<div id="facets-wordcloud-modal">
    <div class="facets-wordcloud-modal-content">
        <div class="facets-wordcloud-modal-loading">
            <h3>Preparing word cloud <i class="fa fa-cog fa-spin"></i></h2>
            <img src="/images/scicrunch.png" style="height: 50px">
        </div>
        <span class="facets-wordcloud-close">&times;</span>
        <h3 class="facets-wordcloud-modal-title"></h3>
        <div class="facets-wordcloud-area" class="wordcloud-tooltip-available">
        </div>
    </div>
</div>
<script type="text/javascript">
    <?php
        echo 'var query_facet_array = ' . json_encode($vars['facet']) . ';';
        echo 'var query_filter_array = ' . json_encode($vars['filter']) . ';';
    ?>
</script>
<script type="text/javascript" src="/js/wordcloud2.js"></script>
<script type="text/javascript" src="/js/facets-wordcloud.js"></script>
<?php } ?>

  <?php ## added search bar -- Vicky-2019-1-4
      echo \helper\htmlElement("components/search-block-slim", Array(
          "community" => $community,
          "user" => $_SESSION["user"],
          "vars" => $vars,
          "search" => $search,
          "expansion" => $results["expansion"],
      ));
  ?>

<div class="container margin-bottom-50">
    <div class="row">

        <!--/col-md-2-->

        <div class="col-md-2" id="left-nav-facets">

            <?php echo \helper\htmlElement("modified-date-picker"); ?>
            <?php if(strpos(http_build_query($_GET), "v_status:") === false): ?>
                <br/><?php echo \helper\htmlElement("new-records-link", Array("vars" => $vars, "search" => $search)); ?>
            <?php endif ?>
            <hr/>
            <?php echo $search->currentFacets($vars, 'table') ?>
            <?php echo \helper\htmlElement("view-facets", Array("results" => $results, "search" => $search, "vars" => $vars)); ?>
            <?php if(count($results["facets"])): ?>
                <hr style="margin-top:10px;margin-bottom:15px;"/>
            <?php endif ?>
            <?php echo \helper\htmlElement("recent-searches-list", Array("recent-searches" => $_SESSION["recent-searches"], "community" => $community)); ?>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12">
                    <!--<h4 id="sc-title">-->
                        <?php //if(isset($sources[$vars["nif"]])) //echo $sources[$vars['nif']]->getTitle(); ?>
                        <?php //echo \helper\htmlElement("archived-source-warning", Array("viewid" => $vars["nif"])) ?>
                        <?php //echo \helper\htmlElement("collection-bookmark", Array("user" => $_SESSION["user"], "uuid" => $vars["nif"], "community" => $community, "view" => "view")); ?>
                    <!--</h4>-->
                    <p id="sc-descr"><?php echo $sources[$vars['nif']]->description; ?></p>
                    <?php if(!is_null($sources[$vars["nif"]])): ?>
                        <p>(last updated: <?php echo date("M j, Y", $sources[$vars['nif']]->data_last_updated) ?>)</p>
                        <?php if(in_array($vars["nif"], Search::$archivedViews)): ?>
                            <p><i style="color:orange" class="fa fa-warning"></i> This source has been archived.  We are no longer crawling it or it is no longer updating with new data.</p>
                        <?php endif ?>
                            <?php
                            $newVars = $vars;
                            $newVars['category'] = 'data';
                            $newVars['subcategory'] = null;
                            $newVars['nif'] = null;
                            $newVars['uuid'] = false;
                            $newVars['facet'] = null;
                            $newVars['filter'] = null;
                            $newVars['page'] = 1;
                            $more_resources_url = $search->generateURL($newVars);
                            ?>
                            <?php foreach($categories as $cat): ?>
                                <a href="<?php echo $more_resources_url . "#category-filter=" . $cat["parentCategory"]. ":" . $cat["category"]?>"><span class="label label-default"><?php echo $cat["category"] ?></span></a>
                            <?php endforeach ?>
                        </p>
                    <?php endif ?>
                </div>
            </div>
            <?php if($results["total"] == 0): ?>
                <?php echo $search->getResultText('table', array($results['total'], $GLOBALS["notif_id"], $subscription_data["modified_time"]), $results['expansion'], $vars) ?>
            <?php else: ?>
                <div class="panel panel-grey margin-bottom-50">
                    <div class="panel-heading">
                        <h3 class="panel-title pull-left">
                            <i class="fa fa-globe"></i> <?php echo $search->getResultText('table', array($results['total'], $GLOBALS["notif_id"], $subscription_data["modified_time"]), $results['expansion'], $vars) ?> -
                            <select class="grey-option per-page-select">
                                <option class="grey-option" value="20" <?php if($search->per_page === 20) echo "selected" ?>>20</option>
                                <option class="grey-option" value="50" <?php if($search->per_page === 50) echo "selected" ?>>50</option>
                                <option class="grey-option" value="100" <?php if($search->per_page === 100) echo "selected" ?>>100</option>
                            </select>
                            per page
                        </h3>
                        <div class="pull-right">
                            <h3 class="panel-title">
                                <?php
                                $newVars = $vars;
                                $newVars['fullscreen'] = 'true';
                                $newVars["page"] = 1;
                                ?>
                                <a href="javascript:void(0)" class="showMoreColumns not-rin" id="smc"><i class="fa fa-plus"></i> Show More Columns</a> |
                                <a class="not-rin" href="<?php echo $search->generateURL($newVars) ?>" target="_blank"><i class="fa fa-arrows-alt"></i> Fullscreen</a> |
                                <a class="ga-download not-rin" href="<?php echo $results['export'] . "&exportType=data" ?>"><i class="fa fa-cloud-download"></i> Download 1000 results</a>
                            </h3>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <!-- <div id="table-container"> -->
                    <div class="panel-body">
<?php
                        if (!empty($results['schemas'])) {
                            foreach ($results['schemas'] as $dataFeedItem) {
                                $dataFeedSchema->dataFeedElementSchema[] = $dataFeedItem;
                            }
                        }
                        echo '<script type="application/ld+json">' . $dataFeedSchema->generateJSON(true) . '</script>';
                        // check if there is extra schemas
                        if (!empty($results['schemas_extras'])) {
                            foreach ($results['schemas_extras'] as $extra) {
                                echo '<script type="application/ld+json">' . $extra->generateJSON() . '</script>';
                            }
                        }
?>
                        <table class="table table-bordered table-striped table-fixed" style="table-layout:fixed" id="result-table">

                            <thead>
                            <tr>
                                <?php
                                $count = 0;
                                foreach ($results['table'][0] as $column => $value) {
                                    if($column == "v_uuid") continue;
                                    if ($count > 6)
                                        echo '<th style="position:relative" class="search-header hidden-column showing"><a href="javascript:void(0)">' . $column . '</a>';
                                    else
                                        echo '<th style="position:relative" class="search-header"><a href="javascript:void(0)">' . $column . '</a>';
                                    if ($count > count($results['table'][0]) - 3)
                                        echo '<div class="column-search" style="left:auto;right:-1px;">';
                                    else
                                        echo '<div class="column-search invis-hide">';
                                    echo '<form method="get" class="column-search-form" column="' . rawurlencode($column) . '">';
                                    echo '<div class="input-group">
                                            <input type="text" class="form-control" name="value" placeholder="Search Column" value="" autocomplete="off">
                                            <span class="input-group-btn">
                                                <button class="btn-u search-filter-btn" type="button"><i class="fa fa-search"></i></button>
                                            </span>
                                        </div>';
                                    echo '</form>';
                                    echo '<hr style="margin:0"/>';
                                    $newVars = $vars;
                                    $newVars['column'] = $column;
                                    $newVars['sort'] = 'asc';
                                    echo '<p><a class="sortin-column" href="' . $search->generateURL($newVars) . '"><i class="fa fa-sort-amount-asc"></i> Sort Ascending</a></p>';

                                    $newVars['sort'] = 'desc';
                                    echo '<p><a class="sortin-column" href="' . $search->generateURL($newVars) . '"><i class="fa fa-sort-amount-desc"></i> Sort Descending</a></p>';
                                    echo '</div>';
                                    if (isset($results['facets'][$column])){
                                        echo '<a class="show-facets-wordcloud" onclick="showWordCloud('
                                            . "'" . $vars['nif'] . "'" . ','
                                            . "'" . htmlentities($vars['q']) . "'" . ','
                                            . "'" . $column . "'" .
                                            ')"><i class="fa fa-cloud wordcloud-button-grow"></i>';
                                    }
                                    echo '</th>';
                                    $count++;
                                }
                                ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $colcount = 0;
                            foreach ($results['table'] as $i => $row) {
                                echo '<tr>';
                                $count = 0;
                                $collection_bookmark = NULL;
                                if(isset($row["v_uuid"])) {
                                    $uuid = $row["v_uuid"];
                                    $rrid_data = RRIDReportItem::rridDataFromViewRow($vars["nif"], $row);
                                    $collection_bookmark = \helper\htmlElement("collection-bookmark", Array("user" => $_SESSION["user"], "uuid" => $uuid, "community" => $community, "view" => $vars["nif"], "rrid-data" => $rrid_data));
                                } else {
                                    $uuid = NULL;
                                }
                                foreach ($row as $column => $value) {
                                    if($column == "v_uuid") continue;
                                    if ($column == "Allele") { // To show the '<' and '>'
                                        $value = htmlentities(htmlentities($value));
                                    }
                                    if($column == "Comments" && strpos($value,"Problematic cell line") !== false) $value = "<span style='color:red'>" . $value . "</span>";
                                    $fmt_value = $value;
                                    if ($source->description_encoded) {
                                        $fmt_value = \helper\formattedDescription($fmt_value);
                                    }
                                    $fmt_value = preg_replace("/http-equiv=['\"]refresh['\"]/", "", $fmt_value);
                                    $fmt_value = $column == "Mentioned In Literature" || $column == "Reference/Provider" ? \helper\checkLongURL($fmt_value, $community, $vars["nif"], $uuid, $column) : $fmt_value;
                                    if($column == "Reference") {
                                        $fmt_value = join("<br>", buildLinks(strip_tags($value), $community));
                                    }
                                    if ($count > 6) {
                                        echo '<td class="hidden-column showing"><span class="search-table-record-td">' . $fmt_value . '</span></td>';
                                    } else {
                                        if($count == 0 && !is_null($collection_bookmark)) {
                                            echo '<td class="bookmark-td">';
                                            echo $collection_bookmark;
                                        } else {
                                            echo '<td>';
                                        }
                                        echo '<span class="search-table-record-td">';
                                        echo $fmt_value;
                                        echo '</span></td>';
                                    }
                                    $count++;
                                    $colcount = $count;
                                }

                                echo '</tr>';
                                //changes the body width when displaying more columns
                                $body_width = ' ';
                                if($colcount > 6) {
                                    $body_width = '150%';
                                }
                                if($colcount > 8) {
                                    $body_width = '200%';
                                }
                                echo '<script>
                                if(' . $colcount . ' > 6) {
                                    $(".showMoreColumns").click(function(){
                                        if($(this).hasClass("active")){
                                            $("body").css("width", "100%");
                                            $("#left-nav-facets").removeAttr("style");
                                        } else {
                                            $("body").css("width", "' . $body_width . '");
                                            $("#left-nav-facets").css("width", "220px");
                                        }
                                    });
                                }
                                </script>';
                            }
                            ?>

                            </tbody>
                        </table>
                    <!-- </div> -->

                    </div>
                </div>
                <?php echo $search->paginateLong($vars) ?>
            <?php endif ?>
        </div>
    </div>
</div>

<?php echo \helper\htmlElement("collection-modals", Array("user" => $_SESSION["user"], "community" => $community)); ?>
<div class="category-graph the-largest-modal back-hide">
    <div class="close dark">X</div>
    <div id="main">
        <div id="sequence"></div>
        <div id="chart">
            <!-- <div id="explanation" style="visibility: hidden;">
                <span id="percentage"></span><br/>
                of results have this facet
            </div> -->
        </div>
    </div>
    <div id="sidebar">


        <h4>Facet Graph</h4>

        <p>
            This is an overview of all the faceted data within your result set. You can click on the lowest level to
            apply the facet to your search.
        </p>

        <p>
            Please note that all facets are present and calculated in the chart, but if the result set has less than
            .001% of the total results returned it may not be visible.
        </p>
        <div id="legend"></div>
    </div>
    <!--    <div id="sidebar">-->
    <!--        <input type="checkbox" id="togglelegend"> Legend<br/>-->
    <!--        <div id="legend" style="visibility: hidden;"></div>-->
    <!--    </div>-->
</div>
<ol id="joyRideTipContent">
    <li data-class="community-logo" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2><?php echo $community->name?> Resources</h2>
        <p>
            Welcome to the <?php echo $community->shortName?> Resources search. From here you can search through
            a compilation of resources used by <?php echo $community->shortName?> and see how data is organized within
            our community.
        </p>
    </li>
    <li data-class="resource-tab" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Navigation</h2>
        <p>
            You are currently on the Community Resources tab looking through categories and sources that <?php echo $community->shortName?>
            has compiled. You can navigate through those categories from here or change to a different tab to execute
            your search through. Each tab gives a different perspective on data.
        </p>
    </li>
    <li data-class="btn-login" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Logging in and Registering</h2>
        <p>
            If you have an account on <?php echo $community->shortName ?> then you can log in from here to get additional
            features in <?php echo $community->shortName ?> such as Collections, Saved Searches, and managing Resources.
        </p>
    </li>
    <li data-class="searchbar" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Searching</h2>
        <p>
            Here is the search term that is being executed, you can type in anything you want to search for. Some tips
            to help searching:
        </p>
        <ol>
            <li style="color:#fff">Use quotes around phrases you want to match exactly</li>
            <li style="color:#fff">You can manually AND and OR terms to change how we search between words</li>
            <li style="color:#fff">You can add "-" to terms to make sure no results return with that term in them (ex. Cerebellum -CA1)</li>
            <li style="color:#fff">You can add "+" to terms to require they be in the data</li>
            <li style="color:#fff">Using autocomplete specifies which branch of our semantics you with to search and can help refine your search</li>
        </ol>
    </li>
    <li data-class="tut-saved" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Collections</h2>
        <p>
            If you are logged into <?php echo $community->shortName ?> you can add data records to your collections to create custom spreadsheets
            across multiple sources of data.
        </p>
    </li>
    <li data-class="multi-facets" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Facets</h2>
        <p>
            Here are the facets that you can filter the data by.
        </p>
    </li>
    <li data-class="tutorial-btn" data-button="Done" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Further Questions</h2>
        <p>
            If you have any further questions please check out our
            <a href="/<?php echo $community->portalName ?>/about/faq">FAQs Page</a> to ask questions and see our tutorials.
            Click this button to view this tutorial again.
        </p>
    </li>
</ol>
<?php $url = $search->generateURLFromDiff(Array("page" => 1, "on_page" => NULL)); ?>


<!-- Go to www.addthis.com/dashboard to customize your tools -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-577ff645c9be4c0f"></script>


<script>

$(function () {
    $("#result-table").stickyTableHeaders();
});

/*! Copyright (c) 2011 by Jonas Mosbech - https://github.com/jmosbech/StickyTableHeaders
    MIT license info: https://github.com/jmosbech/StickyTableHeaders/blob/master/license.txt */

;
(function ($, window, undefined) {
    'use strict';

    var name = 'stickyTableHeaders',
        id = 0,
        defaults = {
            fixedOffset: 0,
            leftOffset: 0,
            marginTop: 0,
            scrollableArea: window
        };

    function Plugin(el, options) {
        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;
        base.id = id++;
        base.$window = $(window);
        base.$document = $(document);

        // Listen for destroyed, call teardown
        base.$el.bind('destroyed',
        $.proxy(base.teardown, base));

        // Cache DOM refs for performance reasons
        base.$clonedHeader = null;
        base.$originalHeader = null;

        // Keep track of state
        base.isSticky = false;
        base.hasBeenSticky = false;
        base.leftOffset = null;
        base.topOffset = null;

        base.init = function () {
            base.$el.each(function () {
                var $this = $(this);


                $this.css('padding', 0);

                base.$originalHeader = $('thead:first', this);
                base.$clonedHeader = base.$originalHeader.clone();
                $this.trigger('clonedHeader.' + name, [base.$clonedHeader]);

                base.$clonedHeader.addClass('tableFloatingHeader');
                base.$clonedHeader.css('display', 'none');

                base.$originalHeader.addClass('tableFloatingHeaderOriginal');

                base.$originalHeader.after(base.$clonedHeader);

                base.$printStyle = $('<style type="text/css" media="print">' +
                    '.tableFloatingHeader{display:none !important;}' +
                    '.tableFloatingHeaderOriginal{position:static !important;}' +
                    '</style>');
                $('head').append(base.$printStyle);
            });

            base.setOptions(options);
            base.updateWidth();
            base.toggleHeaders();
            base.bind();
        };

        base.destroy = function () {
            base.$el.unbind('destroyed', base.teardown);
            base.teardown();
        };

        base.teardown = function () {
            if (base.isSticky) {
                base.$originalHeader.css('position', 'static');
            }
            $.removeData(base.el, 'plugin_' + name);
            base.unbind();

            base.$clonedHeader.remove();
            base.$originalHeader.removeClass('tableFloatingHeaderOriginal');
            base.$originalHeader.css('visibility', 'visible');
            base.$printStyle.remove();

            base.el = null;
            base.$el = null;
        };

        base.bind = function () {
            base.$scrollableArea.on('scroll.' + name, base.toggleHeaders);
            if (!base.isWindowScrolling) {
                base.$window.on('scroll.' + name + base.id, base.setPositionValues);
                base.$window.on('resize.' + name + base.id, base.toggleHeaders);
            }
            base.$scrollableArea.on('resize.' + name, base.toggleHeaders);
            base.$scrollableArea.on('resize.' + name, base.updateWidth);
        };

        base.unbind = function () {
            // unbind window events by specifying handle so we don't remove too much
            base.$scrollableArea.off('.' + name, base.toggleHeaders);
            if (!base.isWindowScrolling) {
                base.$window.off('.' + name + base.id, base.setPositionValues);
                base.$window.off('.' + name + base.id, base.toggleHeaders);
            }
            base.$scrollableArea.off('.' + name, base.updateWidth);
        };

        base.toggleHeaders = function () {
            if (base.$el) {
                base.$el.each(function () {
                    var $this = $(this),
                        newLeft,
                        newTopOffset = base.isWindowScrolling ? (
                        isNaN(base.options.fixedOffset) ? base.options.fixedOffset.outerHeight() : base.options.fixedOffset) : base.$scrollableArea.offset().top + (!isNaN(base.options.fixedOffset) ? base.options.fixedOffset : 0),
                        offset = $this.offset(),

                        scrollTop = base.$scrollableArea.scrollTop() + newTopOffset,
                        scrollLeft = base.$scrollableArea.scrollLeft(),

                        scrolledPastTop = base.isWindowScrolling ? scrollTop > offset.top : newTopOffset > offset.top,
                        notScrolledPastBottom = (base.isWindowScrolling ? scrollTop : 0) < (offset.top + $this.height() - base.$clonedHeader.height() - (base.isWindowScrolling ? 0 : newTopOffset));

                    if (scrolledPastTop && notScrolledPastBottom) {
                        newLeft = offset.left - scrollLeft + base.options.leftOffset;
                        base.$originalHeader.css({
                            'position': 'fixed',
                                'margin-top': base.options.marginTop,
                                'left': newLeft,
                                'z-index': 3,
                                'background-color' : 'white',
                                'border-bottom': 'solid 1px #cccccc'
                        });
                        base.leftOffset = newLeft;
                        base.topOffset = newTopOffset;
                        base.$clonedHeader.css('display', '');
                        if (!base.isSticky) {
                            base.isSticky = true;
                            // make sure the width is correct: the user might have resized the browser while in static mode
                            base.updateWidth();
                        }
                        base.setPositionValues();
                    } else if (base.isSticky) {
                        base.$originalHeader.css('position', 'static');
                        base.$clonedHeader.css('display', 'none');
                        base.isSticky = false;
                        base.resetWidth($('td,th', base.$clonedHeader), $('td,th', base.$originalHeader));
                    }
                });
            }
        };

        base.setPositionValues = function () {
            var winScrollTop = base.$window.scrollTop(),
                winScrollLeft = base.$window.scrollLeft();
            if (!base.isSticky || winScrollTop < 0 || winScrollTop + base.$window.height() > base.$document.height() || winScrollLeft < 0 || winScrollLeft + base.$window.width() > base.$document.width()) {
                return;
            }
            base.$originalHeader.css({
                'top': base.topOffset - (base.isWindowScrolling ? 0 : winScrollTop),
                'left': base.leftOffset - (base.isWindowScrolling ? 0 : winScrollLeft)
            });
        };

        base.updateWidth = function () {
            if (!base.isSticky) {
                return;
            }
            // Copy cell widths from clone
            if (!base.$originalHeaderCells) {
                base.$originalHeaderCells = $('th,td', base.$originalHeader);
            }
            if (!base.$clonedHeaderCells) {
                base.$clonedHeaderCells = $('th,td', base.$clonedHeader);
            }
            var cellWidths = base.getWidth(base.$clonedHeaderCells);
            base.setWidth(cellWidths, base.$clonedHeaderCells, base.$originalHeaderCells);

            // Copy row width from whole table
            base.$originalHeader.css('width', base.$clonedHeader.width());
        };

        base.getWidth = function ($clonedHeaders) {
            var widths = [];
            $clonedHeaders.each(function (index) {
                var width, $this = $(this);

                if ($this.css('box-sizing') === 'border-box') {
                    width = $this[0].getBoundingClientRect().width; // #39: border-box bug
                } else {
                    var $origTh = $('th', base.$originalHeader);
                    if ($origTh.css('border-collapse') === 'collapse') {
                        if (window.getComputedStyle) {
                            width = parseFloat(window.getComputedStyle(this, null).width);
                        } else {
                            // ie8 only
                            var leftPadding = parseFloat($this.css('padding-left'));
                            var rightPadding = parseFloat($this.css('padding-right'));
                            // Needs more investigation - this is assuming constant border around this cell and it's neighbours.
                            var border = parseFloat($this.css('border-width'));
                            width = $this.outerWidth() - leftPadding - rightPadding - border;
                        }
                    } else {
                        width = $this.width();
                    }
                }

                widths[index] = width;
            });
            return widths;
        };

        base.setWidth = function (widths, $clonedHeaders, $origHeaders) {
            $clonedHeaders.each(function (index) {
                var width = widths[index];
                $origHeaders.eq(index).css({
                    'min-width': width,
                        'max-width': width
                });
            });
        };

        base.resetWidth = function ($clonedHeaders, $origHeaders) {
            $clonedHeaders.each(function (index) {
                var $this = $(this);
                $origHeaders.eq(index).css({
                    'min-width': $this.css('min-width'),
                        'max-width': $this.css('max-width')
                });
            });
        };

        base.setOptions = function (options) {
            base.options = $.extend({}, defaults, options);
            base.$scrollableArea = $(base.options.scrollableArea);
            base.isWindowScrolling = base.$scrollableArea[0] === window;
        };

        base.updateOptions = function (options) {
            base.setOptions(options);
            // scrollableArea might have changed
            base.unbind();
            base.bind();
            base.updateWidth();
            base.toggleHeaders();
        };

        // Run initializer
        base.init();
    }

    // A plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[name] = function (options) {
        return this.each(function () {
            var instance = $.data(this, 'plugin_' + name);
            if (instance) {
                if (typeof options === 'string') {
                    instance[options].apply(instance);
                } else {
                    instance.updateOptions(options);
                }
            } else if (options !== 'destroy') {
                $.data(this, 'plugin_' + name, new Plugin(this, options));
            }
        });
    };

})(jQuery, window);


$(function() {
    $(".per-page-select").change(function() {
        var current = <?php echo $search->per_page; ?>;
        var per_page = $(".per-page-select option:selected").val();
        if(current === per_page) return;
        location = "<?php echo $url ?>&per_page=" + per_page;
    });
});
</script>
