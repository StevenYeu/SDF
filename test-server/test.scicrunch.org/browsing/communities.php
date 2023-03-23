<?php
$holder = new Community();

$verified_only = !isset($_GET["unverified"]);

if(isset($_SESSION['user']))
    $results = $holder->searchCommunities(array_keys($_SESSION['user']->levels),$query, ($page - 1) * 20, 20, $verified_only);
else
    $results = $holder->searchCommunities(false,$query, ($page - 1) * 20, 20, $verified_only);

if($query == "" || $query == "*"){
    uasort($results['results'], function($a, $b){
        //if($a->verified === 1 && $b->verified === 0) return -1;
        //if($a->verified === 0 && $b->verified === 1) return 1;
        if($a->name == $b->name) return 0;
        return $a->name < $b->name ? -1 : 1;
    });
}
?>


<span class="results-number">Showing <?php echo count($results['results']) ?> out of <?php echo number_format($results['count']) ?> Communities on page <?php echo $page ?></span>
<?php if($verified_only && ($_SESSION['user']->id == 247 || $_SESSION['user']->id == 36111)): ?>
    <a href="/browse/communities?unverified">Show unverified communities</a>
<?php endif ?>
<?php if(!$verified_only): ?>
    <a href="/browse/communities">Only show verified communities</a>
<?php endif ?>
<!-- Begin Inner Results -->

<?php foreach ($results['results'] as $community): ?>
<?php
    // community link, if community has external or internal
    if($community->redirect_url) {
        $community_link = $community->redirect_url;
        if(!\helper\startsWith($community_link, "http")) $community_link = "http://" . $community_link;
    } else {
        $community_link = "/" . $community->portalName;
    }

    // private icon - font awesome lock
    if($community->private) $private_icon = '<i class="fa fa-lock"></i>';
    else $private_icon = '';

    if($community->verified) $verified_icon = '<i class="fa fa-certificate" style="color:#72c02c"></i>';
    else $verified_icon = '';

    // skip scicrunch community
    if($community->id == 0) continue;

    // get owner of community
    $user = new User();
    $user->getByID($community->uid);
?>
    <div class="inner-results">
        <h3>
            <?php echo $private_icon ?>
            <?php echo $verified_icon ?>
            <a href="<?php echo $community_link ?>"><?php echo $community->name ?></a>
        </h3>
        <ul class="list-inline up-ul">
            <li><?php echo $community->url ?>‎</li>
        </ul>
        <div class="overflow-h">
            <img src="/upload/community-logo/<?php echo $community->logo ?>" alt="">
            <div class="overflow-a">
                <?php if ($query && $query != ''): ?>
                    <p><?php echo preg_replace('/(' . $query . ')/i', '<b>$1</b>', $community->description) ?></p>
                <?php else: ?>
                    <p><?php echo $community->description ?></p>
                <?php endif ?>
                <ul class="list-inline down-ul">
                  <li>Created <?php echo Connection::longTimeDifference($community->time) ?></li>
                </ul>
            </div>
        </div>
    </div>
    <hr/>
<?php endforeach ?>



<div class="margin-bottom-30"></div>

<div class="text-left">
    <?php
    echo '<ul class="pagination">';

    $params = 'query=' . $query;
    if(!$verified_only) $params .= "&unverified";
    $max = ceil($results['count'] / 20);

    if ($page > 1)
        echo '<li><a href="/browse/communities/page/' . ($page - 1) . '?' . $params . '">«</a></li>';
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
        echo '<li><a href="/browse/communities/page/1?' . $params . '">1</a></li>';
        echo '<li><a href="/browse/communities/page/2?' . $params . '">2</a></li>';
        echo '<li><a href="javascript:void(0)">..</a></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            echo '<li class="active"><a href="javascript:void(0)">' . number_format($i) . '</a></li>';
        } else {
            echo '<li><a href="/browse/communities/page/' . $i . '?' . $params . '">' . number_format($i) . '</a></li>';
        }
    }

    if ($end < $max - 3) {
        echo '<li><a href="javascript:void(0)">..</a></li>';
        echo '<li><a href="/browse/communities/page/' . ($max - 1) . '?' . $params . '">' . number_format($max - 1) . '</a></li>';
        echo '<li><a href="/browse/communities/page/' . $max . '?' . $params . '">' . number_format($max) . '</a></li>';
    }

    if ($page < $max)
        echo '<li><a href="/browse/communities/page/' . ($page + 1) . '?' . $params . '">»</a></li>';
    else
        echo '<li><a href="javascript:void(0)">»</a></li>';


    echo '</ul>';
    ?>
</div>
