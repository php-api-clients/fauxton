<?php declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use \React\EventLoop\Factory;
use \Chemem\Fauxton\Actions\Action;
use \Psr\Http\Message\ResponseInterface;

$loop = Factory::create();

$insert = Action::init($loop)->insertSingle('your_database', [
    'name' => isset($argv[1]) ? $argv[1] : 'loki',
    'age' => isset($argv[2]) ? $argv[2] : 1000
])->then(
    function (ResposneInterface $response) {
        echo $response->getBody();
    },
    function (\Exception $error) {
        echo $error->getMessage();
    }
);

$loop->run();
