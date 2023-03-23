<?php

class SchemaGeneratorPublicationXML
{
    public static function generate($paper_xml) 
    {
        $protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
        $attributes = $paper_xml->attributes();
        $schema = new ScholarlyArticleSchema();
        $schema->id = "$protocol://$_SERVER[HTTP_HOST]/".((string)$attributes->pmid); 
        $schema->headline = (string)$paper_xml->title;
        $datetime = new DateTime('0-0-0');
        $datetime->setDate($paper_xml->year, $paper_xml->month, $paper_xml->day);
        $schema->datePublished = $datetime;
        $schema->description = (string)$paper_xml->abstract;
        foreach ($paper_xml->authors->author as $author) {
            $authorSchema = new PersonSchema();
            $authorSchema->name = (string)$author;
            $schema->authorSchema[] = $authorSchema;
        }
        foreach ($paper_xml->grantIds->grantId as $grantID) {
            $schema->award[] = (string)$grantID;
        }
        foreach ($paper_xml->grantAgencies->grantAgency as $sponsor) {
            $sponsorSchema = new OrganizationSchema();
            $sponsorSchema->name = (string)$sponsor;
            $schema->sponsorSchema[] = $sponsorSchema;
        }
        foreach ($paper_xml->meshHeadings->meshHeading as $mesh) {
            $meshSchema = new ThingSchema();
            $meshSchema->name = (string)$mesh;
            $schema->aboutSchema[] = $meshSchema;
        }
        $journal = new PeriodicalSchema();
        $journal->name = (string)$paper_xml->journal;
        $journal->alternateName = (string)$paper_xml->journalShort;
        $schema->isPartOfSchema[] = $journal;
        $schema->image = "$protocol://$_SERVER[HTTP_HOST]/images/scicrunch.png";

        return $schema;
    }
}

?>
