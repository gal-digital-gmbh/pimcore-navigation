<?php

namespace GalDigitalGmbh\PimcoreNavigation\Renderer;

use GalDigitalGmbh\PimcoreNavigation\Model\AbstractNavigation;
use Closure;
use Pimcore\Navigation\Container;
use Pimcore\Navigation\Page;
use Pimcore\Navigation\Page\Document;
use Pimcore\Navigation\Renderer\AbstractRenderer as BaseAbstractRenderer;
use RecursiveIteratorIterator;

/**
 * @phpstan-type ActivePage array{
 *     page?: Page,
 *     depth?: int,
 * }
 *
 * @phpstan-import-type PageIdCallback from AbstractNavigation
 */
abstract class AbstractRenderer extends BaseAbstractRenderer
{
    protected const TMPL_ID_AFTR_OPN_NAV  = 'afterOpeningNav';
    protected const TMPL_ID_BFR_CLS_NAV   = 'beforeClosingNav';
    protected const TMPL_ID_AFTR_OPN_UL   = 'afterOpeningUl';
    protected const TMPL_ID_BFR_CLS_UL    = 'beforeClosingUl';
    protected const TMPL_ID_AFTR_OPN_LI   = 'afterOpeningLi';
    protected const TMPL_ID_BFR_CLS_LI    = 'beforeClosingLi';
    protected const TMPL_ID_AFTR_OPN_PAGE = 'afterOpeningPage';
    protected const TMPL_ID_BFR_CLS_PAGE  = 'beforeClosingPage';

    protected AbstractNavigation $navigation;

    /**
     * @var Container<Page>
     */
    protected Container $container;

    /**
     * @param Container<Page> $container
     * @param mixed[] $arguments
     */
    public function render(Container $container, ...$arguments): string
    {
        $this->init($container, ...$arguments);

        /**
         * @phpstan-var ActivePage
         */
        $active = $this->findActive(
            $this->container,
            $this->navigation->getMinDepth(),
            $this->navigation->getMaxDepth() ?? -1,
        );

        $html               = $this->renderNavigationOpening();
        [$html, $prevDepth] = $this->renderInner($html, $active);

        return $this->renderNavigationClosing($html, $prevDepth);
    }

