<?php
use ezyang\HTMLPurifier;

namespace helper;
include_once('mailer.class.php');



function formattedDescription($text){
    $fmt_text = str_replace("\\\\n", "<br/>", $text);
    $fmt_text = str_replace("Problematic cell line", "<span style='color:red'>Problematic cell line</span>", $fmt_text);
    $fmt_text = html_entity_decode($fmt_text);
    $fmt_text = html_entity_decode($fmt_text);
    return $fmt_text;
}

function writeEachMessage($message){
    $return_msg = "";
    foreach($message as $msg){
        $return_msg .= '<tr><td valign="top">' . $msg . '</td></tr>';
    }
    return $return_msg;
}

function buildEmailMessage($message, $alt=0, $data = NULL){
    ob_start();	// starts reading output into buffer.  allows for html in a function
    include $GLOBALS["DOCUMENT_ROOT"] . '/email.php';
    $new_message = ob_get_clean();	// push ouput and stop using the buffer
    return $new_message;
}

function sendEmail($to, $html_message, $text_message, $subject, $reply_to = NULL, $from_name = "SciCrunch"){
    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;            
        $mail->isSMTP();                                           
        $mail->Host       = 'outbound.ucsd.edu';                     
        $mail->SMTPAuth   = false;                                                                
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                   
    
        $mail->setFrom('syeu@ucsd.edu', 'SDF');
        $mail->addAddress('syeu@ucsd.edu');
        $mail->isHTML(true);                               
        $mail->Subject = "SDF TEST";
        $mail->Body    =  "<h1>TEST</h1>";
        $mail->AltBody = "Test";
    
        $mail->send();
    } catch (Exception $e) {
        error_log("****PHPMailer Message could not be sent. Mailer Error");
    }
}

function checkLongURL($data, $community, $viewid, $uuid, $col){
    if(strpos($data, "dx.doi.org") !== false) return $data;
    if(substr($data, 0, 2) != "<a") return $data;   // make sure it's an anchor tag
    if(strlen(strip_tags($data)) == 0) return $data;
    $ncbi_url = "http://www.ncbi.nlm.nih.gov/pubmed";

    try{
        $references = strip_tags($data);
        $matches = Array();
        preg_match_all("/PMID:(\d+)\b/i", $references, $matches);
        if(count($matches[1]) > 0) {
            $return_text = '<a class="referer-link" href="/' . $community->portalName . '/literature/search?litref=' . $viewid . ':' . $uuid . ':' . $col . '">';
            if(count($matches[1]) > 1) $return_text .= 'References (' . number_format(count($matches[1])) . ')';
            else $return_text .= 'PMID:' . $matches[1][0];
            $return_text .= '</a>';
            return $return_text;
        } else {
            return $data;
        }
    }catch(\Exception $e){
        return $data;
    }
    return $data;
}

function getIDFromRID($scr_id){
    $cxn = new \Connection();
    $cxn->connect();
    $results = $cxn->select("resources", Array("id"), "ss", Array($scr_id, $scr_id), "where original_id=? or rid=?");
    $cxn->close();

    if(count($results) == 1) return $results[0]['id'];
    return NULL;
}

function originalID2ScrID($id){
    $resource = new \Resource();
    $resource->getByOriginal($id);
    if($resource->id){
        return $resource->rid;
    }
    return $id;
}

