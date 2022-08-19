<?php

namespace GalDigitalGmbh\PimcoreNavigation\Model;

use Closure;
use Pimcore\Model\Document;
use Pimcore\Navigation\Page;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type PageCallback Closure(Page $page, ?Document $document): void
 * @phpstan-type PageIdCallback Closure(string $id): string
 *
 * @phpstan-type NavigationOptions array{
 *     root?: ?Document,
 *     active?: ?Document,
 *     routes?: ?Route[],
 *     minDepth?: ?int,
 *     maxDepth?: ?int,
 *     cache?: bool|string|null,
 *     cacheLifetime?: ?int,
 *     pageCallback?: ?PageCallback,
 *     markActiveTrail?: ?bool,
 *     addIdToPage?: ?bool,
 *     pageIdPrefix?: ?string,
 *     pageIdCallback?: ?PageIdCallback,
 *     renderInvisible?: ?bool,
 *     renderPageLabelRaw?: ?bool,
 *     onlyRenderRoutes?: ?bool,
 *     renderPageWithSubpagesAsButton?: ?bool,
 *     addPageClassToLi?: ?bool,
 *     addRoot?: ?bool,
 *     orderForRoot?: ?int,
 *     rootClass?: ?string,
 *     addNavTag?: ?bool,
 *     navClass?: ?string,
 *     navAttr?: ?string[],
 *     ulClass?: string|string[]|null,
 *     ulAttr?: string[]|string[][]|null,
 *     liClass?: string|string[]|null,
 *     liAttr?: string[]|string[][]|null,
 *     liActiveClass?: ?string,
 *     liMainClass?: ?string,
 *     liMainActiveClass?: ?string,
 *     liActiveTrailClass?: ?string,
 *     liWithSubpagesClass?: ?string,
 *     pageClass?: string|string[]|null,
 *     pageAttr?: string[]|string[][]|null,
 *     pageActiveClass?: string|bool|null,
 *     pageMainClass?: string|bool|null,
 *     pageMainActiveClass?: string|bool|null,
 *     pageActiveTrailClass?: string|bool|null,
 *     templateAfterOpeningNav?: ?string,
 *     templateBeforeClosingNav?: ?string,
 *     templateAfterOpeningUl?: ?string,
 *     templateBeforeClosingUl?: ?string,
 *     templateAfterOpeningLi?: ?string,
 *     templateBeforeClosingLi?: ?string,
 *     templateAfterOpeningPage?: ?string,
 *     templateBeforeClosingPage?: ?string,
 *     leafClass?: ?string,
 *     disableLeaf?: ?bool,
 *     addLinkToLeaf?: ?bool,
 *     separator?: ?string,
 * }
 *
 * @method ?Document getRoot()
 * @method self setRoot(?Document $root)
 * @method ?Document getActive()
 * @method self setActive(?Document $active)
 * @method ?Route[] getRoutes()
 * @method self setRoutes(?Route[] $routes)
 * @method ?int getMinDepth()
 * @method self setMinDepth(?int $minDepth)
 * @method ?int getMaxDepth()
 * @method self setMaxDepth(?int $maxDepth)
 * @method bool|string|null getCache()
 * @method self setCache(bool|string|null $cache)
 * @method ?int getCacheLifetime()
 * @method self setCacheLifetime(?int $cacheLifetime)
 * @method ?PageCallback getPageCallback()
 * @method self setPageCallback(?PageCallback $pageCallback)
 * @method ?bool getMarkActiveTrail()
 * @method self setMarkActiveTrail(?bool $markActiveTrail)
 * @method ?bool getAddIdToPage()
 * @method self setAddIdToPage(?bool $addIdToPage)
 * @method ?string getPageIdPrefix()
 * @method self setPageIdPrefix(?string $pageIdPrefix)
 * @method ?PageIdCallback getPageIdCallback()
 * @method self setPageIdCallback(?PageIdCallback $pageIdCallback)
 * @method ?bool getRenderInvisible()
 * @method self setRenderInvisible(?bool $renderInvisible)
 * @method ?bool getRenderPageLabelRaw()
 * @method self setRenderPageLabelRaw(?bool $renderPageLabelRaw)
 * @method ?bool getOnlyRenderRoutes()
 * @method self setOnlyRenderRoutes(?bool $onlyRenderRoutes)
 * @method ?bool getAddPageClassToLi()
 * @method self setAddPageClassToLi(?bool $addPageClassToLi)
 * @method ?bool getRenderPageWithSubpagesAsButton()
 * @method self setRenderPageWithSubpagesAsButton(?bool $renderPageWithSubpagesAsButton)
 * @method ?bool getAddRoot()
 * @method self setAddRoot(?bool $addRoot)
 * @method ?string getRootClass()
 * @method self setRootClass(?string $rootClass)
 * @method ?bool getAddNavTag()
 * @method self setAddNavTag(?bool $addNavTag)
 * @method ?string getNavClass()
 * @method self setNavClass(?string $navClass)
 * @method ?array getNavAttr()
 * @method self setNavAttr(?array $navAttr)
 * @method string|array|null getUlClass()
 * @method self setUlClass(string|array|null $ulClass)
 * @method ?array getUlAttr()
 * @method self setUlAttr(?array $ulAttr)
 * @method string|array|null getLiClass()
 * @method self setLiClass(string|array|null $liClass)
 * @method ?array getLiAttr()
 * @method self setLiAttr(?array $liAttr)
 * @method string|array|null getPageClass()
 * @method self setPageClass(string|array|null $pageClass)
 * @method ?array getPageAttr()
 * @method self setPageAttr(?array $pageAttr)
 * @method ?string getTemplateAfterOpeningNav()
 * @method self setTemplateAfterOpeningNav(?string $templateAfterOpeningNav)
 * @method ?string getTemplateBeforeClosingNav()
 * @method self setTemplateBeforeClosingNav(?string $templateBeforeClosingNav)
 * @method ?string getTemplateAfterOpeningUl()
 * @method self setTemplateAfterOpeningUl(?string $templateAfterOpeningUl)
 * @method ?string getTemplateBeforeClosingUl()
 * @method self setTemplateBeforeClosingUl(?string $templateBeforeClosingUl)
 * @method ?string getTemplateAfterOpeningLi()
 * @method self setTemplateAfterOpeningLi(?string $templateAfterOpeningLi)
 * @method ?string getTemplateBeforeClosingLi()
 * @method self setTemplateBeforeClosingLi(?string $templateBeforeClosingLi)
 * @method ?string getTemplateAfterOpeningPage()
 * @method self setTemplateAfterOpeningPage(?string $templateAfterOpeningPage)
 * @method ?string getTemplateBeforeClosingPage()
 * @method self setTemplateBeforeClosingPage(?string $templateBeforeClosingPage)
 * @method ?mixed[] getTemplateParams()
 * @method self setTemplateParams(?mixed[] $templateParams)
 */
