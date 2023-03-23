<?php

$I = new AcceptanceTester($scenario);
$I->am("user");
$I->wantTo("Test searching the RRID community portal");

$I->amOnPage("/resources/data/source/SCR_013869-1/search?q=%2A&l=");
$I->see("Homo sapiens");
$I->see("Mus musculus");
$I->dontSee("Please Search Again!");
$I->dontSee("We could not find any records in this particular source");

$I->amOnPage("/resources/data/source/nlx_144509-1/search?q=wormbase&l=");
$I->see("A central data repository for nematode biology including the complete genomic sequence, gene predictions and orthology assignments from a range of related nematodes.");
$I->dontSee("Please Search Again!");
$I->dontSee("We could not find any records in this particular source");

$I->amOnPage("/resources/data/source/nif-0000-07730-1/search?q=pristionchus&l=pristionchus");
$I->see("GRK5 Antibody");
$I->dontSee("Please Search Again!");
$I->dontSee("We could not find any records in this particular source");

$I->amOnPage("/resources/data/source/nlx_154697-1/search?q=pristionchus&l=pristionchus");
$I->see("Pristionchus exspectatus");
//$I->see("Pristionchus pacificus wild isolate");
$I->dontSee("Please Search Again!");
$I->dontSee("We could not find any records in this particular source");

?>
