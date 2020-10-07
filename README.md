Copy to your server directory and don't forgot about in this folder
%>composer update

Instruction install WebSocket by SSL connection - wss://

If you use NGINX settings for ssl

      location /wss2/ {
                proxy_pass                              http://127.0.0.1:8180;
                proxy_pass_header                       Server;
                proxy_http_version                      1.1;
                proxy_set_header Upgrade                $http_upgrade;
                proxy_set_header Connection             "upgrade";
                proxy_read_timeout                      86400;
                proxy_set_header Host                   $host;
                proxy_set_header X-Forwarded-For        $proxy_add_x_forwarded_for;
                proxy_set_header X-Real-IP              $remote_addr;
        }
        
        
If you use Apache settings for ssl
You need modules:

    mod_proxy.so
    mod_proxy_wstunnel.so

sudo a2enmod proxy_wstunnel

cd /etc/apache2/sites-enabled/
nano 000-default.conf
and
nano default-ssl.conf
adding the line:
ProxyPass /wss2/ ws://yourdomain.com:


For JS - var ws = new WebSocket("wss://ratchet.mydomain.org/wss2/NNN");

If you use linux Panels then this instruction:
https://ru.stackoverflow.com/questions/757351/%D0%9F%D0%BE%D0%BC%D0%BE%D0%B3%D0%B8%D1%82%D0%B5-%D1%81-%D0%BF%D1%80%D0%BE%D0%BA%D1%81%D0%B8-nginx-%D0%B4%D0%BB%D1%8F-websocket
