#!/bin/bash
rsync --compress-level=9 --progress -av * taquilla@rift.zapto.org:/var/www/

