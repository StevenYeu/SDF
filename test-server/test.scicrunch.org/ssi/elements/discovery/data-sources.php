<?php
    $community = $data["community"];
    if(!$community) {
        return;
    }

    $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
    $data_sources = json_decode($data_sources_list, true);

    $sources = Array();
    foreach ($data_sources as $sourceID => $value) {
        ob_start();
        echo "<a href='".$community->fullURL()."/discovery/source/".$sourceID."/search'>".
              "<img src='".$value['icon']."' style='width: 40px'/> ".
              $value['plural_name']."</a>";
        $title = ob_get_clean();
        ob_start();
        echo $value['description'];
        $body = ob_get_clean();
        $sources[$title] = $body;
    }

    $source_list = Array();
    foreach ($sources as $title => $body) {
        $source_list[] = Array(
            "title" => $title,
            "body" => Array(
                Array("p" => $body),
            ),
        );
    }

    $about_title = "About";
    $about_body = Array(
        Array("p" => 'Our Resource Reports Services provide a streamlined search tool based on a completely new approach. It\'s the only integrated data set and analytics platform combining <a href="https://dknet.org/about/rrid">Research Resource Identifiers (RRIDs)</a>, text mining and data aggregation. Users can identify key resources while also tracking resource use and performance. Our Resource Reports offer:'),
        Array("ul" => Array(
            "A detailed overview of each resource.",
            "Citation metrics from the biomedical literature.",
            "Information about what resources have been used together.",
        )),
        Array("p" => "You'll gain insights about who is using particular resources and how the community rates those resources."),
        Array("p" => 'Resource is not listed above? <a href="mailto:info@dknet.org">Contact us <i class="fa fa-envelope-o"></i></a>'),
    );

    echo \helper\htmlElement("rin-style-page", Array(
        "title" => "Discovery Sources",
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Discovery Sources", "active" => true),
        ),
        "rows" => Array(
            Array(
                Array(
                    "body" => Array(
                        Array("html" => '<h4 class="text-center">A ' . $community->shortName . ' resource report offers a detailed overview of each resource, citation metrics from biomedical literature, information about what resources have been used together and more.</h4>'),
                    ),
                ),
            ),
            $source_list,
            Array(
                Array(
                    "title" => $about_title,
                    "body" => $about_body,
                ),
            ),
        ),
    ));

?>
