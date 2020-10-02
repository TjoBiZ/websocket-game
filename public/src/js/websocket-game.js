document.addEventListener("DOMContentLoaded", function(event) {

    // Setting WebSocket status start

    var socket = new WebSocket("wss://echo.websocket.org");
    var status = document.getElementById('status');
    var socket_messages = document.querySelector('.socket_messages');

    socket.onopen = function(e) {
        //alert("[open] Connection established");
        //alert("Sending to server");
        socket.send("My name is Roman");
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
       socket_messages.innerHTML = `[message] Data received from server: ${event.data}`;
    };

    socket.onerror = function(error) {
        socket_messages.innerHTML = `[error] ${error.message}`;
    };

    // Settings WebSocket status stop


    document.getElementById("websocket-game").addEventListener("click", function(event){
        event.preventDefault()
        var seconds_game = document.getElementById('sec_game').value;
        var script_game = document.getElementById('script_game').value;
        socket.send(seconds_game + ' / ' + script_game);
    });

    console.log('hi');

});