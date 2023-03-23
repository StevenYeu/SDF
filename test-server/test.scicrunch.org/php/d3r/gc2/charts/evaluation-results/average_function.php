<?php

    function get_average_data($vars, $chart, $max_compounds) {
        $compound_count = 0; // count how many ligands processed (max = 6)

        $pose1_count = 0;	// count how many pose1 values processed
        $pose1_sum = 0;		// aggreggate sum of pose 1 values

        $starting_min_sum = 0; // aggreggate sum of best pose values

        $avg_aggregate = 0;
        foreach($vars as $key=>$value) {
        	// **** skip ligand 33 **** 
        	if ($key == 33)
        		continue;

        	$compound_count++;
        	//var_dump($key); // 44, 40, etc
            //print_r($value);	//py/tuple .... 
            // for this $key, we want $value->{"py/tuple"}
            
            // since 5 poses
            $pose_count = 0;
            $pose_sum = 0;
            

            
            
            /* 	each $value is the set of pose values for a ligand 
            	from the pickle to json conversion the data has 'py/tuple'
            	so, the following foreach loop processes each pose value */
            
            foreach ($value as $pytuple) { 
            	$posedata = $pytuple->{"py/tuple"};
            //	var_dump($posedata);
            	$pose_count++;
            	$pose_sum += $posedata[0];
            	
            	// handles the pose1 average stuff
            	if ($posedata[1] == 1) {
            		$pose1_count++;
            		$pose1_sum += $posedata[0];
            	}

            	// handles the best average stuff
            	// save first pose value as the min, and then compare subsequent ones against.            		
            	if ($pose_count == 1)
            		$starting_min = $posedata[0];
            	else
            		$starting_min = min($starting_min, $posedata[0]);
        	}

        	$avg_aggregate += $pose_sum/$pose_count;
        	/*
            var_dump($pose_sum);
        	var_dump($pose_count);
        	var_dump($pose_sum/$pose_count);
        	var_dump($avg_aggregate);
            */

        	$starting_min_sum += $starting_min; // save the starting min for the average
        }

        $obj = new stdClass();
        switch ($chart) {
            case "pose":
                $obj->avg = $pose1_sum/$pose1_count;
                if ($pose1_count < $max_compounds)
                    $obj->lessthan = TRUE;

                break;                

            case "best":
                $obj->avg = $starting_min_sum/$compound_count;
                if ($compound_count < $max_compounds)
                    $obj->lessthan = TRUE;

                break;                

            case "avg":
                $obj->avg = $avg_aggregate/$compound_count;
                if ($compound_count < $max_compounds)
                    $obj->lessthan = TRUE;

                break;                
        }
        return $obj;
    }
