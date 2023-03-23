<?php

// requires data to have:
// count - number of results
// per_page - results per page
// current_page - the current page
// params - any other get params
// page_location - either path (page number in url path) or query (page number in get query)
// query_param_name - if page_location == query, what is the name of the get param
// base_url - the base url of each page, if page_location == path, then there should be %d in the path string

$count = $data["count"];
$per_page = $data["per_page"];
$current_page = $data["current_page"];
$max = ceil($count / $per_page);

if ($current_page - 3 > 0) $start = $current_page - 3;
else $start = 1;
if ($current_page + 3 < $max) $end = $current_page + 3;
else $end = $max;

function baseURL($data, $link_page){
    $base_url = $data["base_url"];
    if($data["page_location"] === "path") $new_base_url = sprintf($base_url, $link_page);
    else $new_base_url = $base_url;
    return $new_base_url;
}

function getParams($data, $link_page){
    $get_params = $data["params"];
    $page_location = $data["page_location"];
    if($page_location === "query"){
        $query_param_name = $data["query_param_name"];
        if(strlen($get_params) > 0) $sep = "&";
        else $sep = "";
        $get_params .= $sep . $query_param_name . "=" . $link_page;
    }
    return $get_params;
}

function fullURL($data, $link_page){
    $base_url = baseURL($data, $link_page);
    $get_params = getParams($data, $link_page);
    $full_url = $base_url . "?" . $get_params;
    return $full_url;
}

?>

<div class="margin-bottom-30"></div>

<div class="text-left">
    <ul class="pagination">

    <?php if ($current_page > 1): ?>
        <li><a href="<?php echo fullURL($data, $current_page - 1) ?>">«</a></li>
    <?php else: ?>
        <li><a href="javascript:void(0)">«</a></li>
    <?php endif ?>

    <?php

    ?>

    <?php if ($start > 2): ?>
        <li><a href="<?php echo fullURL($data, 1) ?>">1</a></li>
        <li><a href="<?php echo fullURL($data, 2) ?>">2</a></li>
        <li><a href="javascript:void(0)">..</a></li>
    <?php endif ?>

    <?php for ($i = $start; $i <= $end; $i++): ?>
        <?php if ($i == $current_page): ?>
            <li class="active"><a href="javascript:void(0)"><?php echo number_format($i) ?></a></li>
        <?php else: ?>
            <li><a href="<?php echo fullURL($data, $i) ?>"><?php echo number_format($i) ?></a></li>
        <?php endif ?>
    <?php endfor ?>

    <?php if ($end < $max - 3): ?>
        <li><a href="javascript:void(0)">..</a></li>
        <li><a href="<?php echo fullURL($data, $max - 1) ?>"><?php echo number_format($max - 1) ?></a></li>
        <li><a href="<?php echo fullURL($data, $max) ?>"><?php echo number_format($max) ?></a></li>
    <?php endif ?>

    <?php if ($current_page < $max): ?>
        <li><a href="<?php echo fullURL($data, $current_page + 1) ?>">»</a></li>
    <?php else: ?>
        <li><a href="javascript:void(0)">»</a></li>
    <?php endif ?>


    </ul>
</div>
