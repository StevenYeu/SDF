<style>
    body {
        background:#fff;
    }
</style>
<div class="full-screen-closed">
    <a href="javascript:void(0)" class="full-side-open" style="color:#fff;font-size: 26px;" title="Open Side Bar"><i
            class="fa fa-caret-square-o-right"></i></a>
</div>
<div class="full-screen-left">
    <?php
    $newVars = $vars;
    $newVars['fullscreen'] = null;
    $newVars["page"] = 1;
    ?>
    <a class="btn-u btn-u-purple" href="<?php echo $search->generateURL($newVars) ?>" style="margin-bottom: 15px"
       type="button">Exit Fullscreen
    </a>
    <a href="javascript:void(0)" class="pull-right full-side-close" title="Collapse Sidebar"
       style="color:#fff;font-size: 26px;"><i class="fa fa-caret-square-o-left"></i></a>

    <?php
    $urlArray = explode('?', $search->generateURL($vars));
    $formUrl = $urlArray[0];
    ?>
    <form method="get" action="<?php echo $url ?>">
        <div class="input-group" style="margin-bottom: 15px;">
            <input type="text" class="form-control" name="q" placeholder="Search words with regular expressions." value="<?php if ($search->display) echo $search->display; else echo $search->query ?>">
            <span class="input-group-btn">
                <button class="btn-u" type="submit"><i class="fa fa-search"></i></button>
            </span>
            <input name="fullscreen" value="true" type="hidden"/>
        </div>
    </form>

    <hr style="margin:15px 0"/>

    <p><i class="fa fa-group"></i> <a href="/<?php echo $community->portalName ?>"><?php echo $community->name ?></a>
    </p>

    <p><i class="fa fa-database"></i> <?php echo $allSources[$search->source]->getTitle() ?></p>

    <p><i class="fa fa-empire"></i> <?php echo number_format($results['total']) ?> results</p>

    <p><i class="fa fa-file"></i> Results <?php echo number_format(($search->page - 1) * 50 + 1) . ' - ' . number_format(($search->page * 50)) ?></p>

    <p><i class="fa fa-clock-o"></i> <?php echo \helper\htmlElement("modified-date-picker"); ?></p>

    <?php if(strpos(http_build_query($_GET), "v_status:") === false): ?>
        <p><i class="fa fa-clock-o"></i> <?php echo \helper\htmlElement("new-records-link", Array("vars" => $vars, "search" => $search)); ?></p>
    <?php endif ?>

    <hr style="margin:15px 0"/>

    <select id="column-select">
        <option value="">-- Select Column to Scroll To --</option>
        <?php
        foreach ($results['table'][0] as $column => $value) {
            echo '<option value="'.rawurlencode($column).'">' . $column . '</option>';
        }
        ?>
    </select>

    <?php echo $search->paginate($vars); ?>

    <hr style="margin:15px 0"/>

    <?php

    if ($search->facet || $search->filter || $search->sort) {
        echo '<h4 style="color:#fff">Current Filters</h4>';
        //print_r($search);
        foreach ($search->filter as $filter) {
            $newVars = $vars;
            $newVars['filter'] = array_diff($search->filter, array($filter));
            echo '<p><a href="' . $search->generateURL($newVars) . '"><i class="fa fa-times-circle" style="color:#f2d9d9"></i></a> ' . Search::filterText($filter) . ' (filter)</p>';
        }
        foreach ($search->facet as $filter) {
            $newVars = $vars;
            $newVars['facet'] = array_diff($search->facet, array($filter));
            echo '<p><a href="' . $search->generateURL($newVars) . '"><i class="fa fa-times-circle" style="color:#f26666"></i></a> ' . $filter . ' (facet)</p>';
        }
        if ($search->column && $search->sort) {
            $newVars = $vars;
            $newVars['column'] = null;
            $newVars['sort'] = null;
            $html = '<p><a href="' . $search->generateURL($newVars) . '"><i class="fa fa-times-circle" style="color:#f26666"></i></a> ' . $search->column . ':';
            if ($search->sort == 'asc')
                $html .= 'Ascending';
            else
                $html .= 'Descending';
            $html .= '</p>';
            echo $html;
        }
        echo '<hr style="margin:15px 0"/>';
    }

    ?>

    <div class="panel-heading" style="background:#d9d9f2">
        <h3 style="margin:0">Facets</h3>
    </div>
    <ul class="list-group sidebar-nav-v1" id="sidebar-nav" style="margin-bottom: 0">
        <?php
        foreach ($results['facets'] as $column => $array) {
            $column_href = str_replace(Array(" ", "/"), "_", $column);
            echo '<li class="list-group-item list-toggle">
                          <a data-toggle="collapse" data-parent="#sidebar-nav" href="#collapse-' . $column_href . '">' . $column . '</a>';
            echo '<ul id="collapse-' . $column_href . '" class="collapse">';
            foreach ($array as $facet) {
                $newVars = $vars;
                $newVars['facet'][] = $column . ':' . $facet['value'];
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . $facet['value'] . ' (' . number_format($facet['count']) . ')</a></li>';
            }

            echo '</ul></li>';
        }
        ?>
    </ul>

    <hr style="margin:15px 0"/>

