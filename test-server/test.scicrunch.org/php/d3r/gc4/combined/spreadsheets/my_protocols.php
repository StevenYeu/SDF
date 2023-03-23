<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

include '../../../../../classes/classes.php';
\helper\scicrunch_session_start();
?>
<html>
<head>
<title><?php echo $_GET['receipt']; ?> Protocol File(s)</title>
<!-- CSS Global Compulsory -->
<link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="/assets/css/style.css">

<style>
    h4 { margin-bottom: 0;  }
    ul, p { margin-top: 0; margin-bottom: 3; padding-left: 10px; }
    body { padding: 10px; }
</style>

</head>
<body>

<?php
    $g = new Challenge();

    $data = new Challenge_Submission();
    $ff = $data->GetProtocolsByUID( $_SESSION['user']->id, $_GET['component']);

    $load_files = array();

    foreach ($ff as $file) {
        if ($file['protocol_id'] == $_GET['receipt'])
            $load_files[] = $file['filename'];
        }

    $path = '../../../../../upload/challenges/validated';
    $folder = $_GET['component'];

    foreach ($load_files as $file) {
        $method = 0;
        $method_text = array();

        $parameters = 0;
        $parameters_text = array();

        $software = 0;
        $software_text = array();

        $content = file_get_contents($path . "/" . $folder . "/" . $file);

        echo "<h1>" . $file . "</h1>\n";
        echo "<div class='well'>\n";
        $line_array = explode("\n", $content);
        
        $checkfor['required'] = array('Name', 'Software', 'System Preparation Parameters', 
        'System Preparation Method' , 'Pose Prediction Parameters', 'Pose Prediction Method', 'Method', 'Parameter', 'Answer 1', 'Answer 2');

//            foreach ($line_array as $line) {
        for ($l=0; $l<sizeof($line_array); $l++) {
            if (substr(trim($line_array[$l]), 0 ,1) == "#")
                continue;

            // if not on the last line, then skip it. 
            // if last line, need to know it's blank to trigger it to stop reading
            if ($l == (sizeof($line_array) - 1))
                if (trim($line_array[$l]) != '')
                    continue;

            if ($method) {

                // if method found, read the rest of the lines until end
                for ($r=$l; $r<sizeof($line_array); $r++) {
                    // only keep reading until some stop flag is reached.
                    if ((stop_reading_lines($line_array[$r], $checkfor)) || ($r == (sizeof($line_array) - 1))) {
                        $l = $r-1;

                        $adjoin = implode("<br />", $method_text[$method]);
                        echo $adjoin;

                        echo "</p>\n";
                        $method = 0;
                        break;
                    }
                    
                    else {
                        $method_text[$method][] = $line_array[$r];
                        $l = $r;
                    }   
                }
            } elseif ($parameters) {
                // if method found, read the rest of the lines until end
                for ($r=$l; $r<sizeof($line_array); $r++) {
                    preg_match("/(.*?):\s*(.*)/", $line_array[$r], $match);
                    // only keep reading until some stop flag is reached.
                    if ($parameters == $match[1]) {
                        $parameters_text[$parameters][] = $match[2];
                        $l = $r+1;

                    } elseif ((stop_reading_lines($line_array[$r], $checkfor)) || ($r == (sizeof($line_array) - 1))) {
                        $l = $r-1;  // subtract so that this line can be processed again in case it's important.
                        break;
                    }
                }

                $adjoin = implode("<br />", $parameters_text[$parameters]);
                echo "<h4>" . $parameters . "</h4>\n";
                echo "<p>" . $adjoin . "</p>\n";
                $parameters = 0;

                continue;
            } elseif ($software) {
                // if method found, read the rest of the lines until end
                for ($r=$l; $r<sizeof($line_array); $r++) {
                    preg_match("/(.*?):\s*(.*)/", $line_array[$r], $match);
                    // only keep reading until some stop flag is reached.
                    if ($software == $match[1]) {
                        $software_text[$software][] = $match[2];
                        $l = $r+1;

                    } elseif ((stop_reading_lines($line_array[$r], $checkfor)) || ($r == (sizeof($line_array) - 1))) {
                        $l = $r-1;  // subtract so that this line can be processed again in case it's important.
                        break;
                    }
                }

                $adjoin = implode("<br />", $software_text[$software]);
                echo "<h4>" . $software . "</h4>\n";
                echo "<p>" . $adjoin . "</p>\n";
                $software = 0;

                continue;
            } else {

                    preg_match("/(.*?):\s*(.*)/", $line_array[$l], $match);
                    if (stripos($match[1], "parametsdfger") !== false) {
                        /* keep reading lines until next line is not parameter ...*/
                        for ($r=$l; $r<sizeof($line_array); $r++) {
                     //   echo "<br /><strong>Reading: </strong>" . $line_array[$l] ;
                            preg_match("/(.*?):\s*(.*)/", $line_array[$l], $match);
                        $parameters = $match[1];
                            if ((stop_reading_lines($line_array[$r], $checkfor)) || ($r == (sizeof($line_array) - 1))) {
                                $l = $r-1;

                                $adjoin = implode("<br />", $parameters_text[$parameters]);
                                echo "<h4>" . $parameters . "</h4>\n";
                                echo "<p>" . $adjoin . "</p>\n";
//                                    break;
                            } else {
                                $parameters_text[$parameters][] = $match[2];
                                $l = $r;
                            }
                        }   
                    } elseif (stripos($match[1], "parameter") !== false) {
                        $parameters = $match[1];
                        $parameters_text[$parameters][] = $match[2];
                    } elseif (stripos($match[1], "software") !== false) {
                        $software = $match[1];
                        $software_text[$software][] = $match[2];
                    } elseif (stripos($match[1], "method") !== false) {
                        echo "<h4>" . $match[1] . "</h4>\n";
                        $method = $match[1];
                        if (trim($match[2]))
                            $method_text[$method][] = $match[2];


                        // if last line and method text is on that line, output method info
                        if ($l == (sizeof($line_array) - 1)) {
                            echo "<p>\n";
                            echo $match[2];
                            echo "</p>\n";
                        // else open the <p> and start loop to grab method info
                        } else {
                            echo "<p>\n";
                        }

                    } else {
                        echo "<h4>" . $match[1] . "</h4>\n";
                        echo "<p>" . $match[2] . "</p>\n";
                    }
                    
            }
        }
        echo "</div>\n";
    }
?>        
