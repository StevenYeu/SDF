<?php

$I = new AcceptanceTester($scenario);
$I->am("user");
$I->wantTo("Test searching the RRID community portal");

$I->amOnPage("/resources/data/search?q=%2A&l=");
$I->see("Integrated: Animals");
$I->see("Cellosaurus: Cell Lines");
$I->see("AntibodyRegistry: Antibodies");
$I->see("SciCrunch: Registry");

?>
