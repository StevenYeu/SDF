<?php
    $search_url = Connection::environment() . "/v1/federation/search.xml";
    $xml_string = Connection::multi(Array($search_url));
    $xml = simplexml_load_string($xml_string[0]);

    $views = Array();
    foreach($xml->result->results->result as $res) {
        $nifid = (string) $res->attributes()->nifId;
        if(isset($views[$nifid])) continue;

        $viewname = ((string) $res->attributes()->db) . ": " . ((string) $res->attributes()->indexable);
        $views[$nifid] = Array("name" => $viewname, "status" => 0);
    }

    foreach($views as $viewid => &$view) {
        $url = Connection::environment() . "/v1/federation/data/" . $viewid . ".xml?q=test";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $view["url"] = $url;
        $view["http_code"] = $http_code;
    }

    uasort($views, function($a, $b) {
        if($a["http_code"] == $b["http_code"]) return 0;
        if($a["http_code"] != 200 && $b["http_code"] == 200) return -1;
        if($a["http_code"] == 200 && $b["http_code"] != 200) return 1;
        if($a["http_code"] > $b["http_code"]) return -1;
        if($a["http_code"] < $b["http_code"]) return 1;
        return 0;
    });
?>

<div class="tab-pane fade in active">
    <div class="table-search-v2 margin-bottom-20">
        <p>
            These are the HTTP statuses for views when running the query "test".  If the view returns a status other than 200, then something is wrong with the view and it needs to be investigated.
        </p>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>View name</th>
                    <th>View ID</th>
                    <th>URL</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($views as $viewid => $view): ?>
                    <tr>
                        <th><?php echo $view["name"] ?></th>
                        <th><?php echo $viewid ?></th>
                        <th><a href="<?php echo $view["url"] ?>"><?php echo $view["url"] ?></a></th>
                        <th <?php if($view["http_code"] != 200): ?>style="color:white;background-color:red"<?php endif ?>><?php echo $view["http_code"] ?></th>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
