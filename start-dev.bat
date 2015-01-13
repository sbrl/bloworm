@echo off

set /a port = (%random% %%10000) + 2048

title http://[::1]:%port%/
php -S [::]:%port%
