<?php

$user = $data["user"];
$community = $data["community"];

if(!$user || !$community) {
    return;
}

$community_access_request = CommunityAccessRequest::loadBy(Array("cid", "uid", "status"), Array($community->id, $user->id, "pending"));

?>

<div style="width: 95%">
    <?php if($community_access_request): ?>
        <h4>
            A community access request has been sent to the owner.
        </h4>
    <?php else: ?>
        <h2>Welcome to the <?php echo strtoupper($community->portalName); ?> Community!</h3>
        <h4 style="padding-left: 20px"><p>You are a General Member of the ODC Community. You can explore and access public datasets, and others in the community can directly share their data with you. Click here to explore the Published Dataset.</p>
            <p>To upload, share, release, and publish your data or explore non-published data, you must be a member of a verified lab. Click here to create or join a lab.</p>
        </h4>

        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css">
        <script>
            $(document).ready( function () {
                $('#shared')
                    .addClass( 'nowrap' )
                    .dataTable( {
                        responsive: true,
                        columnDefs: [
                            { targets: [-1, -3], className: 'dt-body-right' }
                        ]
                    } );
            } );
        </script>

        <h3 class="text-primary">Datasets followed/Shared with me</h3>
        <table id="shared">
        <thead>
            <tr>
                <th>Dataset</th>
                <th>Origin</th>
                <th># Records</th>
                <th># Fields</th>
                <th>Last Visited</th>
                <th>Last Edited</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
    <?php endif ?>
</div>
