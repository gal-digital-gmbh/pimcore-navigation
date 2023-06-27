<?php

declare(strict_types=1);

namespace GalDigitalGmbh\PimcoreNavigation\Model;

use Pimcore\Model\Document as ModelDocument;
use Pimcore\Navigation\Page;
use Pimcore\Navigation\Page\Document;

final class Route extends Document
{
    private ?string $name = null;

    /**
     * @var mixed[]
     */
    private ?array $params = null;

    private ?string $path = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return Route<Page>
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param mixed[] $params
     */
    public function setParams(?array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function setDocument(ModelDocument $document): static
    {
        return $this;
    }

    public function setDocumentId(int $documentId): void
    {
    }

    public function setDocumentType(string $documentType): void
    {
    }

    public function setRealFullPath(string $realFullPath): void
    {
    }
}
