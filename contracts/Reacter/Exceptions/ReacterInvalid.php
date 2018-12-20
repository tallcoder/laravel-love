<?php

/*
 * This file is part of Laravel Love.
 *
 * (c) Anton Komarev <a.komarev@cybercog.su>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Cog\Contracts\Love\Reacter\Exceptions;

use DomainException;

final class ReacterInvalid extends DomainException
{
    public static function notExists(): self
    {
        return new static('Reacter not exists.');
    }
}
