<?php

$community = $data["community"];

$data = Array(
    "title" => "Scientific Rigor and Reproducibility",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports &amp; FAIR Data", "url" => $community->fullURL() . "/rin/rigor-reproducibility-about"),
        Array("text" => "Scientific Rigor and Reproducibility", "active" => true),
    ),
    "rows" => Array(
        Array(
            Array(
                "title" => "Scientific Rigor and Reproducibility",
                "body" => Array(
                    Array("p" => "
                        Scientific rigor and transparency is key to the successful application of knowledge toward improving health outcomes.
                        The NIH rigor and transparency policy was announced in the fall of 2015 and was applied to most research grants submitted on or after January 25, 2016.
                    "),
                    Array("p" => "
                        dkNET is committed to helping researchers understand and comply with new requirements and recommendations coming from the NIH and publishers aimed at improving the rigor, reproducibility and transparency of reported research.
                        dkNET can help researchers generate an authentication report for key biological resources such as antibodies and cell lines, using our resource identification system (RRIDs) and tools, in accordance with the NIH policy*.
                    "),
                ),
            )
        ),
        Array(
            ## change Scientific Rigor and Reproducibility questions array and links -- Vicky-2018-11-25
            Array(
                "title" => '<a target="_blank" href="https://dknet.org/about/NIH-Policy-Rigor-Reproducibility">Are you familiar with the NIH policy on Rigor and Reproducibility?</a>',
            ),
            /*Array(
                "title" => '<a target="_blank" href="https://dknet.org/about/rr3">Do you know that to comply with the NIH Policy on Rigor and Reproducibility you must address 4 focus area in your NIH grant application?</a>',
            ),*/
            Array(
                "title" => '<a target="_blank" href="https://dknet.org/about/authentication-report">How can dkNET assist in preparing the "authentication of key biological and/or chemical resources" report for your grant application?</a>',
            ),
        ),
        /*Array(
            Array(
                "title" => '<a target="_blank" href="https://dknet.org/about/rr4">Are you familiar with how to authenticate Key biological and/or Chemical resources?</a>',
            ),
            Array(
                "title" => '<a target="_blank" href="https://dknet.org/about/rr4">How can dkNET assist in preparing the "authentication of key biological and/or chemical resources" report for your grant application?</a>',
            ),
        ),*/
        Array(
            Array(
                "body" => Array(
                    Array("p" => \helper\htmlElement("rin/rrid-report-disclaimer"))
                )
            ),
        ),
    ),
);

echo \helper\htmlElement("rin-style-page", $data);
?>
