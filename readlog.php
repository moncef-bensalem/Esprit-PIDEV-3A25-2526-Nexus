<?php $lines = file("var/log/dev.log"); echo implode("", array_slice($lines, -150));