    /**
     * @param Page<Page> $page
     */
    public function hasActivePages(Page $page): bool
    {
        if (!$page->hasPages()) {
            return false;
        }

        foreach ($page->getPages() as $page) {
            if ($page->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Page<Page> $page
     */
    public function htmlify(Page $page, int $depth = 0, bool $forceSpanTag = false): string
    {
        $tag     = 'span';
        $attribs = $this->getCommonAttribsFromPage($page, $depth);

        if ($page->getHref() && !$forceSpanTag) {
            $tag     = 'a';
            $attribs = $this->getLinkAttribsFromPage($page, $attribs);
        }

        if (
            $this->navigation->getRenderPageWithSubpagesAsButton()
            && $this->hasVisibleChildren($page, $depth)
            && !$forceSpanTag
        ) {
            $tag             = 'button';
            $attribs['type'] = 'button';

            unset($attribs['href']);
        }

        $attribs = array_merge(
            $attribs,
            $page->getCustomHtmlAttribs(),
        );

        $label = $page->getLabel() ?? '';
        $raw   = $this->navigation->getRenderPageLabelRaw();

        return '<' . $tag . $this->_htmlAttribs($attribs) . '>'
            . $this->renderInsertionTemplate(self::TMPL_ID_AFTR_OPN_PAGE, $page, $depth)
            . ($raw ? $label : htmlspecialchars($label, ENT_COMPAT, 'UTF-8'))
            . $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_PAGE, $page, $depth)
            . '</' . $tag . '>';
    }

    /**
     * @phpstan-param ActivePage $active
     *
     * @return array{
     *     0: string,
     *     1: int,
     * }
     */
    abstract protected function renderInner(string $html, array $active): array;

    /**
     * @param Page<Page> $page
     */
    protected function renderOpeningLi(Page $page, int $depth): string
    {
        $liClasses = $this->getAdditionalLiClasses($page, $depth);

        if ($this->navigation->getAddPageClassToLi()) {
            $liClasses[] = $this->getClassesFromPage($page, $depth);
        }

        $liAttribs = $this->getAttribsWithClass($depth, 'li', $liClasses);
        $html      = '<li' . $this->_htmlAttribs($liAttribs) . '>';
        $html .= $this->renderInsertionTemplate(self::TMPL_ID_AFTR_OPN_LI, $page, $depth);

        return $html;
    }

    /**
     * @phpstan-param 'nav'|'ul'|'li'|'page' $identifier
     *
     * @param string[] $additionalClasses
     *
     * @return string[]
     */
    protected function getAttribsWithClass(
        int $depth,
        string $identifier,
        array $additionalClasses = [],
    ): array {
        $attr = $this->getAttribs($depth, $identifier);

        return array_merge($attr, [
            'class' => $this->getClasses($depth, $identifier, $additionalClasses, $attr),
        ]);
    }

    /**
     * @param Page<Page> $page
     *
     * @return string[]
     */
    protected function getAdditionalLiClasses(Page $page, int $depth): array
    {
        return [];
    }

    /**
     * @param string[] $classes
     */
    protected function modifyClasses(array $classes): string
    {
        return implode(' ', $classes);
    }

    /**
     * @param Page<Page> $page
     */
    protected function hasVisibleChildren(Page $page, int $depth): bool
    {
        return $page->hasVisiblePages()
            && ($depth + 1 < ($this->navigation->getMaxDepth() ?? PHP_INT_MAX));
    }

    /**
     * @phpstan-param self::TMPL_ID_* $identifier
     *
     * @param Page<Page> $page
     */
    protected function renderInsertionTemplate(string $identifier, ?Page $page, int $depth): string
    {
        $template = $this->navigation->{'getTemplate' . ucfirst($identifier)}();

        if (!$template) {
            return '';
        }

        return $this->templatingEngine->render($template, array_merge([
            'page'       => $page,
            'container'  => $this->container,
            'navigation' => $this->navigation,
            'depth'      => $depth,
        ], $this->navigation->getTemplateParams() ?? []));
    }

    /**
     * @return RecursiveIteratorIterator<Container>
     */
    protected function getPageIterator(): RecursiveIteratorIterator
    {
        $maxDepth = $this->navigation->getMaxDepth();

        $iterator = new RecursiveIteratorIterator(
            $this->container,
            RecursiveIteratorIterator::SELF_FIRST,
        );

        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }

        return $iterator;
    }

    /**
     * @param Container<Page> $container
     * @param mixed[] $arguments
     */
    private function init(Container $container, ...$arguments): void
    {
        /**
         * @var AbstractNavigation
         */
        [$navigation] = $arguments;

        $this->navigation = $navigation;
        $this->container  = $container;

        $this->handleIdPrefix();
        $this->setRenderInvisible($this->navigation->getRenderInvisible() ?? false);
    }

    private function renderNavigationOpening(): string
    {
        if (!($this->navigation->getAddNavTag() ?? true)) {
            return '';
        }

        $navAttribs = $this->getAttribsWithClass(0, 'nav');
        $html       = '<nav' . $this->_htmlAttribs($navAttribs) . '>';
        $html .= $this->renderInsertionTemplate(self::TMPL_ID_AFTR_OPN_NAV, null, 0);

        return $html;
    }

    private function renderNavigationClosing(string $html, int $prevDepth): string
    {
        if (!$html) {
            return '';
        }

        for ($i = $prevDepth + 1; $i > 0; $i -= 1) {
            $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_LI, null, $i - 1);
            $html .= '</li>';
            $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_UL, null, $i - 1);
            $html .= '</ul>';
        }

        if ($this->navigation->getAddNavTag() ?? true) {
            $html .= $this->renderInsertionTemplate(self::TMPL_ID_BFR_CLS_NAV, null, 0);
            $html .= '</nav>';
        }

