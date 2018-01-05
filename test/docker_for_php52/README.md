# Docker for PHP 5.2 testing

Run all the following commands from this diretory.

# Building the image

    docker build . -f Dockerfile -t treffynnon/php5.2cli

# Run the tests

    docker run -t -v $(realpath ../..):/tmp/idiorm --rm treffynnon/php5.2cli /root/phpunit -c /tmp/idiorm/phpunit.xml

# Running the container interactively

    docker run -it -v $(realpath ../..):/tmp/idiorm --rm treffynnon/php5.2cli

# Running the tests

    ~/phpunit -c tmp/idiorm/phpunit.xml

# Getting out of the interactive container

    exit