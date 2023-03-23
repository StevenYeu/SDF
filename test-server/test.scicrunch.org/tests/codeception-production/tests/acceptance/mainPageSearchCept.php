<?php

$I = new AcceptanceTester($scenario);
$I->am("user");
$I->wantTo("Test the main page search");

$I->amOnPage("/");
$I->fillField("#search-banner-input", "caenorhabditis");
$I->pressKey("#search-banner-input", WebDriverKeys::ENTER);
$I->wait(5);
$I->dontSee("0 results from 0 data sources");
$I->dontSee("0 resources");
$I->dontSee("0 communities with matching data sources");
$I->dontSee("0 literature");
$I->see(" data sources (only showing 20 data sources)");
$I->see(" resources (only showing 20 results)");
$I->see(" communities with matching data sources");
$I->see(" literature (only showing 20 results");

$I->amOnPage("/");
$I->fillField("#search-banner-input", "caenorhabditiss");
$I->pressKey("#search-banner-input", WebDriverKeys::ENTER);
$I->wait(5);
$I->see("0 results from 0 data sources");
$I->see("0 resources");
$I->see("0 communities with matching data sources");
$I->see("0 literature");

$I->amOnPage("/");
$I->fillField("#search-banner-input", "[Caenorhabditis {NCBITaxon:6237}]");
$I->pressKey("#search-banner-input", WebDriverKeys::ENTER);
$I->wait(5);
$I->dontSee("0 results from 0 data sources");
$I->dontSee("0 resources");
$I->dontSee("0 communities with matching data sources");
$I->dontSee("0 literature");
$I->see(" data sources (only showing 20 data sources)");
$I->see(" resources (only showing 20 results)");
$I->see(" communities with matching data sources");
$I->see(" literature (only showing 20 results");

?>
