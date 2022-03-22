#!/usr/bin/env bash

VERSION="1.6.1.18"

while getopts v: opts; do
   case ${opts} in
      v) VERSION=${OPTARG} ;;
      *) exit 1 ;;
   esac
done

# launch compose crap
PV=${VERSION} docker-compose up -d

# ensure we close docker-stuff
trap "docker-compose stop; docker-compose rm -v -f;" EXIT

# print logs
docker-compose logs -f presta
