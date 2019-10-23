#!/bin/sh

######################################################
#
# T3Bot
#
# @author Simon Gilli <typo3@gilbertsoft.org>
#
# @link http://www.t3bot.de
# @link http://wiki.typo3.org/T3Bot
#
# @param NAME
# @param PID file
# @param LOG file
# @param DAEMON file
# @param DAEMON options
#
######################################################


# Read arguments and shift to get access to all options
NAME=$1
PIDFILE=$2
LOGFILE=$3
DAEMON=$4

shift 4

# Test arguments
test -n $NAME || exit 0
test -f $PIDFILE || exit 0
test -x $DAEMON || exit 0
test -n $@ || exit 0

# Loop while PID file exists
while [ -f $PIDFILE ]
do
    "$DAEMON" $@
    echo "$NAME restored" >> $LOGFILE
done

echo "$NAME has terminated" >> $LOGFILE

exit 0