        return $html;
    }

    /**
     * @phpstan-param 'nav'|'ul'|'li'|'page' $identifier
     *
     * @return string[]
     */
    private function getAttribs(int $depth, string $identifier): array
    {
        $attr = $this->navigation->{'get' . ucfirst($identifier) . 'Attr'}() ?: [];

        return $attr[$depth] ?? $attr['default'] ?? $attr;
    }

    /**
     * @phpstan-param 'nav'|'ul'|'li'|'page' $identifier
     *
     * @param string[] $additionalClasses
     * @param string[]|null $attr
     */
    private function getClasses(
        int $depth,
        string $identifier,
        array $additionalClasses = [],
        ?array $attr = [],
    ): string {
        $class = $this->navigation->{'get' . ucfirst($identifier) . 'Class'}();

        if (is_array($class)) {
            $class = $class[$depth] ?? $class['default'] ?? '';
        } else {
            $class = (string) $class;
        }

        $class .= ' ' . ($attr['class'] ?? '');
        $class .= trim(' ' . implode(' ', array_map('trim', $additionalClasses)));

        return trim($class);
    }

    /**
     * @param Page<Page> $page
     *
     * @return array<?string>
     */
    private function getCommonAttribsFromPage(Page $page, int $depth): array
    {
        $id               = $page->getId();
        $attribs          = $this->getAttribs($depth, 'page');
        $attribs['title'] = $page->getTitle();
        $attribs['class'] = $this->getClassesFromPage($page, $depth);

        if ($this->navigation->getAddIdToPage() && $id) {
            $pageIdCallback = $this->navigation->getPageIdCallback();

            if ($pageIdCallback instanceof Closure) {
                $id = $this->getHashedId($id, $pageIdCallback);
            }

            $attribs['id'] = $id;
        }

        return $attribs;
    }

    /**
     * @param Page<Page> $page
     * @param array<?string> $attribs
     *
     * @return array<?string>
     */
    private function getLinkAttribsFromPage(Page $page, array $attribs): array
    {
        $attribs['href']      = $page->getHref();
        $attribs['target']    = $page->getTarget();
        $attribs['accesskey'] = $page->getAccessKey();

        if ($page instanceof Document) {
            $attribs['tabindex'] = $page->getTabindex();
            $attribs['rel']      = $page->getRelation();
        }

        if (strstr($attribs['target'] ?? '', 'blank')) {
            $attribs['rel'] ??= '';

            if (!str_contains($attribs['rel'], 'noopener')) {
                $attribs['rel'] .= ' noopener';
            }

            if (!str_contains($attribs['rel'], 'noreferrer')) {
                $attribs['rel'] .= ' noreferrer';
            }

            $attribs['rel'] = trim($attribs['rel']);
        }

        return $attribs;
    }

    /**
     * @param Page<Page> $page
     */
    private function getClassesFromPage(Page $page, int $depth): string
    {
        $classes      = explode(' ', trim($page->getClass() ?? ''));
        $classesToSet = $this->getClasses($depth, 'page');

        return trim($classesToSet . ' ' . $this->modifyClasses($classes));
    }

    /**
     * @param PageIdCallback $pageIdCallback
     */
    private function getHashedId(string $id, Closure $pageIdCallback): string
    {
        $pageIdPrefix = $this->navigation->getPageIdPrefix() ?? '';
        $prefixPos    = strpos($id, $pageIdPrefix);

        if ($prefixPos !== false) {
            $id = substr_replace($id, '', $prefixPos, strlen($pageIdPrefix));
        }

        return $pageIdPrefix . $pageIdCallback($id);
    }

    private function handleIdPrefix(): void
    {
        $pageIdPrefix = $this->navigation->getPageIdPrefix();

        if ($pageIdPrefix) {
            $this->setPrefixForId($pageIdPrefix);
        }

        $this->skipPrefixForId();
    }
}
