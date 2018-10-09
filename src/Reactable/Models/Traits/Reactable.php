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

namespace Cog\Laravel\Love\Reactable\Models\Traits;

use Cog\Contracts\Love\Reactable\Models\Reactable as ReactableContract;
use Cog\Contracts\Love\Reactant\Models\Reactant as ReactantContract;
use Cog\Contracts\Love\Reacter\Models\Reacter as ReacterContract;
use Cog\Contracts\Love\ReactionType\Models\ReactionType as ReactionTypeContract;
use Cog\Laravel\Love\Reactant\Models\Reactant;
use Cog\Laravel\Love\Reactant\ReactionCounter\Models\ReactionCounter;
use Cog\Laravel\Love\Reactant\ReactionSummary\Models\ReactionSummary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Trait Reactable.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @package Cog\Laravel\Love\Reacterable\Models\Traits
 */
trait Reactable
{
    protected static function bootReactable(): void
    {
        static::creating(function (ReactableContract $reactable) {
            if ($reactable->isNotRegisteredAsReactant()) {
                $reactant = Reactant::query()->create([
                    'type' => $reactable->getMorphClass(),
                ]);

                $reactable->setAttribute('love_reactant_id', $reactant->getKey());
            }
        });
    }

    public function reactant(): BelongsTo
    {
        return $this->belongsTo(Reactant::class, 'love_reactant_id');
    }

    public function getReactant(): ReactantContract
    {
        // TODO: Return `NullReactant` if not set?
        return $this->getAttribute('reactant');
    }

    public function isRegisteredAsReactant(): bool
    {
        return !$this->isNotRegisteredAsReactant();
    }

    public function isNotRegisteredAsReactant(): bool
    {
        return is_null($this->getAttribute('love_reactant_id'));
    }

    public function scopeWhereReactedBy(Builder $query, ReacterContract $reacter): Builder
    {
        return $query->whereHas('reactant.reactions', function (Builder $reactionsQuery) use ($reacter) {
            $reactionsQuery->where('reacter_id', $reacter->getKey());
        });
    }

    public function scopeWhereReactedWithTypeBy(
        Builder $query,
        ReacterContract $reacter,
        ReactionTypeContract $reactionType
    ): Builder {
        return $query->whereHas('reactant.reactions', function (Builder $reactionsQuery) use ($reacter, $reactionType) {
            $reactionsQuery->where('reacter_id', $reacter->getKey());
            $reactionsQuery->where('reaction_type_id', $reactionType->getKey());
        });
    }

    public function scopeWithReactionCounterOfType(Builder $query, ReactionTypeContract $reactionType): Builder
    {
        $select = $query->getQuery()->columns ?? ["{$this->getTable()}.*"];
        $select[] = DB::raw('coalesce(lrrc.count, 0) as reactions_count');

        return $query
            ->leftJoin((new ReactionCounter())->getTable() . ' as lrrc', function (JoinClause $join) use ($reactionType) {
                $join->on('lrrc.reactant_id', '=', "{$this->getTable()}.love_reactant_id");
                $join->where('lrrc.reaction_type_id', $reactionType->getKey());
            })
            ->select($select);
    }

    public function scopeWithReactionSummary(Builder $query): Builder
    {
        $select = $query->getQuery()->columns ?? ["{$this->getTable()}.*"];
        $select[] = DB::raw('coalesce(lrrs.total_count, 0) as reactions_total_count');
        $select[] = DB::raw('coalesce(lrrs.total_weight, 0) as reactions_total_weight');

        return $query
            ->leftJoin((new ReactionSummary())->getTable() . ' as lrrs', 'lrrs.reactant_id', '=', "{$this->getTable()}.love_reactant_id")
            ->select($select);
    }
}
