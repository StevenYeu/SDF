
<?php
echo Connection::createBreadCrumbs('My Communities',array('Home','Account'),array($profileBase,$profileBase.'account'),'My Communities');
?>

<div class="profile container content">
<div class="row">
<!--Left Sidebar-->
    <?php include $_SERVER['DOCUMENT_ROOT'].'/profile/left-column.php'; ?>
<!--End Left Sidebar-->

<div class="col-md-9">
<!--Profile Body-->
<div class="profile-body">
<!--Service Block v3-->


<!--Table Search v2-->
<div class="table-search-v2 margin-bottom-20">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Logo</th>
                <th class="hidden-sm">Community</th>
                <th>Level</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php

            $levels = array('','User','Moderator','Administrator','Owner');
            $level_class = array('','label-success','label-info','label-warning','label-danger');
            foreach($_SESSION['user']->levels as $cid=>$level){
                if($level==0)
                    continue;
                $comm = new Community();
                $comm->getByID($cid);
                echo '<tr>';
                echo '<td><img class="rounded-x" src="/upload/community-logo/'.$comm->logo.'"/></td>';
                echo '<td class="td-width">';
                echo '<h3><a href="'.Community::fullURLStatic($comm).'">'.$comm->name.'</a></h3>';
                echo '<p>'.$comm->description.'</p>';
                echo '</td>';
                echo '<td><span class="label '.$level_class[$level].'">'.$levels[$level].'</span></td>';
                echo '<td>
                        <ul style="list-style: none; margin: 0; padding: 0">
                            <li><a href="'.Community::fullURLStatic($comm).'">
                                <i class="fa fa-external-link" style="font-size: 16px;margin-right: 10px"></i>
                            Go to</a></li>
                            <li><a href="'.$profileBase.'account/communities/'.$comm->portalName.'">
                                <i class="fa fa-cog" style="font-size: 20px;margin-right: 10px"></i>
                            Manage</a></li>
                            <li><a href="javascript:void(0)" data-cid="'.$cid.'" data-main="' . ($cid == $community->id ? "true" : "false" ). '" data-target="#leaveComm" data-toggle="modal" class="leave-community"><i aria-hidden="true" class="fa fa-sign-out" style="font-size: 20px;margin-right: 10px"></i>
                            Leave</a></li>
                        </ul>

                      </td>';
                echo '</tr>';
                       
            }

            ?>

            </tbody>
        </table>
    </div>
</div>
<!--End Table Search v2-->
</div>
<!--End Profile Body-->
</div>
</div><!--/end row-->
</div><!--/container-->
<!--=== End Profile ===-->


<div id="leaveComm" class="large-modal back-hide leave-comm">
    <div class="close close-btn dark less-right" data-dismiss="modal">X</div>
    <h2>Leaving Community</h2>
    <p style="margin:20px 0">
        Are you sure you want to leave this community? Leaving the commun
ity will revoke any permissions you have been
        granted in this community.
    </p>
    <div class="btn-u btn-u-default close-btn" data-dismiss="modal" class="close">No</div>
    <a class="btn-u btn-u-red yes-leave" href="">Yes</a>
</div> 

<script type="text/javascript">
    $(".leave-community").on("click", function(e){
        //get cid of community user is trying to leave
        var cid = $(this).data("cid");

        //if trying to leave community user is currently in, change GET url var
        var url = '/forms/leave.php?cid=' + cid;
        if($(this).data('main') == true){
            url += "&main";

        }

        $(".yes-leave").attr('href', url )
    });
</script>
