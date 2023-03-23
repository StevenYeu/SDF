<?php

$I = new AcceptanceTester($scenario);
$I->am("user");
$I->wantTo("login");
$I->amOnPage("/");
$I->click("a.topbar-link.btn-login");
$I->fillField("form input[name='email']", "test-account@scicrunch.org");
$I->fillField("form input[name='password']", "sCetRKkV5aIkmjeIM");
$I->click("form button[type='submit']");
$I->wait(5);
$I->see("Logout");

?>
