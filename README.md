# Pokétools
![CI](https://github.com/gamestuff-info/poketools/workflows/CI/badge.svg)

[![](https://raw.githubusercontent.com/gamestuff-info/poketools/master/app/assets/static/logo-cropped.svg)](https://poketools.gamestuff.info)

https://poketools.gamestuff.info

A web application all about Pokémon.

Not affiliated with Nintendo, Game Freak, Creatures, or any of their affiliates.

## Data
Download the database dump [here](https://gitlab.com/gamestuff.info/poketools/-/jobs/artifacts/master/download?job=data).

## Contribute
Want to help fill in data gaps?  See the [data docs](https://gamestuff-info.github.io/poketools/).

All data has a schema associated with it that can help edit data if your editor
supports it.  The schema URLs are listed in the docs.

All data also has a test.  Run `php -d memory_limit=-1 bin/phpunit --configuration=phpunit.xml.dist --testsuite="Data Schema"`
to test the data before importing.
