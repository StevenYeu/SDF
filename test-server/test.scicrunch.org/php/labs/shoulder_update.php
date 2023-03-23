<?php

$xml = file_get_contents("updated.xml");
$xml = str_replace("\n", "%0A", $xml);
$str = 'datacite:' . $xml;

/*
$meta = [
            "creator" => 'Random Citizen',
            'title' => 'Random Thoughts',
            'publisher' => 'Random Houses',
            'publicationyear' => '2015',
            'resourcetype' => 'Text'
        ];
$str = format_metadata($meta);
*/

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/id/doi:10.5072/FK2PZ5974H');
curl_setopt($ch, CURLOPT_USERPWD, 'ucsd_odc:Xehjiw-wemha4-heswac');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER,
  array('Content-Type: text/plain; charset=UTF-8',
        'Content-Length: ' . strlen($str)));
curl_setopt($ch, CURLOPT_POSTFIELDS, $str);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
print curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
print $output . "\n";
curl_close($ch);

function format_metadata($meta)
    {
        $string_meta = "";
        foreach($meta as $key => $value)
        {
            if(substr($key, 0, 1) == '_')
            {
                $string_meta = $string_meta.$key.": ".$value."\r\n";
            }
            else 
            {
                $string_meta = $string_meta."datacite.".$key.": ".$value."\r\n";
            }
        }
        return $string_meta;
    }
