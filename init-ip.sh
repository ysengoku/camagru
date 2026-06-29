#!/usr/bin/env bash

DEV=false
if [[ "$1" == "--dev" ]]; then
    DEV=true
fi

ENV_FILE=".env"
if $DEV; then
    ENV_FILE=".env.dev"
fi

if [ ! -f "$ENV_FILE" ]; then
    echo "No $ENV_FILE detected!"
    exit 1
fi

if [[ "$OSTYPE" == "darwin"* ]]; then
    HOST_IP=$(ifconfig en0 | awk '/inet / {print $2; exit}')
else
    HOST_IP=$(ip route get 8.8.8.8 | awk '{print $7; exit}')
fi

if [ -z "$HOST_IP" ]; then
    echo "Could not determine HOST_IP"
    exit 1
fi

if $DEV; then
    BASE_URL="http://$HOST_IP:8080"
else
    BASE_URL="https://$HOST_IP:8443"
fi

update_or_append() {
    local key="$1"
    local value="$2"
    local file="$3"
    if grep -qE "^${key}=" "$file"; then
        if [[ "$OSTYPE" == "darwin"* ]]; then
            sed -i '' -E "s|^${key}=.*|${key}=${value}|" "$file"
        else
            sed -i -E "s|^${key}=.*|${key}=${value}|" "$file"
        fi
    else
        printf "\n%s=%s" "$key" "$value" >> "$file"
    fi
}

update_or_append "HOST_IP" "$HOST_IP" "$ENV_FILE"
update_or_append "APP_BASE_URL" "$BASE_URL" "$ENV_FILE"

echo "Updated $ENV_FILE:"
echo "  HOST_IP=$HOST_IP"
echo "  APP_BASE_URL=$BASE_URL"
