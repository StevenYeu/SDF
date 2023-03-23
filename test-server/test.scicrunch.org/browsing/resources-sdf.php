<?php

$role = $_SESSION['user'] ? $_SESSION['user']->role : 0;
$holder = new Resource();
$results = $holder->searchColumns($query, ($page - 1) * 20, 20, $fields,$facets,$statusVar);	// last 2 arguments don't matter for old method
$resource_user = new User();
$splits = explode(' ', $query);

$comms = array();
$firstComm = new Community();
$firstComm->name = 'SciCrunch';
$firstComm->id = 0;
$comms[0] = $firstComm;

/******************************************************************************************************************************************************************************************************/

function word_map($a) {
    return '\b' . preg_quote($a, "~") . '\b';
}

/******************************************************************************************************************************************************************************************************/
?>
<!-- span class="results-number">Showing <?php echo count($results['results']) ?> out of <?php echo number_format($results['count']) ?> Resources on page <?php echo $page ?></span -->
<!-- Begin Inner Results -->

<?php
//print_r($results['results']);

if(count($results['results']) > 0):
    foreach ($results['results'] as $data):
        $resource_user->getByID($data->uid);
        $str_name = preg_replace("~(" . implode("|", array_map('word_map', $splits)) . ")~i", "<strong>$1</strong>", $data->columns['Resource Name']);
        $str_description = preg_replace("~(" . implode("|", array_map('word_map', $splits)) . ")~i", "<strong>$1</strong>", $data->columns['Description']);
        $str_description = \helper\decodeUTF8($str_description);
        if(isset($comms[$data->cid])){
            $newComm = $comms[$data->cid];
        } else {
            $newComm = new Community();
            $newComm->getByID($data->cid);
            $comms[$data->cid] = $newComm;
        }
?>

            <?php if ($newComm->id != 0) { ?>
        <div class="inner-results">
             <!-- Changed href to new page previously  echo $community->shortName?>/browse/resourcesedit/ -->
            <h3><a href="/<?php /* Manu */ echo $community->shortName?>/Resources/record/sdf/<?php echo $data->rid ?>/<?php echo "resolver" ?>"><?php echo $str_name ?></a>
            <?php /*if(isset($_SESSION['user']) && $_SESSION['user']->role>0 && isset($data->score)): ?><span style="color:#aaa">(Score = <?php echo $data->score ?>)</span>
            <?php endif */?>
            </h3>
                <div class="overflow-h">
                <p><?php echo $str_description ?></p>
                <ul class="list-inline down-ul">
                    <?php /*
                    
                    
                    <li><?php echo $data->type ?></li>

                    <?php if($newComm->id==0): ?>
                        <li><?php echo $newComm->name ?></li>
                    <?php else: ?>
                        <li><a href="/<?php echo $newComm->portalName ?>"><?php echo $newComm->shortName ?></a></li>
                    <?php endif ?>

                    */
                    ?>

                    <?php if ($data->uid == 0): ?>
                        <li><?php echo Connection::longTimeDifference($data->insert_time) ?> - by Anonymous</li>
                    <?php else: ?>
                        <li><?php echo Connection::longTimeDifference($data->insert_time) ?> - submitted by <?php echo $resource_user->getFullName() ?></li>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['user']) && $_SESSION['user']->role>0): ?>
                        <li>Curation Status: <?php echo $data->status ?></li>
                    <?php endif; ?>

                </ul>
	    </div>
        </div>
        <hr/>
           <?php } ?>
<?php
    endforeach;
endif;
?>


<div class="margin-bottom-30"></div>

<div class="text-left">
    <?php
    echo '<ul class="pagination">';

    $params = 'query=' . $query;
    if($statusVar) $params .= "&status=" . $statusVar;
    $max = ceil($results['count'] / 20);

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
        echo '<li><a href="/browse/resources/page/1?' . $params . '">1</a></li>';
        echo '<li><a href="/browse/resources/page/2?' . $params . '">2</a></li>';
        echo '<li><a href="javascript:void(0)">..</a></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $page) {
            echo '<li class="active"><a href="javascript:void(0)">' . number_format($i) . '</a></li>';
        } else {
            echo '<li><a href="/browse/resources/page/' . $i . '?' . $params . '">' . number_format($i) . '</a></li>';
        }
    }

    if ($end < $max - 3) {
        echo '<li><a href="javascript:void(0)">..</a></li>';
        echo '<li><a href="/browse/resources/page/' . ($max - 1) . '?' . $params . '">' . number_format($max - 1) . '</a></li>';
        echo '<li><a href="/browse/resources/page/' . $max . '?' . $params . '">' . number_format($max) . '</a></li>';
    }

    if ($page < $max)
        echo '<li><a href="/browse/resources/page/' . ($page + 1) . '?' . $params . '">»</a></li>';
    else
        echo '<li><a href="javascript:void(0)">»</a></li>';


    echo '</ul>';
    ?>
</div>
