<?php

$I = new AcceptanceTester($scenario);
$I->am("user");
$I->wantTo("Test the results of the first resolver page to make sure it's returning content");
$I->amOnPage("/resolver");
for($i = 1; $i <= 5; $i++) {
    $I->click(".inner-results:nth-of-type(" . $i . ") .the-title a");
    $I->wait(4);
    $I->dontSee("Could not find");
    $I->see("RRID:");
    $I->moveBack();
    $I->wait(4);
}

?>
