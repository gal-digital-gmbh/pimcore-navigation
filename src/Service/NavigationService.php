<?php

namespace GalDigitalGmbh\PimcoreNavigation\Service;

use Closure;
use GalDigitalGmbh\PimcoreNavigation\Model\AbstractNavigation;
use GalDigitalGmbh\PimcoreNavigation\Model\Breadcrumbs;
use GalDigitalGmbh\PimcoreNavigation\Model\Menu;
use GalDigitalGmbh\PimcoreNavigation\Model\Route;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Model\Document;
use Pimcore\Navigation\Builder;
use Pimcore\Navigation\Container;
use Pimcore\Navigation\Page;
use Pimcore\Navigation\Page\Document as PageDocument;
use Pimcore\Navigation\Page\Url;
use Pimcore\Tool\ClassUtils;
use Pimcore\Twig\Extension\Templating\Navigation as NavigationExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @phpstan-import-type NavigationOptions from AbstractNavigation
 */
class NavigationService
{
    #[Required]
    public Builder $builder;

    #[Required]
    public NavigationExtension $navigationExtension;

    #[Required]
    public DocumentResolver $documentResolver;

    #[Required]
    public RequestStack $requestStack;

    #[Required]
    public RouterInterface $router;

    /**
     * @return Container<Page>
     */
    public function build(AbstractNavigation $navigation): Container
    {
        if (!($navigation->getOnlyRenderRoutes() ?? false)) {
            $container = $this->getContainer($navigation);
        } else {
            $container = new Container();
        }

        foreach ($navigation->getRoutes() ?? [] as $route) {
            $this->addRoutePage($route, $container, $navigation);
        }

        return $container;
    }

    /**
     * @param Container<Page> $container
     * @param mixed $arguments
     */
    public function render(
        Container $container,
        AbstractNavigation $navigation,
        ?string $rendererName = null,
        ?string $renderMethod = null,
        ...$arguments,
    ): string {
        return $this->navigationExtension->render(
            $container,
            $rendererName ?? 'app_' . strtolower(ClassUtils::getBaseName($navigation)),
            $renderMethod ?? 'render',
            ...array_merge([$navigation], $arguments),
        );
    }

    /**
     * @return Container<Page>
     */
    private function getContainer(AbstractNavigation $navigation): Container
    {
        $root = $navigation->getRoot();

        $container = $this->builder->getNavigation([
            'root'            => $root,
            'active'          => $navigation->getActive(),
            'maxDepth'        => $navigation->getMaxDepth(),
            'pageCallback'    => $navigation->getPageCallback(),
            'htmlMenuPrefix'  => $navigation->getPageIdPrefix(),
            'markActiveTrail' => $navigation->getMarkActiveTrail() ?? true,
            'cache'           => $navigation->getCache() ?? true,
            'cacheLifetime'   => $navigation->getCacheLifetime(),
        ]);

        if ($navigation->getAddRoot()) {
            if (!$root instanceof Document) {
                $root = Document::getById(1);
            }

            if ($root instanceof Document) {
                $rootPage = $this->getRootPage($root, $navigation);

                if ($navigation instanceof Breadcrumbs) {
                    $pages = [];

                    /**
                     * @var Page<Page> $page
                     */
                    foreach ($container as $page) {
                        $pages[] = $page;
                    }

                    foreach ($pages as $page) {
                        $page->setParent($rootPage);
                    }
                }

                $container->addPage($rootPage);
            }
        }

        return $container;
    }

