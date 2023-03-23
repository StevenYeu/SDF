<?php

$community = $data["community"];
if(!$community) {
    return;
}

?>

<?php /* ################################################## TOOLS ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/source/nlx_144509-1/search">
    <img src="/images/rin-tool.png" style="width: 40px"/>
    Tools
</a>
<?php $tools_title = ob_get_clean(); ?>
<?php ob_start(); ?>
A curated repository of scientific resources, managed by SciCrunch, with a focus on biomedical resources, including tools, databases, materials, and more.
<?php $tools_body = ob_get_clean(); ?>

<?php /* ################################################## CELL LINES ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/source/SCR_013869-1/search">
    <img src="/images/rin-cell.png" style="width: 40px"/>
    Cell lines
</a>
<?php $cells_title = ob_get_clean(); ?>
<?php ob_start(); ?>
Cell line data collected by <a href="http://web.expasy.org/cellosaurus">Cellosaurus</a> from various sources.
<?php $cells_body = ob_get_clean(); ?>

<?php /* ################################################## ANTIBODIES ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/source/nif-0000-07730-1/search">
    <img src="/images/rin-antibody.png" style="width: 40px"/>
    Antibodies
</a>
<?php $antibodies_title = ob_get_clean(); ?>
<?php ob_start(); ?>
Antibodies from the <a href="http://antibodyregistry.org">Antibody Registry</a>, an authoritative source for antibody identifiers.
<?php $antibodies_body = ob_get_clean(); ?>

<?php /* ################################################## ORGANISMS ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/source/nlx_154697-1/search">
    <img src="/images/rin-organism.png" style="width: 40px"/>
    Organisms
</a>
<?php $organisms_title = ob_get_clean(); ?>
<?php ob_start(); ?>
A virtual database indexing available animal strains and mutants from various sources.
<?php $organisms_body = ob_get_clean(); ?>

<?php /* ################################################## PLASMIDS ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/source/nif-0000-11872-1/search">
    <img src="/images/rin-plasmid-2.png" style="width: 40px"/>
    Plasmids
</a>
<?php $plasmids_title = ob_get_clean(); ?>
<?php ob_start(); ?>
Plasmids from <a target="_blank" href="http://www.addgene.org/">Addgene</a>, a repository of published plasmids for use in research.
<?php $plasmids_body = ob_get_clean(); ?>

<?php /* ################################################## BIOSAMPLES ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/source/nlx_143929-1/search">
    <img src="/images/rin-biosample.png" style="width: 40px"/>
    BioSamples
</a>
<?php $biosamples_title = ob_get_clean(); ?>
<?php ob_start(); ?>
Biosamples registered with <a target="_blank" href="http://www.ncbi.nlm.nih.gov/biosample">NCBI BioSample</a> (initially from the IIDP, Integrated Islet Distribution Program), a database containing descriptions of biological source materials used in experimental assays.
<?php $biosamples_body = ob_get_clean(); ?>

<?php /* ################################################## PROTOCOLS ################################################## */ ?>
<?php ob_start(); ?>
<a href="<?php echo $community->fullURL() ?>/data/source/protocol/search">
    <img src="/images/rin-protocol.png" style="width: 40px"/>
    Protocols
</a>
<?php $protocols_title = ob_get_clean(); ?>
<?php ob_start(); ?>
Protocols from <a target="_blank" href="https://www.protocols.io/">Protocols.io</a>, an open access repository for research protocols and methods [utilizing Digital Object Identifier (DOIs)].
<?php $protocols_body = ob_get_clean(); ?>

<?php /* ################################################## ABOUT ################################################## */ ?>
<?php
$about_title = "About";
$about_body = Array(
    Array("p" => 'Our Resource Reports Services provide a streamlined search tool based on a completely new approach. It\'s the only integrated data set and analytics platform combining <a href="https://dknet.org/about/rrid">Research Resource Identifiers (RRIDs)</a>*, text mining and data aggregation. Users can identify key resources while also tracking resource use and performance. Our Resource Reports offer:'),
    Array("ul" => Array(
        "A detailed overview of each resource.",
        "Citation metrics from the biomedical literature.",
        "Information about what resources have been used together.",
    )),
    Array("p" => "You'll gain insights about who is using particular resources and how the community rates those resources."),
    Array("p" => 'Resource is not listed above? <a href="mailto:info@dknet.org">Contact us <i class="fa fa-envelope-o"></i></a>'),
    Array("p" => '*<span style="font-size:11px">Protocols Resource Reports utilize Digital Object Identifier (DOIs)</span>'),
);
?>

<?php echo \helper\htmlElement("rin-style-page", Array(
    "title" => "Resource Report Types",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Resource Report Types", "active" => true),
    ),
    "rows" => Array(
        Array(
            Array(
                "body" => Array(
                    Array("html" => '<h4 class="text-center">A ' . $community->shortName . ' resource report offers a detailed overview of each resource, citation metrics from biomedical literature, information about what resources have been used together and more.</h4>'),
                ),
            ),
        ),
        Array(
            Array(
                "title" => $tools_title,
                "body" => Array(
                    Array("p" => $tools_body),
                ),
            ),
            Array(
                "title" => $cells_title,
                "body" => Array(
                    Array("p" => $cells_body),
                ),
            ),
        ),
        Array(
            Array(
                "title" => $antibodies_title,
                "body" => Array(
                    Array("p" => $antibodies_body),
                ),
            ),
            Array(
                "title" => $organisms_title,
                "body" => Array(
                    Array("p" => $organisms_body),
                ),
            ),
        ),
        Array(
            Array(
                "title" => $plasmids_title,
                "body" => Array(
                    Array("p" => $plasmids_body),
                ),
            ),
            Array(
                "title" => $biosamples_title,
                "body" => Array(
                    Array("p" => $biosamples_body),
                ),
            ),
        ),
        Array(
            Array(
                "title" => $protocols_title,
                "body" => Array(
                    Array("p" => $protocols_body),
                ),
            ),
            Array(
                "title" => "",
                "body" => Array(
                    Array("p" => ""),
                ),
            ),
        ),
        Array(
            Array(
                "title" => $about_title,
                "body" => $about_body,
            ),
        ),
    ),
)); ?>
