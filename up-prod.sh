export $(cat .env.prod | xargs) && docker stack deploy -c docker-swarm-prod.yml prod_stack