abstract class AbstractNavigation
{
    public const PIMCORE_CSS_CLASS_MAIN         = 'main';
    public const PIMCORE_CSS_CLASS_ACTIVE       = 'active';
    public const PIMCORE_CSS_CLASS_ACTIVE_TRAIL = 'active-trail';
    public const PIMCORE_CSS_CLASS_MAINACTIVE   = 'mainactive';

    public const PIMCORE_CSS_CLASSES = [
        self::PIMCORE_CSS_CLASS_MAIN,
        self::PIMCORE_CSS_CLASS_ACTIVE,
        self::PIMCORE_CSS_CLASS_ACTIVE_TRAIL,
        self::PIMCORE_CSS_CLASS_MAINACTIVE,
    ];

    protected const OPTIONS = [
        'root'                           => ['null', Document::class],
        'active'                         => ['null', Document::class],
        'routes'                         => ['null', 'Route[]'],
        'minDepth'                       => ['null', 'int'],
        'maxDepth'                       => ['null', 'int'],
        'cache'                          => ['null', 'bool', 'string'],
        'cacheLifetime'                  => ['null', 'int'],
        'markActiveTrail'                => ['null', 'bool'],
        'pageCallback'                   => ['null', 'callable'],
        'addIdToPage'                    => ['null', 'bool'],
        'pageIdPrefix'                   => ['null', 'string'],
        'pageIdCallback'                 => ['null', 'callable'],
        'renderInvisible'                => ['null', 'bool'],
        'renderPageLabelRaw'             => ['null', 'bool'],
        'onlyRenderRoutes'               => ['null', 'bool'],
        'addPageClassToLi'               => ['null', 'bool'],
        'renderPageWithSubpagesAsButton' => ['null', 'bool'],
        'addRoot'                        => ['null', 'bool'],
        'rootClass'                      => ['null', 'string'],
        'addNavTag'                      => ['null', 'bool'],
        'navClass'                       => ['null', 'string'],
        'navAttr'                        => ['null', 'array'],
        'ulClass'                        => ['null', 'string', 'array'],
        'ulAttr'                         => ['null', 'array'],
        'liClass'                        => ['null', 'string', 'array'],
        'liAttr'                         => ['null', 'array'],
        'pageClass'                      => ['null', 'string', 'array'],
        'pageAttr'                       => ['null', 'array'],
        'templateAfterOpeningNav'        => ['null', 'string'],
        'templateBeforeClosingNav'       => ['null', 'string'],
        'templateAfterOpeningUl'         => ['null', 'string'],
        'templateBeforeClosingUl'        => ['null', 'string'],
        'templateAfterOpeningLi'         => ['null', 'string'],
        'templateBeforeClosingLi'        => ['null', 'string'],
        'templateAfterOpeningPage'       => ['null', 'string'],
        'templateBeforeClosingPage'      => ['null', 'string'],
        'templateParams'                 => ['null', 'array'],
    ];

