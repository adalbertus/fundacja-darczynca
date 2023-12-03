#!/bin/bash

#symfony console doctrine:database:drop --force
#symfony console doctrine:database:create
symfony console -e test doctrine:database:drop --force
symfony console -e test doctrine:database:create

#rm -rf migrations/Version*.php
#symfony console make:migration

#symfony console doctrine:migrations:migrate -n
symfony console -e test doctrine:migrations:migrate -n