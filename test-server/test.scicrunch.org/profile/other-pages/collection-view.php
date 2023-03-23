<?php

$collection = $_SESSION['user']->collections[$arg1];

$holder = new Sources();
$sources = $holder->getAllSources();

echo Connection::createBreadCrumbs($collection->name,array('Home','Account','My Collections'),array($profileBase,$profileBase.'account',$profileBase.'account/collections'),$collection->name);

$holder = new Item();
$items = $holder->getByCollection($collection->id, $_SESSION['user']->id);

$has_literature = false;
foreach($items as $item) {
    if($item->view == "literature") {
        $has_literature = true;
        break;
    }
}

/*$all_scicrunch = true;
foreach($items as $item2){
    if($item2->view != "nlx_144509-1"){
        $all_scicrunch = false;
        break;
    }
}*/
$collected_views = array();
foreach($items as $item2){
    $collected_views[] = $item2->view;
}


$unique_views_count = array_count_values($collected_views);
$unique_views = array_keys($unique_views_count);

?>

<style>
    .collection-view-table td p {
        margin-left:10px;
    }
    .record-load, .snippet-load, .saved-this-search {
        position: fixed;
        left:50%;
        margin-left:-400px;
        top:20px;
        width:800px;
        padding:20px;
        border: 1px solid #666;
        z-index: 991;
        display: none;
        background:#fff;
        max-height: 90%;
        overflow: auto;
    }
</style>
<div class="profile container content">
    <div class="row">
        <!--Left Sidebar-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>
        <!--End Left Sidebar-->

        <div class="col-md-9">
            <!--Profile Body-->
            <div class="profile-body">

                <div class="dropdown" style="display: inline;" >
                    <button class=" dropdown-toggle btn btn-primary" aria-expanded="false" type="button" data-toggle="dropdown">
                        Download Single View
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" style="margin: 0px; padding:0px; top: 25px;">
                        <?php foreach($unique_views as $views): ?>
                            <?php $source = new Sources();
                                $source->getByView($views);
                            ?>
                            <?php if($source->id): ?>
                                <li  style="margin: 0 0 3px 0;">
                                    <a href="/php/collection-view-csv.php?collection=<?php echo $collection->id ?>&view=<?php echo $views ?>" class="btn btn-default">Download <?php echo $source->source .' ('.$unique_views_count[$views].')'?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="" style="margin-bottom:20px; display: inline;">
                    <a href="/forms/collection-forms/collection.csv.php?collection=<?php echo $collection->id ?>" class="btn btn-primary">Download All</a>
                </div>
                <?php if($has_literature): ?>
                    <div class="" style="margin-bottom:20px; display: inline;">
                        <a href="/forms/collection-forms/collection.ris.php?collection=<?php echo $collection->id ?>" class="btn btn-primary">Download RIS</a>
                    </div>
                <?php endif ?>
                <!--Service Block v3-->
                <div class="table-search-v2 margin-bottom-20">
                    <div class="table-responsive">
                        <table class="table table-hover collection-view-table">
                            <thead>
                            <tr>
                                <th>Data type</th>
                                <th>Data</th>
                                <th>Community</th>
                                <th>View</th>
                                <th>Insert Time</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            $commArray = array();
                            foreach($items as $item){
                                if($item->view != "literature" && $item->view != "view" && !isset($sources[$item->view])) continue;
                                if(!isset($commArray[$item->community])){
                                    $comm = new Community();
                                    $comm->getByID($item->community);
                                    $commArray[$item->community] = $comm;
                                } else {
                                    $comm = $commArray[$item->community];
                                }
                                echo '<tr>';
                                echo '<td>';
                                if($item->view == "literature") {
                                    echo 'Literature';
                                } elseif($item->view == "view") {
                                    echo 'Data view';
                                } else {
                                    echo 'Single data item';
                                }
                                echo '</td>';
                                echo '<td>';
                                $xml = simplexml_load_string($item->snippet);
                                if($item->view == "literature") {
                                    echo '<h3><a href="/' . $item->uuid . '">' . $xml->title . '</a></h3>';
                                    echo '<p>' . $xml->abstract . '</p>';
                                    echo '<p style="margin-top:10px;"><a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $item->uuid . '">http://wwww.ncbi.nlm.nih.gov/pubmed/' . $item->uuid . '</a></p>';
                                } elseif($item->view == "view") {
                                    echo '<h3><a href="/' . $comm->portalName . '/data/source/' . $item->uuid . '/search">' . $xml->title . '</a></h3>';
                                    echo '<p style="margin-top:10px">' . $xml->description . '</p>';
                                } else {
                                    echo '<h3>'.$xml->title.'</h3>';
                                    echo '<p>'.$xml->description.'</p>';
                                    echo '<p style="margin-top:10px;"><b>URL:</b> <a href="'.$xml->url.'">'.$xml->url.'</a></p>';
                                    echo '<p style="margin-top:5px;"><b>Citation:</b> '.$xml->citation.'</p>';
                                }
                                echo '</td>';
                                echo '<td>'.$comm->name.'</td>';
                                if($item->view == "literature") echo '<td>Literature</td>';
                                elseif($item->view == "view") echo "<td>Data view</td>";
                                else echo '<td>'.$sources[$item->view]->getTitle().'</td>';
                                echo '<td>'.date('h:ia F j, Y', $item->time).'</td>';
                                echo '<td>';
                                echo '<div class="btn-group" style="margin-top:-4px">
                                        <button type="button" class="btn-u btn-default dropdown-toggle" data-toggle="dropdown">
                                    Action
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">';
                                echo '<li><a href="/forms/collection-forms/remove-item.php?community='.$item->community.'&uuid='.$item->uuid.'&view='.$item->view.'&collection='.$item->collection.'&redirect=true"><i class="fa fa-times"></i> Remove</a></li>
                                        </ul>
                                    </div>';
                                echo '</td>';
                                echo '</tr>';
                            }

                            ?>

                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
        <!--End Profile Body-->
    </div>
    <!--/end row-->
</div>

<!--/container-->
<!--=== End Profile ===-->
<div class="background"></div>
<div class="record-load back-hide"></div>
<div class="saved-this-search back-hide no-padding">
    <div class="close dark less-right">X</div>
    <form method="post" action="/forms/other-forms/edit-saved-search.php"
          id="header-component-form" class="sky-form" enctype="multipart/form-data">
        <header>Rename This Saved Search</header>
        <fieldset>
            <section>
                <label class="label">Name</label>
                <label class="input">
                    <i class="icon-append fa fa-question-circle"></i>
                    <input type="hidden" name="id" class="saved-id-input"/>
                    <input type="text" name="name" class="saved-name-input" placeholder="Focus to view the tooltip">
                    <b class="tooltip tooltip-top-right">The name of your saved search.</b>
                </label>
            </section>
        </fieldset>

        <footer>
            <button type="submit" class="btn-u btn-u-default" style="width:100%">Rename</button>
        </footer>
    </form>
</div>
