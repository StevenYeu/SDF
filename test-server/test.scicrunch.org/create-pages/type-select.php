<?php

$logged_in = isset($_SESSION["user"]);

$holder = new Resource_Type();
$results = $holder->getByCommunity(0);
$results_normal = Array();
$results_out = Array();
foreach($results as $data){
    if($data->url) array_push($results_out, $data);
    else array_push($results_normal, $data);
}

/******************************************************************************************************************************************************************************************************/

function typeSection($data){
    if($data->url) {
        $link_url = $data->url;
        $target = 'target="blank"';
    } else {
        $link_url = "/create/resource?form=" . $data->name;
        $target = '';
    }

    ob_start(); ?>

    <tr values="<?php echo strtolower($data->name) ?>">
        <td>
            <h3><a <?php echo $target ?> href="<?php echo $link_url ?>"><?php echo $data->name ?></a></h3>
            <p><?php echo $data->description ?></p>
        </td>
        <td>
            <a <?php echo $target ?> href="<?php echo $link_url ?>"><i class="icon-custom icon-sm rounded-x icon-line icon-bg-green fa fa-chevron-right"></i></a>
        </td>
    </tr>

    <?php
    $html = ob_get_clean();
    return $html;
}

function suggestionHTML() {
    ob_start();
    ?>
    <tr>
        <td>
            <h3><a href="/create/resourcesuggestion">Suggest a resource (resources include software, organizations, databases, etc).  Organisms and antibodies should not be submitted to the resource registry.</a></h3>
            <p>Just provide the minimal information for a resource and we'll fill in the rest.  Suggesting a resource will not generate an RRID until a SciCrunch curator approves it.</p>
        </td>
        <td>
            <a href="/create/resourcesuggestion"><i class="icon-custom icon-sm rounded-x icon-line icon-bg-green fa fa-chevron-right"></i></a>
        </td>
    </tr>
    <?php
    $html = ob_get_clean();
    return $html;
}


/******************************************************************************************************************************************************************************************************/
?>
<div class="breadcrumbs-v3">
    <div class="container">
        <ul class="pull-left breadcrumb">
            <li><a href="/">Home</a></li>
            <li class="active">Resource Type Select</li>
        </ul>
        <h1 class="pull-right">Add a Resource</h1>
    </div>
</div>


<div class="container s-results margin-bottom-50" style="margin-top:50px">
    <div class="row">
        <div class="col-md-3 hidden-xs related-search">
            <div class="row">
                <div class="col-md-12 col-sm-4">
                    <p>
                        <a href="/browse/resourcedashboard">SciCrunch Registry</a> is
                        a dynamic database of research resources (databases, data sets, software tools, materials and services)
                        of interest to and produced by biomedical researchers.
                    </p>
                    <p>
                        Each Research Resource receives a unique ID that allows it to be tracked in the literature and linked
                        to useful information. If you would like to add your resource or recommend a resource for inclusion in the
                        Registry, please follow the steps on this page.
                    </p>
                    <p>
                        Please read our <a href="https://scicrunch.org/page/tutorials/422">tutorial</a> describing the different types of resources we accept to help decide which type your resource belongs to.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-9 <?php if($vars['editmode']) echo "editmode" ?>">
            <div class="table-search-v2 margin-bottom-20">
                <h2>Step 1. Type the name of your resource here</h2>
                <form method="get" class="resource-find-form">
                    <div class="input-group margin-bottom-20">
                        <input type="text" class="form-control type-find" placeholder="Check if your resource already exists"
                               value="">

                                    <span class="input-group-btn">
                                        <button class="btn-u" type="submit">Go</button>
                                    </span>
                    </div>
                </form>
                <div class="resource-load"></div>
                <hr/>
                <h2>Step 2. Enter the resource into the SciCrunch Registry</h2>
                <div class="table-responsive">
                    <table class="table table-hover type-table">
                        <thead>
                            <tr class="first">
                                <th>SciCrunch registry</th>
                                <th style="width:50px">Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if($logged_in) {
                                foreach ($results_normal as $data) {
                                    echo typeSection($data);
                                }
                            } else {
                                echo suggestionHTML();
                            }
                            ?>
                            <?php if(count($results_out)): ?>
                                <tr><th><h2>Outside registries</h2></th><th></th></tr>
                                <?php foreach($results_out as $data): ?>
                                    <?php echo typeSection($data) ?>
                                <?php endforeach ?>
                            <?php endif ?>
                            <?php if($logged_in): ?>
                            <tr><th><h2>Other resources</h2></th><th></th></tr>
                            <tr>
                                <td>
                                    <h3><a href="/create/resource?form=resource">Other</a></h3>
                                </td>
                                <td>
                                    <a href="/create/resource?form=resource"><i class="icon-custom icon-sm rounded-x icon-line icon-bg-green fa fa-chevron-right"></i></a>
                                </td>

                            </tr>
                            <?php echo suggestionHTML() ?>
                            <?php endif ?>
                        </tbody>
                    </table>

                </div>
            </div>

            <?php if ($vars['editmode']) {
                echo '<div class="body-overlay"><h3>Community Resource Forms</h3>';
                echo '<div class="pull-right">';
                echo '<button class="btn-u simple-toggle" modal=".type-existing-form"><i class="fa fa-plus"></i><span class="button-text"> Add Existing</span></button><a href="javascript:void(0)" class="btn-u btn-u-default simple-toggle" modal=".type-add-form"><i class="fa fa-plus"></i><span class="button-text"> Add New</span></a></div>';
                echo '</div>';
            } ?>
        </div>
    </div>
