<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

        <title>D3R | GC2 Pose Prediction Results</title>

        <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
    </head>

    <body>

        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css" />
<script src="https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
$('#gc2results').dataTable( {
  "pageLength": 10} );
} );
</script>

<!--<style>
.dataTables_wrapper .dataTables_filter {
float: right;
text-align: right;
visibility: hidden;
}
</style>
-->
<a href="index.php?method=fe1_stage1">fe1_stage1</a> ||
<a href="index.php?method=fe2_stage1">fe2_stage1</a> ||
<a href="index.php?method=fe1_stage2">fe1_stage2</a> ||
<a href="index.php?method=fe2_stage2">fe2_stage2</a> ||
<a href="index.php?method=pose">pose</a> ||
<a href="index.php?method=scoring_stage1">scoring_stage1</a> ||
<a href="index.php?method=scoring_stage2">scoring_stage2</a> ||

<table id="gc2results" class="display" cellpadding="2" cellspacing="0">

<?php
    if (isset($_GET['method'])) {
        switch(strtolower($_GET['method'])) {
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

    echo "<h1>" . $title . "</h1>\n";

    if (($handle = fopen("csv/" . $file, "r")) !== FALSE) {
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
                    else
                        echo "\t<" . $td_or_th . ">" . $data[$c] . "</" . $td_or_th . ">";
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

                        if ($data[3] == 0) {
                            // if 1st cell, show download link
                            if ($c == 0)
                                echo "\t<" . $td_or_th . "><a target='_blank' href='../../../file-download.php?type=usersubmissions&receipt=" . $data[$c] . "&component=" . $id . "&file-type=" . $index . "'>" . $data[$c] . "</a></" . $td_or_th . ">";
                            elseif (($c == 9) && (substr($_GET['method'], 0, 2) == 'fe'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='p-software.php?receipt=" . $data[0] . "'>" . $data[9] . "</a></" . $td_or_th . ">";
                            elseif (($c == 7) && (substr($_GET['method'], 0, 2) != 'fe'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='p-software.php?receipt=" . $data[0] . "'>" . $data[7] . "</a></" . $td_or_th . ">";
                            else
                                echo "\t<" . $td_or_th . ">" . $data[$c] . "</" . $td_or_th . ">";
                        } else {
                            if (($c == 1) || ($c == 2))
                                echo "\t<" . $td_or_th . ">&nbsp;</" . $td_or_th . ">";
                            elseif (($c == 9) && (substr($_GET['method'], 0, 2) == 'fe'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='p-software.php?receipt=" . $data[0] . "'>" . $data[9] . "</a></" . $td_or_th . ">";
                            elseif (($c == 7) && (substr($_GET['method'], 0, 2) != 'fe'))
                                echo "\t<" . $td_or_th . "><a target='_blank' href='p-software.php?receipt=" . $data[0] . "'>" . $data[7] . "</a></" . $td_or_th . ">";
                            else
                                echo "\t<" . $td_or_th . ">" . $data[$c] . "</" . $td_or_th . ">";
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

</body>
</html>
