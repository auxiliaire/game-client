game-client
===========

*CLI Client for the Game of Three server written in Symfony*

This is a small demonstration application for REST API based communication between two clients. It requires a running server - see there: (https://github.com/auxiliaire/game-api).
The server itself supports as many games as demanded but one game can be played by two players only.

Features
--------

* Auto, manual and partially manual modes
* Full interaction in manual mode
* Error handling
* Human vs Computer, Human vs human, Computer vs human and Computer vs computer modes
* CLI switch to enable full automated mode

Usage
-----

1. Run `php bin/console app:play` for manual and partially manual modes or `php bin/console app:play -a` for automated mode.
2. Provide input if required.
3. Wait the second client to join (Run the same way in a different terminal window or tab.)

Docker support
--------------

0. Make sure server is running properly (https://github.com/auxiliaire/game-api).
1. Run `docker-compose build` in client project root to create container from preconfigured files.
2. Start client one using a command like this `docker run -it --rm --net host --name gameclient_php_2 -v "$PWD":/app -w /app php:7.0-cli php bin/console app:play`. (Alternatively you can add the -a switch to avoid interaction and run auto mode.)
3. Start client two using a similar command (with different name): `docker run -it --rm --net host --name gameclient_php_3 -v "$PWD":/app -w /app php:7.0-cli php bin/console app:play`.
4. The clients will stop automatically after finishing the game.

Notice
------

Because the game checks the user input on client and server side as well, that means the course of the game ie. the moves are deterministic, that means the initial choice of number determines the end result. The players have no real choice to tweak the moves since there's only one valid choice or step to be taken that results in a number which is dividable with three without modulus.
