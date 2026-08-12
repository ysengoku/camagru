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

DOMAIN_NAME=$(grep -E "^DOMAIN_NAME=" "$ENV_FILE" | cut -d= -f2-)

DEPLOY=false
if ! $DEV && [ -n "$DOMAIN_NAME" ]; then
    DEPLOY=true
fi

if ! $DEV && ! $DEPLOY; then
    HOST_NAME=$(hostname -f 2>/dev/null)
fi

# ----- Resolve IP ---------------------------------------------------------------
if $DEPLOY; then
    HOST_IP=$(grep -E "^HOST_IP=" "$ENV_FILE" | cut -d= -f2-)
else
    if [[ "$OSTYPE" == "darwin"* ]]; then
        HOST_IP=$(ifconfig en0 | awk '/inet / {print $2; exit}')
    else
        HOST_IP=$(ip route get 8.8.8.8 | awk '{print $7; exit}')
    fi

fi

if [ -z "$HOST_IP" ]; then
    echo "Could not determine HOST_IP"
    exit 1
fi

# ----- Resolve Hostname --------------------------------------------------------
resolve_host() {
    local name="$1"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        dscacheutil -q host -a name "$name" 2>/dev/null | grep -q "^ip_address:"
    else
        getent hosts "$name" >/dev/null 2>&1
    fi
}

if $DEV; then
    APP_HOST="localhost"
elif $DEPLOY; then
    APP_HOST="$DOMAIN_NAME"
elif [ -n "$HOST_NAME" ] && resolve_host "$HOST_NAME"; then
    APP_HOST="$HOST_NAME"
else
    APP_HOST="$HOST_IP"
fi

if $DEV; then
    BASE_URL="http://$APP_HOST:8080"
elif $DEPLOY; then
    BASE_URL="https://$APP_HOST"
else
    BASE_URL="https://$APP_HOST:8443"
fi

# ----- Update env file ---------------------------------------------------------
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
update_or_append "APP_HOST" "$APP_HOST" "$ENV_FILE"
update_or_append "APP_BASE_URL" "$BASE_URL" "$ENV_FILE"

echo "Updated $ENV_FILE:"
echo "  HOST_IP=$HOST_IP"
echo "  APP_HOST=$APP_HOST"
echo "  APP_BASE_URL=$BASE_URL"
