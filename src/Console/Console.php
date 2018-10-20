<?php

namespace Chemem\Fauxton\Console;

use Chemem\Fauxton\Http;
use Chemem\Fauxton\Config\State;
use \Chemem\Bingo\Functional\Algorithms as A;
use \Chemem\Bingo\Functional\Functors\Monads\IO;
use \Chemem\Bingo\Functional\Functors\Monads as M;
use function \Chemem\Bingo\Functional\PatternMatching\patternMatch;

const execCmd = 'Chemem\\Fauxton\\Console\\execCmd';

function execCmd(string $cmd) : IO
{
    return patternMatch(
        [
            '["cred", type, user, pass]' => function (string $type, string $user, string $pass) {
                return configWrite(function (array $config) use ($user, $pass, $type) {
                    $config['username'][$type] = $user;
                    $config['password'][$type] = $pass;
                    $res = A\compose(A\partialRight('json_encode', \JSON_PRETTY_PRINT), IO\IO);
                    return $res($config);
                });
            },
            '["use", option]' => function (string $option) {
                return configWrite(function (array $config) use ($option) {
                    $config['local'] = patternMatch(
                        [
                            '"local"' => function () {
                                return true;
                            },
                            '_' => function () {
                                return false;
                            }
                        ],
                        $option
                    );
                    $res = A\compose(A\partialRight('json_encode', \JSON_PRETTY_PRINT), IO\IO);
                    return $res($config);
                });
            },
            '["gzip", database, file]' => function (string $database, string $file) {
                $zip = M\mcompose(function (string $contents) use ($file) {
                    $check = function (array $data) : array {
                        return isset($data['rows']) ? A\pluck($data, 'rows') : A\identity([]);
                    };
                    $final = A\compose(A\partialRight('json_decode', true), $check, 'json_encode');
                    $resource = \gzopen($file, 'w9');
                    \gzwrite($resource, $final($contents));
                    return IO\IO(\gzclose($resource) ? 'Success' : 'Failure');
                }, A\partialRight(Http\allDocs, ['include_docs' => 'true']));

                return $zip(IO\IO($database));
            },
            '["unzip", file]' => function (string $file) {
                $read = A\compose(
                    'gzfile', 
                    A\partialRight(A\pluck, 0),  
                    A\partialRight('json_decode', true),
                    A\partialRight('json_encode', \JSON_PRETTY_PRINT),
                    formatOutput
                );
                return $read($file);
            },
            '["search", database, selector]' => function (string $database, string $selector) {
                return configRead(function ($contents) use ($selector, $database) {
                    $res = A\compose(
                        A\partialRight('json_decode', true),
                        A\partialRight(A\pluck, 'console'),
                        A\partialRight(A\pluck, 'search'),
                        A\partialLeft(A\extend, ['selector' => \json_decode($selector, true)]),
                        A\partial(Http\search, $database)
                    );

                    return M\bind(function (string $docs) {
                        $res = A\compose(
                            A\partialRight('json_decode', true), 
                            A\partialRight(A\pluck, 'docs'), 
                            A\partialRight('json_encode', \JSON_PRETTY_PRINT),
                            formatOutput
                        );
                        return $res($docs);
                    }, $res($contents));
                });
            },
            '["alldocs", database]' => function (string $database) {
                return configRead(function (string $contents) use ($database) {
                    $res = A\compose(
                        A\partialRight('json_decode', true),
                        A\partialRight(A\pluck, 'console'),
                        A\partialRight(A\pluck, 'alldocs')
                    );

                    return M\bind(function ($contents) {
                        $res = A\compose(
                            A\partialRight('json_decode', true), 
                            A\partialRight('json_encode', \JSON_PRETTY_PRINT),
                            formatOutput
                        );
                        return $res($contents);
                    }, Http\allDocs($database, $res($contents)));
                });
            },
            '["doc", database, docId]' => function (string $database, string $docId) {
                return M\bind(function ($contents) {
                    $res = A\compose(
                        A\partialRight('json_decode', true),
                        A\partialRight('json_encode', \JSON_PRETTY_PRINT),
                        formatOutput
                    );
                    return $res($contents);
                }, Http\doc($database, $docId));
            },
            '["config"]' => function () {
                $config = M\mcompose(function ($contents) {
                    return formatOutput($contents);
                }, IO\readFile);

                return $config(IO\IO(Http\path(State::CLIENT_CONFIG_FILE)));
            },
            '["exit"]' => function () {
                M\bind(function (string $msg) {
                    $result = A\compose(A\partial('printf', '%s'), IO\IO);
                    return $result($msg);
                }, IO\IO('Thanks for using the console'));
                exit();
            },
            '_' => function () {
                return IO\IO('Input not recognized');
            }
        ],
        explode(' ', $cmd)
    );
}

function configRead(callable $action) : IO
{
    $read = M\mcompose($action, IO\readFile);
    return $read(IO\IO(Http\path(State::CLIENT_CONFIG_FILE)));
}

function configWrite(callable $action) : IO
{
    return configRead(function (string $contents) use ($action) {
        $result = M\mcompose(A\partial(IO\writeFile, Http\path(State::CLIENT_CONFIG_FILE)), $action);
        return M\bind(function (int $result) {
            return IO\IO($result > 0 ? 'Credentials updated' : 'Credentials not updated');
        }, $result(IO\IO(json_decode($contents, true))));
    }); 
}

function replPrompt() : IO
{
    $prompt = M\mcompose(
        function ($prompt) {
            $action = A\compose(A\partial('printf', '%s'), IO\IO);
            return $action($prompt);
        }, 
        function (array $contents) {
            $result = A\compose(A\partialRight(A\pluck, 'prompt'), IO\IO);
            return $result($contents);
        }    
    );

    return $prompt(IO\IO(State::CONSOLE_FEATURES));
}

const formatOutput = 'Chemem\\Fauxton\\Console\\formatOutput';

function formatOutput(string $contents) : IO
{
    $format = A\compose(
        A\partial('str_replace', '{', \implode('-', \array_fill(0, 10, '-'))),
        A\partial('str_replace', '}', \implode('-', \array_fill(0, 10, '-'))),
        A\partial('str_replace', '[', \implode('=', \array_fill(0, 10, '='))),
        A\partial('str_replace', ']', \implode('=', \array_fill(0, 10, '='))),
        A\partial('str_replace', '\\t', ' '),
        A\partial('str_replace', '"', ''),
        A\partial('str_replace', ',', ''),
        A\partial('str_replace', '\\', ''),
        IO\IO
    );

    return $format($contents);
}
