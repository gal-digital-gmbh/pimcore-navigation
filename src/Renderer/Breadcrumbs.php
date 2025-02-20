<?php

declare(strict_types=1);

namespace GalDigitalGmbh\PimcoreNavigation\Renderer;

use GalDigitalGmbh\PimcoreNavigation\Model\AbstractNavigation;
use GalDigitalGmbh\PimcoreNavigation\Model\Breadcrumbs as ModelBreadcrumbs;
use Pimcore\Navigation\Container;
use Pimcore\Navigation\Page;

use function array_filter;
use function array_map;
use function implode;
use function in_array;
use function trim;

/**
 * @phpstan-import-type ActivePage from AbstractRenderer
 */
final class Breadcrumbs extends AbstractRenderer
{
    /**
     * @phpstan-param ActivePage $active
     *
     * @return array{
     *     0: string,
     *     1: int,
     * }
     */
    protected function renderInner(string $html, array $active): array
    {
        if (!$this->navigation instanceof ModelBreadcrumbs) {
            return ['', -1];
        }

        $activePage = $active['page'] ?? null;
        $depth      = $active['depth'] ?? 0;

        if (!$activePage instanceof Page) {
            return ['', -1];
        }

        $innerHtml = '';

        if (!($this->navigation->getDisableLeaf() ?? false) && $this->accept($activePage)) {
            $activePage->setClass(trim($activePage->getClass() . ' ' . trim($this->navigation->getLeafClass() ?? '')));

            $innerHtml .= $this->renderOpeningLi($activePage, $depth);
            $innerHtml .= $this->htmlify($activePage, $depth, !$this->navigation->getAddLinkToLeaf());
            $innerHtml .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_LI, $activePage, $depth);
            $innerHtml .= '</li>';
        }

        $innerHtml = $this->renderBreadcrumbPages(
            $innerHtml,
            $activePage,
            $depth,
            $depth,
        );

        $ulAttribs = $this->getAttribsWithClass(0, 'ul');
        $innerHtml = '<ul' . $this->_htmlAttribs($ulAttribs) . '>'
            . $this->renderInsertionTemplate(self::TMPL_ID_AFTR_OPN_UL, $activePage, 0)
            . $innerHtml;

        $innerHtml .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_UL, $activePage, 0);
        $innerHtml .= '</ul>';

        return [$html . $innerHtml, -1];
    }

    /**
     * @param string[] $classes
     */
    protected function modifyClasses(array $classes): string
    {
        $classes = array_filter(
            $classes,
            static fn (string $class) => !in_array($class, AbstractNavigation::PIMCORE_CSS_CLASSES, true),
        );

        return implode(' ', array_map('trim', $classes));
    }

    /**
     * @param Container<Page>|Page<mixed> $page
     */
    private function renderBreadcrumbPages(
        string $html,
        Container|Page|null $page,
        int $depth,
        int $maxDepth,
    ): string {
        if (!$this->navigation instanceof ModelBreadcrumbs) {
            return $html;
        }

        $isPage = $page instanceof Page;
        $parent = null;
        $depth -= 1;

        if ($isPage) {
            $parent = $page->getParent();
        }

        if ($parent instanceof Page && $this->accept($parent)) {
            $renderSeparator = true;

            if ($depth === $maxDepth - 1) {
                $renderSeparator = !$this->navigation->getDisableLeaf();
            }

            $renderSeparator = $renderSeparator && (!$isPage || $this->accept($page));

            $html = $this->renderOpeningLi($parent, $depth)
                . $this->htmlify($parent, $depth)
                . ($renderSeparator ? ($this->navigation->getSeparator() ?? '') : '')
                . $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_LI, $parent, $depth)
                . '</li>'
                . $html;
        }

        if ($parent === null || $parent === $this->container) {
            return $html;
        }

        return $this->renderBreadcrumbPages(
            $html,
            $parent,
            $depth,
            $maxDepth,
        );
    }
}
