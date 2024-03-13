<?php

declare(strict_types=1);

namespace GalDigitalGmbh\PimcoreNavigation\Model;

use Closure;
use Pimcore\Model\Document;
use Pimcore\Navigation\Container;
use Pimcore\Navigation\Page;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_merge;
use function count;
use function lcfirst;
use function mb_substr;
use function property_exists;
use function sprintf;
use function str_starts_with;

/**
 * @phpstan-type RootCallback Closure(Container $container): void
 * @phpstan-type PageCallback Closure(Page $page, ?Document $document): void
 * @phpstan-type PageIdCallback Closure(string $id): string
 * @phpstan-type NavigationOptions array{
 *     root?: ?Document,
 *     active?: ?Document,
 *     routes?: ?Route[],
 *     minDepth?: ?int,
 *     maxDepth?: ?int,
 *     cache?: bool|string|null,
 *     cacheLifetime?: ?int,
 *     rootCallback?: ?RootCallback,
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
 * @method ?Document getActive()
 * @method ?bool getAddIdToPage()
 * @method ?bool getAddNavTag()
 * @method ?bool getAddPageClassToLi()
 * @method ?bool getAddRoot()
 * @method bool|string|null getCache()
 * @method ?int getCacheLifetime()
 * @method ?array getLiAttr()
 * @method string|array|null getLiClass()
 * @method ?bool getMarkActiveTrail()
 * @method ?int getMaxDepth()
 * @method ?int getMinDepth()
 * @method ?array getNavAttr()
 * @method ?string getNavClass()
 * @method ?bool getOnlyRenderRoutes()
 * @method ?array getPageAttr()
 * @method ?RootCallback getRootCallback()
 * @method ?PageCallback getPageCallback()
 * @method string|array|null getPageClass()
 * @method ?PageIdCallback getPageIdCallback()
 * @method ?string getPageIdPrefix()
 * @method ?bool getRenderInvisible()
 * @method ?bool getRenderPageLabelRaw()
 * @method ?bool getRenderPageWithSubpagesAsButton()
 * @method ?Document getRoot()
 * @method ?string getRootClass()
 * @method ?Route[] getRoutes()
 * @method ?string getTemplateAfterOpeningLi()
 * @method ?string getTemplateAfterOpeningNav()
 * @method ?string getTemplateAfterOpeningPage()
 * @method ?string getTemplateAfterOpeningUl()
 * @method ?string getTemplateBeforeClosingLi()
 * @method ?string getTemplateBeforeClosingNav()
 * @method ?string getTemplateBeforeClosingPage()
 * @method ?string getTemplateBeforeClosingUl()
 * @method ?mixed[] getTemplateParams()
 * @method ?array getUlAttr()
 * @method string|array|null getUlClass()
 * @method static setActive(?Document $active)
 * @method static setAddIdToPage(?bool $addIdToPage)
 * @method static setAddNavTag(?bool $addNavTag)
 * @method static setAddPageClassToLi(?bool $addPageClassToLi)
 * @method static setAddRoot(?bool $addRoot)
 * @method static setCache(bool|string|null $cache)
 * @method static setCacheLifetime(?int $cacheLifetime)
 * @method static setLiAttr(?array $liAttr)
 * @method static setLiClass(string|array|null $liClass)
 * @method static setMarkActiveTrail(?bool $markActiveTrail)
 * @method static setMaxDepth(?int $maxDepth)
 * @method static setMinDepth(?int $minDepth)
 * @method static setNavAttr(?array $navAttr)
 * @method static setNavClass(?string $navClass)
 * @method static setOnlyRenderRoutes(?bool $onlyRenderRoutes)
 * @method static setPageAttr(?array $pageAttr)
 * @method static setRootCallback(?RootCallback $rootCallback)
 * @method static setPageCallback(?PageCallback $pageCallback)
 * @method static setPageClass(string|array|null $pageClass)
 * @method static setPageIdCallback(?PageIdCallback $pageIdCallback)
 * @method static setPageIdPrefix(?string $pageIdPrefix)
 * @method static setRenderInvisible(?bool $renderInvisible)
 * @method static setRenderPageLabelRaw(?bool $renderPageLabelRaw)
 * @method static setRenderPageWithSubpagesAsButton(?bool $renderPageWithSubpagesAsButton)
 * @method static setRoot(?Document $root)
 * @method static setRootClass(?string $rootClass)
 * @method static setRoutes(?Route[] $routes)
 * @method static setTemplateAfterOpeningLi(?string $templateAfterOpeningLi)
 * @method static setTemplateAfterOpeningNav(?string $templateAfterOpeningNav)
 * @method static setTemplateAfterOpeningPage(?string $templateAfterOpeningPage)
 * @method static setTemplateAfterOpeningUl(?string $templateAfterOpeningUl)
 * @method static setTemplateBeforeClosingLi(?string $templateBeforeClosingLi)
 * @method static setTemplateBeforeClosingNav(?string $templateBeforeClosingNav)
 * @method static setTemplateBeforeClosingPage(?string $templateBeforeClosingPage)
 * @method static setTemplateBeforeClosingUl(?string $templateBeforeClosingUl)
 * @method static setTemplateParams(?mixed[] $templateParams)
 * @method static setUlAttr(?array $ulAttr)
 * @method static setUlClass(string|array|null $ulClass)
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
        'rootCallback'                   => ['null', 'callable'],
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
        'rootCallback'                   => null,
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

    protected ?Closure $rootCallback;

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
        $property = lcfirst(mb_substr($method, 3));

        if (str_starts_with($method, 'set')) {
            return $this->set($property, $arguments);
        }

        if (str_starts_with($method, 'get')) {
            return $this->get($property);
        }

        throw new RuntimeException(sprintf(
            'Attempted to call undefined method "%s" of class %s.',
            $method,
            static::class,
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
            static::class,
        ));
    }
}
