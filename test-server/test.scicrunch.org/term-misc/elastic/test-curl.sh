#curl -XGET "localhost:9200/scicrunch/term/_search?pretty" -d '
#curl -XGET "biocaddie.scicrunch.io:80/scicrunch/term/_search?pretty" -d   '
curl -XGET "search-interlex-jnsyal3bwjqhaledmfvvwytzy4.us-west-2.es.amazonaws.com:80/scicrunch/term/_search?pretty" -d   '
    {
        "from" : 0,
        "size" : 100,
        "query": { "bool":{ "must":[{"multi_match": {"query": "neuron","fields":[
        "ilx^3",
        "label^4",
        "type^2",
        "definition^2",
        "comment",
        "ontologies.url^2",
        "synonyms.literal^3",
        "existing_ids.curie^2",
        "existing_ids.iri^2",
        "superclasses.ilx",
        "superclasses.label",
        "relationships.term1_label",
        "relationships.term1_ilx",
        "relationships.term12_label",
        "relationships.term2_ilx",
        "relationships.relationship_term_label",
        "relationships.relationship_term_ilx",
        "annotations.term_label",
        "annotations.term_ilx",
        "annotations.annotation_term_label",
        "annotations.annotation_term_ilx",
        "annotations.value"
    ]}},{},{}]}},
        "highlight": {
        "fields":{
            "label" : {},
            "type" : {},
            "definition": {},
            "ilx": {},
            "ontology.url": {},
            "synonyms.literal": {},
            "existing_ids.curie": {},
            "existing_ids.iri": {},
            "comment": {},
            "superclass.ilx": {},
            "superclasses.label": {},
            "relationships.term1_label": {},
            "relationships.term2_label": {},
            "relationships.term1_ilx": {},
            "relationships.term2_ilx": {},
            "relationships.relationship_term_label": {},
            "relationships.relationship_term_ilx": {},
            "annotations.term_label": {},
            "annotations.term_ilx": {},
            "annotations.annotation_term_label": {},
            "annotations.annotation_term_ilx": {},
            "annotations.value": {}
        }
    }
    }
    '
