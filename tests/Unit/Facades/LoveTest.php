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

namespace Cog\Tests\Laravel\Love\Unit\Facades;

use Cog\Contracts\Love\ReactionType\Exceptions\ReactionTypeInvalid;
use Cog\Laravel\Love\Facades\Love;
use Cog\Laravel\Love\Reactant\Models\Reactant;
use Cog\Laravel\Love\Reacter\Models\Reacter;
use Cog\Laravel\Love\Reaction\Models\Reaction;
use Cog\Laravel\Love\ReactionType\Models\ReactionType;
use Cog\Tests\Laravel\Love\Stubs\Models\Article;
use Cog\Tests\Laravel\Love\Stubs\Models\User;
use Cog\Tests\Laravel\Love\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class LoveTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_check_is_reaction_of_type_name(): void
    {
        $reactionType1 = factory(ReactionType::class)->create();
        $reactionType2 = factory(ReactionType::class)->create();
        $reaction = factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType1->getKey(),
        ]);

        $true = Love::isReactionOfTypeName($reaction, $reactionType1->getName());
        $false = Love::isReactionOfTypeName($reaction, $reactionType2->getName());

        $this->assertTrue($true);
        $this->assertFalse($false);
    }

    /** @test */
    public function it_can_check_is_reaction_not_of_type_name(): void
    {
        $reactionType1 = factory(ReactionType::class)->create();
        $reactionType2 = factory(ReactionType::class)->create();
        $reaction = factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType1->getKey(),
        ]);

        $false = Love::isReactionNotOfTypeName($reaction, $reactionType1->getName());
        $true = Love::isReactionNotOfTypeName($reaction, $reactionType2->getName());

        $this->assertFalse($false);
        $this->assertTrue($true);
    }

    /** @test */
    public function it_throw_invalid_reaction_type_exception_on_check_is_reaction_of_type_name_when_type_name_in_unknown(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reaction = factory(Reaction::class)->create();

        Love::isReactionOfTypeName($reaction, 'UnknownType');
    }

    /** @test */
    public function it_throw_invalid_reaction_type_exception_on_check_is_reaction_not_of_type_name_when_type_name_in_unknown(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reaction = factory(Reaction::class)->create();

        Love::isReactionNotOfTypeName($reaction, 'UnknownType');
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable_with_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactant->getReactable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable_with_type_name_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedToWithTypeName(
            null,
            $reactant->getReactable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableReactedToWithTypeName(
            null,
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable_with_type_name_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedToWithTypeName(
            $reacterable,
            $reactant->getReactable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableReactedToWithTypeName(
            $reacterable,
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable_with_type_name_when_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactable,
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactable,
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable_with_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableNotReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactant->getReactable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableNotReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable_with_type_name_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableNotReactedToWithTypeName(
            null,
            $reactant->getReactable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableNotReactedToWithTypeName(
            null,
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable_with_type_name_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableNotReactedToWithTypeName(
            $reacterable,
            $reactant->getReactable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableNotReactedToWithTypeName(
            $reacterable,
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable_with_type_name_when_reactable_has_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReacterableNotReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactable,
            $reactionType->getName()
        );
        $isNotReacted = Love::isReacterableNotReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactable,
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant1 = factory(Reactant::class)->states('withReactable')->create();
        $reactant2 = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant1->getKey(),
        ]);
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant2->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedTo(
            $reacter->getReacterable(),
            $reactant1->getReactable()
        );
        $isNotReacted = Love::isReacterableReactedTo(
            $reacter->getReacterable(),
            $reactant2->getReactable()
        );

        $this->assertTrue($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedTo(
            null,
            $reactant->getReactable()
        );

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedTo(
            $reacterable,
            $reactant->getReactable()
        );

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_reacted_to_reactable_when_reactable_has_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReacterableReactedTo(
            $reacter->getReacterable(),
            $reactable
        );

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant1 = factory(Reactant::class)->states('withReactable')->create();
        $reactant2 = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant1->getKey(),
        ]);
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant2->getKey(),
        ]);

        $isNotReacted = Love::isReacterableNotReactedTo(
            $reacter->getReacterable(),
            $reactant1->getReactable()
        );
        $isReacted = Love::isReacterableNotReactedTo(
            $reacter->getReacterable(),
            $reactant2->getReactable()
        );

        $this->assertTrue($isNotReacted);
        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableNotReactedTo(
            null,
            $reactant->getReactable()
        );

        $this->assertTrue($isReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReacterableNotReactedTo(
            $reacterable,
            $reactant->getReactable()
        );

        $this->assertTrue($isReacted);
    }

    /** @test */
    public function it_can_check_is_reacterable_not_reacted_to_reactable_when_reactable_has_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReacterableNotReactedTo(
            $reacter->getReacterable(),
            $reactable
        );

        $this->assertTrue($isReacted);
    }

    /** @test */
    public function it_throw_reaction_type_invalid_exception_on_is_reacterable_reacted_to_reactable_with_type_name_when_type_unknown(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();

        Love::isReacterableReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactant->getReactable(),
            'UnknownType'
        );
    }

    /** @test */
    public function it_throw_reaction_type_invalid_exception_on_is_reacterable_not_reacted_to_reactable_with_type_name_when_type_unknown(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();

        Love::isReacterableNotReactedToWithTypeName(
            $reacter->getReacterable(),
            $reactant->getReactable(),
            'UnknownType'
        );
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable_with_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableReactedByWithTypeName(
            $reactant->getReactable(),
            $reacter->getReacterable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableReactedByWithTypeName(
            $reactant->getReactable(),
            $reacter->getReacterable(),
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable_with_type_name_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableReactedByWithTypeName(
            $reactant->getReactable(),
            null,
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableReactedByWithTypeName(
            $reactant->getReactable(),
            null,
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable_with_type_name_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableReactedByWithTypeName(
            $reactant->getReactable(),
            $reacterable,
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableReactedByWithTypeName(
            $reactant->getReactable(),
            $reacterable,
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable_with_type_name_when_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReactableReactedByWithTypeName(
            $reactable,
            $reacter->getReacterable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableReactedByWithTypeName(
            $reactable,
            $reacter->getReacterable(),
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable_with_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableNotReactedByWithTypeName(
            $reactant->getReactable(),
            $reacter->getReacterable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableNotReactedByWithTypeName(
            $reactant->getReactable(),
            $reacter->getReacterable(),
            $otherReactionType->getName()
        );

        $this->assertFalse($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable_with_type_name_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableNotReactedByWithTypeName(
            $reactant->getReactable(),
            null,
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableNotReactedByWithTypeName(
            $reactant->getReactable(),
            null,
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable_with_type_name_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableNotReactedByWithTypeName(
            $reactant->getReactable(),
            $reacterable,
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableNotReactedByWithTypeName(
            $reactant->getReactable(),
            $reacterable,
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable_with_type_name_when_reactable_has_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReactableNotReactedByWithTypeName(
            $reactable,
            $reacter->getReacterable(),
            $reactionType->getName()
        );
        $isNotReacted = Love::isReactableNotReactedByWithTypeName(
            $reactable,
            $reacter->getReacterable(),
            $otherReactionType->getName()
        );

        $this->assertTrue($isReacted);
        $this->assertTrue($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant1 = factory(Reactant::class)->states('withReactable')->create();
        $reactant2 = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant1->getKey(),
        ]);
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant2->getKey(),
        ]);

        $isReacted = Love::isReactableReactedBy(
            $reactant1->getReactable(),
            $reacter->getReacterable()
        );
        $isNotReacted = Love::isReactableReactedBy(
            $reactant2->getReactable(),
            $reacter->getReacterable()
        );

        $this->assertTrue($isReacted);
        $this->assertFalse($isNotReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableReactedBy(
            $reactant->getReactable(),
            null
        );

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableReactedBy(
            $reactant->getReactable(),
            $reacterable
        );

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_reacted_by_reacterable_when_reactable_has_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReactableReactedBy(
            $reactable,
            $reacter->getReacterable()
        );

        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant1 = factory(Reactant::class)->states('withReactable')->create();
        $reactant2 = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant1->getKey(),
        ]);
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
            'reactant_id' => $reactant2->getKey(),
        ]);

        $isNotReacted = Love::isReactableNotReactedBy(
            $reactant1->getReactable(),
            $reacter->getReacterable()
        );
        $isReacted = Love::isReactableNotReactedBy(
            $reactant2->getReactable(),
            $reacter->getReacterable()
        );

        $this->assertTrue($isNotReacted);
        $this->assertFalse($isReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable_when_reacterable_is_null(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableNotReactedBy(
            $reactant->getReactable(),
            null
        );

        $this->assertTrue($isReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable_when_reacterable_has_null_reacter(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacterable = new User();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $isReacted = Love::isReactableNotReactedBy(
            $reactant->getReactable(),
            $reacterable
        );

        $this->assertTrue($isReacted);
    }

    /** @test */
    public function it_can_check_is_reactable_not_reacted_by_reacterable_when_reactable_has_null_reactant(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reacter_id' => $reacter->getKey(),
        ]);

        $isReacted = Love::isReactableNotReactedBy(
            $reactable,
            $reacter->getReacterable()
        );

        $this->assertTrue($isReacted);
    }

    /** @test */
    public function it_throw_reaction_type_invalid_exception_on_is_reactable_reacted_by_reacterable_with_type_name_when_type_unknown(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();

        Love::isReactableReactedByWithTypeName(
            $reactant->getReactable(),
            $reacter->getReacterable(),
            'UnknownType'
        );
    }

    /** @test */
    public function it_throw_reaction_type_invalid_exception_on_is_reactable_not_reacted_by_reacterable_with_type_name_when_type_unknown(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reacter = factory(Reacter::class)->states('withReacterable')->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();

        Love::isReactableNotReactedByWithTypeName(
            $reactant->getReactable(),
            $reacter->getReacterable(),
            'UnknownType'
        );
    }

    /** @test */
    public function it_can_get_reactable_reactions_count_for_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 3)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $count = Love::getReactableReactionsCountForTypeName(
            $reactant->getReactable(),
            $reactionType->getName()
        );

        $this->assertSame(3, $count);
    }

    /** @test */
    public function it_can_get_reactable_with_null_reactant_reactions_count_for_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
        ]);

        $count = Love::getReactableReactionsCountForTypeName(
            $reactable,
            $reactionType->getName()
        );

        $this->assertSame(0, $count);
    }

    /** @test */
    public function it_can_get_reactable_reactions_count_for_type_name_if_no_reactions_exists(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 3)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $count = Love::getReactableReactionsCountForTypeName(
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertSame(0, $count);
    }

    /** @test */
    public function it_throw_exception_on_invalid_reaction_type_in_get_reactable_reactions_count_for_type_name(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 3)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        Love::getReactableReactionsCountForTypeName(
            $reactant->getReactable(),
            'InvalidType'
        );
    }

    /** @test */
    public function it_can_get_reactable_reactions_weight_for_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create([
            'weight' => 2,
        ]);
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 3)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $weight = Love::getReactableReactionsWeightForTypeName(
            $reactant->getReactable(),
            $reactionType->getName()
        );

        $this->assertSame(6, $weight);
    }

    /** @test */
    public function it_can_get_reactable_with_null_reactant_reactions_weight_for_type_name(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
        ]);

        $weight = Love::getReactableReactionsWeightForTypeName(
            $reactable,
            $reactionType->getName()
        );

        $this->assertSame(0, $weight);
    }

    /** @test */
    public function it_can_get_reactable_reactions_weight_for_type_name_if_no_reactions_exists(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $otherReactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 3)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $weight = Love::getReactableReactionsWeightForTypeName(
            $reactant->getReactable(),
            $otherReactionType->getName()
        );

        $this->assertSame(0, $weight);
    }

    /** @test */
    public function it_throw_exception_on_invalid_reaction_type_in_get_reactable_reactions_weight_for_type_name(): void
    {
        $this->expectException(ReactionTypeInvalid::class);

        $reactionType = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 3)->create([
            'reaction_type_id' => $reactionType->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        Love::getReactableReactionsWeightForTypeName(
            $reactant->getReactable(),
            'InvalidType'
        );
    }

    /** @test */
    public function it_can_get_reactable_reactions_total_count(): void
    {
        $reactionType1 = factory(ReactionType::class)->create();
        $reactionType2 = factory(ReactionType::class)->create();
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 2)->create([
            'reaction_type_id' => $reactionType1->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType2->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $count = Love::getReactableReactionsTotalCount(
            $reactant->getReactable()
        );

        $this->assertSame(3, $count);
    }

    /** @test */
    public function it_can_get_reactable_with_null_reactant_reactions_total_count(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
        ]);

        $count = Love::getReactableReactionsTotalCount(
            $reactable
        );

        $this->assertSame(0, $count);
    }

    /** @test */
    public function it_can_get_reactable_reactions_total_weight(): void
    {
        $reactionType1 = factory(ReactionType::class)->create([
            'weight' => 2,
        ]);
        $reactionType2 = factory(ReactionType::class)->create([
            'weight' => 3,
        ]);
        $reactant = factory(Reactant::class)->states('withReactable')->create();
        factory(Reaction::class, 2)->create([
            'reaction_type_id' => $reactionType1->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType2->getKey(),
            'reactant_id' => $reactant->getKey(),
        ]);

        $weight = Love::getReactableReactionsTotalWeight(
            $reactant->getReactable()
        );

        $this->assertSame(7, $weight);
    }

    /** @test */
    public function it_can_get_reactable_with_null_reactant_reactions_total_weight(): void
    {
        $reactionType = factory(ReactionType::class)->create();
        $reactable = new Article();
        factory(Reaction::class)->create([
            'reaction_type_id' => $reactionType->getKey(),
        ]);

        $weight = Love::getReactableReactionsTotalWeight(
            $reactable
        );

        $this->assertSame(0, $weight);
    }
}