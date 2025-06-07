#!/bin/bash

export $(cat .env.prod | xargs)
docker stack deploy -c docker-swarm-certbot.yml certbot_stack
