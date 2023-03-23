<?php

class SchemaGeneratorTerm {
    public static function generate($term) {
        $schema = new ThingSchema();
        $schema->name = $term->label;
        $schema->url = "http://uri.interlex.org/base/" . $term->ilx;
        $schema->description = $term->definition;
        return $schema;
    }
}

?>
