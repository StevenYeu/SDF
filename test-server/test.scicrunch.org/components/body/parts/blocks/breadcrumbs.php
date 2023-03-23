<style>
    <?php if($component->color3){?>
    .breadcrumbs-v3 {
        background: <?php echo '#'. $component->color3?>;
    }

    <?php } elseif($component->image){ ?>
    .breadcrumbs-v3 {
        background: url('/upload/community-components/<?php echo $component->image?>') 100% 100% no-repeat;
    }

    <?php } ?>
    <?php if($component->color1){?>
    .breadcrumbs-v3 h1, .breadcrumbs-v3 .breadcrumb li a {
        color: <?php echo '#'. $component->color1?>;
    }

    <?php } ?>
    <?php if($component->color2){?>
    .breadcrumbs-v3 .breadcrumb li a:hover, .breadcrumbs-v3 .breadcrumb li.active {
        color: <?php echo '#'. $component->color2?>;
    }

    <?php } ?>
</style>

<div class="breadcrumbs-v3 <?php if ($vars['editmode']) echo 'editmode' ?>" style="padding:0px">
    <div class="container">
        <?php
        if(isset($vars['stripped'])&&$vars['stripped']=='true')
            $para = '/stripped';
        else
            $para = '';

        if ($vars['type'] == 'join') {
            echo '<ul class="pull-left breadcrumb">';
            echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
            echo '<li class="active">Register</li>';
            echo '</ul>';
            echo '<h1 class="pull-right">Register</h1>';
        } elseif ($vars['type'] == 'about') {
            if ($vars['title'] == 'sources') {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                if ($vars['id']) {
                    echo '<li><a href="/'.$community->portalName.$para.'/about/sources">Our Sources</a></li>';
                    echo '<li class="active">'.$sources[$vars['id']]->getTitle().'</li>';
                } else {
                    echo '<li class="active">Our Sources</li>';
                }
                echo '</ul>';
                echo '<h1 class="pull-right">'.$community->shortName.' Sources</h1>';
            } elseif($vars['title']=='registry'){
                if($vars['mode'] && $vars['mode']=='edit'){
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li><a href="/' . $community->portalName.$para . '/about/registry">'.$community->shortName.' Registry</a></li>';
                    echo '<li><a href="/' . $community->portalName.$para . '/about/registry/'.$vars['id'].'">View Resource</a></li>';
                    echo '<li class="active">Edit Resource</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">Edit Resource</h1>';
                } elseif($vars['id']){
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li><a href="/' . $community->portalName.$para . '/about/registry">'.$community->shortName.' Registry</a></li>';
                    echo '<li class="active">View Resource</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">View Resource</h1>';
                } else {
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li class="active">'.$community->shortName.' Registry</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">Search through '.$community->shortName.' Resources</h1>';
                }
            } elseif ($vars['title'] == 'resource' && isset($vars['form'])) {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li><a href="/' . $community->portalName.$para . '/about/resource">Resource Type Select</a></li>';
                echo '<li class="active">Resource Submission</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Add a Resource</h1>';
            } elseif ($vars['title'] == 'resource' && isset($vars['submit'])) {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li><a href="/' . $community->portalName.$para . '/about/resource">Resource Type Select</a></li>';
                echo '<li class="active">Submission Successful</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Submission Successful</h1>';
            } elseif ($vars['title'] == 'resourcesedit') {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li><a href="/' . $community->portalName.$para . '/account/resources">Resources</a></li>';
                echo '<li class="active">Resource edit</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Resource edit</h1>';
            } elseif ($vars['title'] == 'term') {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li><a href="/'.$community->portalName.'/interlex/dashboard">Term Dashboard </a></li>';
                echo '<li class="active">ILX:' . preg_replace("/ilx_/", "", $vars['article']) . '</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Term View</h1>';
            } elseif ($vars['title'] == 'resourcementionupload') {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li class="active">Resource mention upload</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Resource mention upload</h1>';
            } elseif ($vars['title'] == 'resource') {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li class="active">Select a Resource Type</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Add a Resource</h1>';
            } elseif ($vars['title'] == 'search') {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li class="active">Browse Content</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Browse Content</h1>';
            } elseif ($vars['title'] == 'faq') {
                if ($faq) {
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li><a href="/'.$community->portalName.$para.'/about/faqs">FAQs Home</a></li>';
                    echo '<li class="active">'.$theTitle.'</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">'.$theTitle.'</h1>';
                } else {
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li class="active">FAQs Page</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">FAQs Page</h1>';
                }
            } elseif ($vars["title"] == "keyaction") {
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li class="active">Update</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">Update</h1>';
            } else {
                if ($vars['id']) {
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li><a href="/' . $community->portalName.$para . '/about/' . $thisComp->text2 . '">' . $thisComp->text1 . '</a></li>';
                    echo '<li class="active">' . $data->title . '</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">' . $thisComp->text1 . '</h1>';
                } else {
                    echo '<ul class="pull-left breadcrumb">';
                    echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                    echo '<li class="active">' . $thisComp->text1 . '</li>';
                    echo '</ul>';
                    echo '<h1 class="pull-right">' . $thisComp->text1 . '</h1>';
                }
            }
        } elseif($vars['type'] === 'datasets') {
        ?>
            <h1 class="pull-left"><?php echo $community->shortName ?> Datasets</h1>
            <ul class="pull-right breadcrumb">
                <li><a href="/<?php echo $community->portalName . $para ?>">Home</a></li>
                <li class="active">Datasets</li>
            </ul>
        <?php
        } elseif($vars['type'] === 'dataset') {
        ?>
            <h1 class="pull-left">Dataset</h1>
            <ul class="pull-right breadcrumb">
                <li><a href="/<?php echo $community->portalName . $para ?>">Home</a></li>
                <li><a href="/<?php echo $community->portalName . $para ?>/datasets">Datasets</a></li>
                <li class="active">Dataset</li>
            </ul>
        <?php
        } elseif($vars['type'] === 'rrid-report') {
        ?>
            <h1 class="pull-left">Authentication Report</h1>
            <ul class="pull-right breadcrumb">
                <li><a href="/<?php echo $community->portalName . $para ?>">Home</a></li>
                <li class="active">Authentication Report</li>
            </ul>
        <?php
        } elseif(isset($vars['view']) && (isset($vars['uuid']) || isset($vars['rrid']))){
            $holder = new Sources();
            $sources = $holder->getAllSources();
            echo '<ul class="pull-left breadcrumb">';
            echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';

            if ($vars['category']!='data'&&$vars['category']!='literature'&&$vars['category']!='Any') {
                $newVars = $vars;
                $newVars['nif'] = false;
                $newVars['subcategory'] = false;
                $newVars['view'] = false;
                $newVars['uuid'] = false;
                $newVars['category'] = 'Any';
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . Community::getSearchNameCommResources($community) . '</a></li>';
            }
            if ($vars['category']) {
                $newVars = $vars;
                $newVars['nif'] = false;
                $newVars['subcategory'] = false;
                $newVars['view'] = false;
                $newVars['uuid'] = false;
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . $vars['category'] . '</a></li>';
            }
            if ($vars['subcategory']) {
                $newVars = $vars;
                $newVars['nif'] = false;
                $newVars['view'] = false;
                $newVars['uuid'] = false;
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . $vars['subcategory'] . '</a></li>';
            }
            if($vars['nif']){
                $newVars = $vars;
                $newVars['view'] = false;
                $newVars['uuid'] = false;
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . $sources[$vars['nif']]->getTitle() . '</a></li>';
            }

            echo '<li class="active">Resource Details</li>';

            echo '</ul>';
            echo '<h1 class="pull-right">Resource Details</h1>';
        } elseif ($vars['nif']) {

            $holder = new Sources();
            $sources = $holder->getAllSources();
            echo '<ul class="pull-left breadcrumb">';
            echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';

            if ($vars['category']!='data'&&$vars['category']!='literature'&&$vars['category']!='Any') {
                $newVars = $vars;
                $newVars['nif'] = false;
                $newVars['subcategory'] = false;
                $newVars['view'] = false;
                $newVars['uuid'] = false;
                $newVars['category'] = 'Any';
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . Community::getSearchNameCommResources($community) . '</a></li>';
            }

            if ($vars['category']) {
                $newVars = $vars;
                $newVars['nif'] = false;
                $newVars['subcategory'] = false;
                if(!is_null($referer_category_filter)) $newVars["category-filter"] = $referer_category_filter;
                if($vars['category']=='data'){
                    if($community->id == 0 || $community->id == 72) echo '<li><a href="' . $search->generateURL($newVars) . '">Data</a></li>';
                    else echo '<li><a href="' . $search->generateURL($newVars) . '">' . Community::getSearchNameMoreResources($community) . '</a></li>';
                }
                else{
                    echo '<li><a href="' . $search->generateURL($newVars) . '">' . $vars['category'] . '</a></li>';
                }
            }

            if ($vars['subcategory']) {
                $newVars = $vars;
                $newVars['nif'] = false;
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . $vars['subcategory'] . '</a></li>';
            }

            echo '<li class="active">' . $sources[$vars['nif']]->getTitle() . '</li>';
            echo '</ul>';
            echo '<h1 class="pull-right">' . $sources[$vars['nif']]->getTitle() . '</h1>';
        } elseif ($vars['subcategory']) {
            echo '<ul class="pull-left breadcrumb">';
            echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';

            if ($vars['category']!='data'&&$vars['category']!='literature'&&$vars['category']!='Any') {
                $newVars = $vars;
                $newVars['nif'] = false;
                $newVars['subcategory'] = false;
                $newVars['view'] = false;
                $newVars['uuid'] = false;
                $newVars['category'] = 'Any';
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . Community::getSearchNameCommResources($community) . '</a></li>';
            }

            if ($vars['category']) {
                $newVars = $vars;
                $newVars['subcategory'] = false;
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . $vars['category'] . '</a></li>';
            }

            echo '<li class="active">' . $vars['subcategory'] . '</li>';
            echo '</ul>';
            echo '<h1 class="pull-right">' . $vars['subcategory'] . '</h1>';
        } elseif ($vars['category'] == 'data') {
            echo '<ul class="pull-left breadcrumb">';
            echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
            if($community->id == 0 || $community->id == 72) echo '<li class="active">Data</li>';
            else echo '<li class="active">' . Community::getSearchNameMoreResources($community) . '</li>';
            echo '</ul>';
            if($community->id == 0 || $community->id == 72) echo '<h1 class="pull-right">Data</h1>';
            else echo '<h1 class="pull-right">' . Community::getSearchNameMoreResources($community) . '</h1>';
        } elseif ($vars['category'] == 'literature') {
            if($vars["pmid"]) {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li><a href="' . Community::fullURLStatic($community) . '/literature/search">' . Community::getSearchNameLiterature($community) . '</a></li>';
                echo '<li class="active">Publication</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">Publication</h1>';
            } else {
                echo '<ul class="pull-left breadcrumb">';
                echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';
                echo '<li class="active">' . Community::getSearchNameLiterature($community) . '</li>';
                echo '</ul>';
                echo '<h1 class="pull-right">' . Community::getSearchNameLiterature($community) . '</h1>';
            }
        } elseif ($vars['category']) {
            $category_name = $vars['category'] == "Any" ? "Community Resources" : $vars["category"];
            echo '<ul class="pull-left breadcrumb">';
            echo '<li><a href="/' . $community->portalName.$para . '">Home</a></li>';


            if ($vars['category']!='data'&&$vars['category']!='literature'&&$vars['category']!='Any') {
                $newVars = $vars;
                $newVars['nif'] = false;
                $newVars['subcategory'] = false;
                $newVars['view'] = false;
                $newVars['uuid'] = false;
                $newVars['category'] = 'Any';
                echo '<li><a href="' . $search->generateURL($newVars) . '">' . Community::getSearchNameCommResources($community) . '</a></li>';
            }

            echo '<li class="active">' . $category_name . '</li>';
            echo '</ul>';
            echo '<h1 class="pull-right">' . $category_name . '</h1>';
        }

        ?>
    </div>

    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3 style="margin-left:10px;margin-top:10px">' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        echo '<button class="btn-u btn-u-default edit-body-btn" componentType="other" componentID="' . $component->id . '"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button></div>';
        echo '</div>';
    } ?>
</div>
