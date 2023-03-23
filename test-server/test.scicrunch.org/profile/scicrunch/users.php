<?php

$cxn = new Connection();
$cxn->connect();
$all_communities = $cxn->select("communities", Array("*"), "", Array(), "where portalName != '' and portalName != 'scicrunch'");
$cxn->close();
usort($all_communities, function($a, $b) {
    return strtolower($a["portalName"]) > strtolower($b["portalName"]);
});

if(!$query)
    $query = '';

if(!$currPage)
    $currPage = 1;

// Filters: Corner case where none is selected
if (!$vrf && !$unvrf) {
    $vrf = true;
    $unvrf = true;
}
if (!$usr && !$curtr && !$mod && !$admin) {
    $usr = true;
    $curtr = true;
    $mod = true;
    $admin = true;
}
if (!$bnnd && !$nobnnd) {
    $bnnd = true;
    $nobnnd = true;
}
if (!$cid_filter) {
    $cid_filter = false;
}
$holder = new User();
$users = $holder->getUsersQuery($query, ($currPage - 1) * 20, 20, $vrf, $unvrf, $usr, $curtr, $mod, $admin, $bnnd, $nobnnd, $cid_filter);



function checkedAttr($attr){
    return ($attr) ? 'checked="checked"' : '';
}

?>
<?php
echo Connection::createBreadCrumbs('SciCrunch Users',array('Home','Account','Manage SciCrunch'),array($profileBase,$profileBase.'account',$profileBase.'account/scicrunch?tab=information'),'SciCrunch Users');
?>
<div class="profile container content">
    <div class="row">
        <!--Left Sidebar-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>
        <!--End Left Sidebar-->

        <div class="col-md-9">
            <!--Profile Body-->
            <div class="profile-body">
                <?php echo Connection::createProfileTabs(0,$profileBase.'account/scicrunch'); ?>
                <!-- Form submission logic at the bottom of the file-->
                <form method="get" id="query-form" action="<?php echo $profileBase?>account/scicrunch/users" _lpchecked="1">
                    <div class="input-group margin-bottom-20">
                        <input type="text" class="form-control user-find" name="query" placeholder="Search for Users" value="<?php echo $query?>">
                        <div class="autocomplete_append auto" style="z-index:10"></div>
                        <span class="input-group-btn">
                            <button class="btn-u" type="submit">Go</button>
                        </span>
                    </div>
                </form>
                <div class="table-search-v2 margin-bottom-20">
                    <div class="table-responsive">
                        <h4>Filters</h4>
                        <div id="filters" class="filters-box">
                            <!-- Form submission logic at the bottom of the file-->
                            <form id="filters-form" method="get" action="<?php echo $profileBase?>account/scicrunch/users">
                                <!--Verified/Unverified-->
                                <div class="row">
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="vrf" type="checkbox"
                                            <?php echo checkedAttr($vrf) ?>>Verified
                                    </label>
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="unvrf" type="checkbox"
                                            <?php echo checkedAttr($unvrf) ?>>Unverified
                                    </label>
                                </div>

                                <!--Level-->
                                <div class="row">
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="usr" type="checkbox"
                                            <?php echo checkedAttr($usr) ?>>User
                                    </label>
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="curtr" type="checkbox"
                                            <?php echo checkedAttr($curtr) ?>>Curator
                                    </label>
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="mod" type="checkbox"
                                            <?php echo checkedAttr($mod) ?>>Moderator
                                    </label>
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="admin" type="checkbox"
                                            <?php echo checkedAttr($admin) ?>>Administrator
                                    </label>
                                </div>

                                <!--Banned-->
                                <div class="row">
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="bnnd" type="checkbox"
                                            <?php echo checkedAttr($bnnd) ?>>Banned
                                    </label>
                                    <label class="checkbox-inline">
                                        <input class="filter-check" name="nobnnd" type="checkbox"
                                            <?php echo checkedAttr($nobnnd) ?>>Not banned
                                    </label>
                                </div>

                                <!--Communities-->
                                <div class="row">
                                    <label style="font-weight:normal">
                                        In community
                                        <select name="cid_filter">
                                            <option value="0" <?php if(!$cid_filter) echo "selected" ?>>
                                                [See all]
                                            </option>
                                            <?php foreach($all_communities as $comm): ?>
                                                <option value="<?php echo $comm["id"] ?>" <?php if($cid_filter == $comm["id"]) echo "selected" ?>>
                                                    <?php echo $comm["portalName"] ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </label>
                                </div>

                                <!--Apply button-->
                                <div class="row">
                                    <button class="btn btn-default" type="submit" id="apply-filters-btn">Apply</button>
                                </div>
                            </form>
                        </div>
                        <div style="margin-top:20px">Results: <?php echo $users["count"] ?></div>
                        <table class="table table-hover margin-top-20">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th class="hidden-sm">Email</th>
                                <th>Join Date</th>
                                <th>Verified</th>
                                <th>Level</th>
                                <th>Edit Permissions</th>
                                <th>Ban User</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $levels = array('User', 'Curator', 'Moderator', 'Administrator');
                            $level_class = array('label-success', 'label-info', 'label-warning', 'label-danger');
                            foreach ($users['results'] as $user): ?>
                                <td><?php echo $user->getFullName() ?></td>
                                <td><?php echo $user->email ?></td>
                                <td><?php echo date('h:ia F j, Y', $user->created) ?></td>
                                <td><input type="checkbox" disabled="disabled" <?php echo checkedAttr($user->verified) ?>/></td>
                                <?php if($user->banned === 1): ?>
                                    <td><span class="label label-danger">BANNED</span></td>
                                <?php else: ?>
                                    <td><span class="label <?php echo $level_class[$user->role] ?>"><?php echo $levels[$user->role] ?></span></td>
                                <?php endif ?>
                                <?php if ($_SESSION['user']->role > $user->role): ?>
                                    <td>
                                        <a href="javascript:void(0)" level="<?php echo $user->role ?>" class="user-edit" uid="<?php echo $user->id ?>" user="<?php echo $user->getFullName(); ?>">
                                            <i class="fa fa-wrench"></i> Edit
                                        </a>
                                    </td>
                                    <td>
                                        <?php if($user->banned === 1): ?>
                                            <a href="/forms/scicrunch-forms/user-ban.php?ban=0&uid=<?php echo $user->id ?>"><i class="fa fa-plus-circle"></i> Unban</a>
                                        <?php else: ?>
                                            <a href="/forms/scicrunch-forms/user-ban.php?ban=1&uid=<?php echo $user->id ?>"><i class="fa fa-times-circle"></i> Ban</a>
                                        <?php endif ?>
                                    </td>
                                <?php else: ?>
                                    <td></td><td></td>
                                <?php endif ?>
                                </tr>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                        <div class="text-left">
                            <?php
                            echo '<ul class="pagination">';

                            parse_str($_SERVER['QUERY_STRING'], $qParams);
                            unset($qParams['currPage']);

                            $params = http_build_query($qParams);
                            $max = ceil($users['count'] / 20);

                            if ($currPage > 1)
                                echo '<li><a href="'.$profileBase.'account/scicrunch/users?currPage=' . ($currPage - 1) . '&' . $params . '">«</a></li>';
                            else
                                echo '<li><a href="javascript:void(0)">«</a></li>';

                            if ($currPage - 3 > 0) {
                                $start = $currPage - 3;
                            } else
                                $start = 1;
                            if ($currPage + 3 < $max) {
                                $end = $currPage + 3;
                            } else
                                $end = $max;

                            if ($start > 2) {
                                echo '<li><a href="'.$profileBase.'account/scicrunch/users?currPage=1&' . $params . '">1</a></li>';
                                echo '<li><a href="'.$profileBase.'account/scicrunch/users?currPage=2&' . $params . '">2</a></li>';
                                echo '<li><a href="javascript:void(0)">..</a></li>';
                            }

                            for ($i = $start; $i <= $end; $i++) {
                                if ($i == $currPage) {
                                    echo '<li class="active"><a href="javascript:void(0)">' . number_format($i) . '</a></li>';
                                } else {
                                    echo '<li><a href="'.$profileBase.'account/scicrunch/users?currPage=' . $i . '&' . $params . '">' . number_format($i) . '</a></li>';
                                }
                            }

                            if ($end < $max - 3) {
                                echo '<li><a href="javascript:void(0)">..</a></li>';
                                echo '<li><a href="'.$profileBase.'account/scicrunch/users?currPage=' . ($max - 1) . '&' . $params . '">' . number_format($max - 1) . '</a></li>';
                                echo '<li><a href="'.$profileBase.'account/scicrunch/users?currPage=' . $max . '&' . $params . '">' . number_format($max) . '</a></li>';
                            }

                            if ($currPage < $max)
                                echo '<li><a href="'.$profileBase.'account/scicrunch/users?currPage=' . ($currPage + 1) . '&' . $params . '">»</a></li>';
                            else
                                echo '<li><a href="javascript:void(0)">»</a></li>';


                            echo '</ul>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End Table Search v2-->
    </div>
    <!--End Profile Body-->
