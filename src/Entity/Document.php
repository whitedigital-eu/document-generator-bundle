<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\DocumentGeneratorBundle\Api\Resource\DocumentResource;
use WhiteDigital\EntityResourceMapper\Attribute\Mapping;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;
use WhiteDigital\StorageItemResource\Entity\StorageItem;

#[ORM\Entity]
#[ORM\Table(name: 'wd_document')]
#[Mapping(DocumentResource::class)]
class Document extends BaseEntity
{
    use Id;

    #[ORM\Column]
    private ?string $type = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $sourceData = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $templateData = null;

    #[ORM\ManyToOne]
    private ?StorageItem $file = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSourceData(): ?array
    {
        return $this->sourceData;
    }

    public function setSourceData(?array $sourceData): self
    {
        $this->sourceData = $sourceData;

        return $this;
    }

    public function getTemplateData(): ?array
    {
        return $this->templateData;
    }

    public function setTemplateData(?array $templateData): self
    {
        $this->templateData = $templateData;

        return $this;
    }

    public function getFile(): ?StorageItem
    {
        return $this->file;
    }

    public function setFile(?StorageItem $file): self
    {
        $this->file = $file;

        return $this;
    }
}
