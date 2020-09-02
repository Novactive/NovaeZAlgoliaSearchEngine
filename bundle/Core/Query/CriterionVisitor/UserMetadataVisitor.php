<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Query\CriterionVisitor;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

final class UserMetadataVisitor implements CriterionVisitor
{
    public function supports(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\UserMetadata &&
               \in_array(
                   $criterion->operator,
                   [
                       Criterion\Operator::EQ,
                       Criterion\Operator::IN
                   ],
                   true
               );
    }

    public function visit(CriterionVisitor $dispatcher, Criterion $criterion, string $additionalOperators = ''): string
    {
        $fieldName = $this->getTargetField($criterion);

        return '('.
               implode(
                   'NOT ' === $additionalOperators ? ' AND ' : ' OR ',
                   array_map(
                       static function ($value) use ($fieldName, $additionalOperators) {
                           return $additionalOperators.$fieldName.'='.$value;
                       },
                       $criterion->value
                   )
               ).
               ')';
    }

    private function getTargetField(Criterion $criterion): string
    {
        switch ($criterion->target) {
            case Criterion\UserMetadata::MODIFIER:
                $fieldName = 'content_version_creator_user_id_i';
                break;
            case Criterion\UserMetadata::OWNER:
                $fieldName = 'content_owner_user_id_i';
                break;
            case Criterion\UserMetadata::GROUP:
                $fieldName = 'content_owner_user_group_id_mi';
                break;
            default:
                throw new NotImplementedException(
                    'No visitor available for target: '.$criterion->target.' with operator: '.$criterion->operator
                );
        }

        return $fieldName;
    }
}
