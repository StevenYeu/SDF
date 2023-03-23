<h4>Types have been changed.</h4>
<h4>Previous Facets</h4>
<?php if(isset($_SESSION["term_pre_facets"]) && count($_SESSION["term_pre_facets"]) > 0): ?>
    <?php foreach ($_SESSION["term_pre_facets"] as $pre_facet): ?>
        <p><?php echo $pre_facet." (facet)" ?></p>
    <?php endforeach ?>
<?php endif ?>
<hr/>

<?php unset($_SESSION["term_pre_facets"]); ?>
