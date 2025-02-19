<?php

declare(strict_types=1);

namespace GalDigitalGmbh\PimcoreNavigation\Renderer;

use GalDigitalGmbh\PimcoreNavigation\Model\AbstractNavigation;
use GalDigitalGmbh\PimcoreNavigation\Model\Menu as ModelMenu;
use Pimcore\Navigation\Page;

use function trim;

/**
 * @phpstan-import-type ActivePage from AbstractRenderer
 */
final class Menu extends AbstractRenderer
{
    /**
     * @var array<int, Page<Page>>
     */
    private array $prevPagesByDepth = [];

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
        if (!$this->navigation instanceof ModelMenu) {
            return ['', -1];
        }

        $pageIterator           = $this->getPageIterator();
        $minDepth               = $this->navigation->getMinDepth();
        $prevDepth              = -1;
        $this->prevPagesByDepth = [];

        /**
         * @var Page<Page> $page
         */
        foreach ($pageIterator as $page) {
            $depth = $pageIterator->getDepth();

            if ($depth < $minDepth || !$this->accept($page)) {
                continue;
            }

            $depth -= $minDepth;

            if ($depth > $prevDepth) {
                $ulAttribs = $this->getAttribsWithClass($depth, 'ul');
                $html .= '<ul' . $this->_htmlAttribs($ulAttribs) . '>';

                $useDepth    = $depth - 1;
                $currentPage = null;

                if ($depth > 0) {
                    $currentPage = $this->prevPagesByDepth[$useDepth];
                }

                $html .= $this->renderInsertionTemplate(self::TMPL_ID_AFTR_OPN_UL, $currentPage, $useDepth);
            } elseif ($prevDepth > $depth) {
                for ($i = $prevDepth; $i > $depth; $i -= 1) {
                    $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_LI, $this->prevPagesByDepth[$i], $i);
                    $html .= '</li>';
                    $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_UL, $this->prevPagesByDepth[$i - 1], $i - 1);
                    $html .= '</ul>';
                }

                $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_LI, $this->prevPagesByDepth[$depth], $depth);
                $html .= '</li>';
            } else {
                $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_LI, $this->prevPagesByDepth[$depth], $depth);
                $html .= '</li>';
            }

            $html .= $this->renderOpeningLi($page, $depth);
            $html .= $this->htmlify($page, $depth);

            $prevDepth                      = $depth;
            $this->prevPagesByDepth[$depth] = $page;
        }

        return [$html, $prevDepth];
    }

    protected function renderNavigationClosing(string $html, int $prevDepth): string
    {
        if (!$html) {
            return '';
        }

        for ($i = $prevDepth + 1; $i > 0; $i -= 1) {
            $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_LI, $this->prevPagesByDepth[$i - 1] ?? null, $i - 1);
            $html .= '</li>';
            $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_UL, $this->prevPagesByDepth[$i - 2] ?? null, $i - 2);
            $html .= '</ul>';
        }

        return parent::renderNavigationClosing($html, $prevDepth);
    }

    /**
     * @param string[] $classes
     */
    protected function modifyClasses(array $classes): string
    {
        if (!$this->navigation instanceof ModelMenu) {
            return '';
        }

        $classesToSet = '';

        foreach ($classes as $class) {
            if ($class === AbstractNavigation::PIMCORE_CSS_CLASS_ACTIVE) {
                $classesToSet .= ' ' . trim((string) ($this->navigation->getPageActiveClass() ?? $class));
            } elseif ($class === AbstractNavigation::PIMCORE_CSS_CLASS_MAIN) {
                $classesToSet .= ' ' . trim((string) ($this->navigation->getPageMainClass() ?? $class));
            } elseif ($class === AbstractNavigation::PIMCORE_CSS_CLASS_MAINACTIVE) {
                $classesToSet .= ' ' . trim((string) ($this->navigation->getPageMainActiveClass() ?? $class));
            } elseif ($class === AbstractNavigation::PIMCORE_CSS_CLASS_ACTIVE_TRAIL) {
                $classesToSet .= ' ' . trim((string) ($this->navigation->getPageActiveTrailClass() ?? $class));
            } else {
                $classesToSet .= ' ' . $class;
            }

            $classesToSet = trim($classesToSet);
        }

        return $classesToSet;
    }

    /**
     * @param Page<Page> $page
     *
     * @return string[]
     */
    protected function getAdditionalLiClasses(Page $page, int $depth): array
    {
        if (!$this->navigation instanceof ModelMenu) {
            return [];
        }

        $liClasses = [];

        if ($depth === 0) {
            $liClasses[] = trim($this->navigation->getLiMainClass() ?? '');
        }

        if ($page->isActive(true)) {
            $liClasses[] = trim($this->navigation->getLiActiveClass() ?? '');

            if ($this->hasActivePages($page)) {
                $liClasses[] = trim($this->navigation->getLiActiveTrailClass() ?? '');
            } elseif ($depth === 0) {
                $liClasses[] = trim($this->navigation->getLiMainActiveClass() ?? '');
            }
        }

        if ($this->hasVisibleChildren($page, $depth)) {
            $liClasses[] = trim($this->navigation->getLiWithSubpagesClass() ?? '');
        }

        return $liClasses;
    }
}
