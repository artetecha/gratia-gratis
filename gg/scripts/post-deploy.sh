#!/usr/bin/env bash

set -euo pipefail

cd wordpress

if ! wp core is-installed; then
	echo "WordPress is not installed yet; skipping post-deploy tasks."
	exit 0
fi

wp upsun sanitize --if-needed
