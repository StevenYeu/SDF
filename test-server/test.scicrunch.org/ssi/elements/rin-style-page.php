<?php

$title = $data["title"];
$rows = $data["rows"];
$html_body = $data["html-body"];
$breadcrumbs = $data["breadcrumbs"];
$before_data = $data["before-data"];
$no_style = $data["no-style"];
$title_center = $data["title-center"] ? true : false;

## add "new search button", "new literature search button", "previous search results button", "search another resource report type button" and "go to authentication report button" in the header -- Vicky-2019-3-11
$n = count($breadcrumbs);
$new_search_button = "";
$search_result_button = "";
$select_resouce_type_button = "";
$go_to_reproducibility_report_button = "";
$type_list = array("Tools", "Cell Lines", "Antibodies", "Organisms", "Plasmids", "Biosamples", "Discovery Portal", "Literature", "Protocols");
$home_url = $breadcrumbs[0]["url"];
$actual_link = "{$_SERVER['REQUEST_URI']}";
$actual_host = "{$_SERVER['HTTP_HOST']}";

foreach($breadcrumbs as $idx=>$bc){
    if(in_array($bc["text"], $type_list)) {
        if($idx != $n-1) {
            $search_button = "<a target='_self' href='".$bc["url"]."' class='btn btn-primary'><i class='fa fa-search'></i>";
            if($bc["text"] == "Discovery Portal") $new_search_button = $search_button . "New Discovery Portal Search</a> ";
            else if($bc["text"] == "Literature") $new_literature_search_button = $search_button . "New Literature Search</a> ";
            else $new_search_button = $search_button . "New Search</a> ";

            if(isset($_GET["q"]) && $bc["text"] != "Discovery Portal")
                $search_result_button = "<a target='_self' href='".$bc["url"]."?q=".$_GET["q"]."&l=".$_GET["l"]."' class='btn btn-primary'><i class='fa fa-history'></i> Previous Search Results</a> ";
        } else if(!in_array($bc["text"], ["Discovery Portal", "Literature"]) && $breadcrumbs[$idx - 1]["text"] != "Discovery Sources") {
            $select_resouce_type_button = "<a target='_self' href='".$home_url."/rin/rrids' class='btn btn-primary'><i class='fa fa-bars'></i> Select Another Resource Report Type</a> ";
        }
        if(in_array($bc["text"], ["Cell Lines", "Antibodies"])) {
            $go_to_reproducibility_report_button = "<a target='_self' href='".$home_url."/rin/rrid-report' class='btn btn-primary'><i class='fa fa-gears'></i> Go to Authentication Report</a> ";
        }
    }
}

/*
    ### rows structure: ###
    "rows" => Array(
        Array(  1 or 2 elements in this array. 1 = col-md-12, 2 = col-md-6
            Array(
                "title" => "this is the sections title",
                "body" => Array(
                    Array(
                        "p" => "paragraph data"
                    ),
                    Array(
                        "ul" => Array(
                            "a list of",
                            "ul items",
                        )
                    ),
                    Array(
                        "html" => "<ul><li>preformatted</li><li>html</li></ul>"
                    ),
                ),
            ),
        )
    )

    ### breadcrumbs structure: ###
    "breadcrumbs" => Array(
        Array("url" => "/", "text" => "Home"),
        Array("url" => "/profile", "text" => "Profile home"),
        Array("active" => true, "text" => "This page"),
    )
*/

?>

<?php if(!$no_style): ?>
    <link href="/css/rin.css" rel="stylesheet" />
<?php endif ?>
<?php if($before_data): ?>
    <?php echo $before_data ?>
