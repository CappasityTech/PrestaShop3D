#!/usr/bin/env bash

./scripts/prepare.sh 0.0.0-dev
docker exec presta php app/console prestashop:module install ./dev-src/build/cappasity3d.zip
