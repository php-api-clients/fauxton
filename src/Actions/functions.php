<?php declare(strict_types=1);

namespace Chemem\Fauxton\Actions;

use \Chemem\Bingo\Functional\Algorithms as A;

function updateMerge(array $original, array $update) : array
{
    $update = A\compose(A\partial(A\omit, '_id', '_rev'), A\partial(A\extend, $update));

    return $update($original);
}