</div>
<div class="fixed-header">
    <table class="table table-bordered" style="table-layout: fixed;margin-bottom:0;height:50px;width:<?php echo (200 * (count($results['table'][0]) - 1)) . 'px'; ?>">
        <tr>
            <?php $counter = 0; ?>
            <?php foreach ($results['table'][0] as $column => $value): ?>
                <?php if($column == "v_uuid") continue; ?>
                <?php $style_align = $counter < 2 ? "right" : "left"; ?>
                <th class="search-header" style="width:200px;padding:5px;background:#d9d9f2;border:1px solid #999;position:relative;" column="<?php echo rawurlencode($column) ?>">
                    <a href="javascript:void(0)"><?php echo $column ?></a>
                    <div class="column-search invis-hide" style="<?php echo $style_align ?>:auto; right:-1px;">
                        <form method="get" class="column-search-form" column="<?php echo rawurlencode($column) ?>">
                            <div class="input-group">
                                <input type="text" class="form-control" name="value" placeholder="Search Column" value="" autocomplete="off">
                                <span class="input-group-btn">
                                    <button class="btn-u search-filter-btn" type="button"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                        <hr style="margin:0"/>
                        <?php
                        $newVars = $vars;
                        $newVars["column"] = $column;
                        $newVars["sort"] = "asc";
                        ?>
                        <p><a class="sortin-column" href="<?php echo $search->generateURL($newVars) ?>"><i class="fa fa-sort-amount-asc"></i> Sort Ascending</a></p>
                        <?php $newVars["sort"] = "desc"; ?>
                        <p><a class="sortin-column" href="<?php echo $search->generateURL($newVars) ?>"><i class="fa fa-sort-amount-desc"></i> Sort Descending</a></p>
                    </div>
                </th>
                <?php $counter += 1; ?>
            <?php endforeach ?>
        </tr>
    </table>
</div>
<div class="full-screen-right">
    <table class="table table-bordered table-striped full-screen-inner"
           style="table-layout: fixed;overflow-y: scroll;width:<?php echo (200 * (count($results['table'][0]) - 1)) . 'px'; ?>">

        <tbody>
        <?php
        foreach ($results['table'] as $i => $row) {
            if(isset($row["v_uuid"])) {
                $uuid = $row["v_uuid"];
            } else {
                $uuid = NULL;
            }
            echo '<tr>';
            foreach ($row as $column => $value) {
                if($column == "v_uuid") continue;
                $final_value = $column == "Reference" || $column == "Mentioned In Literature" || $column == "Reference/Provider" ? \helper\checkLongURL($value, $community, $vars["nif"], $uuid, $column) : $value;
                echo '<td style="width:200px; word-break: break-all;">' . $final_value . '</td>';
            }
            echo '</tr>';
        }
        ?>

        </tbody>
    </table>
</div>
