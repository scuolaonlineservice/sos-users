#!/usr/bin/env bash
/home/start.sh & disown

until test -f "/var/www/html/$SITE_NAME/configuration.php"; do
  echo "Waiting for site creation";
  sleep 10;
done;

cd "/var/www/html/$SITE_NAME";
composer require google/apiclient:"^2.0" --ignore-platform-reqs;

/bin/bash