</div>
<!--=== End Profile ===-->
<div class="background"></div>
<div class="back-hide user-edit-container no-padding">
    <div class="close dark less-right">X</div>
    <form action="/forms/scicrunch-forms/user-edit.php" method="post"
          class="sky-form create-form" enctype="multipart/form-data">

        <fieldset>
            <section>
                <label class="label">User</label>
                <div class="theName"></div>
                <input name="uid" type="hidden" class="uid"/>
            </section>
            <section>
                <label class="label">User Level</label>
                <label class="select">
                    <i class="icon-append fa fa-question-circle"></i>
                    <select name="level" class="edit-level">
                        <?php
                        for($i=0;$i<3;$i++){
                            if($_SESSION['user']->role>=$i)
                                echo '<option value="'.$i.'">'.$levels[$i].'</option>';
                        }
                        ?>
                    </select>
                </label>
            </section>
        </fieldset>
        <footer>
            <button class="btn-u btn-u-default" type="submit">Edit User</button>
        </footer>
    </form>
</div>

<script>
    function getUrlVars() {
        var search = location.search.substring(1);
        return JSON.parse('{"' + decodeURI(search).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
    }


    $('#query-form').submit(function (e) {
        var queryVars = getUrlVars();
        // Filters are already applied
        if (queryVars.vrf || queryVars.unvrf || queryVars.usr || queryVars.curtr
        || queryVars.mod || queryVars.admin || queryVars.bnnd || queryVars.nobnnd) {
            e.preventDefault();
            queryVars.query = $('#query-form').find('input[name=query]').val();
            window.location.href = window.location.pathname + '?' + $.param(queryVars);
        }
    });

    $('#filters-form').submit(function (e) {
        var query = getUrlVars().query;
        if (query) {
            e.preventDefault();
            window.location.href = window.location.pathname + '?query=' + query + '&' + ($(this).serialize());
        }
    });
</script>
