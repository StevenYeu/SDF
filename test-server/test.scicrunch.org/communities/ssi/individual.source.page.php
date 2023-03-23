<?php

$snippet = new Snippet();
$snippet->getSnippetByView($community->id, $vars['id']);

$splits = explode('-', $vars['id']);
$rootID = join('-', array_slice($splits, 0, count($splits) - 1));

$url = Connection::environment() . '/v1/federation/data/nlx_144509-1.xml?q=*&count=1&filter=original_id:' . $rootID;
$xml = simplexml_load_file($url);
if ($xml) {
    foreach ($xml->result->results->row->data as $data) {
        $record[(string)$data->name] = (string)$data->value;
    }
}

$views_data = \helper\getViewsFromOriginalID($vars['id']);
$views = $views_data['views'];

$source = $sources[$vars['id']];

?>

<div class="container">
    <h1 style="margin: 40px 0"><?php echo $record['Resource Name'] ?></h1>

    <div class="tab-v5">
        <ul class="nav nav-tabs nav-tabs-js" role="tablist">
            <li class="active"><a href="#description" role="tab" data-toggle="tab">Description</a></li>
            <li><a href="#views" role="tab" data-toggle="tab">Views</a></li>
            <li><a href="#licenses" role="tab" data-toggle="tab">License</a></li>
        </ul>

        <div class="tab-content">
            <!-- Description -->
            <div class="tab-pane fade in active" id="description">
                <div class="row">
                    <div class="col-md-7">
                        <p><?php echo \helper\formattedDescription($record['Description']) ?></p><br>

                        <h3 class="heading-md margin-bottom-20">Details</h3>

                        <div class="row">

                            <?php
                            $count = 0;
                            foreach ($record as $key => $value) {
                                if ($key == 'Resource Name' || $key == 'Description')
                                    continue;
                                if ($count < 5)
                                    $left[$key] = $value;
                                else
                                    $right[$key] = $value;
                                $count++;
                            }
                            ?>
                            <div class="col-sm-6">
                                <ul class="list-unstyled specifies-list">
                                    <?php foreach ($left as $key => $value) {
                                        echo '<li style="margin-left:15px;text-indent:-15px;margin-bottom:5px;"><i class="fa fa-caret-right"></i> <b><u>' . $key . ':</u></b> <span>' . $value . '</span></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <ul class="list-unstyled specifies-list">
                                    <?php foreach ($right as $key => $value) {
                                        echo '<li style="margin-left:15px;text-indent:-15px;margin-bottom:5px;"><i class="fa fa-caret-right"></i> <b><u>' . $key . ':</u></b> <span>' . $value . '</span></li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="responsive-image" style="text-align: center">
                            <img src="<?php echo $source->image ?>" class="responsive-image"/>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Description -->

            <!-- Reviews -->
            <div class="tab-pane fade" id="licenses">
                <div style="margin-bottom: 10px;"><b>License URL: </b> <a href="<?php echo $views_data['license-url'] ?>"><?php echo $views_data['license-url'] ?></a></div>
                <div style="margin-bottom: 10px;margin-left: 15px;text-indent: -15px"><b>License Information: </b><br/><?php echo $views_data['license'] ?></div>
            </div>
            <div class="tab-pane fade" id="views">
                <?php echo \helper\generateHTMLViewsTab($views, $community); ?>
            </div>
            <!-- End Reviews -->
        </div>
    </div>
</div>
