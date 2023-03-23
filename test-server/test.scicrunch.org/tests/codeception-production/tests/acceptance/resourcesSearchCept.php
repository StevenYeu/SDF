<?php

$I = new AcceptanceTester($scenario);
$I->am("user");
$I->wantTo("Test searching the RRID community portal");

$I->amOnPage("/resources");
$I->pressKey("#search-banner-input", WebDriverKeys::ENTER);
$I->wait(5);
$I->see("Integrated: Animals (");
$I->see("Cellosaurus: Cell Lines (");
$I->see("AntibodyRegistry: Antibodies (");
$I->see("SciCrunch: Registry (");

?>
