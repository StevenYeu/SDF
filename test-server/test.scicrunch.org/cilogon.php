<?php 
include require_once __DIR__ . "/classes/classes.php";
\helper\scicrunch_session_start();

    function query_CILOGON($endpoint, $data) {
        $ch = curl_init();
        $http_data = http_build_query($data, '', '&');
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        // curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $http_data);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, TRUE);
    }
    $cid = 56;
    $CI_LOGON_TOKEN_URL = "https://cilogon.org/oauth2/token";
    $CI_LOGON_CLIENT_ID = "cilogon:/client_id/" . getenv("CI_LOGON_CLIENT_ID");
    $CI_LOGON_CLIENT_SECRET = getenv("CI_LOGON_SECRET");


    $CI_LOGON_CODE = $_GET["code"];
    $CI_LOGON_REDIRECT_URL = "https://sdf.sdsc.edu/auth/cilogon";
    $data = array("grant_type" => "authorization_code",
                  "client_id" =>  $CI_LOGON_CLIENT_ID,
                  "client_secret" => $CI_LOGON_CLIENT_SECRET,
                  "code" => $CI_LOGON_CODE,
                  "redirect_uri" => $CI_LOGON_REDIRECT_URL
    );
    $CI_LOGON_USER_INFO_ENDPOINT = "https://cilogon.org/oauth2/userinfo";
    $response = query_CILOGON($CI_LOGON_TOKEN_URL, $data);
    $access_token = $response["access_token"];
    $user_info_data = array("access_token" => $access_token);
    $user_info = query_CILOGON($CI_LOGON_USER_INFO_ENDPOINT, $user_info_data);
        
    
    if ($cid) {
        $community = new Community();
        $community->getByID($cid);
    }

    $user = new User();
    $user->login_with_cilogon($user_info["sub"]);

    if ($user->id && $user->verified == 2){
        $_SESSION['nonuser'] = $user;
        header("location:/verification?type=sent");
        exit;
    } elseif ((int) $user->banned === 1) {
        $error = Error::staticCreate(Array("type" => "login-fail", "message" => "This account has been disabled"));
        $previous_page = $_SERVER["HTTP_REFERER"];
        $get_delim = strpos($previous_page, "?") === false ? "?" : "&";
        header("location:" . $_SERVER["HTTP_REFERER"] . $get_delim . "errorID=" . $error->id);
        exit;
    } elseif ($user->id) {
        $_SESSION['user'] = $user;
        $_SESSION['user']->last_check = $_SESSION['user']->updateOnline();
        $_SESSION['user']->last_check = time();
        $_SESSION['user']->onlineUsers = $user->getOnlineUsers();

        $user_extra_data = UsersExtraData::getWebsiteByUser($user);
        if ($user_extra_data) {
            foreach($user_extra_data as $ued) {
                $web = $ued->value;
                $_SESSION['user']->website = $web['url'];
            }
        }
    } else {

        // Case: User has never logged on before. Need to create user entrty and mapping.
        $user_data = array("firstname" => $user_info["given_name"], "lastname" => $user_info["family_name"], "email" => $user_info["email"]);
        $new_user = new User();
        $new_user->create_with_cilogon($user_data);
        $id = $new_user->insertIntoDBCI($user_info["sub"]);
        $community->join($id, $new_user->getFullName(), 1);

        $login_user = new User();
        $login_user->login_with_cilogon($user_info["sub"]);

        $_SESSION['user'] = $login_user;
        $_SESSION['user']->last_check = $_SESSION['user']->updateOnline();
        $_SESSION['user']->last_check = time();
        $_SESSION['user']->onlineUsers = $login_user->getOnlineUsers();

        $user_extra_data = UsersExtraData::getWebsiteByUser($login_user);
        if ($user_extra_data) {
            foreach($user_extra_data as $ued) {
                $web = $ued->value;
                $_SESSION['user']->website = $web['url'];
            }
        }
    }
    
    header('location:' . $community->fullURL());

    

?>