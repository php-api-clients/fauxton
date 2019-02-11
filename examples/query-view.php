<?php declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use \React\EventLoop\Factory;
use \Chemem\Fauxton\Actions\Action;
use \Psr\Http\Message\ResponseInterface;

$loop = Factory::create();

$query = Action::init($loop)->queryView('your_database', 'design_doc', 'view_name', [
    'descending' => 'true',
    'group' => 'false'
])->then(
    function (ResponseInterface $response) {
        echo $response->getContents();
    },
    function (\Exception $error) {
        echo $error->getMessage();
    }
);

$loop->run();
