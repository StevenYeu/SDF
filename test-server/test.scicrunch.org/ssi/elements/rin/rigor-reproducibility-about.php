<?php

$community = $data["community"];

function icon($fa, $color) {
    $icon = '
    <span class="fa-stack fa-md">
        <i class="fa fa-circle fa-stack-2x color-' . $color . '"></i>
        <i class="fa fa-' . $fa . ' fa-stack-1x fa-inverse color-white"></i>
    </span>';
    return $icon;
}

$data = Array(
    "title" => "Authentication Reports &amp; FAIR Data",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports &amp; FAIR Data", "active" => true),
    ),
    "rows" => Array(
        Array(
            Array(
                "title" => icon("file-text", "darkblue") . " Authentication Reports",
                "body" => Array(
                    Array("p" => "
                        dkNET's offering expanded services to make it easier to identify and validate research resources.
                        Researchers can generate an authentication report when submitting a proposal or publication.
                    "),
                    Array("ul" => Array(
                            "<a href=\"" . $community->fullURL() . "/rin/rrid-report\">Create your authentication report now!</a>",
                            "<a target=\"_blank\" href=\"" . $community->fullURL() . "/rin/comply-with-nih-mandates\">Learn about NIH mandates on resource identification and authentication</a>",
                        )
                    ),
                ),
            ),
            Array(
                "title" => icon("arrows-alt", "orange") . " FAIR Data Resources",
                "body" => Array(
                    Array("p" => "dkNET now offers information on how to manage data in compliance with the FAIR Data Principles."),
                    Array("ul" => Array(
                        "<a href=\"" . $community->fullURL() . "/rin/research-data-management\">Find information for best practices in managing research data</a>",
                        "<a href=\"https://dknet.org/rin/suggested-data-repositories\">List of community approved repositories",
                        "<a target=\"_blank\" href=\"https://goo.gl/forms/XiGYqaQadiAg95Hh2\">Request a data repository recommendation</a>",
                        "Tool for creating a FAIR Data Plan (Coming soon)",
                    )),
                ),
            ),
        ),
        Array(
            Array(
                "title" => "About",
                "body" => Array(
                    Array("p" => "
                        Today's biomedical researchers face tough <a target=\"_blank\" href=\"https://dknet.org/about/NIH-Policy-Rigor-Reproducibility\">new mandates for research rigor and reproducibility</a>.
                        Funders and journals regularly update their requirements for resource identification and authentication, transparency and openness, and sharing of data.
                        These mandates are part of a larger movement to ensure that data is FAIR – findable, accessible, interoperable and reusable (<a target=\"_blank\" href=\"https://www.nature.com/articles/sdata201618\">Wilkinson MD et al. Scientific Data 2016</a>). dkNET gives NIDDK-supported researchers the tools and services they need to comply with these mandates and manage digital assets more effectively.
                        You can use dkNET to:
                    "),
                    Array("ul" => Array(
                        "<a target=\"_blank\" href=\"" . $community->fullURL() . "/rin/comply-with-nih-mandates\">Comply with NIH mandates on resource identification and authentication.</a>",
                        "Manage and share FAIR research data.",
                        "Generate a resource authentication report when you submit a grant proposal.",
                    )),
                    Array("p" => "
                        Coming soon is a tool that will guide you as you generate data management plans, giving you a deeper understanding of what FAIR means to you.
                    "),
                ),
            ),
        ),
    ),
);

echo \helper\htmlElement("rin-style-page", $data);
?>
