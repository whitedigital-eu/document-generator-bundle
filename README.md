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
3. Html to Pdf service - reusable service to generate pdf files from html. Here used
   within transformer but can be used elsewhere. Uses gotenberg in background.

### Usage
1. Define new `Task` that extends `AbstractDocumentTask`
```php

```
