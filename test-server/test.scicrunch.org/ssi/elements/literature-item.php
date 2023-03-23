<?php
    $months = array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

    if($data["type"] == "pmid") {
        $id = "PMID:" . $data["pmid"];  // without PMID prefix
        $url = "/" . $data["pmid"];
    } elseif($data["type"] == "doi") {
        $id = "DOI:" . $data["doi"];    // without DOI prefix
        $url = $data["url"];
    }

    $title = $data["title"];
    $author = $data["author"];  // lastName, firstName
    $year = $data["year"];  // 2017
    $month = $data["month"];    // 1
    $day = $data["day"];    // 1
    $abstract = $data["abstract"];
    $journal = $data["journal"];
    $use_snippet = $data["use-snippet"] ? true : false;
    $snippet = $data["snippet"];
    $exact = $data["exact"];
    $snippet_split = explode($exact, $snippet);
    if(count($snippet_split) > 1) {
        $snippet_html = $snippet_split[0] . "<strong>" . $exact . "</strong>" . $snippet_split[1];
    } else {
        $snippet_html = $snippet;
    }
    $grant_infos = $data["grant-infos"];
?>

<div class="row">
    <div class="col-md-12">
        <div class="tag-box tag-box-v2 box-shadow shadow-effect-1">
            <h2><a href="<?php echo $url ?>"><?php echo $title ?></a></h2>
            <ul class="list-inline up-ul">
                <?php if($author): ?><li><?php echo $author ?></li><?php endif ?>
                <li><?php echo $journal ?></li>
                <li><?php echo $year.' '.$months[$month].' '.$day ?></li>
            </ul>
            <?php if($use_snippet): ?>
                <p>
                    <strong>Literature context:</strong> <?php echo $snippet_html ?>
                </p>
                <br/>
                <strong>Abstract:</strong>
            <?php endif ?>
            <p class="truncate-desc"><?php echo $abstract ?></p>
            <ul class="list-inline up-ul" style="margin-top:7px">
                <li><a href="<?php echo $url ?>"><?php echo $id ?></a></li>
            </ul>
            <?php if($grant_infos): ?>
                <strong>Funding information:</strong>
                <ul>
                    <?php foreach($grant_infos as $gi): ?>
                        <li><?php echo $gi->agency . " - " . $gi->identifier . "(" . $gi->country . ")"; ?></li>
                    <?php endforeach ?>
                </ul>
            <?php endif ?>
        </div>
    </div>
</div> 
