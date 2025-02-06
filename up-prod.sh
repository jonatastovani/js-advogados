export $(cat .env.prod | xargs) && docker stack deploy -c docker-swarm.yml prod_stack
