<?php

$community = $data["community"];

$data = Array(
    "title" => "Research Data Management Overview",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports &amp; FAIR Data", "url" => $community->fullURL() . "/rin/rigor-reproducibility-about"),
        Array("text" => "Research Data Management Overview", "active" => true),
    ),
    "rows" => Array(
        Array(
            Array(
                "title" => "Data Management, Sharing and Publishing",
                "body" => Array(
                    Array("p" => "
                        Effective data management is the key to producing <a target=\"_blank\" href=\"https://www.nature.com/articles/sdata201618\">FAIR data</a> in support of data sharing policies. Formally, data management refers to: \"...the development, execution and supervision of plans, policies, programs and practices that control, protect, deliver and enhance the value of data and information assets\" (<a target=\"_blank\" href=\"https://en.wikipedia.org/wiki/Data_management\">Wikipedia</a>). That may seem a bit daunting, but what making a data management plan really means is considering in advance how you will handle the valuable data you generate from your experiments. A good data management plan will include the following:
                    "),
                    Array("ul" => Array(
                        "Where you will store your data?",
                        "How will you organize multiple data files?",
                        "What formats will you use for your data?",
                        "What metadata will others need to understand, reuse and search for your data?",
                        "Are there community standards that should be adhered to?",
                        "How will your data be preserved for the life of the experiment and afterward?",
                    )),
                    Array("p" => "
                        Even if you never release the data to the public, properly managing your data ensures that they are FAIR – findable, accessible, interoperable and reusable – for you and your lab members. Careful data management ensures that you retain access to valuable digital assets even when graduate students and postdocs leave the laboratory. In addition, you are now able to publish your data as a separate journal article - usually providing detailed descriptions of your data which focus on helping others reuse data instead of testing hypotheses.
                    "),
                    Array("p" => "
                        With the release of the new <a target=\"_blank\" href=\"https://grants.nih.gov/grants/guide/notice-files/NOT-OD-21-013.html\">NIH data sharing policy</a>, starting January 25, 2023, you will be required to manage and share all data generated from NIH funding. Many <a target=\"_blank\" href=\"https://libraries.mit.edu/data-management/share/journal-requirements/\">journals</a> already require that data be shared at the time of publication or that access to data be specified in the article.
                    "),
                    Array("p" => "
                        dkNET can help you with data management and FAIR data.  We provide:
                    "),
                    Array("ul" => Array(
                        "Information and training for researchers through our <a target=\"_blank\" href=\"https://dknet.org/about/webinar\">webinar series</a>",
                        "The <a target=\"_blank\" href=\"https://dknet.org/about/Summer-Internship\">dkNET Summer of Data Student Internship Program</a> for students",
                        "A curated <a target=\"_blank\" href=\"https://dknet.org/rin/suggested-data-repositories\">list of data repositories</a> for publishing your data",
                        "Resources to help create effective data management plans",
                        "A “FAIR data wizard” to help managing and publishing your data (coming 2021)",
                    )),
                    Array("p" => "
                        <H2>Data management plans</H2>
                    "),
                    Array("p" => "
                        A <a target=\"_blank\" href=\"http://dmptool.org/general_guidance#introduction\">growing number of funders</a>, including both the NIH and the NSF, require that data management plans be included in grant proposals. Fortunately, many research libraries are building data management capacity. These libraries often have personnel and resources , – such as data management planning tools and institutional repositories – to help you comply with funder mandates and manage data effectively. Some examples include:
                    "),
                    Array("ul" => Array(
                        "<a href=\"https://dmptool.org/\">California Digital Library Data Management Planning Tool</a>: An online tool for creating data management plans.",
                        "<a href=\"http://libguides.lmu.edu/c.php?g=509009&p=3976802\">Loyola-Marymount University Research Data Management</a>: A nice set of questions about what should go into a data management plan.",
                        "<a href=\"https://libraries.mit.edu/data-management/\">MIT Libraries Data Management Resources</a>: General information on data management and on the tools and services available from MIT Libraries.",
                        "<a href=\"https://mantra.edina.ac.uk/\">Mantra</a>:  Online training course for data management hosted by the University of Edinburgh",
                    )),
                    Array("p" => "
                        Check with your institutional library to find out what resources are available to you. The <a target=\"_blank\" href=\"http://v2.sherpa.ac.uk/opendoar/\">Directory of Open Access Repositories</a> also has a searchable database of institutional repositories. Some commercial providers, such as <a target=\"_blank\" href=\"https://data.mendeley.com/\">Mendeley</a> and <a target=\"_blank\" href=\"https://www.digital-science.com/researchers/keep-data-safe/\">Digital Science</a>, also provide services for data management.
                    "),
                ),
            ),
        ),
    ),
);

echo \helper\htmlElement("rin-style-page", $data);
?>
