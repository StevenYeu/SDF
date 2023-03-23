<?php

    // $docroot = "..";
    // require_once $docroot . "/classes/classes.php";
    // require_once $docroot . "/classes/connection.class.php";
    // require_once $docroot . "/classes/term.class.php";

    $community = new Community();
    $cxn = new Connection();
    $cxn->connect();
    $results = $cxn->select("term_notifications", Array("*"), "", Array(), "where status=1");

    $term_notifications = Array();
    foreach ($results as $result) {
        $term_notifications[$result['uid']][] = $result;
    }

    foreach ($term_notifications as $uid => $notifications) {
        $html_rows = Array();
        foreach($notifications as $term_notification) {
            if($term_notification['send_notification'] && time() >= $term_notification['next_notification_time']) {
                /* check term updates */
                $term_updates = $cxn->select("term_update_logs", Array("*"), "sii", Array($term_notification['ilx'], $term_notification['last_notification_time'], time()), "where ilx=? and update_time>? and update_time<=? order by id");

                switch ($term_notification["update_type"]) {
                    case "daily":
                        $next_notification_time = strtotime("+1 day");
                        break;

                    case "weekly":
                        $next_notification_time = strtotime("+5 day");
                        break;

                    case "monthly":
                        $next_notification_time = strtotime("+28 day");
                        break;
                }

                /* check children updates */
                $children_updates = Array();
                if($term_notification['follow_children']) {
                    $search_manager = ElasticInterLexManager::managerByViewID("interlex");
                    $search_results = $search_manager->searchChildren($term_notification['ilx']);
                    foreach ($search_results as $result) {
                        $updates = $cxn->select("term_update_logs", Array("*"), "sii", Array($result->getField("ID"), $term_notification['last_notification_time'], time()), "where ilx=? and update_time>? and update_time<=? order by id");
                        if(count($updates) > 0) {
                            $children_updates[] = Array(
                                  "name" => $result->getField("Name"),
                                  "ilx" => $result->getField("ID"),
                                  "updates" => $updates,
                            );
                        }
                    }
                }

                if(count($term_updates) == 0 && count($children_updates) == 0)
                    $cxn->update("term_notifications", "ii", Array("next_notification_time"), Array($next_notification_time, $term_notification['id']), "where id=?");
                else if(count($term_updates) > 0)
                    $cxn->update("term_notifications", "iiii", Array("last_updated_time", "last_notification_time", "next_notification_time"), Array($term_updates[count($term_updates)-1]['update_time'], time(), $next_notification_time, $term_notification['id']), "where id=?");
                else
                    $cxn->update("term_notifications", "iii", Array("last_notification_time", "next_notification_time"), Array(time(), $next_notification_time, $term_notification['id']), "where id=?");

                if(count($term_updates) > 0 || count($children_updates) > 0) {
                    $html_rows[] = build_email_notification($term_updates, $children_updates, $term_notification['label'], $term_notification['ilx'], $term_notification['update_type'], $community->fullURL());
                }
            }
        }

        if(count($html_rows)) {
            $user = $cxn->select("users", Array("firstName", "email"), "i", Array($uid), "where guid=?")[0];
            send_email_notification($html_rows, $user['email'], $user['firstName'], $community->fullURL());
        }
    }
    $cxn->close();

    /******************************************************************************************************************************************************************************************************/

    function build_email_notification($term_updates, $children_updates, $term_name, $ilx, $update_type, $base_url) {
        /* build term updates email */
        if(count($term_updates) > 0) {
            $html_body .= '<p>
                              We found update(s) for the term <a href="'.$base_url.'/interlex/view/'.$ilx.'">'.$term_name.'</a> in InterLex ('.$update_type.' update).
                              &nbsp;&nbsp;&nbsp;&nbsp
                              <a href="'.$base_url.'/interlex/view/'.$ilx.'">
                                  <button style="background:#b8f0ff">View '.$term_name.'</button>
                              </a>
                          </p>';
            $html_body .= '<ul>';
            usort($term_updates, function($a, $b) {return strcmp($a["changed_type"], $b["changed_type"]);});
            foreach ($term_updates as $update) {
                $html_body .= '<li>'.$update['term_label'].' '.$update['changed_type'].'. ('.$update['changed_des'].')</li>';
            }
            $html_body .= '</ul>';
        }

        /* build children updates email */
        if(count($children_updates) > 0) {
            $html_body .= '<p>We found additional update(s) for the children of term <a href="'.$base_url.'/interlex/view/'.$ilx.'">'.$term_name.'</a></p>';
            $html_body .= '<ul>';
            usort($children_updates, function($a, $b) {return strcmp($a["name"], $b["name"]);});
            foreach ($children_updates as $updates) {
                $key++;
                $html_body .= '<li><a href="'.$base_url.'/interlex/view/'.$updates['ilx'].'">'.$updates['name'].'</a></li>';
                $html_body .= '<ul>';
                usort($updates['updates'], function($a, $b) {return strcmp($a["changed_type"], $b["changed_type"]);});
                foreach ($updates['updates'] as $children_update) {
                    $html_body .= '<li>'.$children_update['term_label'].' '.$children_update['changed_type'].'. ('.$children_update['changed_des'].')</li>';
                }
                $html_body .= '</ul>';
            }
            $html_body .= '</ul>';
        }

        return $html_body;
    }

    function send_email_notification($html_rows, $email, $first_name, $base_url) {
        $text_rows = Array();
        $html_rows[0] = '<div class="container"><h2>InterLex Terminology Portal</h2>' .
                        '<p>Hello '.$first_name.',</p>' . $html_rows[0];
        $html_rows[] = '<p>To edit your InterLex notifications please visit your dashboard page: <a href="'.$base_url.'/account/notifications">'.$base_url.'/account/notifications</a></P></div>';
        foreach ($html_rows as $row) {
            $text_rows[] = strip_tags($row);
        }
        $text_message = implode("\n", $text_rows);
        $html_message = \helper\buildEmailMessage($html_rows, 3);
        \helper\sendEmail($email, $html_message, $text_message, "InterLex Updates");
    }

?>
