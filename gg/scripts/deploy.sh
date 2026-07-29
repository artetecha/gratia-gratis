#!/usr/bin/env bash

set -euo pipefail

cd wordpress

if ! wp core is-installed; then
	echo "WordPress is not installed yet; see README.md for the explicit first-install procedure."
	exit 0
fi

wp core update-db
wp upsun migrate
wp redis enable || true
wp cron event run --due-now || true
