#!/usr/bin/env bash

if [ ! -f .env ]; then
    echo "No .env detected!"
    exit 1
fi

if [[ "$OSTYPE" == "darwin"* ]]; then
    # Mac
    HOST_IP=$(ifconfig en0 | awk '/inet / {print $2; exit}')
else
    # Linux
    HOST_IP=$(ip route get 8.8.8.8 | awk '{print $7; exit}')
fi

if [ -z "$HOST_IP" ]; then
    echo "Could not determine HOST_IP"
    exit 1
fi

if grep -qE "^HOST_IP=" .env; then
    if [[ "$OSTYPE" == "darwin"* ]]; then
      # Mac
      sed -i '' -E "s/^HOST_IP=.*/HOST_IP=$HOST_IP/" .env
    else
      # Linux
      sed -i -E "s/^HOST_IP=.*/HOST_IP=$HOST_IP/" .env
    fi
    echo "Host IP was updated in the .env with $HOST_IP"
else
    printf "\n# IP of the host machine\nHOST_IP=$HOST_IP" >> .env
    echo "Host IP $HOST_IP was added to the .env"
fi