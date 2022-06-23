<?php

namespace GalDigitalGmbh\PimcoreNavigation\Model;

/**
 * @phpstan-import-type NavigationOptions from AbstractNavigation
 *
 * @method ?int getOrderForRoot()
 * @method self setOrderForRoot(?int $orderForRoot)
 * @method ?string getLiActiveClass()
 * @method self setLiActiveClass(?string $liActiveClass)
 * @method ?string getLiMainClass()
 * @method self setLiMainClass(?string $liMainClass)
 * @method ?string getLiMainActiveClass()
 * @method self setLiMainActiveClass(?string $liMainActiveClass)
 * @method ?string getLiActiveTrailClass()
 * @method self setLiActiveTrailClass(?string $liActiveTrailClass)
 * @method ?string getLiWithSubpagesClass()
 * @method self setLiWithSubpagesClass(?string $liWithSubpagesClass)
 * @method string|bool|null getPageActiveClass()
 * @method self setPageActiveClass(string|bool|null $pageActiveClass)
 * @method string|bool|null getPageMainClass()
 * @method self setPageMainClass(string|bool|null $pageMainClass)
 * @method string|bool|null getPageMainActiveClass()
 * @method self setPageMainActiveClass(string|bool|null $pageMainActiveClass)
 * @method string|bool|null getPageActiveTrailClass()
 * @method self setPageActiveTrailClass(string|bool|null $pageActiveTrailClass)
 */
class Menu extends AbstractNavigation
{
    protected const OPTIONS = [
        'orderForRoot'         => ['null', 'int'],
        'liActiveClass'        => ['null', 'string'],
        'liMainClass'          => ['null', 'string'],
        'liMainActiveClass'    => ['null', 'string'],
        'liActiveTrailClass'   => ['null', 'string'],
        'liWithSubpagesClass'  => ['null', 'string'],
        'pageActiveClass'      => ['null', 'string', 'bool'],
        'pageMainClass'        => ['null', 'string', 'bool'],
        'pageMainActiveClass'  => ['null', 'string', 'bool'],
        'pageActiveTrailClass' => ['null', 'string', 'bool'],
    ];

    protected const DEFAULTS = [
        'orderForRoot'         => -1,
        'liActiveClass'        => '',
        'liMainClass'          => '',
        'liMainActiveClass'    => '',
        'liActiveTrailClass'   => '',
        'liWithSubpagesClass'  => '',
        'pageActiveClass'      => null,
        'pageMainClass'        => null,
        'pageMainActiveClass'  => null,
        'pageActiveTrailClass' => null,
    ];

    protected const SENSIBLE_DEFAULTS = [
        'liWithSubpagesClass'  => 'navigation__item--has-subpages',
        'pageActiveClass'      => 'navigation__page--is-active',
        'pageMainClass'        => false,
        'pageMainActiveClass'  => false,
        'pageActiveTrailClass' => false,
    ];

    protected ?int $orderForRoot;

    protected ?string $liActiveClass;

    protected ?string $liMainClass;

    protected ?string $liMainActiveClass;

    protected ?string $liActiveTrailClass;

    protected ?string $liWithSubpagesClass;

    protected string|bool|null $pageActiveClass;

    protected string|bool|null $pageMainClass;

    protected string|bool|null $pageMainActiveClass;

    protected string|bool|null $pageActiveTrailClass;

    public static function getDefaults(): array
    {
        /**
         * @phpstan-var NavigationOptions
         */
        return array_merge(parent::DEFAULTS, self::DEFAULTS);
    }

    public static function getSensibleDefaults(): array
    {
        /**
         * @phpstan-var NavigationOptions
         */
        return array_merge(parent::SENSIBLE_DEFAULTS, self::SENSIBLE_DEFAULTS);
    }
}
