#!/bin/bash
function goto
{
    label=$1
    cmd=$(sed -n "/$label:/{:a;n;p;ba};" $0 | grep -v ':$')
    eval "$cmd"
    exit
}

re='^[0-9]+$'
portmax=65535

goto portloop
portloop:

read -p 'Enter port: ' port

if [ -z $port ]; then
    echo "Port is mandatory";
    goto portloop
fi

if ! echo "$port" | grep -Eq ^[0-9]+$; then
    echo "Port \`$port\` must be numeric";
    goto portloop
fi;

if [ $port -gt $portmax ] || [ $port -eq 0 ]; then
    echo "Port \`$port\` must be in range from 1 to $portmax";
    goto portloop
fi

goto folderloop
folderloop:

read -p 'Enter file folder: ' folder

if [ -z $folder ]; then
    echo "Folder is mandatory";
    goto folderloop
fi

if [ ! -d "$folder" ]; then
    echo "\`$folder\` is not a folder"
    goto folderloop
else
    docker rm -f opendatatool > /dev/null 2>&1 || true \
    && docker run -it -d --name opendatatool -v /var/run/docker.sock:/var/run/docker.sock -v $folder:/var/files -v opendatatooldb:/var/lib/mysql -p $port:80 --restart always asapbg/data-egov-bg-tool \
    && docker run -d --name watchtower --restart always -v /var/run/docker.sock:/var/run/docker.sock v2tec/watchtower > /dev/null 2>&1 || true \
    && echo 'Installation finished'
fi

