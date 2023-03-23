<?php

$query = str_replace("RRID:", "", $query);

$holder = new Sources();
$allSources = $holder->getAllSources();
$search = new Search();
$per_page = 4;
$urls = array(
    Connection::environment() . '/v1/federation/data/nif-0000-07730-1.xml?q=' . rawurlencode($query) . '&exportType=all&count=' . $per_page . '&offset=' . (($page - 1) * $per_page),
    Connection::environment() . '/v1/federation/data/nlx_144509-1.xml?q=' . rawurlencode($query) . '&exportType=all&count=' . $per_page . '&offset=' . (($page - 1) * $per_page),
    Connection::environment() . '/v1/federation/data/nlx_154697-1.xml?q=' . rawurlencode($query) . '&exportType=all&count=' . $per_page . '&offset=' . (($page - 1) * $per_page),
    Connection::environment() . '/v1/federation/data/SCR_013869-1.xml?q=' . rawurlencode($query) . '&exportType=all&count=' . $per_page . '&offset=' . (($page - 1) * $per_page),
    Connection::environment() . '/v1/federation/data/nif-0000-03179-1.xml?q=' . rawurlencode($query) . '&exportType=all&count=' . $per_page . '&offset=' . (($page - 1) * $per_page),
    ## add BioSamples -- Vicky-2018-11-2
    Connection::environment() . '/v1/federation/data/nlx_143929-1.xml?q=' . rawurlencode($query) . '&exportType=all&count=' . $per_page . '&offset=' . (($page - 1) * $per_page),
    ## add AddGene -- Vicky-2018-11-9
    Connection::environment() . '/v1/federation/data/nif-0000-11872-1.xml?q=' . rawurlencode($query) . '&exportType=all&count=' . $per_page . '&offset=' . (($page - 1) * $per_page),
);

