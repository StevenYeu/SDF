<?php

$community = $data["community"];

?>

<?php ob_start(); ?>
<div>
    <p>
        dkNET is partnering with the Signaling Pathways Project (SPP) knowledgebase, which has developed a powerful new meta-analysis platform that polls millions of DK mission-relevant biocurated 'omics data points to make high-confidence biological connections between cellular signaling pathway nodes and their downstream genomic targets.
        SPP simplifies powerful data mining and hypothesis generation strategies for the bench researcher, putting a 200 million data point-strong universe of 'omics data points at their fingertips.
        Examples include:
    </p>
    <ul>
        <li>
            Browse the datasets:
            <a target="_blank" href="https://beta.signalingpathways.org/datasets/index.jsf">
                https://beta.signalingpathways.org/datasets/index.jsf
            </a>
        </li>
        <li>
            Single gene queries
            <ul>
                <li>
                    <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?geneSearchType=gene&findMax=y&gene=CD44&foldChangeMin=2&foldChangeMax=30&significance=0.05&species=all&reportsBy=pathways&omicsCategory=tm&countMax=3000">
                        Transcriptomic evidence for receptors, enzymes, transcription factors that regulate expression of the CD44 gene
                    </a>
                </li>
                <li>
                    <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?geneSearchType=gene&findMax=y&gene=CD44&species=all&reportsBy=pathways&omicsCategory=cistromics&countMax=3000">
                        ChIP-Seq evidence for transcription factors, enzymes and other coregulators that bind to the CD44 promoter
                    </a>
                </li>
            </ul>
        </li>
        <li>
            GO term queries
            <ul>
                <li>
                    <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?geneSearchType=goTerm&findMax=y&gene=adipose+tissue+development&foldChangeMin=2&foldChangeMax=30&significance=0.05&signalingPathway=103&pathwayType=cclass&species=Human&reportsBy=pathways&omicsCategory=tm&countMax=3000">
                        Regulation of adipose tissue development genes by catalytic receptors
                    </a>
                    (transcriptomic)
                </li>
                <li>
                    <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?geneSearchType=goTerm&findMax=y&gene=inflammatory+response&foldChangeMin=2&foldChangeMax=30&significance=0.05&signalingPathway=3005&pathwayType=cclass&species=Human&reportsBy=pathways&omicsCategory=tm&countMax=3000">
                        Regulation of inflammatory response genes by acetyltransferases
                    </a>
                    (transcriptomic)
                </li>
                <li>
                    <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?geneSearchType=goTerm&findMax=y&gene=carbohydrate+biosynthetic+process&signalingPathway=3014&pathwayType=cclass&species=House+Mouse&reportsBy=pathways&omicsCategory=cistromics&countMax=3000">
                        Promoters of carbohydrate biosynthesis enzyme-encoding genes bound by E3 ubiquitin ligases
                    </a>
                    (ChIP-Seq)
                </li>
                <li>
                    <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?geneSearchType=goTerm&findMax=y&gene=gluconeogenesis&signalingPathway=4009&pathwayType=cclass&species=House+Mouse&reportsBy=pathways&omicsCategory=cistromics&countMax=3000">
                        Promoters of gluconeogenesis genes bound by BZIP transcription factors
                    </a>
                    (ChIP-Seq)
                </li>
            </ul>
        </li>
        <li>
            Consensomes
            <ul>
                <li>
                    Transcriptomic evidence for gene targets most frequently regulated by the <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?doi=10.1621%2F1ZnHm3YIVm.1&geneSearchType=consensome">insulin receptor</a>, <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?doi=10.1621%2Fjt8dwYSNLL.1&geneSearchType=consensome">leptin receptor</a>, or <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?doi=10.1621%2FeBg6TcnQue.1&geneSearchType=consensome">cyclin-dependent kinases</a>
                </li>
                <li>
                    Genes most frequently regulated across all transcriptomic experiments in <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?doi=10.1621%2FT8CUBZ4tkM.1&geneSearchType=consensome">adipose tissue</a> or <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?doi=10.1621%2FCe7F6yaObV.1&geneSearchType=consensome">liver</a>
                </li>
                <li>
                    ChIP-Seq evidence for promoters most frequently bound by members of the human <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?doi=10.1621%2FLPfAHbcMFr.1&geneSearchType=consensome">CEBP family</a> or the <a target="_blank" href="https://beta.signalingpathways.org/ominer/query.jsf?doi=10.1621%2Fjx6Qirj3WK.1&geneSearchType=consensome">glucocorticoid receptor</a>
                </li>
            </ul>
        </li>
    </ul>
    <p>
        Read more:
    </p>
    <p>
        <a target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/28442630">Becnel, LB et al. Discovering relationships between nuclear receptor signaling pathways, genes, and tissues in Transcriptomine. Sci Signal. 2017 Apr 25;10(476). pii: eaah6275. doi: 10.1126/scisignal.aah6275.</a>
    </p>
    <p>
        <a target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/27413121">Darlington, TF et al. Improving the discoverability, accessibility, and citability of omics datasets: a case report. J Am Med Inform Assoc. 2017 Mar 1;24(2):388-393. doi: 10.1093/jamia/ocw096</a>
    </p>
    <p>
        <a target="_blank" href="https://www.biorxiv.org/content/early/2018/08/27/401729.1">Ochsner S et al. The Signaling Pathways Project: an integrated 'omics knowledgebase for mammalian cellular signaling pathways. bioRxiv Preprint. 2018. doi: https://doi.org/10.1101/401729</a>
    </p>
</div>
<?php $html = ob_get_clean(); ?>

<?php

$rin_data = Array(
    "title" => "Signaling Pathways Project (SPP) - Coming soon!",
    "rows" => Array(
        Array(
            Array(
                "title" => "About",
                "body" => Array(
                    Array("html" => $html),
                ),
            ),
        ),
    ),
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Signaling Pathways Project", "active" => true),
    ),
);
echo \helper\htmlElement("rin-style-page", $rin_data);

?>
