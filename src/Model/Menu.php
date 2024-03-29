<?php

declare(strict_types=1);

namespace GalDigitalGmbh\PimcoreNavigation\Model;

use function array_merge;

/**
 * @phpstan-import-type NavigationOptions from AbstractNavigation
 *
 * @method ?string getLiActiveClass()
 * @method ?string getLiActiveTrailClass()
 * @method ?string getLiMainActiveClass()
 * @method ?string getLiMainClass()
 * @method ?string getLiWithSubpagesClass()
 * @method ?int getOrderForRoot()
 * @method string|bool|null getPageActiveClass()
 * @method string|bool|null getPageActiveTrailClass()
 * @method string|bool|null getPageMainActiveClass()
 * @method string|bool|null getPageMainClass()
 * @method static setLiActiveClass(?string $liActiveClass)
 * @method static setLiActiveTrailClass(?string $liActiveTrailClass)
 * @method static setLiMainActiveClass(?string $liMainActiveClass)
 * @method static setLiMainClass(?string $liMainClass)
 * @method static setLiWithSubpagesClass(?string $liWithSubpagesClass)
 * @method static setOrderForRoot(?int $orderForRoot)
 * @method static setPageActiveClass(string|bool|null $pageActiveClass)
 * @method static setPageActiveTrailClass(string|bool|null $pageActiveTrailClass)
 * @method static setPageMainActiveClass(string|bool|null $pageMainActiveClass)
 * @method static setPageMainClass(string|bool|null $pageMainClass)
 */
final class Menu extends AbstractNavigation
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