$snippets = array(
    '<xml>
        <title>${ab_name}, ${vendor}</title>
        <description>
            Cite this &lt;b&gt;${vendor} Cat# ${catalog_num} Lot# RRID:AB_${ab_id}&lt;/b&gt;&lt;br/&gt;
            &lt;b&gt;Vendor Catalog #:&lt;/b&gt; ${Cat Num} &lt;br&gt;
            &lt;b&gt;AB Registry ID :&lt;/b&gt; AB_${ab_id} &lt;br&gt;
            &lt;b&gt;Host Organism:&lt;/b&gt;  ${Host Organism}&lt;br&gt;
            &lt;b&gt;Clonality:&lt;/b&gt; ${Clonality}&lt;br&gt;
            &lt;b&gt;Target(s):&lt;/b&gt; ${Target Antigen}
        </description>
        <rrid>RRID:AB_${ab_id}</rrid>
    </xml>',
    '<xml>
        <title>${resource_name}</title>
        <description>
            Cite this &lt;b&gt;${proper_citation}&lt;/b&gt;&lt;br/&gt;
            &lt;b&gt;Description:&lt;/b&gt; ${description}
        </description>
        <rrid>RRID:${see_full_record}</rrid>
    </xml>',
    '<xml>
        <title>${name} ${species}</title>
        <description>
            Cite this &lt;b&gt;${database} Cat# ${catalog_id}, ${proper_citation}&lt;/b&gt;&lt;br&gt;
            &lt;b&gt;Source Database:&lt;/b&gt; ${database}, catalog #
            &lt;a class="external" target="_blank" href="${url_p}"&gt; ${catalog_id}&lt;/a&gt; &lt;br&gt;
            &lt;b&gt;Genetic Background:&lt;/b&gt; ${background}&lt;br&gt;
            &lt;b&gt;Affected Genes:&lt;/b&gt; ${gene}&lt;br&gt;
            &lt;b&gt;Variant Alleles:&lt;/b&gt; ${genomic_alteration}&lt;br&gt;
        </description>
        <rrid>${Proper Citation}</rrid>
    </xml>',
    '<xml>
        <title>${name} cell line, ${vendor}</title>
        <description>
            Cite this &lt;b&gt;${proper_citation}&lt;/b&gt;&lt;br&gt;
            &lt;b&gt;Organism:&lt;/b&gt; ${species}&lt;br&gt;
            &lt;b&gt;Disease:&lt;/b&gt; ${diseases}&lt;br&gt;
            &lt;b&gt;Category:&lt;/b&gt; ${category}&lt;br&gt;
            &lt;b&gt;Comment:&lt;/b&gt; ${comments}&lt;br&gt;
        </description>
        <rrid>RRID:${id}</rrid>
    </xml>',
    '<xml>
        <title>${Scientific Name}</title>
        <description>
            Cite this &lt;b&gt;${scientific_name}, ${tax_id}&lt;/b&gt;&lt;br&gt;
            &lt;b&gt;Taxonomic Rank:&lt;/b&gt; ${Taxonomic Rank}&lt;br/&gt;
            &lt;b&gt;Division Name:&lt;/b&gt; ${Division Name}&lt;br/&gt;
            &lt;b&gt;Synonyms:&lt;/b&gt; ${Synonyms}&lt;br/&gt;
        </description>
        <rrid>RRID:${tax_id}</rrid>
    </xml>',
    ## add BioSamples -- Vicky-2018-11-2
    '<xml>
        <title>${name} ${Vendor}</title>
        <description>
            Cite this &lt;b&gt;${proper_citation}&lt;/b&gt;&lt;br&gt;
            &lt;b&gt;Organism:&lt;/b&gt; ${species}&lt;br&gt;
            &lt;b&gt;Disease:&lt;/b&gt; ${diseases}&lt;br&gt;
            &lt;b&gt;Category:&lt;/b&gt; ${category}&lt;br&gt;
            &lt;b&gt;Comment:&lt;/b&gt; ${comments}&lt;br&gt;
        </description>
        <rrid>RRID:${id}</rrid>
    </xml>',
    ## add AddGene -- Vicky-2018-11-9
    '<xml>
        <title>${plasmid_name}</title>
        <description>
            Cite this &lt;b&gt;${proper_citation}&lt;/b&gt;&lt;br&gt;
            &lt;b&gt;Plasmid Name:&lt;/b&gt; ${Plasmid Name}&lt;br&gt;
            &lt;b&gt;Insert Name:&lt;/b&gt; ${Insert Name}&lt;br&gt;
            &lt;b&gt;Organism:&lt;/b&gt; ${Organism}&lt;br&gt;
            &lt;b&gt;Comment:&lt;/b&gt; ${Comment}&lt;br&gt;
        </description>
        <rrid>${proper_citation}</rrid>
    </xml>',
);
$nifs = array('nif-0000-07730-1','nlx_144509-1','nlx_154697-1','SCR_013869-1','nif-0000-03179-1', 'nlx_143929-1', 'nif-0000-11872-1');      ## add BioSample & AddGene -- Vicky-2018-11-9
$types = array('Antibody','Resource','Animal','Cell lines','Taxonomy', 'BioSamples', 'AddGene');     ## add BioSamples & AddGene -- Vicky-2018-11-9

$orderArray = array();
$count = 0;
$newURLs = array();
foreach ($urls as $i => $url) {
    $string = $search->checkLocalStore($url);
    if ($string) {
        $theFiles[$i] = $string;
    } else {
        $orderArray[$count] = $i;
        $newURLs[$count] = $url;
        $count++;
    }
}

if (count($newURLs) > 0) {
    $files = Connection::multi($newURLs);

    foreach ($files as $i => $file) {
        $search->insertIntoLocalStore($newURLs[$i], $file);
        $theFiles[$orderArray[$i]] = $file;
    }
}

$total = 0;
$num = 0;
$theMax = 0;
foreach ($theFiles as $i => $file) {
    $count = 0;
    $xml = simplexml_load_string($file);
    if ($xml) {
        $totals[$i] = (int)$xml->result['resultCount'];
        $total += (int)$xml->result['resultCount'];
        if ((int)$xml->result['resultCount'] > $theMax) {
            $theMax = (int)$xml->result['resultCount'];
        }
        foreach ($xml->result->results->row as $row) {
            $snippet = $snippets[$i];
            foreach ($row->data as $data) {
                $snippet = str_replace('${' . $data->name . '}', htmlspecialchars((string)$data->value), $snippet);
            }
            $results[$count][] = $snippet;
            $theTypes[$count][] = $types[$i];
            $theNIFs[$count][] = $nifs[$i];
            $count++;
            $num++;
        }
    }
}

