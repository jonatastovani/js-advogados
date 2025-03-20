export $(cat .env.dev | xargs) && docker stack deploy -c docker-swarm-dev.yml dev_stack
