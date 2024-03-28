<?php declare(strict_types=1);

namespace WhiteDigital\DocumentGeneratorBundle\Api\Resource;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Serializer\Filter\GroupFilter;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\Groups;
use WhiteDigital\DocumentGeneratorBundle\DataProvider\DocumentDataProvider;
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Resource\BaseResource;
use WhiteDigital\StorageItemResource\Api\Resource\StorageItemResource;

#[ApiResource(
    shortName: 'Document',
    operations: [
        new Get(
            requirements: ['id' => '\d+', ],
            write: false,
        ),
    ],
    normalizationContext: ['groups' => [self::READ, ]],
    provider: DocumentDataProvider::class
)]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'groups', 'overrideDefaultGroups' => false, ]),]
#[Mapping(Document::class)]
class DocumentResource extends BaseResource
{
    public const PREFIX = 'document:';

    public const READ = self::PREFIX . 'read'; // document:read

    #[ApiProperty(identifier: true)]
    #[Groups([self::READ, ])]
    public mixed $id = null;

    #[Groups([self::READ, ])]
    public ?string $type = null;

    #[Groups([self::READ, ])]
    public ?array $sourceData = null;

    #[Groups([self::READ, ])]
    public ?array $templateData = null;

    #[Groups([self::READ, ])]
    public ?StorageItemResource $file = null;

    #[Groups([self::READ, ])]
    public ?DateTimeImmutable $createdAt = null;

    #[Groups([self::READ, ])]
    public ?DateTimeImmutable $updatedAt = null;
}
