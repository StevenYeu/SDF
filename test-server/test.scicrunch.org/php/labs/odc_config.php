<?php

//nothing secure here, so should be OK to leave here.

$conf['odc-sci']['community_id'] = 97;
$conf['odc-tbi']['community_id'] = 501;
$conf['odc-pwc']['community_id'] = 502;

/* Production server parameters */
if (FQDN == 'scicrunch.org') {

    // abel, mike, austin, jeff, romana, michael orr, russel huie, anastasia keller, karim
    $conf['odc-sci']['curation_team'] = array(34206, 31651, 35258, 247, 35485, 36968, 33476, 41363, 33464);
    $conf['odc-sci']['curation_team']['email'] = array('data@odc-sci.org', 'michiu@ucsd.edu');

    // abel, mike, austin, adam
    $conf['odc-tbi']['curation_team'] = array(34206, 35258, 31651, 33353);
    $conf['odc-tbi']['curation_team']['email'] = array('data@odc-tbi.org', 'michiu@ucsd.edu');

    // karim, romana
    $conf['odc-sci']['editorial_team'] = array(35485, 33464);
    $conf['odc-sci']['editorial_team']['email'] = array('michiu@ucsd.edu', 'kfouad@ualberta.ca', 'rvavrek@ualberta.ca');

    // publication EZID DOI "shoulder"
    $conf['shoulder'] = '10.34945/F5';
    $conf['reserve_url'] = 'https://ezid.cdlib.org/shoulder/doi:' . $conf['shoulder'];
}

/* Test server parameters */
elseif (FQDN == 'test.scicrunch.org') {

    // abel, mike, austin, jeff, romana, michael orr, russel huie, anastasia keller, karim
    $conf['odc-sci']['curation_team'] = array(34206, 31651, 35258, 247, 35485, 36968, 33476, 41363, 33464);
    $conf['odc-sci']['curation_team']['email'] = array('data@odc-sci.org', 'michiu@ucsd.edu');

    // abel, mike, austin, adam
    $conf['odc-tbi']['curation_team'] = array(34206, 35258, 31651, 33353);
    $conf['odc-tbi']['curation_team']['email'] = array('data@odc-tbi.org', 'michiu@ucsd.edu');

    // karim, romana
    $conf['odc-sci']['editorial_team'] = array(35485, 33464);
    $conf['odc-sci']['editorial_team']['email'] = array('michiu@ucsd.edu', 'kfouad@ualberta.ca', 'rvavrek@ualberta.ca');

    // Test EZID DOI "shoulder"
    $conf['shoulder'] = '10.5072/FK2';
    $conf['reserve_url'] = 'https://ezid.cdlib.org/shoulder/doi:' . $conf['shoulder'];
}

/* Mike's dev server parameters */

else {

    // abel, mike, austin, jeff, romana, michael orr, russel huie, anastasia keller, karim
    $conf['odc-sci']['curation_team'] = array(31651);
    $conf['odc-sci']['curation_team']['email'] = array('michiu@ucsd.edu', 'overlunch@gmail.com');

    $conf['odc-tbi']['curation_team'] = array(31651);
    $conf['odc-tbi']['curation_team']['email'] = array('michiu@ucsd.edu', 'overlunch@gmail.com');

    // karim, romana
    $conf['odc-sci']['editorial_team'] = array(31651);
    $conf['odc-sci']['editorial_team']['email'] = array('michiu@ucsd.edu', 'overlunch@gmail.com');

    // dev EZID DOI "shoulder"
    $conf['shoulder'] = '10.5072/FK2';
    $conf['reserve_url'] = 'https://ezid.cdlib.org/shoulder/doi:' . $conf['shoulder'];
}

?>
