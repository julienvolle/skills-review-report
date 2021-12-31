<?php

use FriendsOfTwig\Twigcs\Config\Config;
use FriendsOfTwig\Twigcs\Finder\TemplateFinder;
use FriendsOfTwig\Twigcs\Ruleset\Official;

return Config::create()
    ->addFinder(TemplateFinder::create()->in(__DIR__ . '/templates'))
    ->setSeverity('ignore')
    ->setDisplay('all')
    ->setReporter('console')
    ->setRuleSet(Official::class)
;
