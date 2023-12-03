#!/bin/bash

#echo "################ Dev START"
#symfony console doctrine:schema:drop --force
#symfony console doctrine:schema:create
#symfony console doctrine:fixtures:load --no-interaction
#symfony console app:import-members
#echo "################ Dev DONE"

echo "################ Test START"
symfony console -e test doctrine:schema:drop --force
symfony console -e test doctrine:schema:create
symfony console -e test doctrine:fixtures:load --no-interaction
echo "################ Test DONE"

