<?php declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use \Chemem\Fauxton\Actions\Action;
use \Clue\React\Buzz\Browser;

const LIMIT = 5; //arbitrary limit value

const TIMER = 10; //arbitrary periodic loop timer value

$loop = \React\EventLoop\Factory::create();

$since = -5; //arbitrary since parameter value

$action = Action::init($loop);

$loop->addPeriodicTimer(TIMER, function () use (&$action, &$since, &$loop) {
    $since += 5;
    $action->changes('your_database', [
        'descending' => 'true',
        'since' => $since,
        'limit' => LIMIT
    ])
        ->then(
            function ($result) {
                echo $result->getBody();
            },
            function ($error) {
                echo $error->getMessage();
            }
        );

    if ($since == 25) {
        $loop->cancelTimer(); //cancel timer after 5 log operations
    }
});

$loop->run();
