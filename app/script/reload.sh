#!/bin/sh

php merge_xml.php ../gconfig/ config.xml
php load_xml.php ../gconfig/config.xml
