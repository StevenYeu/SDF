<?php

$row = 1;
if (($handle = fopen("Stage1b_website_plots.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
//        echo "<p> $num fields in line $row: <br /></p>\n";
        $row++;
//        for ($c=0; $c < $num; $c++) {
            $split[$data[0]][$data[1]][] = $data[3];
//        }
    }
    fclose($handle);
}

//var_dump(array_keys($split));

foreach (array_keys($split) as $receipt) {
/*
    if ($receipt !== '5u63s')
        continue;
*/
    foreach (array_keys($split[$receipt]) as $ligand) {
        $test = array();
        foreach ($split[$receipt][$ligand] as $rnd) {
            $pvalue = $split[$receipt][$ligand][$rnd];
            if (in_array($pvalue, $test))
                echo "Bad pose ". $receipt  . "/" . $ligand . "/ is bad\n";
            else 
                $test[] = $pvalue;
        }
/*  

       if (sizeof(array_keys($split[$receipt][$ligand])) > 1)
            echo "error with " . $receipt  . "/" . $ligand . "/ is bad\n";
            echo sizeof(array_keys($split[$receipt][$ligand]));
            exit;

        if ((sizeof(array_keys($split[$receipt][$ligand]))) > 4)
            echo $receipt  . "/" . $ligand . "/ is bad\n";
    }
*/            
  //  exit;
  }
}