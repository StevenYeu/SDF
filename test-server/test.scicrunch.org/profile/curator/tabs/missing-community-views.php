<?php

$holder = new Sources();
$sources = $holder->getAllSources();

$missing_views = Array();

$cxn = new Connection();
$cxn->connect();
$community_structure = $cxn->select("community_structure", Array("cid", "category", "subcategory", "source"), "", Array(), "");
$cxn->close();

$communities = Array();

foreach($community_structure as $cs) {
    if(!isset($sources[$cs["source"]])) {
        $missing_views[] = $cs;
        if(!isset($communities[$cs["cid"]])) {
            $community = new Community();
            $community->getByID($cs["cid"]);
            $communities[$community->id] = $community;
        }
    }
}

?>

<div class="tab-pane fade in active">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Community</th>
                        <th>Missing View ID</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($missing_views as $mv): ?>
                        <tr>
                            <td><?php echo $communities[$mv["cid"]]->name ?></td>
                            <td><?php echo $mv["source"] ?></td>
                            <td><?php echo $mv["category"] ?></td>
                            <td><?php echo $mv["subcategory"] ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
