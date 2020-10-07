document.addEventListener("DOMContentLoaded", function(event) {

    // Setting WebSocket status start

    //var socket = new WebSocket("wss://echo.websocket.org"); // Test WebSocket connection for another server
    var socket = new WebSocket("wss://site.loc/wss2/:8180"); // SSL HTTPS WebSocket

    //var socket = new WebSocket("ws://site.loc:8180"); //HTTP WebSocket

    var status = document.getElementById('status');
    var socket_messages = document.querySelector('.socket_messages');

    socket.onopen = function(e) {
        //alert("[open] Connection established");
        //alert("Sending to server");
        var send = {
            block_btn: false,
            data: "Pls, put here your script game and submit"
        };
        //socket.send(JSON.stringify(send));
        status.innerHTML = "Connection established";
    };

    socket.onclose = function(event) {
        if (event.wasClean) {
            status.innerHTML = '[close] Connection closed cleanly, code=' + event.code + 'reason=' + event.reason;
        } else {
            // e.g. server process killed or network down
            // event.code is usually 1006 in this case
            status.innerHTML = '[close] Connection died';
        }
    };

    socket.onmessage = function(event) {
        clearInterval(tickerID); //Stop clock ticking
        get_data= JSON.parse(event.data)
        socket_messages.innerHTML = '[message] Data received from server:' + get_data.data;// + $event.data;
        document.getElementById('websocket-game').classList.remove('disabled');
        document.getElementById('websocket-game').textContent = 'Start game';
    };

    socket.onerror = function(error) {
        socket_messages.innerHTML = '[error] ' + error.message;
    };

    // Settings WebSocket status stop

    var websocketgame = document.getElementById("websocket-game");

        websocketgame.addEventListener("click", function(event){
        event.preventDefault();
            document.getElementById('throwformatsec').innerHTML = document.getElementById('throwformatscript').innerHTML = '';
            //validation seconds input
        var patt = /[1-9]{1}\d{0,2}/i;
        s = document.getElementById('sec_game').value;
        var n = s.match(patt);
        if (n === null || n[0] !== s) {
            document.getElementById('throwformatsec').innerHTML = '<p style="color:red;">Wrong format, pls enter numbers 1-999!</p>';
            throw 'The Seconds Input has not correct value. You should write 1-999';
        }
        var plan_game = {}
        plan_game['sec_game'] = n[0];
            //validation script game input
        var patt = /(\d|\w|\s)+/i;
        s = document.getElementById('script_game').value;
        var n = s.match(patt);
        if (n === null || n[0] !== s) {
                document.getElementById('throwformatscript').innerHTML = '<p style="color:red;">Wrong format, pls enter numbers and letters!</p>';
                throw 'The Script Input has not correct value';
        }
        document.getElementById('websocket-game').classList.add('disabled');
            document.getElementById('websocket-game').textContent = 'Waiting';
        var arr_script = document.getElementById('script_game').value.split('\n');
            //Prepear array for send to
            var game = {};
            //this.one_second = []; //One second more the one command
            //this.one_second_countries = []; //One second more Countries
            this.arr_script = arr_script;
            arr_script.forEach(function (currentValue , index, array) {

                //Delete all space rows! Regular expression
                var patt = /^\s+$/i;
                var check_space = currentValue.match(patt);
                if (check_space !== null && check_space[0] === currentValue) {
                    currentValue = '';
                }

                if (currentValue !== '') {
                    patt = /^\d+/i;
                    var key_game = currentValue.match(patt);
                    patt = /(?<=^\d+\s).*/i;
                    var value_game = currentValue.match(patt);
                    if (value_game !== null) {
                        patt = /^\s+$/i;
                        check_space = value_game[0].match(patt);
                        if (check_space !== null && check_space[0] === value_game[0]) {
                            value_game[0] = '';
                        }
                    }
                    if (value_game === null) {
                        value_game = '';
                    } else {
                        var group_one_sec = []
                        function one_second_teams(value) {
                            patt = /^\d+/i;
                            var sec = value.match(patt);
                            if (sec !== null && sec[0] === key_game[0]) {
                                patt = /(?<=^\d+\s).*/i;
                                var band_country= value.match(patt);
                                if (band_country !==null) {
                                    group_one_sec.push(band_country[0]);
                                }
                            }
                        }
                        this.arr_script.filter(one_second_teams);
                    }
                    if (value_game !== null && value_game[0] !=0) {
                        game[key_game[0]] = group_one_sec;
                    }
                }
            }, this);
        plan_game['game'] = game;
        var json_game = JSON.stringify(plan_game);
        socket.send(json_game);
        init(); //clock ticking
    });

    console.log( '%c     %cWebSocket game', 'width: 100%', "text-align: center; color:white;font-family:system-ui;font-size:2rem;-webkit-text-stroke: 1px black;font-weight:bold" );

});