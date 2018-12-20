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

namespace Cog\Laravel\Love\Reaction\Models;

use Cog\Contracts\Love\Reactant\Models\Reactant as ReactantContract;
use Cog\Contracts\Love\Reacter\Models\Reacter as ReacterContract;
use Cog\Contracts\Love\Reaction\Models\Reaction as ReactionContract;
use Cog\Contracts\Love\ReactionType\Models\ReactionType as ReactionTypeContract;
use Cog\Laravel\Love\Reactant\Models\Reactant;
use Cog\Laravel\Love\Reacter\Models\Reacter;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use TypeError;

final class Reaction extends Model implements ReactionContract
{
    protected $table = 'love_reactions';

    protected $fillable = [
        'reaction_type_id',
        'reactant_id',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReactionType::class, 'reaction_type_id');
    }

    public function reactant(): BelongsTo
    {
        return $this->belongsTo(Reactant::class, 'reactant_id');
    }

    public function reacter(): BelongsTo
    {
        return $this->belongsTo(Reacter::class, 'reacter_id');
    }

    public function getId(): string
    {
        $id = $this->getAttribute('id');

        if (is_null($id)) {
            throw new TypeError();
        }

        return $id;
    }

    public function getType(): ReactionTypeContract
    {
        $type = $this->getAttribute('type');

        if (is_null($type)) {
            throw new TypeError();
        }

        return $type;
    }

    public function getReactant(): ReactantContract
    {
        $reactant = $this->getAttribute('reactant');

        if (is_null($reactant)) {
            throw new TypeError();
        }

        return $reactant;
    }

    public function getReacter(): ReacterContract
    {
        $reacter = $this->getAttribute('reacter');

        if (is_null($reacter)) {
            throw new TypeError();
        }

        return $reacter;
    }

    public function getWeight(): int
    {
        return $this->getType()->getWeight();
    }

    public function isOfType(ReactionTypeContract $reactionType): bool
    {
        return $this->getType()->isEqualTo($reactionType);
    }

    public function isNotOfType(ReactionTypeContract $reactionType): bool
    {
        return $this->getType()->isNotEqualTo($reactionType);
    }

    public function isToReactant(ReactantContract $reactant): bool
    {
        return $this->getReactant()->getId() === $reactant->getId();
    }

    public function isNotToReactant(ReactantContract $reactant): bool
    {
        return !$this->isToReactant($reactant);
    }

    public function isByReacter(ReacterContract $reacter): bool
    {
        return $this->getReacter()->getId() === $reacter->getId();
    }

    public function isNotByReacter(ReacterContract $reacter): bool
    {
        return !$this->isByReacter($reacter);
    }
}
