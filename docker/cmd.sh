#!/usr/bin/env bash

# This can be used for misc docker-compose commands
# For instance:
#  * See your docker-compose logs: `./cmd.sh logs`
#  * See your docker-compose logs and follow them: `./cmd.sh logs -f`
#  * Restart your docker-compose environment: `./cmd.sh restart`
#  * To see what commands you can run: `./cmd.sh help`
docker-compose -f docker-compose.yml -f docker-compose.unison.yml $@