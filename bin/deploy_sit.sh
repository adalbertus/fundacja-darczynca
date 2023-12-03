#!/bin/bash

DEPLOY_DIR="~/exmple.com/deploy"
DOMAIN="example.com"
SSH_ACCESS="user@example.com"
SERVER_SIDE_SCRIPT="server_side_deploy.sh"

rm /tmp/build.tgz

set -x
rm -rf public/build
yarn build
tar czf /tmp/build.tgz public/build
scp /tmp/build.tgz bin/$SERVER_SIDE_SCRIPT $SSH_ACCESS:$DEPLOY_DIR
rm /tmp/build.tgz
ssh $SSH_ACCESS "chmod +x $DEPLOY_DIR/$SERVER_SIDE_SCRIPT && $DEPLOY_DIR/$SERVER_SIDE_SCRIPT $DOMAIN sit"
