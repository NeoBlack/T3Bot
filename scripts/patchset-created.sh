#!/bin/sh

# patchset-created --change <change id> --project <project name> --branch <branch> --commit <sha1> --patchset <patchset id>

CHANGE_ID=$2
PROJECT_NAME=$4
BRANCH=$6
COMMIT=$8
PATCHSET=${10}
TOKEN="test123"
DATA_STRING="changeId=$CHANGE_ID&projectName=$PROJECT_NAME&branch=$BRANCH&commit=$COMMIT&patchset=$PATCHSET&token=$TOKEN"

curl --data "$DATA_STRING" http://stage.t3bot.de/hooks/gerrit/patchset-created/
# curl --data "$DATA_STRING" http://www.t3bot.de/hooks/gerrit/patchset-created/
