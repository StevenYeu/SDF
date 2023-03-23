<?php

class WorldCatInterface {
    private static $init = false;
    private static $usable = false;
    private static $base_ocrc = NULL;
    private static $ocrc_image = NULL;
    private static $ocrc_text = NULL;
    private static $template_text = '<span id="ocrc" style="margin-left:10px;margin-bottom:-5px"><a style="color:#005544" alt="%s" href="%s" target="_blank"><img src="%s" /></a></span>';

    public static function getHTML($pmid) {
        if(!self::$init) {
            self::$init = true;
            $ip = \helper\getIP($_SERVER);
            $xml = simplexml_load_file("http://www.worldcat.org/registry/lookup?IP=" . $ip);
            if($xml->resolverRegistryEntry){
                $resolver = $xml->resolverRegistryEntry->resolver;
                $ocrc = (string) $resolver->baseURL;
                if(\helper\startsWith($ocrc, "http")) {
                    self::$usable = true;
                    self::$base_ocrc = $ocrc;
                    self::$ocrc_image = (string) $resolver->linkIcon;
                    self::$ocrc_text = (string) $resolver->linkText;
                } else {
                    self::$usable = false;
                }
            }
        }
        if(!self::$usable) return NULL;
        $full_ocrc_link = self::$base_ocrc . "?genre=article&id=pmid:" . $pmid;
        $html_text = sprintf(self::$template_text, self::$ocrc_text, $full_ocrc_link, self::$ocrc_image);
        return $html_text;
    }
}

?>
