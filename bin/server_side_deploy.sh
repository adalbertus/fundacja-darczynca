#!/bin/bash

$GIT_URL="git@github.com:adalbertus/fundacja.git";

export TERM=xterm-256color
set -x
working_dir=$1
app_env=$2
# set -o xtrace
date=$(date '+%Y-%m-%d_%H%M%S')

cd $working_dir
mkdir -p releases/$date
cd releases/$date
git clone $GIT_URL .
cp ../../.env.local.php .
tar xzf ../../deploy/build.tgz

export APP_ENV=$app_env
php82 ~/bin/composer.phar install --no-dev --optimize-autoloader
php82 bin/console doctrine:migrations:migrate
chmod +x bin/dhosting_membeship_fee_generate.sh
cd ../../
mv current current.bak && cp -a releases/$date/ current && rm -rf current.bak
rm deploy/build.tgz
