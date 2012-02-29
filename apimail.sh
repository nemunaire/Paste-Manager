#!/bin/sh

DIR="/var/www/p0m.fr/paste/"

tmpfile=`mktemp`

cat <&0 > $tmpfile

from=`cat "$tmpfile" | formail -x From | tail -n 1 | $DIR/conv2047-0.1.pl -d`
subject=`cat "$tmpfile" | formail -x subject | $DIR/conv2047-0.1.pl -d`

cat "$tmpfile" | $DIR/apimail.php "$from" "$subject"
#chmod +r `cat "$tmpfile" | $DIR/apimail.php "$from" "$subject"`
