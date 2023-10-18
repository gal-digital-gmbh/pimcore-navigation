<?php

declare(strict_types=1);

namespace GalDigitalGmbh\PimcoreNavigation\Model;

use function array_merge;

/**
 * @phpstan-import-type NavigationOptions from AbstractNavigation
 *
 * @method ?bool getAddLinkToLeaf()
 * @method ?bool getDisableLeaf()
 * @method ?string getLeafClass()
 * @method ?string getSeparator()
 * @method static setAddLinkToLeaf(?bool $addLinkToLeaf)
 * @method static setDisableLeaf(?bool $disableLeaf)
 * @method static setLeafClass(?string $leafClass)
 * @method static setSeparator(?string $separator)
 */
final class Breadcrumbs extends AbstractNavigation
{
    protected const OPTIONS = [
        'leafClass'     => ['null', 'string'],
        'disableLeaf'   => ['null', 'bool'],
        'addLinkToLeaf' => ['null', 'bool'],
        'separator'     => ['null', 'string'],
    ];

    protected const DEFAULTS = [
        'leafClass'     => '',
        'disableLeaf'   => false,
        'addLinkToLeaf' => false,
        'separator'     => '',
    ];

    protected const SENSIBLE_DEFAULTS = [
        'leafClass' => 'navigation__leaf',
        'separator' => '<span aria-hidden="true" class="navigation__separator">❯</span>',
    ];

    protected ?string $leafClass;

    protected ?bool $disableLeaf;

    protected ?bool $addLinkToLeaf;

    protected ?string $separator;

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
