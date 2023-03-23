<?php

$type = $data["type"];
switch($type) {
    case "default":
        $type_class = "label-default";
        break;
    default:
        $type_class = "label-success";
        break;
}

?>
<span class="label <?php echo $type_class ?>" title="This resource has updated information"><?php echo $data["text"] ?></span>
