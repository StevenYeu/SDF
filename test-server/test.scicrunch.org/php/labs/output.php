
<?php
    /*** create a SimpleXML object ***/
    if( ! $xml = simplexml_load_file("address.xml") )
    {
        echo "Unable to load XML file";
    }
    else
    {
        /*** show the firstname element from all nodes ***/
        $info = $xml->xpath("//*[firstname='Sheila']");

        /*** initialize the string ***/
        $xml_string = '';

        /*** loop over the results ***/
        while(list( , $node) = each($info))
        {
            $xml_string .= $node->asXML(); // <c>text</c> and <c>stuff</c>
        }

        /*** output the xml ***/
        echo $xml_string;
    }
?>
