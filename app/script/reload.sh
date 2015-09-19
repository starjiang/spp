#!/bin/sh

php merge_xml.php ../xmlconfig/ config.xml
php load_xml.php ../xmlconfig/config.xml
