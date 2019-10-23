#!/bin/sh

######################################################
#
# T3Bot
#
# @author Frank Nägler <frank.naegler@typo3.org>
#
# @link http://www.t3bot.de
# @link http://wiki.typo3.org/T3Bot
######################################################

# Installation
# - Move this to /etc/init.d/botty
# - chmod +x this
#
# Starting and stopping
# - Start: `service botty start` or `/etc/init.d/botty start`
# - Stop: `service botty stop` or `/etc/init.d/botty stop`

#ref http://till.klampaeckel.de/blog/archives/94-start-stop-daemon,-Gearman-and-a-little-PHP.html
#ref http://unix.stackexchange.com/questions/85033/use-start-stop-daemon-for-a-php-server/85570#85570
#ref http://serverfault.com/questions/229759/launching-a-php-daemon-from-an-lsb-init-script-w-start-stop-daemon

NAME=botty
DESC="Daemon for botty script"
PIDFILE="/var/run/${NAME}.pid"
LOGFILE="/var/log/${NAME}.log"

# CHANGE this to your PHP path
DAEMON="/usr/bin/php"
# CHANGE this to your bot path
DAEMON_HELPER="/var/www/t3bot.de/botty.sh"
DAEMON_OPTS="/var/www/t3bot.de/botty.php"

START_OPTS="--start --background --make-pidfile --pidfile ${PIDFILE} --exec ${DAEMON_HELPER} ${NAME} ${PIDFILE} ${DAEMON} ${DAEMON_OPTS}"
STOP_OPTS="--stop --pidfile ${PIDFILE}"

test -x $DAEMON || exit 0
test -x $DAEMON_HELPER || exit 0

set -e

case "$1" in
    start)
        echo -n "Starting ${DESC}: "
        /sbin/start-stop-daemon $START_OPTS >> $LOGFILE
        echo "$NAME."
        ;;
    stop)
        echo -n "Stopping $DESC: "
        /sbin/start-stop-daemon $STOP_OPTS
        echo "$NAME."
        rm -f $PIDFILE
        ;;
    restart|force-reload)
        echo -n "Restarting $DESC: "
        /sbin/start-stop-daemon $STOP_OPTS
        sleep 1
        /sbin/start-stop-daemon $START_OPTS >> $LOGFILE
        echo "$NAME."
        ;;
    *)
        N=/etc/init.d/$NAME
        echo "Usage: $N {start|stop|restart|force-reload}" >&2
        exit 1
        ;;
esac

exit 0
