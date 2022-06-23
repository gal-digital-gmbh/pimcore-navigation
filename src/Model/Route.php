<?php

namespace GalDigitalGmbh\PimcoreNavigation\Model;

use Pimcore\Model\Document as ModelDocument;
use Pimcore\Navigation\Page;
use Pimcore\Navigation\Page\Document;

class Route extends Document
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
     *
     * @return Route<Page>
     */
    public function setParams(?array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @return Route<Page>
     */
    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param ModelDocument $document
     *
     * @return Route<Page>
     */
    public function setDocument($document)
    {
        return $this;
    }

    /**
     * @param int $documentId
     *
     * @return Route<Page>
     */
    public function setDocumentId($documentId)
    {
        return $this;
    }

    /**
     * @param string $documentType
     *
     * @return Route<Page>
     */
    public function setDocumentType($documentType)
    {
        return $this;
    }

    /**
     * @param string $realFullPath
     *
     * @return Route<Page>
     */
    public function setRealFullPath($realFullPath)
    {
        return $this;
    }
}
