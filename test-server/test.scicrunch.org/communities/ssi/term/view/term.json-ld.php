<?php
    echo '<script type="application/ld+json">';

    $data = Array(
        "@context" => "https://schema.org/",
        "@type" => "DefinedTerm",
        "@id" => "http://uri.interlex.org/" . $term->ilx,
        "name" => $term->label,
        "termCode" => $term->ilx,
        "description" => $term->definition,
        "inDefinedTermSet" => "https://interlex.org/",
        "alternateName" => Array(),
        "identifier" => Array(),
        "url" => "http://uri.interlex.org/" . $term->ilx,
    );

    foreach ($term->synonyms as $synonym) {
        $data["alternateName"][] = $synonym->literal;
    }

    foreach ($term->existing_ids as $existing_id) {
        $data["identifier"][] = $existing_id->curie;
    }

    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    echo '</script>';
?>
