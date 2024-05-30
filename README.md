Document Generator bundle
---

### What is it?
Document Generator bundle is step based document generator 
library for projects using symfony and api-platform.

### System Requirements
PHP 8.2+  
Symfony 6.3+

### Installation
The recommended way to install is via Composer:

```shell
composer require whitedigital-eu/document-generator-bundle
```

After this, you need to update your database schema to use Document entity.  
If using migrations:
```shell
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```
If by schema update:
```shell
bin/console doctrine:schema:update --force
``` 
This will enable new `Document` api resource with `/api/documents` iri.

### Components
1. Task - requirement and config class that extends AbstractDocumentTask
   and defines most of the logical parts in document generator.
2. Transformer - class that transforms input data into structure defined in your
   defined task.  
   For now, package only comes with transformer that turns twig into pdf but this can be changed
   within project by creating your own transformers by implementing TransformerInterface
3. Generator - class that produces a generated result (binary data of path to a file), from defined data, template and additional, optional context
4. GeneratorContext - class that holds additional, optional context for generator to perform the document generation
5. Html to Pdf service - reusable service to generate pdf files from html. Here used
   within transformer but can be used elsewhere. Uses gotenberg in background.

### Usage
1. Define new `Task` that extends `AbstractDocumentTask`
```php
use Doctrine\ORM\EntityManagerInterface;
use WhiteDigital\DocumentGeneratorBundle\Generator\TwigToPdfGenerator;
use WhiteDigital\DocumentGeneratorBundle\Task\AbstractDocumentTask;

class TestDocumentTask extends AbstractDocumentTask
{

}
```
and define required functions:  
__construct
```php
public function __construct(
     EntityManagerInterface $em,
     TwigToPdfGenerator $twigToPdfGenerator,
     ReceiptTransformer $receiptTransformer,
 ) {
     parent::__construct($em, $twigToPdfGenerator, $receiptTransformer);
 }
```
AbstractDocumentTask requires 3 services:
1. EntityManagerInterface
2. Generator - TwigToPdfGenerator from library or other defined one
3. Transformer - service defined by you (more about it below)

getTransformerFields
```php
public function getTransformerFields(): array
{
    return [
        'field1' => 'string',
        'array1' => [
            'array2' => [
                'field2' => 'bool',
            ],
        ],
    ]; // array of fields that Transformer can contain
}
```
getTemplatePath
```php
public function getTemplatePath(): string
{
    return '/path/to/defined/template.html.twig'; // must be in directory visible by twig, usually /templates
}
```
getType
```php
public function getType(): string 
{
    return 'TEST'; // simple identifier to separate different documents
}
```
getInputType
```php
public function getInputType(): string
{
    return 'array'; // what type of data does Transformer require as input
}
```
2. Define new Transformer that implements TransformerInterface:  
```php
class TestTransformer implements Transformer
{
    public function getTransformedFields(mixed $input): array {
        return [
            'field1' => 'abc',
             'array1' => [
                'array2' => [
                    'field2' => false,
                ],
            ],
            'field3' => 3
        ];
    }
}
```
transformer can return none, some or all of the fields listed in getTransformedFields an array of fields

3. Use generation
Defined task can now be used as a service
```php
public function __construct(private TestDocumentTask $task)
{
}

public function abc()
{
    return $this->task->generate([1, 2, 3]);
}
```
Input of generate function must be type defined in Transformer.  
generate function will return already Document entity or throw an error
if something is wrong.

### Regenerate
If for any reason you need to regenerate existing document,
you can use built-in DocumentTask with existing document entity.

```php
use WhiteDigital\DocumentGeneratorBundle\Entity\Document;use WhiteDigital\DocumentGeneratorBundle\Task\DocumentTask;
use Doctrine\ORM\EntityManagerInterface;

public function __construct(private DocumentTask $task, private EntityManagerInterface $em)
{
}

public function abc()
{
    return $this->task->generate($this->em->getRepository(Document::class)->find(123));
}
```
