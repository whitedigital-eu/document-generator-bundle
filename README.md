# Document Generator Bundle

## Overview
Document Generator bundle is a step-based document generator library for Symfony and API Platform projects. It provides a flexible way to generate documents (like PDFs) from templates using a transformer-based architecture.

## Features
- Template-based document generation
- PDF generation from Twig templates
- Extensible transformer system
- API Platform integration
- Document entity with API endpoints
- Support for document regeneration

## System Requirements
- PHP 8.2+
- Symfony 6.3+
- Gotenberg service (for PDF generation)

## Installation

1. Install via Composer:
```shell
composer require whitedigital-eu/document-generator-bundle
```

2. Update your database schema:

Using migrations (recommended):
```shell
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

Or using schema update:
```shell
bin/console doctrine:schema:update --force
```

This will create the `Document` entity table and enable the `/api/documents` API endpoint.

## Core Components

### 1. Task
Tasks are the main configuration units that define how documents should be generated. They extend `AbstractDocumentTask` and specify:
- Input data requirements
- Template location
- Document type
- Transformer configuration

### 2. Transformer
Transformers convert input data into the structure required by your templates. They implement `TransformerInterface` and handle data transformation logic.

### 3. Generator
Generators produce the final document output. The bundle includes `TwigToPdfGenerator` by default, but you can create custom generators.

### 4. GeneratorContext
Holds additional configuration for the generation process, such as:
- PDF options
- Custom headers
- Metadata

### 5. Html to PDF Service
A reusable service for PDF generation using Gotenberg.

## Usage Guide

### 1. Creating a Task

Create a new task class that extends `AbstractDocumentTask`:

```php
use Doctrine\ORM\EntityManagerInterface;
use WhiteDigital\DocumentGeneratorBundle\Generator\TwigToPdfGenerator;
use WhiteDigital\DocumentGeneratorBundle\Task\AbstractDocumentTask;

class InvoiceDocumentTask extends AbstractDocumentTask
{
    public function __construct(
        EntityManagerInterface $em,
        TwigToPdfGenerator $twigToPdfGenerator,
        InvoiceTransformer $transformer,
    ) {
        parent::__construct($em, $twigToPdfGenerator, $transformer);
    }

    public function getTransformerFields(): array
    {
        return [
            'invoiceNumber' => 'string',
            'customer' => [
                'name' => 'string',
                'address' => 'string',
            ],
            'items' => [
                'description' => 'string',
                'amount' => 'float',
            ],
        ];
    }

    public function getTemplatePath(): string
    {
        return 'documents/invoice.html.twig';
    }

    public function getType(): string 
    {
        return 'INVOICE';
    }

    public function getInputType(): string
    {
        return 'array';
    }
}
```

### 2. Creating a Transformer

Create a transformer that implements `TransformerInterface`:

```php
use WhiteDigital\DocumentGeneratorBundle\Transformer\TransformerInterface;

class InvoiceTransformer implements TransformerInterface
{
    public function getTransformedFields(mixed $input): array 
    {
        return [
            'invoiceNumber' => $input['number'],
            'customer' => [
                'name' => $input['customerName'],
                'address' => $input['customerAddress'],
            ],
            'items' => array_map(fn($item) => [
                'description' => $item['desc'],
                'amount' => $item['price'],
            ], $input['items']),
        ];
    }
}
```

### 3. Using the Generator

```php
class InvoiceService
{
    public function __construct(
        private InvoiceDocumentTask $documentTask
    ) {}

    public function generateInvoice(array $data): Document
    {
        // Optional: Configure generation context
        $context = new GeneratorContext();
        $context->setPdfOptions([
            'marginTop' => 20,
            'marginBottom' => 20,
        ]);

        return $this->documentTask->generate($data, $context);
    }
}
```

### 4. Document Regeneration

To regenerate an existing document:

```php
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;
use WhiteDigital\DocumentGeneratorBundle\Task\DocumentTask;

class DocumentService
{
    public function __construct(
        private DocumentTask $task,
        private EntityManagerInterface $em,
    ) {}

    public function regenerateDocument(int $documentId): Document
    {
        $document = $this->em->getRepository(Document::class)->find($documentId);
        return $this->task->generate($document);
    }
}
```

## API Endpoints

The bundle provides the following API endpoints:

- `GET /api/documents`: List all documents
- `GET /api/documents/{id}`: Get a specific document
- `GET /api/documents/{id}/download`: Download the document file

## Configuration Options

You can configure the bundle in your `config/packages/white_digital_document_generator.yaml`:

```yaml
white_digital_document_generator:
    pdf:
        gotenberg_url: 'http://localhost:3000'
        default_options:
            marginTop: 20
            marginBottom: 20
            marginLeft: 20
            marginRight: 20
    storage:
        path: '%kernel.project_dir%/var/storage/documents'
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This bundle is released under the MIT license.
