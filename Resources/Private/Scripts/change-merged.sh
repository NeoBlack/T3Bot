#!/bin/sh

# change-merged --change <change id> --project <project name> --branch <branch> --submitter <submitter> --commit <sha1>

CHANGE_ID=$2
PROJECT_NAME=$4
BRANCH=$6
SUBMITTER=$8
COMMIT=${10}
TOKEN="test123"
DATA_STRING="changeId=$CHANGE_ID&projectName=$PROJECT_NAME&branch=$BRANCH&submitter=$SUBMITTER&commit=$COMMIT&token=$TOKEN"

curl --data "$DATA_STRING" http://stage.t3bot.de/hooks/gerrit/change-merged/
# curl --data "$DATA_STRING" http://www.t3bot.de/hooks/gerrit/change-merged/