?>

<div class="container s-results margin-bottom-50">
    <div class="row">
        <div class="col-md-3 hidden-xs related-search">
            <h2>RRID Project</h2>
            <p>
                The RRID project was created as a way to give publications a resource to create consistent links to
                the actual data used within the paper. Citations are in the format:
            </p>
            <p>RRID:Identifier</p>
            <p>
                The current resources we identify are resources, antibodies, and animals. These resources come from
                the SciCrunch Registry, Antibody Registry, and various animal vendors.
            </p>
        </div>
        <div class="col-md-9 col-sm-8">
            <span class="results-number">
                Showing <?php echo number_format($num) ?> results out of <?php echo number_format($total)?> results on Page <?php echo $page?>
            </span>
            <!-- Begin Inner Results -->

            <?php

            foreach ($results as $j=>$array) {
                foreach ($array as $i=>$row) {
                    //echo $row;
                    $record = simplexml_load_string($row);
                    if(!$record) continue;
                    $source = $allSources[$theNIFs[$j][$i]];

                    echo '<div class="inner-results">';
                    echo '<div class="the-title">';
                    echo ' <h3 style="display:inline-block"><a href="/resolver/'.$record->rrid.'">' . strip_tags($record->title) . '</a></h3>';


                    echo '</div>';

                    echo '<div class="overflow-h">';
                    if (strlen($source->image) > 20)
                        $imageSrc = $source->image;
                    else $imageSrc = '/upload/source-images/notfound.gif';
                    echo '<a target="_blank" href="/scicrunch/data/source/' . $source->nif . '/search?q=' . $query . '"><img src="' . $imageSrc . '" alt=""></a>';
                    echo '<div class="overflow-a">';
                    echo '<p>' . $record->description . '</p>';
                    echo '<ul class="list-inline down-ul">';
                    echo '<li>'.$theTypes[$j][$i].'</li>';
                    echo '</ul>';
                    echo '</div></div></div>';
                    echo '<hr/>';
                }
            }
            ?>



            <div class="margin-bottom-30"></div>

            <div class="text-left">
                <?php
                echo '<ul class="pagination">';

                $params = 'query=' . $query;
                $max = ceil($theMax / 7);

                if ($page > 1)
                    echo '<li><a href="/resolver/page/' . ($page - 1) . '?' . $params . '">«</a></li>';
                else
                    echo '<li><a href="javascript:void(0)">«</a></li>';

                if ($page - 3 > 0) {
                    $start = $page - 3;
                } else
                    $start = 1;
                if ($page + 3 < $max) {
                    $end = $page + 3;
                } else
                    $end = $max;

                if ($start > 2) {
                    echo '<li><a href="/resolver/page/1?' . $params . '">1</a></li>';
                    echo '<li><a href="/resolver/page/2?' . $params . '">2</a></li>';
                    echo '<li><a href="javascript:void(0)">..</a></li>';
                }

                for ($i = $start; $i <= $end; $i++) {
                    if ($i == $page) {
                        echo '<li class="active"><a href="javascript:void(0)">' . number_format($i) . '</a></li>';
                    } else {
                        echo '<li><a href="/resolver/page/' . $i . '?' . $params . '">' . number_format($i) . '</a></li>';
                    }
                }

                if ($end < $max - 3) {
                    echo '<li><a href="javascript:void(0)">..</a></li>';
                    echo '<li><a href="/resolver/page/' . ($max - 1) . '?' . $params . '">' . number_format($max - 1) . '</a></li>';
                    echo '<li><a href="/resolver/page/' . $max . '?' . $params . '">' . number_format($max) . '</a></li>';
                }

                if ($page < $max)
                    echo '<li><a href="/resolver/page/' . ($page + 1) . '?' . $params . '">»</a></li>';
                else
                    echo '<li><a href="javascript:void(0)">»</a></li>';


                echo '</ul>';
                ?>
            </div>
        </div>
    </div>
</div>
