var tickerID
function init()     {
    if (typeof sec !== "undefined") {
        clearInterval(tickerID);
    }
    sec = 0;
    tickerID = setInterval(ticking, 1000);
}

function ticking() {
    sec++;
    document.getElementById("ticking").innerText = sec;
}