    protected const DEFAULTS = [
        'root'                           => null,
        'active'                         => null,
        'routes'                         => [],
        'minDepth'                       => 0,
        'maxDepth'                       => null,
        'cache'                          => true,
        'cacheLifetime'                  => null,
        'pageCallback'                   => null,
        'markActiveTrail'                => true,
        'addIdToPage'                    => false,
        'pageIdPrefix'                   => '',
        'pageIdCallback'                 => null,
        'renderInvisible'                => false,
        'renderPageLabelRaw'             => false,
        'onlyRenderRoutes'               => false,
        'addPageClassToLi'               => false,
        'renderPageWithSubpagesAsButton' => false,
        'addRoot'                        => false,
        'rootClass'                      => '',
        'addNavTag'                      => true,
        'navClass'                       => '',
        'navAttr'                        => [],
        'ulClass'                        => '',
        'ulAttr'                         => [],
        'liClass'                        => '',
        'liAttr'                         => [],
        'pageClass'                      => '',
        'pageAttr'                       => [],
        'templateAfterOpeningNav'        => '',
        'templateBeforeClosingNav'       => '',
        'templateAfterOpeningUl'         => '',
        'templateBeforeClosingUl'        => '',
        'templateAfterOpeningLi'         => '',
        'templateBeforeClosingLi'        => '',
        'templateAfterOpeningPage'       => '',
        'templateBeforeClosingPage'      => '',
        'templateParams'                 => [],
    ];

    protected const SENSIBLE_DEFAULTS = [
        'maxDepth'  => 3,
        'navClass'  => 'navigation',
        'ulClass'   => 'navigation__list',
        'liClass'   => 'navigation__item',
        'pageClass' => 'navigation__page',
    ];

    protected const ACCESSIBLE_DEFAULTS = [
        'ulAttr' => [
            0 => [
                'aria-role' => 'menubar',
            ],
            'default' => [
                'aria-role' => 'menu',
            ],
        ],
        'liAttr' => [
            'aria-role' => 'none',
        ],
        'pageAttr' => [
            'aria-role' => 'menuitem',
        ],
    ];

    protected ?Document $root;

    protected ?Document $active;

    /**
     * @var Route[]
     */
    protected ?array $routes;

    protected ?int $minDepth;

    protected ?int $maxDepth;

    protected bool|string|null $cache;

    protected ?int $cacheLifetime;

    protected ?Closure $pageCallback;

    protected ?bool $markActiveTrail;

    protected ?bool $addIdToPage;

