#!/usr/bin/env bash

echo 'Generate readme.'
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php
