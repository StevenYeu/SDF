
<table id="gc2results" class="display" cellpadding="2" cellspacing="0">

<?php
    if (isset($include_method)) {
        switch(strtolower($include_method)) {
            case "fe1_stage1":
                $title = "Free Energy Set 1 Stage 1";
//                $file = 'FE_set_1_stage_1_table.csv';
                $file = 'FE_set_1_stage_1_scoring_FE_methods.csv';
                $id = "417";
                $index = 4;
                break;
                
            case "fe1_stage2":
                $title = "Free Energy Set 1 Stage 2";
//                $file = 'FE_set_1_stage_2_table.csv';
                $file = 'FE_set_1_stage_2_scoring_FE_methods.csv';
                $id = "443";
                $index = 4;
                break;
                
            case "fe2_stage1":
                $title = "Free Energy Set 2 Stage 1";
//                $file = 'FE_set_2_stage_1_table.csv';
                $file = 'FE_set_2_stage_1_scoring_FE_methods.csv';
                $id = "417";
                $index = 5;
                break;
                
            case "fe2_stage2":
                $title = "Free Energy Set 2 Stage 2";
//                $file = 'FE_set_2_stage_2_table.csv';
                $file = 'FE_set_2_stage_2_scoring_FE_methods.csv';
                $id = "443";
                $index = 5;
                break;
                
            case "pose":
                $title = "Pose Prediction";
                $file = 'Pose_Prediction_table.csv';
                $id = "417";
                $index = 1;
                break;
                
            case "scoring_stage1":
                $title = "Scoring Stage 1";
                $file = 'Scoring_stage_1_table.csv';
                $id = "417";
                $index = 2;
                break;
                
            case "scoring_stage2":
                $title = "Scoring Stage 2";
                $file = 'Scoring_stage_2_table.csv';
                $id = "443";
                $index = 2;
                break;
        }
    }

    if ($_GET['partial'])
        $file = str_replace(".csv", "_partial.csv", $file);
    else
        $file = str_replace(".csv", "_complete.csv", $file);

    if (($handle = fopen("../spreadsheets/csv2dec/" . $file, "r")) !== FALSE) {
        $row = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            $num = count($data);
            // if row = 1, then use <th> vs <td>
            // may want to have preset css or row widths specified elsewhere ...
            if ($row == 1) {
                echo "<thead>\n<tr>\n";
                $td_or_th = "th";
                for ($c=0; $c < $num; $c++) {
                    if ($c ==3) 
                        continue;
                    elseif (($include_method == 'pose') && (($c == 6) || ($c == 7)))
                        continue;
                    else {
                        if (preg_match("/(\w* RMSD )\(.*\)/", $data[$c], $matches))
                            $data[$c] = $matches[1] . "(Å)";
                        echo "\t<" . $td_or_th . ">" . $data[$c] . "</" . $td_or_th . ">";
                    }
                }
                echo "</tr>\n</thead>\n<tbody>\n";
            } else {
                echo "<tr>\n";
                $td_or_th = "td";
                for ($c=0; $c < $num; $c++) {
                    if ($c ==3) 
                        continue;
                    else {
                        // if last cell, remove phrase method
                        $data[$num - 1] = str_replace("method", "", $data[$num - 1]);

                        // anonymous
                        if ($data[3] == 0) {
                            // if 1st cell, show download link
                            if ($c == 0) 
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../../../../file-download.php?type=usersubmissions&receipt=" . $data[$c] . "&component=" . $id . "&file-type=" . $index . "'>" . $data[$c] . "</a></" . $td_or_th . ">";
                            elseif (($c == 10) && ($include_method == 'pose'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../spreadsheets/p-software.php?receipt=" . $data[0] . "'>" . $data[10] . "</a></" . $td_or_th . ">";
                            elseif (($c == 9) && (substr($include_method, 0, 2) == 'fe'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../spreadsheets/p-software.php?receipt=" . $data[0] . "'>" . $data[9] . "</a></" . $td_or_th . ">";
                            elseif (($c == 7) && (substr($include_method, 0, 7) == 'scoring'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../spreadsheets/p-software.php?receipt=" . $data[0] . "'>" . $data[7] . "</a></" . $td_or_th . ">";
                            else {
                                if (($include_method == 'pose') && (($c == 6) || ($c == 7)))
                                    continue;

                                if (($c > 4) && is_numeric($data[$c]))
                                    echo "\t<" . $td_or_th . ">" . sprintf("%.2f", round($data[$c], 2)) . "</" . $td_or_th . ">";
                                else
                                    echo "\t<" . $td_or_th . ">" . $data[$c] . "</" . $td_or_th . ">";
                            }
                        } else {
                            if (($c == 1) || ($c == 2))
                                echo "\t<" . $td_or_th . ">&nbsp;</" . $td_or_th . ">";
                            elseif (($c == 10) && ($include_method == 'pose'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../spreadsheets/p-software.php?receipt=" . $data[0] . "'>" . $data[10] . "</a></" . $td_or_th . ">";
                            elseif (($c == 9) && (substr($include_method, 0, 2) == 'fe'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../spreadsheets/p-software.php?receipt=" . $data[0] . "'>" . $data[9] . "</a></" . $td_or_th . ">";
                            elseif (($c == 7) && (substr($include_method, 0, 7) == 'scoring'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../spreadsheets/p-software.php?receipt=" . $data[0] . "'>" . $data[7] . "</a></" . $td_or_th . ">";
                            else {
                                if (($include_method == 'pose') && (($c == 6) || ($c == 7)))
                                    continue;

                                if (($c > 4) && is_numeric($data[$c]))
                                    echo "\t<" . $td_or_th . ">" . sprintf("%.2f", round($data[$c], 2)) . "</" . $td_or_th . ">";
                                else
                                    echo "\t<" . $td_or_th . ">" . $data[$c] . "</" . $td_or_th . ">";
                            }
                        }
                    }
                }
                    echo "</tr>\n";
            }
                                echo "\n";

        }
        
        echo "</tbody>\n</table>\n";

        fclose($handle);
    }
?>
            <script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
            <script>
                
            </script>
