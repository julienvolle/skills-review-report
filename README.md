# Skills Review

Manage interview frameworks, conduct interviews and generate reports.

## Release

- View history change in the [CHANGELOG](CHANGELOG.md) file
- View work in progress in the [TODO](TODO.md) file
- View current version in the [SEMVER](.semver) file


## Stack

- Langage: `PHP 7.4`
- Framework: `Symfony 5.4`
- Database: `MySQL, SQLite`
- ORM: `Doctrine`
- Templating: `Twig`
- Log: `Monolog`
- Asset: `Webpack, SASS, Typescript`


## Dependencies

- Composer
- Npm
- Yarn
- Symfony CLI // optional


## Installation

Run `make install` or 
> composer install  
> yarn install  
> symfony console d:d:c  
> symfony console d:m:m  
> symfony console d:f:l // optional  


## Utils

To see the available tasks list, run `make`

```
 -- INSTALL --               
                             
install                        Install [env=dev]
                             
install-package-manager        Install package manager
install-dependencies           Install dependencies
install-assets                 Install assets [env=dev]
install-database               Install database [env=dev]
install-database-test          Install database for testing
install-fixtures               Install fixtures [env=dev]
                             
reset                          Reset install [env=dev]
                             
 -- CACHE --                 
                             
cc                             Clear Cache [env=dev]
                             
cc-package-manager             Clear Cache Package Manager
cc-application                 Clear Cache Application [env=dev]
cc-database                    Clear Cache Database [env=dev]
                             
 -- TEST --                  
                             
test                           Test
                             
test-database                  Test database [env=dev]
test-container                 Test container
test-deprecations              Test deprecations
test-yaml                      Test yaml files
test-twig                      Test twig files
test-php                       Test php files
test-unit                      Test unit
test-functional                Test functional
test-code-coverage             Test code coverage [percent=100]
                             
 -- TOOL --                  
                             
phpcs                          Run PHP Code Sniffer
phpmd                          Run PHP Mess Detector
phpcbf                         Run PHP Code Beautifier & Fixer
phpcsf                         Run PHP Code Sniffer Fixer
                             
report-coverage                Create coverage HTML report
report-metrics                 Create metrics report

```
