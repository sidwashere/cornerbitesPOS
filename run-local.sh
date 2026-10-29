#!/usr/bin/env bash
# WallacePOS - run locally (MySQL + PHP built-in server + Node feed server)
# Usage: ./run-local.sh [stop]
set -e
cd "$(dirname "$0")"
ROOT="$PWD"

MYSQL_HOME="$HOME/wpos-mysql"
MYSQLD="$MYSQL_HOME/bin/mysqld"
MYSQL="$MYSQL_HOME/bin/mysql"
MYCNF="$MYSQL_HOME/my.cnf"
PHP_PORT="${PHP_PORT:-8000}"
FEED_PORT="$(python3 -c "import json;print(json.load(open('docs/.config.json')).get('feedserver_port',8080))" 2>/dev/null || echo 8080)"

if [ "$1" = "stop" ]; then
  pkill -f "php -S 127.0.0.1:$PHP_PORT" && echo "PHP server stopped" || echo "PHP server not running"
  pkill -f "node .*api/server.js" && echo "Feed server stopped" || echo "Feed server not running"
  "$MYSQL" --defaults-file="$MYCNF" -u root -e "SHUTDOWN;" 2>/dev/null && echo "MySQL stopped" || echo "MySQL not running"
  exit 0
fi

# 1. MySQL
if ! nc -z 127.0.0.1 3306 2>/dev/null; then
  echo "Starting MySQL..."
  nohup "$MYSQLD" --defaults-file="$MYCNF" --user="$(whoami)" > "$MYSQL_HOME/log/mysqld.out" 2>&1 &
  for i in $(seq 1 40); do nc -z 127.0.0.1 3306 2>/dev/null && break; sleep 1; done
fi
nc -z 127.0.0.1 3306 && echo "MySQL OK (127.0.0.1:3306)" || { echo "MySQL failed - see $MYSQL_HOME/log/mysqld.err"; exit 1; }

# 2. Feed (socket.io) server
if ! nc -z 127.0.0.1 "$FEED_PORT" 2>/dev/null; then
  echo "Starting feed server on :$FEED_PORT ..."
  (node api/server.js > /tmp/wpos-feedserver.log 2>&1 &)
  sleep 2
fi
nc -z 127.0.0.1 "$FEED_PORT" && echo "Feed server OK (:$FEED_PORT)"

# 3. First-time DB install (schema + admin/admin)
if [ ! -f library/wpos/.dbconfig.json ]; then
  printf '{"host":"127.0.0.1","port":"3306","database":"wpos","user":"wpos","pass":"wpos"}' > library/wpos/.dbconfig.json
fi
if ! "$MYSQL" --defaults-file="$MYCNF" -u root -e "SELECT 1 FROM wpos.auth LIMIT 1" >/dev/null 2>&1; then
  echo "Initialising database + schema..."
  "$MYSQL" --defaults-file="$MYCNF" -u root < "$ROOT/setup/init-db.sql"
  php setup/install_db.php
fi

# 4. PHP app server (foreground)
echo ""
echo "  POS      ->  http://localhost:$PHP_PORT/"
echo "  Admin    ->  http://localhost:$PHP_PORT/admin/   (login: admin / admin)"
echo "  Kitchen  ->  http://localhost:$PHP_PORT/kitchen/"
echo ""
exec php -S 127.0.0.1:"$PHP_PORT" -d date.timezone=Africa/Nairobi router.php
