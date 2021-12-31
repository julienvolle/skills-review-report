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
- Asset: `Webpack, SASS, Typescript`


## Dependencies

- Composer
- Npm
- Yarn
- Symfony CLI // optional


## Docker

Run `sh docker.sh` (add flag `--build` to build images before starting containers) or
```
// start all containers (add flag --build to build images before starting containers)
docker-compose up -d --remove-orphans  

// connect to php container
docker-compose exec -u root php /bin/sh -c "cd /var/www/html; exec /bin/sh -l"
```

## Install

Run `make install` or 
```
# run inside php container

// install vendor
composer install  

// install assets
yarn install --force  
yarn run build

// install database
symfony console d:d:c  
symfony console d:m:m

// optional fixtures  
symfony console d:f:l  
```

### Enable SonarQube

```
cp docker-compose.override.yml.dist docker-compose.override.yml  

docker-compose up -d --build --remove-orphans  
```

Open SonarQube dashboard `user/pass=admin`, create a new local project and get the project key and the token.

```
cp sonar-project.properties.dist sonar-project.properties

// edit "sonar-project.properties"
sonar.projectKey=_______
```

```
cp sonar-scanner.sh.dist sonar-scanner.sh

// edit "sonar-scanner.sh"
SONAR_HOST_URL=_______
SONAR_PROJECT_KEY=_______
SONAR_TOKEN=_______

// add your optional properties
```

#### Scan project files

Run `sh sonar-scanner.sh` (outside a docker container)



## Utils

A `makefile` is there to help you. To see the available tasks list, run `make`

```
 -- INSTALL --               
                             
install                        Install [env=dev]
                             
install-dependencies           Install dependencies
install-assets                 Install assets [env=dev]
install-database               Install database [env=dev]
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
                             
composer-check                 Check composer.lock and outdated dependencies

```
