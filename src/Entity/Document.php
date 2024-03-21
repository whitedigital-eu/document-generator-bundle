<?php declare(strict_types = 1);

namespace WhiteDigital\DocumentGeneratorBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use WhiteDigital\EntityResourceMapper\Entity\BaseEntity;
use WhiteDigital\EntityResourceMapper\Entity\Traits\Id;

#[ORM\Entity]
#[ORM\Table(name: 'wd_document')]
class Document extends BaseEntity
{
    use Id;

    #[ORM\Column]
    private ?string $type = null;

    #[ORM\Column]
    private ?string $contentType = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $sourceData = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $templateData = null;

    #[ORM\Column]
    private ?string $filePath = null;

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

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): self
    {
        $this->contentType = $contentType;

        return $this;
    }
}
