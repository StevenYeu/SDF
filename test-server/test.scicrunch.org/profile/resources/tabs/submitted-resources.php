<div class="tab-pane fade" id="submitted-resources">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Identifier</th>
                    <th class="hidden-sm">Resource Name</th>
                    <th>Status</th>
                    <th>Insert Time</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
    
                $level_class = array('Curated'=>'label-success','Pending'=>'label-info','Rejected'=>'label-danger');
                foreach($resources['results'] as $resource){
                    $resource->getColumns();
                    echo '<tr>';
                    echo '<td>'.$resource->rid.'</td>';
                    echo '<td>';
                    echo $resource->columns['Resource Name'];
                    echo '</td>';
                    echo '<td><span class="label '.$level_class[$resource->status].'">'.$resource->status.'</span></td>';
                    echo '<td>'.date('h:ia F j, Y', $resource->insert_time).'</td>';
                    echo '<td><a href="/'.Community::getPortalName($community).'/about/resourcesedit/'.$resource->rid.'"><i class="fa fa-cog" style="font-size: 20px;margin-right: 10px"></i></a></td>';
          //           echo '<td><a href="/browse/resourcesedit/'.$resource->rid.'"><i class="fa fa-cog" style="font-size: 20px;margin-right: 10px"></i></a></td>';
                    echo '</tr>';
                }
    
                ?>
    
                </tbody>
            </table>
        </div>
    </div>
</div>
