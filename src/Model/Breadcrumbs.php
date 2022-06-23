<?php

namespace GalDigitalGmbh\PimcoreNavigation\Model;

/**
 * @phpstan-import-type NavigationOptions from AbstractNavigation
 *
 * @method ?string getLeafClass()
 * @method self setLeafClass(?string $leafClass)
 * @method ?bool getDisableLeaf()
 * @method self setDisableLeaf(?bool $disableLeaf)
 * @method ?bool getAddLinkToLeaf()
 * @method self setAddLinkToLeaf(?bool $addLinkToLeaf)
 * @method ?string getSeparator()
 * @method self setSeparator(?string $separator)
 */
class Breadcrumbs extends AbstractNavigation
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
