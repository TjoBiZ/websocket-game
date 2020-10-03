function init()
{
    sec = 0;
    setInterval(ticking, 1000);
}

function ticking()
{
    sec++;
    document.getElementById("ticking").innerText = sec;
}