</div>
<?php if($vars['editmode']){?>
    <div class="back-hide large-modal type-existing-form no-padding">
        <div class="close dark less-right">X</div>
        <form method="post"
              action="/forms/resource-forms/type-add-exist.php?cid=<?php echo $community->id ?>"
              id="header-component-form" class="sky-form" style="margin-bottom: 40px;"
              enctype="multipart/form-data">
            <?php
            if ($community->id != 0) {
                $holder = new Form_Relationship();
                $relationships = $holder->getByCommunity($community->id, 'resource');
                foreach($relationships as $rel){
                    $typesIDs[] = $rel->rid;
                }

                $holder = new Resource_Type();
                $check = $holder->getAll();

                foreach($check as $type){
                    if(!in_array($type->id,$typesIDs))
                        $types2[] = $type;
                    $types[] = $type;
                }

                function cmp2($a, $b)
                {
                    if ($a->name == $b->name) {
                        return 0;
                    }
                    return ($a->name < $b->name) ? -1 : 1;
                }

                usort($types2, "cmp2");
                usort($types, "cmp2");
            } else {
                $holder = new Resource_Type();
                $types2 = $holder->getAllNotMade(0);
                $types = $holder->getAll();
            }

            ?>
            <header>Add Existing Resource Type</header>
            <fieldset>
                <section>
                    <label class="label">Existing Types</label>
                    <label class="select">
                        <i class="icon-append fa fa-question-circle"></i>
                        <select name="type">
                            <?php
                            foreach ($types2 as $type) {
                                echo '<option value="' . $type->id . '">' . $type->name . '</option>';
                            }
                            ?>
                        </select>
                    </label>
                </section>
            </fieldset>

            <footer>
                <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
            </footer>
        </form>
    </div>
    <div class="back-hide large-modal type-add-form no-padding">
        <div class="close dark less-right">X</div>
        <form method="post" action="/forms/resource-forms/type-add.php?cid=<?php echo $community->id ?>"
              id="header-component-form" class="sky-form" enctype="multipart/form-data">
            <header>Add New Resource Type</header>
            <fieldset>
                <section>
                    <label class="label">Resource Type Label</label>
                    <label class="input">
                        <i class="icon-append fa fa-question-circle"></i>
                        <input type="text" name="name" placeholder="Focus to view the tooltip">
                        <b class="tooltip tooltip-top-right">The Type name</b>
                    </label>
                </section>
                <section>
                    <label class="label">Description</label>
                    <label class="textarea">
                        <i class="icon-append fa fa-question-circle"></i>
                        <textarea rows="3" placeholder="Focus to view the tooltip"
                                  name="description"></textarea>
                        <b class="tooltip tooltip-top-right">A description of this resource type</b>
                    </label>
                </section>
                <section>
                    <label class="label">Parent Resource Type</label>
                    <label class="select">
                        <i class="icon-append fa fa-question-circle"></i>
                        <select name="parent">
                            <option value="0">No Parent
                                Container Type
                            </option>
                            <?php
                            foreach ($types as $data) {
                                echo '<option value="' . $data->id . '">' . $data->name . '</option>';
                            }
                            ?>
                        </select>
                        <b class="tooltip tooltip-top-right">If there is a parent resource type to select
                            before submitting.</b>
                    </label>
                </section>
                <section>
                    <label class="label">Parent Resource Type Facet</label>
                    <label class="input">
                        <i class="icon-append fa fa-question-circle"></i>
                        <input type="text" name="facet" placeholder="Focus to view the tooltip"
                            >
                        <b class="tooltip tooltip-top-right">If there is a parent resource to select, what
                            should the facet for filtering the registry to select it be?</b>
                    </label>
                </section>
                <section>
                    <label class="label">Redirect URL</label>
                    <label class="input">
                        <i class="icon-append fa fa-question-circle"></i>
                        <input type="text" name="url" placeholder="Focus to view the tooltip"
                            >
                        <b class="tooltip tooltip-top-right">Only filled in if the type should go to an external
                            form</b>
                    </label>
                </section>
            </fieldset>

            <footer>
                <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
            </footer>
        </form>
    </div>
<?php } ?>
