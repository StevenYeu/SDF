<?php

$community = $data["community"];
$preview_id = $data["preview-id"];

$allowed_ids = Array("1", "2", "3", "4", "5", "6", "7", "8", "9", "11", "12", "13", "14");
if(!in_array($preview_id, $allowed_ids)) {
    return;
}

?>

<?php ob_start(); ?>
<div>
    <img src="/images/spp/<?php echo $preview_id ?>.svg" style="width: 100%" />
</div>
<?php $html = ob_get_clean(); ?>

<?php

$rin_data = Array(
    "title" => "Hypothesis Center - Featuring the Signaling Pathways Project",
    "rows" => Array(
        Array(
            Array(
                "body" => Array(
                    Array("html" => $html),
                ),
            ),
        ),
    ),
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Hypothesis Center", "url" => $community->fullURL() . "/about/hypothesis_center"),
        Array("text" => "Preview", "active" => true),
    ),
);
echo \helper\htmlElement("rin-style-page", $rin_data);

?>
