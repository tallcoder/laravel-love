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

namespace Cog\Laravel\Love\Reacter\Models;

use Cog\Contracts\Love\Reactant\Models\Reactant;
use Cog\Contracts\Love\Reacter\Models\Reacter as ReacterContract;
use Cog\Contracts\Love\Reacterable\Models\Reacterable as ReacterableContract;
use Cog\Contracts\Love\ReactionType\Models\ReactionType;

final class NullReacter implements ReacterContract
{
    private $reacterable;

    public function __construct(ReacterableContract $reacterable)
    {
        $this->reacterable = $reacterable;
    }

    public function getReacterable(): ReacterableContract
    {
        return $this->reacterable;
    }

    public function getReactions(): iterable
    {
        return [];
    }

    public function reactTo(Reactant $reactant, ReactionType $reactionType): void
    {
        // TODO: Throw cannot `reactTo`
        // TODO: Cover with tests
    }

    public function unreactTo(Reactant $reactant, ReactionType $reactionType): void
    {
        // TODO: Throw cannot `reactTo`
        // TODO: Cover with tests
    }

    public function isReactedTo(Reactant $reactant): bool
    {
        return false;
    }

    public function isNotReactedTo(Reactant $reactant): bool
    {
        return true;
    }

    public function isReactedWithTypeTo(Reactant $reactant, ReactionType $reactionType): bool
    {
        return false;
    }

    public function isNotReactedWithTypeTo(Reactant $reactant, ReactionType $reactionType): bool
    {
        return true;
    }
}