<?php endif ?>
<div class="rin">
    <div class="report">
        <?php if(strpos($home_url, "dknet") || strpos($actual_host, "dknet") || strpos($actual_link, "dknet")): ?>
            <div class="header">
                <div class="container">
                    <div class="row">
                      <?php ## changed header style (include rin.css)-- Vicky-2018-12-17 ?>
                      <div style="float:left">
                        <a target='_self' href="<?php echo $home_url ?>"><img src="https://scicrunch.org/upload/community-logo/_434153.png" class="w3-round-large" alt="Norway" style="width:150px"></a>
                      </div>
                      <div>
                        <?php
                          if($title){
                              if($title_center) {
                                echo '<h1 style="margin-top:50px;text-align:center">';
                              } else {
                                echo '<h1 style="margin-top:50px">';
                              }
                              ## added "new search button", "new literature search button", "previous search results button", "search another resource report type button" and "go to authentication report button" in the header -- Vicky-2019-3-11
                              echo $title." ".$new_search_button.$search_result_button.$select_resouce_type_button.$new_literature_search_button." ".$go_to_reproducibility_report_button."</h1>";
                          }
                        ?>
                        <?php if($breadcrumbs): ?>
                            <ol class="breadcrumb">
                                <?php foreach($breadcrumbs as $bc): ?>
                                    <li>
                                        <a
                                            class="color-white <?php if($bc["active"]) echo 'active' ?>"
                                            target="_self"
                                            <?php if($bc["url"]) echo 'href="' . $bc["url"] . '"' ?>
                                        >
                                            <?php echo $bc["text"] ?>
                                        </a>
                                    </li>
                                <?php endforeach ?>
                            </ol>
                        <?php endif ?>
                      </div>
                    </div>
                </div>
            </div>
        <?php elseif((strpos($home_url, "neuinfo") || strpos($home_url, "nif") || strpos($actual_host, "nif") || strpos($actual_link, "nif")) && in_array(end($breadcrumbs)["text"], ["Suggested data repositories", "Suggested software"])): ?>
            <!-- Brand and toggle get grouped for better mobile display -->
            <!--=== Breadcrumbs v3 ===-->
            <div class="breadcrumbs-v3" style="padding: 0px 0px 20px 0px">
                <div class="container">
                    <ul class="pull-left breadcrumb" style="font-size: 12px">
                        <?php foreach($breadcrumbs as $bc): ?>
                            <?php if($bc["active"]): ?>
                                <li class="active"><?php echo $bc["text"] ?></li>
                            <?php else: ?>
                                <li><a href="<?php echo $bc["url"] ?>" style="color: white;"><?php echo $bc["text"] ?></a></li>
                            <?php endif ?>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
        <?php endif ?>
        <div class="header-background"></div>
        <div class="row">
            <div class="container">
                <div class="wrapper">
                    <?php foreach($rows as $row): ?>
                        <?php
                            if(count($row) == 1) {
                                $colclass = "col-md-12";
                            } elseif(count($row) == 2) {
                                $colclass = "col-md-6";
                            }
                        ?>
                        <div class="row">
                            <?php foreach($row as $col): ?>
                                <div class="<?php echo $colclass ?>">
                                    <div class="section">
                                        <?php if($col["title"]): ?>
                                            <div class="title"><?php echo $col["title"] ?></div>
                                        <?php endif ?>
                                        <?php if($col["body"]): ?>
                                            <?php $body_class = $col["title"] ? "body" : "body-no-margin"; ?>
                                            <div class="<?php echo $body_class ?>">
                                                <?php foreach($col["body"] as $body): ?>
                                                    <?php if($body["p"]): ?>
                                                        <p><?php echo $body["p"] ?></p>
                                                    <?php endif ?>
                                                    <?php if($body["ul"]): ?>
                                                        <ul>
                                                            <?php foreach($body["ul"] as $li): ?>
                                                                <li><?php echo $li ?></li>
                                                            <?php endforeach ?>
                                                        </ul>
                                                    <?php endif ?>
                                                    <?php if($body["html"]): ?>
                                                        <?php echo $body["html"] ?>
                                                    <?php endif ?>
                                                <?php endforeach ?>
                                            </div>
                                        <?php endif ?>
                                    </div>
                                </div>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>
                    <?php if($html_body): ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php echo $html_body ?>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
