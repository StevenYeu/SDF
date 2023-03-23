<html>
<head>


        <!-- CSS Global Compulsory -->
        <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="/assets/css/style.css">

        <!-- CSS Implementing Plugins -->
        <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
        <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">

        <!-- CSS Theme -->
        <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
        <!-- CSS Theme -->
        <link rel="stylesheet" href="/assets/css/pages/blog.css">
        <link rel="stylesheet" href="/assets/css/custom.css">
        <link rel="stylesheet" href="/assets/plugins/summernote/summernote.css"/>
        
<style>
    h4 { margin-bottom: 0;  }
    ul, p { margin-top: 0; margin-bottom: 3; padding-left: 10px; }
    body { padding: 10px; }
</style>
</head>
<body>

<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

include '../../../../classes/classes.php';
//    \helper\scicrunch_session_start();
$g = new Challenge();
        $data = new Challenge_Submission();
        $data->GetUserInfoFromReceipt($_GET['receipt']);
        $sub = $data->getSubmissionFromReceipt($_GET['receipt'], $data->uid);
        
//        var_dump($sub);
        if ($sub['component'] == '417')
            $stage = 'stage1';
        elseif ($sub['component'] == '443')
            $stage = 'stage2';

        $folder0 = "all_" . $stage . "_protocols";
        $folder = 'GC2_' . $stage . "_";
        $load_files = array();

        switch ($sub['type']) {
            case "pose":
                $folder .= "Pose_Predictions";
                $load_files[] = $_GET['receipt'] . '-PosePredictionProtocol.txt';
                break;

            case "scoreligand":
                $folder .= "Ligand-Based_Scoring";
                $load_files[] = $_GET['receipt'] . '-LigandScoringProtocol.txt';
                $load_files[] = $_GET['receipt'] . '-PosePredictionProtocol.txt';
                break;

            case "scorestructure":
                $folder .= "Structure-Based_Scoring";
                $load_files[] = $_GET['receipt'] . '-PosePredictionProtocol.txt';
                break;

            case "freeenergy1":
                $folder .= "Free_Energy_Set_1";
                $load_files[] = $_GET['receipt'] . '-FreeEnergyProtocol.txt';
                $load_files[] = $_GET['receipt'] . '-PosePredictionProtocol.txt';
                break;

            case "freeenergy2":
                $folder .= "Free_Energy_Set_2";
                $load_files[] = $_GET['receipt'] . '-FreeEnergyProtocol.txt';
                $load_files[] = $_GET['receipt'] . '-PosePredictionProtocol.txt';
                break;
        }
        $folder .= "_protocol";

        $path = '/Users/mchiu/Documents/D3R/_challenges/gc2016/_postchallenge';
        foreach ($load_files as $file) {
            $method = 0;
            $method_text = array();

            $parameters = 0;
            $parameters_text = array();

            $content = file_get_contents($path . "/" . $folder0 . "/" . $folder . "/" . $file);
            echo "<h1>" . $file . "</h1>\n";
            echo "<div class='well'>\n";
            $line_array = explode("\n", $content);
            
            $checkfor['required'] = array('Name', 'Software', 'System Preparation Parameters', 
            'System Preparation Method' , 'Pose Prediction Parameters', 'Pose Prediction Method', 'Method', 'Parameter');

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
//var_dump($l);
//var_dump(sizeof($line_array));
                    continue;
                } else {
/* would be nice to group parameters together ...
maybe if field has "parameter" in it, then keep grabbing lines until we have all parameters.
then show those with <ul>
*/
//                        echo "<strong>wwww Te4sting: </strong>" . $line_array[$l] . "<br />\n";
//var_dump($l);
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
                        
    //                    var_dump($match);
                }
            }
            echo "</div>\n";
//            echo str_replace("\n", "<br />\n", $content);
        }
?>        
<!--
// using the receipt, load the protocol file info and webify ...

GC2_stage1_Free_Energy_Set_1_protocol
GC2_stage1_Free_Energy_Set_2_protocol
GC2_stage1_Pose_Predictions_protocol
GC2_stage1_Structure-Based_Scoring_protocol
GC2_stage1_Ligand-Based_Scoring_protocol

fe1/2_stage1
5bvwx-FreeEnergyProtocol.txt
5bvwx-PosePredictionProtocol.txt

pose
cfn8u-PosePredictionProtocol

structure
c0l1t-LigandScoringProtocol.txt
c0l1t-PosePredictionProtocol.txt

ligand
kz0dz-LigandScoringProtocol

-->