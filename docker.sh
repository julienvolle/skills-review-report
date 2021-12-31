#!/bin/sh

if hash docker-compose 2>/dev/null; then
  echo "start all containers..."
  docker-compose up -d --remove-orphans $1
  echo "connect to php container..."
  docker-compose exec -u root php /bin/sh -c "cd /var/www/html; exec /bin/sh -l"
else
  echo "start all containers..."
  docker compose up -d --remove-orphans $1
  echo "connect to php container..."
  docker compose exec -u root php /bin/sh -c "cd /; exec /bin/sh -l; cd /var/www/html"
fi
