#!/bin/sh

set -e

cd "$(dirname "$0")/../.."

if [ ! -f ./.env ]; then
    cp ./.env.dist ./.env
fi