function sendPostRequest($url, $post_data, $headers=NULL, $user_pass=NULL, $pre_formatted=false, $port=NULL){
    if($pre_formatted) {
        $formatted_post_data = $post_data;
    } else {
        $formatted_post_data = http_build_query($post_data);
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $formatted_post_data);
    if(!is_null($port)) curl_setopt($ch, CUROPT_PORT, $port);
    if(!is_null($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if(!is_null($user_pass)) curl_setopt($ch, CURLOPT_USERPWD, $user_pass);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);
    return $server_output;
}

function sendGetRequest($base_url, $get_data, $headers=NULL, $user_pass=NULL){
    if($get_data) {
        $get_args = Array();
        foreach($get_data as $key => $val){
            $get_args[] = $key . "=" . $val;
        }
        $url = $base_url . "?" . implode("&", $get_args);
    } else {
        $url = $base_url;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if(!is_null($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if(!is_null($user_pass)) curl_setopt($ch, CURLOPT_USERPWD, $user_pass);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec($ch);
    curl_close($ch);
    return $server_output;
}

function swap(&$x1, &$x2){
    $tmp = $x1;
    $x1 = $x2;
    $x2 = $tmp;
}

function topPageHTML(){
    ob_start(); ?>

    <noscript>
        <div class="alert alert-danger">
            This site relies heavily on JavaScript.  Many functions will not work if you continue with JavaScript disabled.
        </div>
    </noscript>
    <?php /* Manu: commented */ /* if(!startsWith($_SERVER['SERVER_NAME'], "scicrunch")): ?>
        <div class="alert alert-warning">
            This is not the production SciCrunch server
        </div>
    <?php endif */ ?>
    <?php if(isset($_SESSION) && isset($_SESSION["betaenvironment"]) && $_SESSION["betaenvironment"] === true): ?>
        <div class="alert alert-info">
            You are currently using stage data services: <strong><?php echo \Connection::environment() ?></strong>
        </div>
    <?php endif ?>
    <?php echo \SystemMessage::alertsHTML(\SystemMessage::ALL_CID); ?>

    <?php return ob_get_clean();
}

function startsWith($haystack, $needle)
{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

function getViewsFromOriginalID($original_id){
    $holder = new \Sources();
    $sources = $holder->getAllSources();
    $results = Array();
    $base_url = 'http://cm.neuinfo.org:8080/cm_services/sources/summary?viewNifId=';

    $url = $base_url . $original_id;
    $xml = simplexml_load_file($url);
    if(!$xml){
        $cxn = new \Connection();
        $cxn->connect();
        $nifs = $cxn->select("scicrunch_sources", Array("nif"), "s", Array($original_id . "%"), "where nif like ? limit 1");
        $cxn->close();
        if(count($nifs) > 0){
            $url = $base_url . $nifs[0]['nif'];
            $xml = simplexml_load_file($url);
        }
    }
    $views = Array();
    if ($xml) {
        $results['license'] = (string)$xml->license;
        $results['license-url'] = (string)$xml->{'license-url'};
        foreach ($xml->views->view as $view) {
            if ((string)$view['isView'] != 'true' || (string)$view['indexed']=='false' || !$sources[(string)$view['nifId']] || $sources[(string)$view["nifId"]]->active == 0) continue;
            $views[] = $sources[(string)$view['nifId']];
        }
        $results['views'] = $views;
        return $results;
    }
    return NULL;
}

function generateHTMLViewsTab($views, $community){
    $holder = new \Category();
    $return = $holder->getUsed();
    $communities = array();
    $sources2 = array();
    $colors = array();
    $who = array();
    foreach ($return as $cat) {
        if (!$communities[$cat->cid]) {
            $comm = new \Community();
            $comm->getByID($cat->cid);
            if($comm->name == "") continue;
            $communities[$cat->cid] = $comm;
        }
        if ($communities[$cat->cid]->private == 1) continue;
        if (!isset($sources2[$cat->source]) || !in_array($cat->cid, $sources2[$cat->source])) {
            $sources2[$cat->source][] = $cat->cid;
            $colors[$cat->source][] = $communities[$cat->cid]->communityColor();
            $who[$cat->source][] = $communities[$cat->cid];
        }
    }
    ob_start();
    include $_SERVER['DOCUMENT_ROOT'] . "/ssi/pages/views_tab.php";
    return ob_get_clean();
}

function getIRIFragment($iri){
    $iri_split = explode("#", $iri);
    if(count($iri_split) == 1) $iri_split = explode("/", $iri);
    return $iri_split[count($iri_split) - 1];
}

function decodeUTF8($text){
    //return preg_replace_callback("/(&#[0-9]+;)/", function($m){ return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $text);
    return $text;
}

function mentionIDFormat($mentionid){
    return preg_match("/(^PMID: ?[0-9]+|DOI: ?.+|PMC: ?.+)$/", $mentionid);
}

function mentionIDFormatMultiple($mentionids, $delim) {
    if(!$mentionids) return true;
    $mentionids_array = explode($delim, $mentionids);
    foreach($mentionids_array as $ma) {
        $mentionid = trim($ma);
        if(!mentionIDFormat($mentionid)) return false;
    }
    return true;
}

/**
 * aR
 * handles input sanitzation
 *
 * @param mixed the argument that's being sanitized
 * @param string the type of argument to be sanitized. s - string, f - float, i (default) - integer
 * @return mixed the sanitized argument
 */
function aR($request, $type){   // aR - argumentRequest
    if(is_null($request)) return NULL;
    $options = NULL;
    switch($type){
        case "s":
            $sanitize = FILTER_SANITIZE_STRING;
            break;
        case "f":
            $sanitize = FILTER_SANITIZE_NUMBER_FLOAT;
            $options = FILTER_FLAG_ALLOW_FRACTION;
            break;
        default:
            $sanitize = FILTER_SANITIZE_NUMBER_INT;
            break;
    }
    if(!is_null($options)) return filter_var($request, $sanitize, $options);
    return filter_var($request, $sanitize);
}

function getDOIJSON($doi){
    $url_base = "http://dx.doi.org/";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_base . $doi);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Accept: application/vnd.citationstyles.csl+json"));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($status !== 200) return NULL;
    return json_decode($output, true);
}

function getPreviousPage($page){
    if(startsWith($page, "/")) return $page;
    return "/";
}

function loginForm($msg=NULL){
    ob_start();
    include $_SERVER["DOCUMENT_ROOT"] . '/ssi/pages/login_form.php';
    return ob_get_clean();
}

function htmlElement($element, $data=NULL){
    $file_name = $_SERVER["DOCUMENT_ROOT"] . "/ssi/elements/".$element.".php";
    if(!file_exists($file_name)) return NULL;
    ob_start();
    include $file_name;
    return ob_get_clean();
}

function cleanXML($xml){
    static $HTML401NamedToNumeric = array(
        '&nbsp;'     => '&#160;',
        '&iexcl;'    => '&#161;',
        '&cent;'     => '&#162;',
        '&pound;'    => '&#163;',
        '&curren;'   => '&#164;',
        '&yen;'      => '&#165;',
        '&brvbar;'   => '&#166;',
        '&sect;'     => '&#167;',
        '&uml;'      => '&#168;',
        '&copy;'     => '&#169;',
        '&ordf;'     => '&#170;',
        '&laquo;'    => '&#171;',
        '&not;'      => '&#172;',
        '&shy;'      => '&#173;',
        '&reg;'      => '&#174;',
        '&macr;'     => '&#175;',
        '&deg;'      => '&#176;',
        '&plusmn;'   => '&#177;',
        '&sup2;'     => '&#178;',
        '&sup3;'     => '&#179;',
        '&acute;'    => '&#180;',
        '&micro;'    => '&#181;',
        '&para;'     => '&#182;',
        '&middot;'   => '&#183;',
        '&cedil;'    => '&#184;',
        '&sup1;'     => '&#185;',
        '&ordm;'     => '&#186;',
        '&raquo;'    => '&#187;',
        '&frac14;'   => '&#188;',
        '&frac12;'   => '&#189;',
        '&frac34;'   => '&#190;',
        '&iquest;'   => '&#191;',
        '&Agrave;'   => '&#192;',
        '&Aacute;'   => '&#193;',
        '&Acirc;'    => '&#194;',
        '&Atilde;'   => '&#195;',
        '&Auml;'     => '&#196;',
        '&Aring;'    => '&#197;',
        '&AElig;'    => '&#198;',
        '&Ccedil;'   => '&#199;',
        '&Egrave;'   => '&#200;',
        '&Eacute;'   => '&#201;',
        '&Ecirc;'    => '&#202;',
        '&Euml;'     => '&#203;',
        '&Igrave;'   => '&#204;',
        '&Iacute;'   => '&#205;',
        '&Icirc;'    => '&#206;',
        '&Iuml;'     => '&#207;',
        '&ETH;'      => '&#208;',
        '&Ntilde;'   => '&#209;',
        '&Ograve;'   => '&#210;',
        '&Oacute;'   => '&#211;',
        '&Ocirc;'    => '&#212;',
        '&Otilde;'   => '&#213;',
        '&Ouml;'     => '&#214;',
        '&times;'    => '&#215;',
        '&Oslash;'   => '&#216;',
        '&Ugrave;'   => '&#217;',
        '&Uacute;'   => '&#218;',
        '&Ucirc;'    => '&#219;',
        '&Uuml;'     => '&#220;',
        '&Yacute;'   => '&#221;',
        '&THORN;'    => '&#222;',
        '&szlig;'    => '&#223;',
        '&agrave;'   => '&#224;',
        '&aacute;'   => '&#225;',
        '&acirc;'    => '&#226;',
        '&atilde;'   => '&#227;',
        '&auml;'     => '&#228;',
        '&aring;'    => '&#229;',
        '&aelig;'    => '&#230;',
        '&ccedil;'   => '&#231;',
        '&egrave;'   => '&#232;',
        '&eacute;'   => '&#233;',
        '&ecirc;'    => '&#234;',
        '&euml;'     => '&#235;',
        '&igrave;'   => '&#236;',
        '&iacute;'   => '&#237;',
        '&icirc;'    => '&#238;',
        '&iuml;'     => '&#239;',
        '&eth;'      => '&#240;',
        '&ntilde;'   => '&#241;',
        '&ograve;'   => '&#242;',
        '&oacute;'   => '&#243;',
        '&ocirc;'    => '&#244;',
        '&otilde;'   => '&#245;',
        '&ouml;'     => '&#246;',
        '&divide;'   => '&#247;',
        '&oslash;'   => '&#248;',
        '&ugrave;'   => '&#249;',
        '&uacute;'   => '&#250;',
        '&ucirc;'    => '&#251;',
        '&uuml;'     => '&#252;',
        '&yacute;'   => '&#253;',
        '&thorn;'    => '&#254;',
        '&yuml;'     => '&#255;',
        '&fnof;'     => '&#402;',
        '&Alpha;'    => '&#913;',
        '&Beta;'     => '&#914;',
        '&Gamma;'    => '&#915;',
        '&Delta;'    => '&#916;',
        '&Epsilon;'  => '&#917;',
        '&Zeta;'     => '&#918;',
        '&Eta;'      => '&#919;',
        '&Theta;'    => '&#920;',
        '&Iota;'     => '&#921;',
        '&Kappa;'    => '&#922;',
        '&Lambda;'   => '&#923;',
        '&Mu;'       => '&#924;',
        '&Nu;'       => '&#925;',
        '&Xi;'       => '&#926;',
        '&Omicron;'  => '&#927;',
        '&Pi;'       => '&#928;',
        '&Rho;'      => '&#929;',
        '&Sigma;'    => '&#931;',
        '&Tau;'      => '&#932;',
        '&Upsilon;'  => '&#933;',
        '&Phi;'      => '&#934;',
        '&Chi;'      => '&#935;',
        '&Psi;'      => '&#936;',
        '&Omega;'    => '&#937;',
        '&alpha;'    => '&#945;',
        '&beta;'     => '&#946;',
        '&gamma;'    => '&#947;',
        '&delta;'    => '&#948;',
        '&epsilon;'  => '&#949;',
        '&zeta;'     => '&#950;',
        '&eta;'      => '&#951;',
        '&theta;'    => '&#952;',
        '&iota;'     => '&#953;',
        '&kappa;'    => '&#954;',
        '&lambda;'   => '&#955;',
        '&mu;'       => '&#956;',
        '&nu;'       => '&#957;',
        '&xi;'       => '&#958;',
        '&omicron;'  => '&#959;',
        '&pi;'       => '&#960;',
        '&rho;'      => '&#961;',
        '&sigmaf;'   => '&#962;',
        '&sigma;'    => '&#963;',
        '&tau;'      => '&#964;',
        '&upsilon;'  => '&#965;',
        '&phi;'      => '&#966;',
        '&chi;'      => '&#967;',
        '&psi;'      => '&#968;',
        '&omega;'    => '&#969;',
        '&thetasym;' => '&#977;',
        '&upsih;'    => '&#978;',
        '&piv;'      => '&#982;',
        '&bull;'     => '&#8226;',
        '&hellip;'   => '&#8230;',
        '&prime;'    => '&#8242;',
        '&Prime;'    => '&#8243;',
        '&oline;'    => '&#8254;',
        '&frasl;'    => '&#8260;',
        '&weierp;'   => '&#8472;',
        '&image;'    => '&#8465;',
        '&real;'     => '&#8476;',
        '&trade;'    => '&#8482;',
        '&alefsym;'  => '&#8501;',
        '&larr;'     => '&#8592;',
        '&uarr;'     => '&#8593;',
        '&rarr;'     => '&#8594;',
        '&darr;'     => '&#8595;',
        '&harr;'     => '&#8596;',
        '&crarr;'    => '&#8629;',
        '&lArr;'     => '&#8656;',
        '&uArr;'     => '&#8657;',
        '&rArr;'     => '&#8658;',
        '&dArr;'     => '&#8659;',
        '&hArr;'     => '&#8660;',
        '&forall;'   => '&#8704;',
        '&part;'     => '&#8706;',
        '&exist;'    => '&#8707;',
        '&empty;'    => '&#8709;',
        '&nabla;'    => '&#8711;',
        '&isin;'     => '&#8712;',
        '&notin;'    => '&#8713;',
        '&ni;'       => '&#8715;',
        '&prod;'     => '&#8719;',
        '&sum;'      => '&#8721;',
        '&minus;'    => '&#8722;',
        '&lowast;'   => '&#8727;',
        '&radic;'    => '&#8730;',
        '&prop;'     => '&#8733;',
        '&infin;'    => '&#8734;',
        '&ang;'      => '&#8736;',
        '&and;'      => '&#8743;',
        '&or;'       => '&#8744;',
        '&cap;'      => '&#8745;',
        '&cup;'      => '&#8746;',
        '&int;'      => '&#8747;',
        '&there4;'   => '&#8756;',
        '&sim;'      => '&#8764;',
        '&cong;'     => '&#8773;',
        '&asymp;'    => '&#8776;',
        '&ne;'       => '&#8800;',
        '&equiv;'    => '&#8801;',
        '&le;'       => '&#8804;',
        '&ge;'       => '&#8805;',
        '&sub;'      => '&#8834;',
        '&sup;'      => '&#8835;',
        '&nsub;'     => '&#8836;',
        '&sube;'     => '&#8838;',
        '&supe;'     => '&#8839;',
        '&oplus;'    => '&#8853;',
        '&otimes;'   => '&#8855;',
        '&perp;'     => '&#8869;',
        '&sdot;'     => '&#8901;',
        '&lceil;'    => '&#8968;',
        '&rceil;'    => '&#8969;',
        '&lfloor;'   => '&#8970;',
        '&rfloor;'   => '&#8971;',
        '&lang;'     => '&#9001;',
        '&rang;'     => '&#9002;',
        '&loz;'      => '&#9674;',
        '&spades;'   => '&#9824;',
        '&clubs;'    => '&#9827;',
        '&hearts;'   => '&#9829;',
        '&diams;'    => '&#9830;',
        '&quot;'     => '&#34;',
        '&amp;'      => '&#38;',
        '&lt;'       => '&#60;',
        '&gt;'       => '&#62;',
        '&OElig;'    => '&#338;',
        '&oelig;'    => '&#339;',
        '&Scaron;'   => '&#352;',
        '&scaron;'   => '&#353;',
        '&Yuml;'     => '&#376;',
        '&circ;'     => '&#710;',
        '&tilde;'    => '&#732;',
        '&ensp;'     => '&#8194;',
        '&emsp;'     => '&#8195;',
        '&thinsp;'   => '&#8201;',
        '&zwnj;'     => '&#8204;',
        '&zwj;'      => '&#8205;',
        '&lrm;'      => '&#8206;',
        '&rlm;'      => '&#8207;',
        '&ndash;'    => '&#8211;',
        '&mdash;'    => '&#8212;',
        '&lsquo;'    => '&#8216;',
        '&rsquo;'    => '&#8217;',
        '&sbquo;'    => '&#8218;',
        '&ldquo;'    => '&#8220;',
        '&rdquo;'    => '&#8221;',
        '&bdquo;'    => '&#8222;',
        '&dagger;'   => '&#8224;',
        '&Dagger;'   => '&#8225;',
        '&permil;'   => '&#8240;',
        '&lsaquo;'   => '&#8249;',
        '&rsaquo;'   => '&#8250;',
        '&euro;'     => '&#8364;',
    );
    return strtr($xml, $HTML401NamedToNumeric);
}

function getIP($server){
    if($server["HTTP_X_FORWARDED_FOR"]){
        $forwarded_ip = explode(",", $server["HTTP_X_FORWARDED_FOR"]);
        $ip = $forwarded_ip[0];
    }else{
        $ip = $server["REMOTE_ADDR"];
    }
    return $ip;
}

function errorPage($type, $data = NULL, $exit = true){
    include $_SERVER["DOCUMENT_ROOT"] . "/errorr.php";
    if($exit) {
        exit;
    }
}

function sanitizeHTMLString($obj, \HTMLPurifier $user_purifier = NULL) {
    if(is_null($user_purifier)) {
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
    } else {
        $purifier = $user_purifier;
    }

// use the config settings
    $config = \HTMLPurifier_Config::createDefault();
    $config->set('Attr', 'EnableID', TRUE);
    $purifier = new \HTMLPurifier($config);
    
    if(is_array($obj)) {
        foreach($obj as $i => $o) {
            $obj[$i] = sanitizeHTMLString($o, $purifier);
        }
    } else {
        $obj = $purifier->purify($obj);
    }
    return $obj;
}

function checkReferer($referer) {
    // make sure the referer is not quoted
    if($referer[0] == '"' && $referer[strlen($referer) - 1] == '"') $referer = substr($referer, 1, strlen($referer) - 2);

    // sanitize referer
    $referer = sanitizeHTMLString($referer);

    // make sure referer is a relative path (not pointing to an outside host)
    $referer_parse = parse_url($referer, PHP_URL_HOST);
    if(isset($referer_parse["host"])) return NULL;

    return $referer;
}

function char2Entity($string) {
    $string = str_replace("α", "&alpha;", $string);
    $string = str_replace("β", "&beta;", $string);
    $string = str_replace("μ", "&mu;", $string);
    return $string;
}

function floatCompare($f1, $f2, $precision=0.0000000001) {
    if(is_nan($f1) || is_nan($f2)) return false;
    return (abs($f1 - $f2) < $precision);
}

function setGetQueryParam($url, $param, $value, $is_array = false) {
    /* this function takes a GET parameter and changes it or appends it to the get parameters */

    $parsed_url = parse_url($url);
    parse_str($parsed_url["query"], $params);

    if(is_null($value)) {
        unset($params[$param]);
    } else {
        if($is_array) {
            $params[$param][] = $value;
        } else {
            $params[$param] = $value;
        }
    }

    $parsed_url["query"] = http_build_query($params);
    $return_url = "";
    if($parsed_url["scheme"]) {
        $return_url .= $parsed_url["scheme"] . "://";
    }
    $return_url .= $parsed_url["host"] . $parsed_url["path"] . "?" . $parsed_url["query"];

    return $return_url;
}

function scicrunch_session_start() {
    session_start();
    if ($_SESSION['user'] && (!$_SESSION['lastRefreshed'] || (time() - $_SESSION['lastRefreshed'] > 10))) {
        $_SESSION['user']->loginProcess(false);
        $_SESSION['lastRefreshed'] = time();
    }
    if(($_SESSION["lastRefreshedCommunities"]) || (time() - $_SESSION["lastRefreshedCommunities"]) > 60) {
        $_SESSION["communities"] = Array();
        $_SESSION["lastRefreshedCommunities"] = time();
    }
}

function httpHost() {
    if(isset($_SERVER["HTTP_X_FORWARDED_HOST"])) {
        return $_SERVER["HTTP_X_FORWARDED_HOST"];
    } elseif(isset($_SERVER["HTTP_HOST"])) {
        return $_SERVER["HTTP_HOST"];
    } else {
        return FQDN;
    }
}

function metaDescription($desc) {
    $sdesc = strip_tags($desc);
    if(strlen($sdesc) <= 162) return $sdesc;
    $pos = strpos($sdesc, ' ', 162);
    if($pos === false) $pos = 162;
    return substr($sdesc, 0, $pos);
}

function queryableClasses() {
    static $qc = Array(
        "API log" => Array("classname" => "APIKeyLog"),
        "Entity mapping" => Array("classname" => "EntityMapping"),
        "User" => Array("classname" => "UserDBO"),
        "RRID report" => Array("classname" => "RRIDReport"),
        "RRID report freeze" => Array("classname" => "RRIDReportFreeze"),
        "RRID report item" => Array("classname" => "RRIDReportItem"),
        "RRID report item subtype" => Array("classname" => "RRIDReportItemSubtype"),
        "RRID report item user data" => Array("classname" => "RRIDReportItemUserData"),
        "RRID report item subtype user data" => Array("classname" => "RRIDReportItemSubtypeUserData"),
        "Dataset" => Array("classname" => "Dataset"),
        "Dataset field" => Array("classname" => "DatasetField"),
        "Dataset field template" => Array("classname" => "DatasetFieldTemplate"),
        "Dataset metadata" => Array("classname" => "DatasetMetadata"),
        "Dataset metadata field" => Array("classname" => "DatasetMetadataField"),
        "Lab" => Array("classname" => "Lab"),
        "Lab Membership" => Array("classname" => "LabMembership"),
        "Lab Membership Role" => Array("classname" => "LabMembershipRole"),
        "Server cache" => Array("classname" => "ServerCache"),
        "Users extra data" => Array("classname" => "UsersExtraData"),
        "RRID Mentions" => Array("classname" => "RRIDMention"),
        "RRID Literature Records" => Array("classname" => "RRIDMentionsLiteratureRecord"),
        "RRID Grant Info" => Array("classname" => "RRIDMentionsGrantInfo"),
    );
    return $qc;
}

function getOrcidOauthAccessToken() {
    $cached_token = \ServerCache::loadBy(Array("name"), Array("orcid-oauth-access-token"));
    if(!is_null($cached_token)) {
        $token = json_decode($cached_token->value, true);
        return $token["access_token"];
    }
    $raw_result = sendPostRequest("https://orcid.org/oauth/token", Array("client_id" => ORCID_CLIENT_ID, "client_secret" => ORCID_CLIENT_SECRET, "scope" => "/read-public", "grant_type" => "client_credentials"), Array("Accept: application/json"));
    $result = json_decode($raw_result, true);
    if(!isset($result["access_token"])) return NULL;
    \ServerCache::createNewObj("orcid-oauth-access-token", $raw_result);
    return $result["access_token"];
}

function mailchimpPut($api_key, $list_id, $status, $email, $firstname, $lastname) {
    $memberId = md5(strtolower($email));
    $dataCenter = substr($api_key, strpos($api_key, '-') +1);
    $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $memberId;

    $json = json_encode([
        'email_address' => $email,
        'status'        => $status, // "subscribed","unsubscribed","cleaned","pending"
        'merge_fields'  => [
            'FNAME'     => $firstname,
            'LNAME'     => $lastname
        ]
    ]);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $api_key);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode);
}

function assocDiff($old_assoc, $new_assoc) {
    require_once __DIR__ . "/../lib/other/finediff/finediff.php";

    $diff = Array("added" => Array(), "removed" => Array(), "modified" => Array(), "children" => Array());

    $nak = array_keys($new_assoc);
    foreach($nak as $n) {
        if(!isset($old_assoc[$n]) || !$old_assoc[$n]) {
            $diff["added"][] = $n;
        }
    }

    foreach($old_assoc as $key => $val) {
        if(is_array($val)) {
            $diff["children"][$key] = assocDiff($val, $new_assoc[$key]);
        } else {
            if(!isset($new_assoc[$key]) || !$new_assoc[$key]) {
                $diff["removed"][$key] = $val;
            } elseif($val !== $new_assoc[$key]) {
                $diff["modified"][$key] = FineDiff::getDiffOpcodes($new_assoc[$key], $val);
            }
        }
    }

    return $diff;
}

function dateFormat($type, $time) {
    switch($type) {
        case "date":
            $format = "M j, Y";
            break;
        case "normal":
        default:
            $format = "M j, Y g:i A";
            break;
    }
    return date($format, $time);
}

function rridToUUID($rrid) {
    $cxn = new \Connection();
    $cxn->connect();
    $rows = $cxn->select("resources", Array("uuid"), "ss", Array($rrid, $rrid), "where rid=? or original_id=?");
    $cxn->close();

    if(empty($rows)) {
        return NULL;
    }
    return $rows[0]["uuid"];
}

function uuidtoRRID($uuid) {
    $cxn = new \Connection();
    $cxn->connect();
    $rows = $cxn->select("resources", Array("rid"), "s", Array($uuid), "where uuid=?");
    $cxn->close();

    if(empty($rows)) {
        return NULL;
    }
    return $rows[0]["rid"];
}

/**
 * derefArray
 * get a value from an assoc array with a key of arbitrary depth
 *
 * @param array the array to dereference
 * @param mixed a string (delimited by '.') or an array of keys to dereference with
 * @return mixed the dereferenced value or null if not found
 */
function derefArray($data, $keys, $delim=".", $array_field="[]") {
    if(!is_array($keys)) {
        $keys = explode($delim, $keys);
    }
    $count = count($keys);
    foreach($keys as $i => $key) {
        if(!is_array($data)) {
            return NULL;
        }

        /* if array */
        if($key === $array_field) {
            $final_data = Array();
            $datum_keys = array_slice($keys, $i + 1);
            $need_to_flatten = in_array($array_field, $datum_keys);
            foreach($data as $d) {
                if($count - 1 == $i) {
                    return $final_data[] = $d;
                } else {
                    $final_datum = derefArray($d, $datum_keys, $delim, $array_field);
                    if(is_null($final_datum)) {
                        continue;
                    }
                    if($need_to_flatten) {
                        $final_data = array_merge($final_data, $final_datum);
                    } else {
                        $final_data[] = $final_datum;
                    }
                }
            }
            return $final_data;
        }

        /* if normal */
        if(!array_key_exists($key, $data)) {
            return NULL;
        }
        $data = $data[$key];
    }
    return $data;
}

/**
 * rinBreadCrumbsToNormalBreadCrumbs
 * convert RIN style array of breadcrumbs into the normal scicrunch breadcrumbs
 *
 * @param Array array of breadcrumbs in RIN style
 * @return String html of the Connection::createBreadCrumbs function
 */
function rinBreadCrumbsToNormalBreadCrumbs($breadcrumbs) {
    $title = "";
    $paths = Array();
    $urls = Array();
    foreach($breadcrumbs as $bc) {
        if($bc["active"]) {
            $title = $bc["text"];
        } else {
            $paths[] = $bc["text"];
            $urls[] = $bc["url"];
        }
    }
    return \Connection::createBreadCrumbs($title, $paths, $urls, $title);
}

/**
 * urlGetAppendChar
 * check if get vars already exist in url
 * return a ? if not or ampersand if they do
 *
 * @param string url
 * @return string either a question mark or ampersand
 */
function urlGetAppendChar($url) {
    if(strpos($url, "?") === false) {
        return "?";
    }
    return "&";
}

function format_ezid_metadata($meta) {
    $string_meta = "";
    foreach($meta as $key => $value) {
        $string_meta = $string_meta."datacite.".$key.": ".$value."\r\n";
    }
    return $string_meta;
}

function regexp_doi($str) {
    $pattern = "/(10\.[0-9]{4}[^\s]*)\s/";
    if (preg_match($pattern, $str, $matches))
        return $matches[1];
    else
        return false;
}

function isODCAdmin($user_id) {
    require $_SERVER["DOCUMENT_ROOT"] . "/php/labs/odc_config.php";

    $access = Array();

    foreach (array('odc-sci', 'odc-tbi') as $community) {
        foreach (array('curation_team', 'editorial_team') as $team) {
            if (in_array($user_id, $conf['odc-sci'][$team]))
                $access[$community][] = $team;
        }
    }

    return $access;
}

?>
