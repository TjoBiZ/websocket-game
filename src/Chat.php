<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\LoopInterface;


class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's'); //Send to console server WebSocket $msg

        foreach ($this->clients as $client) {
            //if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg); //Send to WebBrowser
            //}
            /** ================================================================================================ **/
            $loop = \React\EventLoop\Factory::create();
            $loop->addTimer(0, function () use ($msg, $client) {
            //$msg = '{"sec_game":"19","game":{"0":"","2":"Australia","4":"Mexico","5":"","6":"","7":"Australia","8":"","9":"Brazil","10":"Germany","11":"India","12":"United Kingdom","13":"Canada","14":"","15":"United States","16":"Australia","17":"Canada","18":"Australia","19":""}}';
                $msg2 = json_decode($msg, true);
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
                    if (isset($plan_game[$sec]) && $plan_game[$sec] != "") {
                        // Events for send processing game
                        if (!isset($result_game_web[$msg2["game"][$sec]])) {
                            $result_game_web[$msg2["game"][$sec]] = 1;
                            $r01 = $result_game_web;
                            arsort($r01);
                            $this->gaming($r01, $response_game, $client);
                        } else {
                            $result_game_web[$msg2["game"][$sec]]++;
                            $r01 = $result_game_web;
                            arsort($r01);
                            $this->gaming($r01, $response_game, $client);
                        }
                        if (!isset($result_game_log_file[$sec])) {
                            $result_game_log_file[$sec] =  $plan_game[$sec] . ':' . $result_game_web[$msg2["game"][$sec]];
                        }
                    }
                    sleep(1);
                    if ($sec == $msg2["sec_game"])  { break; } else { $sec++; };
                }

                //This place Event Finish game.
                $r02 = $result_game_log_file;
                $this->finish_game($r01, $r02, $msg2["sec_game"], $msg2, $response_game, $client);
                echo "After timer\n";
            });

            echo "Before timer\n";

          $loop->run();
            /** ================================================================================================ **/


        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }




    public function gaming($r01, $response_game, $client){
        $r01_web = '';
        foreach ($r01 as $key_c => $item) {
            $r01_web .= $item . ' ' . $key_c . '<br>';
        }
        $output_web = <<<HERE
    The game is processing now, you can watch it.<br><br>Game scores at this moment:<br><br>$r01_web;
    HERE;
        //Send to websocket json format
        $response_game['data'] = $output_web;
        echo json_encode($response_game);
        $client->send(json_encode($response_game));
    }

    public function finish_game($r01, $r02, $sec_game, $msg2, $response_game, $client) {
        $accum = [];
        $win = false;
        $win_list = $r01_web = $r01_log = $r02_web = $r02_log = '';
        $count = '0';
        foreach ($r01 as $k => $v) {
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

        foreach ($r01 as $key_c => $item) {
            $r01_web .= $item . ' ' . $key_c . '<br>';
            $r01_log .= $item . ' ' . $key_c . "\n";
        }
        foreach ($r02 as $key_sec => $item_goals) {
            $r02_web .= $item_goals . ' scored a goal at ' . $key_sec . ' seconds ' . '<br>';
            $r02_log .= $item_goals . ' scored a goal at ' . $key_sec . ' seconds ' . "\n";
        }

        if ($win) {
            $seconds_goal = [];
            foreach ($msg2["game"] as $key_s =>$value) {
                if ($value == $win_list) {
                    array_push($seconds_goal, $key_s);
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
        $fp=fopen('history_game.log',"a");
        fwrite($fp, "\r\n" . $output_log_file);
        fclose($fp);
        //Send to websocket json format
        $response_game['block_btn'] = 'false';
        $response_game['data'] = $output_web;
        echo json_encode($response_game);
        $client->send(json_encode($response_game));

    }




}