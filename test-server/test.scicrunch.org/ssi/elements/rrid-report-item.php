<?php
    $item = $data["rrid-item"];   // array of items with the same type, but multiple subtypes
    if(!$item) return NULL;
?>
<h3>
    <?php if(RRIDReportItem::$allowed_types[$item->type]["pretty-type-name"] == "Cell line"): ?>
      <a class="external" target="_blank" href="http://web.expasy.org/cellosaurus/<?php echo $item->getData("ID", true) ?>"><?php echo $item->getData("name", false) ?></a>
      <a target="_blank" href="/data/record/SCR_013869-1/<?php echo $item->getData("ID", true) ?>/resolver?i=<?php echo $item->getData("Uid", true) ?>" data-toggle="tooltip" title="Resource report">
        <span class="fa-stack fa-md">
          <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
          <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
        </span>
      </a>
    <?php elseif(RRIDReportItem::$allowed_types[$item->type]["pretty-type-name"] == "Antibody"): ?>
      <a class="external" target="_blank" href="http://antibodyregistry.org/<?php echo $item->getData("Antibody ID", true) ?>"><?php echo $item->getData("name", false) ?></a>
      <a target="_blank" href="/data/record/nif-0000-07730-1/<?php echo $item->getData("Antibody ID", true) ?>/resolver?i=<?php echo $item->getData("Uid", true) ?>" data-toggle="tooltip" title="Resource report">
        <span class="fa-stack fa-md">
          <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
          <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
        </span>
      </a>
    <?php endif ?>
</h3>
<?php echo "<h5>Type: " . RRIDReportItem::$allowed_types[$item->type]["pretty-type-name"] . "</h5>"; ?>
<?php if(!empty($item->subtypes())): ?>
    <?php echo "<h5>Uses:</h5>"; ?>
    <ul>
        <?php foreach($item->subtypes() as $subtype): ?>
            <li><?php echo $subtype->subtype ?></li>
        <?php endforeach ?>
    </ul>
<?php endif ?>
<?php echo "<h5>Data:</h5>"; ?>
<table style="border-spacing: 10px 0px; border-collapse: collapse">
    <?php foreach(RRIDReportItem::$allowed_types[$item->type]["rrid-data-cols"] as $col): ?>
        <tr style="border: solid; border-width: 1px; 0">
            <td style="padding-left: 5px"><strong><?php echo $col ?>:</strong> &nbsp;</td>
            <td style="padding-right: 5px"><?php echo $item->getData($col, true) ?></td>
        </tr>
    <?php endforeach ?>
</table>
