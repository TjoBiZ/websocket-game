document.addEventListener("DOMContentLoaded", function(event) {

    // Setting WebSocket status start

    //var socket = new WebSocket("wss://echo.websocket.org"); // Test WebSocket connection for another server
    var socket = new WebSocket("wss://site.loc/wss2/:8080"); // SSL HTTPS WebSocket

    //var socket = new WebSocket("ws://site.loc:8080"); //HTTP WebSocket

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
            status.innerHTML = `[close] Connection closed cleanly, code=${event.code} reason=${event.reason}`;
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

    };

    socket.onerror = function(error) {
        socket_messages.innerHTML = `[error] ${error.message}`;
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
        var one_second = '';
        plan_game['sec_game'] = n[0];
            //validation script game input
        var patt = /(\d|\w|\s)+/i;
        s = document.getElementById('script_game').value;
        var n = s.match(patt);
        if (n === null || n[0] !== s) {
                document.getElementById('throwformatscript').innerHTML = '<p style="color:red;">Wrong format, pls enter numbers and letters!</p>';
                throw 'The Script Input has not correct value';
        }
        var arr_script = document.getElementById('script_game').value.split('\n');
            //Prepear array for send to
            var game = {};
            arr_script.forEach(function (currentValue , index, array) {
                if (currentValue !== '') {
                    var patt = /^\d+/i;
                    var key_game = currentValue.match(patt);
                    var patt = /(?<=^\d+\s).*/i;
                    value_game = currentValue.match(patt);
                    if (value_game === null) {
                        value_game = '';
                    } else {
                        value_game = value_game[0];
                    }
                    game[key_game[0]] = value_game;
                }
            });
        plan_game['game'] = game;
        var json_game = JSON.stringify(plan_game);
        socket.send(json_game);
        init(); //clock ticking
    });

    console.log( '%c     %cWebSocket game', 'width: 100%', "text-align: center; color:white;font-family:system-ui;font-size:2rem;-webkit-text-stroke: 1px black;font-weight:bold" );

});