    /**
     * @return Page<Page>
     */
    private function getRootPage(Document $document, AbstractNavigation $navigation): Page
    {
        $id              = $document->getId();
        $currentDocument = $this->documentResolver->getDocument();
        $active          = $currentDocument?->getId() === $id;

        $orderForRoot = $navigation instanceof Menu
            ? ($navigation->getOrderForRoot() ?? -1)
            : -1;

        $page = (new PageDocument())
            ->setId($navigation->getPageIdPrefix() . $id)
            ->setActive($active)
            ->setOrder($orderForRoot)
            ->setLabel($document->getProperty('navigation_name'))
            ->setTitle($document->getProperty('navigation_title'))
            ->setClass($document->getProperty('navigation_class'))
            ->setTarget($document->getProperty('navigation_target'))
            ->setAccesskey($document->getProperty('navigation_accesskey'))
        ;

        if ($page instanceof PageDocument) {
            $page
                ->setRelation($document->getProperty('navigation_relation'))
                ->setTabindex($document->getProperty('navigation_tabindex'))
                ->setDocument($document)
            ;
        }

        if ($page instanceof Url && !$document instanceof Document\Folder) {
            $page->setUri($this->getUriForDocument($document));
        }

        if ($document->getProperty('navigation_exclude') || !$document->getPublished()) {
            $page->setVisible(false);
        }

        $this->addClasses($page, $navigation, $active, true);
        $this->handlePageCallback($page, $navigation, $document);

        return $page;
    }

    /**
     * @param Route<Page> $route
     * @param Container<Page> $container
     */
    private function addRoutePage(Route $route, Container $container, AbstractNavigation $navigation): void
    {
        $request          = $this->requestStack->getCurrentRequest();
        $currentRouteName = (string) $request?->attributes->get('_route');
        $params           = $route->getParams() ?? $request?->attributes->get('_route_params') ?? [];
        $name             = $route->getName();
        $uri              = $route->getPath() ?? $this->router->generate($name ?? $currentRouteName, $params);
        $parent           = $this->findClosestParent($container, $uri);
        $active           = ($request?->getPathInfo() ?? '') === $uri;

        $route
            ->setUri($uri)
            ->setActive($active)
            ->setId($navigation->getPageIdPrefix() . ($route->getId() ?? uniqid()))
        ;

        $this->addClasses($route, $navigation, $active);

        if (!$parent) {
            $container->addPage($route);
        } else {
            $parent->addPage($route);
        }

        $parent = $route->getParent();

        if ($parent instanceof Page && ($currentRouteName === $name)) {
            $parent->setClass(trim($parent->getClass() . ' ' . AbstractNavigation::PIMCORE_CSS_CLASS_ACTIVE_TRAIL));
        }

        $this->handlePageCallback($route, $navigation);
    }

    private function getUriForDocument(Document $document): string
    {
        return $document->getFullPath()
            . $document->getProperty('navigation_parameters')
            . $document->getProperty('navigation_anchor');
    }

    /**
     * @param Container<Page> $container
     *
     * @return Page<Page>
     */
    private function findClosestParent(Container $container, string $uri): ?Page
    {
        $pathFragments = explode('/', parse_url($uri, PHP_URL_PATH) ?: '');

        array_pop($pathFragments);

        $parentUri = implode('/', $pathFragments);

        if (!$parentUri) {
            return null;
        }

        $parent = null;

        if (str_contains($uri, $parentUri)) {
            $parent = $container->findOneBy('uri', $parentUri);

            if (!$parent) {
                $parent = $container->findOneBy('realFullPath', $parentUri);
            }
        }

        if ($parent && !$parent->getLabel()) {
            return null;
        }

        return $parent ?? $this->findClosestParent($container, $parentUri);
    }

    /**
     * @param Page<Page> $page
     */
    private function addClasses(
        Page $page,
        AbstractNavigation $navigation,
        bool $active,
        bool $isRoot = false,
    ): void {
        $classes = [trim($page->getClass() ?? '')];

        if ($isRoot) {
            $classes[] = trim($navigation->getRootClass() ?? '');
            $classes[] = AbstractNavigation::PIMCORE_CSS_CLASS_MAIN;
        }

        if ($active) {
            $classes[] = AbstractNavigation::PIMCORE_CSS_CLASS_ACTIVE;

            if ($isRoot) {
                $classes[] = AbstractNavigation::PIMCORE_CSS_CLASS_MAINACTIVE;
            }
        }

        $page->setClass(trim(implode(' ', $classes)));
    }

    /**
     * @param Page<Page> $page
     */
    private function handlePageCallback(
        Page $page,
        AbstractNavigation $navigation,
        ?Document $document = null,
    ): void {
        $pageCallback = $navigation->getPageCallback();

        if ($pageCallback instanceof Closure) {
            $pageCallback($page, $document);
        }
    }
}
