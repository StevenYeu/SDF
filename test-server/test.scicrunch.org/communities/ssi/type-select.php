<?php
$logged_in = isset($_SESSION["user"]);

$holder = new Form_Relationship();
$relationships = $holder->getByCommunity($community->id, 'resource');
foreach ($relationships as $relationship) {
    $type = new Resource_Type();
    $type->getByID($relationship->rid);
    $results[] = $type;
    $relArray[$type->id] = $relationship->id;
}
function cmp($a, $b) {
    if ($a->name == $b->name) {
        return 0;
    }
    return ($a->name < $b->name) ? -1 : 1;
}

usort($results, "cmp");

function suggestionHTML($community) {
    ob_start();
    ?>
    <tr>
        <td>
            <h3><a href="/<?php echo $community->portalName ?>/about/resource?resource_suggestion">Suggest a resource (resources include software, organizations, databases, etc).  Organisms and antibodies should not be submitted to the resource registry.</a></h3>
            <p>Just provide the minimal information for a resource and we'll fill in the rest.  Suggesting a resource will not generate an RRID until a SciCrunch curator approves it.</p>
        </td>
        <td>
            <a href="/<?php echo $community->portalName ?>/about/resource?resource_suggestion"><i class="icon-custom icon-sm rounded-x icon-line icon-bg-green fa fa-chevron-right"></i></a>
        </td>
    </tr>
    <?php
    $html = ob_get_clean();
    return $html;
}

?>
<div class="container s-results margin-bottom-50" style="margin-top:50px">
    <div class="row">
        <div class="col-md-3 hidden-xs related-search" style="border-right:1px solid #eee">
            <div class="row">
                <div class="col-md-12 col-sm-4">
                    <?php if($community->portalName == "legacy-niddk"): ?>
                        <h4>Add a Resource</h4>
                        <p>
                            dkNET assists you in obtaining Research Resource Identifiers (RRID),
                            a unique ID for research resources that allow them to be tracked in the literature and linked to useful information.
                            To learn more about RRIDs, please go to <a href="https://scicrunch.org/legacy-niddk/about/rrid">https://scicrunch.org/legacy-niddk/about/rrid</a>.
                        </p>
                        <p>
                            RRIDs are issued by an authoritative database specialized for a particular resource type.
                            If you need to obtain an RRID for a resource that is not in the database,
                            please click on the link for the appropriate resource and you will be given instructions.
                        </p>
                    <?php elseif($community->portalName == "resources"): ?>
                        <h4>What is a Resource?</h4>
                        <p>
                            RRID contributes to the SciCrunch Registry, the <a target="_blank" href="http://antibodyregistry.org">antibodyregistry.org</a>, Cellosaurus database and a large number of model organism databases.
                        </p>
                        <p>
                            To submit your information for a new research resource, you must first select the type of resource.
                            You will most likely be taken to a site outside of the RRID portal because the RRID is based on identifiers that are available from your favorite community sources.
                        </p>
                        <p>
                            If you are having trouble, or someone is not responding to your submission please <a href="mailto:rii-help@scicrunch.org">contact us</a> and we will try to help.
                            In most cases, we can either submit the resource for you, or contact an administrator that will take you through the process.
			</p>
		    <?php elseif ($community->id == 56):  /* Manu added this block */?>
                        <h4>What is a Resource?</h4>
                        <p>
                            A resource could be a software or data product that is being developed as a result of funding from NSF or other agencies. The resource could be developed using other software tools and libraries.
                        </p>

                    <?php else: ?>
                        <h4>What is a Resource?</h4>
                        <p>
                            <?php echo $community->shortName ?> contributes to the
                            <a href="/<?php echo $community->portalName?>/data/source/nlx_144509-1/search">SciCrunch Registry</a>,
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
                    <?php endif ?>
                </div>
            </div>
        </div>

        <div class="col-md-9 <?php if($vars['editmode']) echo "editmode" ?>">
            <h2>Choose a resource type</h2>
            <div class="table-search-v2 margin-bottom-20">
                <div class="table-responsive">
                    <table class="table table-hover type-table">
                        <thead>
                        <tr class="first">
                            <th>Resource Type</th>
                            <th style="width:50px">Select</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($results as $data) {
                            if(!$data->url && !$logged_in && $community->portalName != "legacy-niddk") continue;    // only show outside resources when not logged in
                            echo '<tr values="' . strtolower($data->name) . '"><td>';
                            if ($data->url) {
                                echo '<h3><a target="_blank" href="' . $data->url . '">' . $data->name . '</a></h3>';
                                $data_description = $data->description;
                            } else {
                                if($data->id == 1 && $community->portalName == "legacy-niddk") {
                                    $data_name = "Digital Resource";
                                    $data_description = "Provide the minimal information for a digital resource at SciCrunch Registry.";
                                } else {
                                    $data_name = $data->name;
                                    $data_description = $data->description;
                                }
                                echo '<h3><a href="/' . $community->portalName . '/about/resource?form=' . $data->name . '&rel=' . $relArray[$data->id] . '">' . $data_name . '</a></h3>';
                            }
                            echo '<p>' . $data_description . '</p>';
                            echo '</td>';
                            if ($data->url)
                                echo '<td><a target="_blank" href="' . $data->url . '"><i class="icon-custom icon-sm rounded-x icon-line icon-bg-green fa fa-chevron-right"></i></a></td>';
                            else
                                echo '<td><a href="/' . $community->portalName . '/about/resource?form=' . $data->name . '&rel=' . $relArray[$data->id] . '"><i class="icon-custom icon-sm rounded-x icon-line icon-bg-green fa fa-chevron-right"></i></a></td>';
                            echo '</tr>';
                        }
                        ?>
                        <?php if($community->portalName != "legacy-niddk"): ?>
                            <?php if(!$logged_in): ?>
                                <?php echo suggestionHTML($community) ?>
                            <?php endif ?>
                        <?php else: ?>
                            <tr class="last">
                                <td>
                                    <h3>Bulk Resources</h3>
                                    <p>If you need to add bulk resources, please contact our curator directly (scicrunchregistry@gmail.com).</p>
                                </td>
                                <td></td>
                            </tr>
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
