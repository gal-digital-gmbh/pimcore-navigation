<?php

declare(strict_types=1);

namespace GalDigitalGmbh\PimcoreNavigation\Twig;

use GalDigitalGmbh\PimcoreNavigation\Model\Breadcrumbs;
use GalDigitalGmbh\PimcoreNavigation\Model\Menu;
use GalDigitalGmbh\PimcoreNavigation\Model\Route;
use GalDigitalGmbh\PimcoreNavigation\Service\NavigationService;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class NavigationExtension extends AbstractExtension
{
    #[Required]
    public NavigationService $navigationService;

    public function getFunctions()
    {
        return [
            new TwigFunction('menu', static fn (array $options = []) => new Menu($options)),
            new TwigFunction('breadcrumbs', static fn (array $options = []) => new Breadcrumbs($options)),
            new TwigFunction('route', static fn () => new Route()),
            new TwigFunction('build_navigation', [$this->navigationService, 'build']),
            new TwigFunction('render_navigation', [$this->navigationService, 'render'], ['is_safe' => ['html']]),
            new TwigFunction('menu_defaults_sensible', [
                Menu::class,
                'getSensibleDefaults',
            ]),
            new TwigFunction('menu_defaults_accessible', [
                Menu::class,
                'getAccessibleDefaults',
            ]),
            new TwigFunction('menu_defaults_sensible_and_accessible', [
                Menu::class,
                'getSensibleAndAccessibleDefaults',
            ]),
            new TwigFunction('breadcrumbs_defaults_sensible', [
                Breadcrumbs::class,
                'getSensibleDefaults',
            ]),
            new TwigFunction('breadcrumbs_defaults_accessible', [
                Breadcrumbs::class,
                'getAccessibleDefaults',
            ]),
            new TwigFunction('breadcrumbs_defaults_sensible_and_accessible', [
                Breadcrumbs::class,
                'getSensibleAndAccessibleDefaults',
            ]),
        ];
    }
}
