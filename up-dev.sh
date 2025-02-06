export $(cat .env.dev | xargs) && docker stack deploy -c docker-swarm.yml dev_stack