    protected ?string $pageIdPrefix;

    protected ?Closure $pageIdCallback;

    protected ?bool $renderInvisible;

    protected ?bool $renderPageLabelRaw;

    protected ?bool $onlyRenderRoutes;

    protected ?bool $addPageClassToLi;

    protected ?bool $renderPageWithSubpagesAsButton;

    protected ?bool $addRoot;

    protected ?string $rootClass;

    protected ?bool $addNavTag;

    protected ?string $navClass;

    /**
     * @var string[]|null
     */
    protected ?array $navAttr;

    /**
     * @var string|string[]|null
     */
    protected string|array|null $ulClass;

    /**
     * @var string[]|string[][]|null
     */
    protected ?array $ulAttr;

    /**
     * @var string|string[]|null
     */
    protected string|array|null $liClass;

    /**
     * @var string[]|string[][]|null
     */
    protected ?array $liAttr;

    /**
     * @var string|string[]|null
     */
    protected string|array|null $pageClass;

    /**
     * @var string[]|string[][]|null
     */
    protected ?array $pageAttr;

    protected ?string $templateAfterOpeningNav;

    protected ?string $templateBeforeClosingNav;

    protected ?string $templateAfterOpeningUl;

    protected ?string $templateBeforeClosingUl;

    protected ?string $templateAfterOpeningLi;

    protected ?string $templateBeforeClosingLi;

    protected ?string $templateAfterOpeningPage;

    protected ?string $templateBeforeClosingPage;

    /**
     * @var mixed[]|null
     */
    protected ?array $templateParams;

    /**
     * @param mixed[] $options
     * @phpstan-param NavigationOptions $options
     */
    public function __construct(array $options = [])
    {
        $optionsResolver = (new OptionsResolver())->setDefaults(static::getDefaults());

        foreach (array_merge(self::OPTIONS, static::OPTIONS) as $property => $types) {
            $optionsResolver->setAllowedTypes($property, $types);
        }

        foreach ($optionsResolver->resolve($options) as $property => $value) {
            $this->set($property, [$value]);
        }
    }

    /**
     * @param mixed[] $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        $property = lcfirst(substr($method, 3));

        if (str_starts_with($method, 'set')) {
            return $this->set($property, $arguments);
        }

        if (str_starts_with($method, 'get')) {
            return $this->get($property);
        }

        throw new RuntimeException(sprintf(
            'Attempted to call undefined method "%s" of class %s.',
            $method,
            get_class($this),
        ));
    }

    /**
     * @return mixed[]
     * @phpstan-return NavigationOptions
     */
    public static function getDefaults(): array
    {
        /**
         * @phpstan-var NavigationOptions
         */
        return self::DEFAULTS;
    }

    /**
     * @return mixed[]
     * @phpstan-return NavigationOptions
     */
    public static function getSensibleDefaults(): array
    {
        return self::SENSIBLE_DEFAULTS;
    }

    /**
     * @return mixed[]
     * @phpstan-return NavigationOptions
     */
    public static function getAccessibleDefaults(): array
    {
        return self::ACCESSIBLE_DEFAULTS;
    }

    /**
     * @return mixed[]
     * @phpstan-return NavigationOptions
     */
    public static function getSensibleAndAccessibleDefaults(): array
    {
        /**
         * @phpstan-var NavigationOptions
         */
        return array_merge(
            static::getSensibleDefaults(),
            static::getAccessibleDefaults(),
        );
    }

    /**
     * @param Route<Page> $route
     */
    public function addRoute(Route $route): self
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @param mixed[] $arguments
     */
    private function set(string $property, array $arguments): self
    {
        $this->assertExistingProperty($property, 'set');

        if (count($arguments)) {
            $this->{$property} = $arguments[0];
        }

        return $this;
    }

    private function get(string $property): mixed
    {
        $this->assertExistingProperty($property, 'get');

        return $this->{$property};
    }

    private function assertExistingProperty(string $property, string $type): void
    {
        if (property_exists($this, $property)) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Attempted to %s undefined property "%s" of class %s.',
            $type,
            $property,
            get_class($this),
        ));
    }
}
