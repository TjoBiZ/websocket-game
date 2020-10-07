<?php

//$msg = '{"sec_game":"19","game":{"0":"","2":"Australia","2":"Mexico","5":"","6":"","7":"Australia","8":"","9":"Brazil","10":"Germany","11":"India","12":"United Kingdom","13":"Canada","14":"","15":"United States","16":"Australia","17":"Canada","18":"Australia","19":""}}';

$msg = '{"sec_game":"18","game":{"1":["Canada"],"3":["Estonia<?php info(); ?>","Japan","<b>Australia</b>"],"4":["Germany"],"5":["India"],"6":["China"],"7":["United Kingdom"],"8":["Canada"],"10":["United States"],"11":["Australia"],"12":["Canada"],"13":["Australia"]}}';

    $msg2 = json_decode($msg, true);
    array_walk_recursive($msg2, 'validator_json_order'); //Step check validate post data for hacker attack
    $response_game = [];
    $response_game['block_btn'] = 'true';
    $response_game['data'] = 'The game stared...';
    $response_game_json = json_encode($response_game);
    $time_game = $msg2["sec_game"];
    $plan_game = $msg2["game"];
    $sec = 0;
    $result_game_web = [];
    $result_game_log_file = [];
    /**
     * END logic prepare start data and function for send data to frontend
     */

    while ($sec <= $time_game) {
            // Events for send processing game
        if (isset($msg2["game"][$sec])) { //If this second exist plan game then start logic output processing game
            foreach ($msg2["game"] as $k => $v) {
                asort($v);
                $v = array_values($v);
                $r01[$k] = $v;
            }
            $r01 = array_filter($r01, function ($k) use ($sec){
                return $k <= $sec;
            }, ARRAY_FILTER_USE_KEY);
            gaming($r01, $response_game, false);
            foreach ($r01[$sec] as $k => $v) { //For log file time got goal
                $result_game_log_file[$sec] .=  $v . ' '; //History time goals log
            }
        }
        //sleep(1);
        if ($sec == $msg2["sec_game"])  { break; } else { $sec++; };
    }


    function gaming($r01, $response_game, $call_finish){
        $r01_web = '';
        $now_goals = [];
        foreach ($r01 as $key_c => $item) {
            foreach ($item as $country) {
                array_push($now_goals, $country);
            }
        }
        $now_goals = array_count_values($now_goals);
        ksort($now_goals);
        arsort($now_goals);
        if ($call_finish == true) { return $now_goals; }
        foreach ($now_goals as $country => $goals) {
            $r01_web .= $goals . ' ' . $country . '<br>';
        }
        $output_web = <<<HERE
    The game is processing now, you can watch it.<br><br>Game scores at this moment:<br><br>$r01_web;
    HERE;
        //Send to websocket json format
        $response_game['data'] = $output_web;
        echo json_encode($response_game);
    }

    function finish_game($r01, $r02, $sec_game, $msg2, $response_game) {
        $accum = [];
        $win = false;
        $win_list = $r01_web = $r01_log = $r02_web = $r02_log = '';
        $count = '0';
        $goals = gaming($r01, $response_game, true);
        foreach ($goals as $k => $v) {
            if ($count == 0) {
                array_push($accum, $v);
                $win_list = $k;
                $win = true;
            } else {
                $key = array_key_last($accum);
                if ($v == $accum[$key]) {
                    array_push($accum, $v);
                    $win_list = $win_list . ', ' . $k;
                    $win = false;
                }
            }
            $count++;
        }

        foreach ($goals as $key_c => $item) {
            $r01_web .= $item . ' ' . $key_c . '<br>';
            $r01_log .= $item . ' ' . $key_c . "\n";
        }
        foreach ($r02 as $key_sec => $item_goals) {
            $r02_web .= $item_goals . ' scored a goal at ' . $key_sec . ' seconds ' . '<br>';
            $r02_log .= $item_goals . ' scored a goal at ' . $key_sec . ' seconds ' . "\n";
        }

        if ($win) {
            $seconds_goal = [];
            foreach ($msg2["game"] as $second =>$arr) {
                foreach ($arr as $k => $v) {
                    if ($v == $win_list) {
                        array_push($seconds_goal, $second);
                    }
                }
            }
            $last_second = max($seconds_goal);
            $output_web = <<<HERE
        In a game of $sec_game seconds...<br><br>$win_list wins, having taken the lead at $last_second seconds!<br><br>The final scores:<br><br>$r01_web<br>=================<br>Time GOAL history:<br><br>$r02_web<br>
HERE;
            $output_log_file = <<<HERE
    In a game of $sec_game seconds...
    
    $win_list wins, having taken the lead at $last_second seconds!
    
    The final scores:
    
    $r01_log
    
    =================
    Time GOAL history:
    
    $r02_log
    HERE;

        } else {
            $output_web = <<<HERE
        In a game of $sec_game seconds...<br><br>$win_list going to final.<br><br>The final scores:<br><br>$r01_web<br>=================<br>Time GOAL history:<br><br>$r02_web<br>
        HERE;
            $output_log_file = <<<HERE
    In a game of $sec_game seconds...
    
    $win_list going to final.
    
    The final scores:
    
    $r01_web
    
    =================
    Time GOAL history:
    
    $r02_log
    HERE;
        }

        //Put into log file
//        $fp=fopen('history_game.log',"a");
//        fwrite($fp, "\r\n" . $output_log_file);
//        fclose($fp);
        //Send to websocket json format
        $response_game['block_btn'] = 'false';
        $response_game['data'] = $output_web;
        echo json_encode($response_game);

    }

/**
 * Check validate data in json array for hack XSS and delete enter(next row "\n") but change it to <br>.
 * array_walk_recursive($data_form, array($this, 'validator_json_order')); //Step check validate post data for hack with OOP
 */
    function validator_json_order(&$val,  $key)
    {
        if (!($val === null || $val === true || $val === false || $val === '' || $val === 'undefined')) {
            $val = htmlspecialchars(strip_tags($val));
            $key = htmlspecialchars(strip_tags($key));
        }
    }


    //This place Event Finish game.
    $r02 = $result_game_log_file;
    finish_game($r01, $r02, $msg2["sec_game"], $msg2, $response_game);