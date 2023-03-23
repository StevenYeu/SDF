<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Relase Notes',array($home, 'Term Dashboard'),array('/'.$community->portalName, '/'.$community->portalName . '/interlex/dashboard'),'Release Notes');
?>

<div class='container'>
    <h2>Term Release Notes:</h2>
    <p><i>- Last updated 2020-01-09</i></p>
    <ul style="font-size:16px">
        <li>Added comment section for each term entity</li>
        <li>Added term version history tab</li>
        <li>Added searching and sorting functions in relationships tab</li>
        <li>Created history dashboard (<a target="_blank" href="/<?php echo $community->portalName ?>/interlex/dashboard-history?origCid=&page=1&sort=desc">Link</a>)</li>
    </ul>
</div>
