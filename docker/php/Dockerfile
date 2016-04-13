FROM php:7.0-cli

RUN useradd -ms /bin/bash vezir

USER vezir

WORKDIR /app

CMD [ "php", "bin/console", "app:play" ]
