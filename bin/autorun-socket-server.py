import subprocess
import socket
import os
import sys
import time


while 0 < 1:
    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM) #check by port about open or close program
    result = sock.connect_ex(('127.0.0.1', 8180))
    if result == 0:
        print("Port is open")
        time.sleep(30)
    else:
        pid = str(os.getpid())      #Save pid python file if you run program autorun, for kill process if you want start websocket server from consloe if you need monitoring online log
        pidfile = "mydaemon.pid"
        #if os.path.isfile(pidfile):
        #    print("%s already exists, exiting" % pidfile)
        #    sys.exit()
        open(pidfile, 'w').write(pid)
        proc = subprocess.Popen("php game-server.php", shell=True,
                                stdout=subprocess.PIPE)  # /Users/joker/Downloads/websites/site-vagrant/bin/
        script_response = proc.stdout.readline() # Start PHP WebSocket Server
        sock.close()
        time.sleep(5)
