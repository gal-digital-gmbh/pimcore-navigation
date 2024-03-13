## Table of contents

- [Usage](#usage)
  - [Twig](#twig)
  - [PHP](#php)
- [Options](#options)
  - [Common](#common)
  - [Menu](#menu)
  - [Breadcrumbs](#breadcrumbs)
- [Working with routes](#working-with-routes)
- [Customizing classes and attributes](#customizing-classes-and-attributes)
- [Template injection](#template-injection)

___
<br>

## Usage

### Twig

```twig
{%- set menu = menu() -%}
{%- set container = build_navigation(menu) -%}

{{- render_navigation(container, menu) -}}

{%- set breadcrumbs = breadcrumbs() -%}
{%- set container = build_navigation(breadcrumbs) -%}

{{- render_navigation(container, breadcrumbs) -}}
```
<br>

The navigation can be either be configured by using the various setter methods or by passing the options as an array.

```twig
{%- set menu = menu()
  .setRoot(nav_root)
  .setPageCallback(page_callback)
  .setLiClass('navigation__item') -%}

{# OR #}

{%- set menu = menu({
  root: nav_root,
  pageCallback,
  liClass: 'navigation__item',
}) -%}
```
<br>

Various predefined default configurations are available:

```twig
{%- set menu = menu(menu_defaults_sensible()) -%}
{%- set menu = menu(menu_defaults_accessible()) -%}
{%- set menu = menu(menu_defaults_sensible_and_accessible()) -%}

{%- set breadcrumbs = breadcrumbs(breadcrumbs_defaults_sensible()) -%}
{%- set breadcrumbs = breadcrumbs(breadcrumbs_defaults_accessible()) -%}
{%- set breadcrumbs = breadcrumbs(breadcrumbs_defaults_sensible_and_accessible()) -%}
```
<br>

### PHP

```php
<?php

namespace App\Service;

use App\Navigation\Model\Breadcrumbs;
use App\Navigation\Model\Menu;
use App\Service\NavigationService;
use Symfony\Contracts\Service\Attribute\Required;

class MyService
{
    #[Required]
    public NavigationService $navigationService;

    public function myMenuFunction(): void
    {
        $menu      = new Menu();
        $container = $this->navigationService->build($menu);
        $html      = $this->navigationService->render($container, $menu);
    }

    public function myBreadcrumbsFunction(): void
    {
        $breadcrumbs = new Breadcrumbs();
        $container   = $this->navigationService->build($breadcrumbs);
        $html        = $this->navigationService->render($container, $breadcrumbs);
    }
}
```
<br>

```php
<?php

namespace App\Service;

use App\Navigation\Model\Menu;

class MyService
{
    public function myMenuFunction(): void
    {
        $menu = (new Menu())
            ->setMaxDepth(4)
            ->setAddIdToPage(true)
            ->setPageIdPrefix('my-id-prefix')
        ;

        // OR

        $menu = new Menu([
            'maxDepth'     => 4,
            'addIdToPage'  => true,
            'pageIdPrefix' => 'my-id-prefix',
        ]);
    }
}
```
<br>

Defaults can be retrieved from the corresponding navigation class:

```php
<?php

namespace App\Service;

use App\Navigation\Model\Breadcrumbs;
use App\Navigation\Model\Menu;

class MyService
{
    public function myFunction(): void
    {
        $menu = new Menu(Menu::getSensibleDefaults());
        $menu = new Menu(Menu::getAccessibleDefaults());
        $menu = new Menu(Menu::getSensibleAndAccessibleDefaults());

        $breadcrumbs = new Breadcrumbs(Breadcrumbs::getSensibleDefaults());
        $breadcrumbs = new Breadcrumbs(Breadcrumbs::getAccessibleDefaults());
        $breadcrumbs = new Breadcrumbs(Breadcrumbs::getSensibleAndAccessibleDefaults());
    }
}
```

___
<br>

## Options

### Common

|Option|Type/Default|Description|
|---|---|---|
|`root`|`Document\|null`<br>Default: Document with id 1|The root document to use when building the navigation. If none is supplied the Pimcore document with id 1 will be used|
|`active`|`Document\|null`<br>Default: null|The currently active document|
|`routes`|`Route[]\|null`<br>Default: []|An array of routes that should be added to the navigation container (_See_ [_Working with routes_](#working-with-routes))|
|`minDepth`|`int\|null`<br>Default: 0|The depth a page should at least have to be added to the container|
|`maxDepth`|`int\|null`<br>Default: null|The depth a page should at most have to be added to the container|
|`cache`|`bool\|string\|null`<br>Default: true|string: custom cache key, bool: whether to cache the pimcore navigation builder output|
|`cacheLifetime`|`int\|null`<br>Default: null|How long to cache the pimcore navigation builder output (in seconds)|
|`rootCallback`|`Closure(Container): void`<br>Default: null|Callback applied to the root page for custom logic before rendering|
|`pageCallback`|`Closure(Page, Document\|Route): void`<br>Default: null|Callback applied to each page for custom logic before rendering|
|`addIdToPage`|`bool\|null`<br>Default: false|Whether to add the page id to the page tag|
|`pageIdPrefix`|`string\|null`<br>Default: ''|Custom prefix to prepend to the id|
|`pageIdCallback`|`Closure(string): string`<br>Default: null|Callback applied to all page ids for custom transformations|
|`renderInvisible`|`bool\|null`<br>Default: false|Whether to show invisible pages|
|`renderPageLabelRaw`|`bool\|null`<br>Default: false|Whether to render the page labels raw|
|`onlyRenderRoutes`|`bool\|null`<br>Default: false|Whether to exclude all non route pages from the navigation (_See_ [_Working with routes_](#working-with-routes))|
|`addPageClassToLi`|`bool\|null`<br>Default: false|Whether to add the page classes to the parent li tag|
|`renderPageWithSubpagesAsButton`|`bool\|null`<br>Default: false|Whether to render all pages with visible subpages as button tags|
|`addRoot`|`bool\|null`<br>Default: false|Whether to add the navigation root to the container|
|`rootClass`|`string\|null`<br>Default: ''|CSS class for the root page tag|
|`addNavTag`|`bool\|null`<br>Default: true|Whether to wrap the navigation with a nav tag|
|`navClass`|`string\|null`<br>Default: ''|CSS class for the nav tag|
|`navAttr`|`string[]\|null`<br>Default: []|HTML attributes for the nav tag|
|`ulClass`|`string\|string[]\|null`<br>Default: ''|CSS class for the ul tags (_See_ [_Customizing classes and attributes_](#customizing-classes-and-attributes))|
|`ulAttr`|`string[]\|string[][]\|null`<br>Default: []|HTML attributes for the ul tags (_See_ [_Customizing classes and attributes_](#customizing-classes-and-attributes))|
|`liClass`|`string\|string[]\|null`<br>Default: ''|CSS class for the li tags (_See_ [_Customizing classes and attributes_](#customizing-classes-and-attributes))|
|`liAttr`|`string[]\|string[][]\|null`<br>Default: []|HTML attributes for the li tags (_See_ [_Customizing classes and attributes_](#customizing-classes-and-attributes))|
|`pageClass`|`string\|string[]\|null`<br>Default: ''|CSS class for the page tags (_See_ [_Customizing classes and attributes_](#customizing-classes-and-attributes))|
|`pageAttr`|`string[]\|string[][]\|null`<br>Default: []|HTML attributes for the page tags (_See_ [_Customizing classes and attributes_](#customizing-classes-and-attributes))|
|`templateAfterOpeningNav`|`string\|null`<br>Default: ''|Template inserted after the opening nav tag| (_See_ [_Template injection_](#template-injection))
|`templateBeforeClosingNav`|`string\|null`<br>Default: ''|Template inserted before the closing nav tag| (_See_ [_Template injection_](#template-injection))
|`templateAfterOpeningUl`|`string\|null`<br>Default: ''|Template inserted after the opening ul tags (_See_ [_Template injection_](#template-injection))|
|`templateBeforeClosingUl`|`string\|null`<br>Default: ''|Template inserted before the closing ul tags (_See_ [_Template injection_](#template-injection))|
|`templateAfterOpeningLi`|`string\|null`<br>Default: ''|Template inserted after the opening li tags (_See_ [_Template injection_](#template-injection))|
|`templateBeforeClosingLi`|`string\|null`<br>Default: ''|Template inserted before the closing li tags (_See_ [_Template injection_](#template-injection))|
|`templateAfterOpeningPage`|`string\|null`<br>Default: ''|Template inserted after the opening page tags (_See_ [_Template injection_](#template-injection))|
|`templateBeforeClosingPage`|`string\|null`<br>Default: ''|Template inserted before the closing page tags (_See_ [_Template injection_](#template-injection))|
|`templateParams`|`mixed[]\|null`<br>Default: []|Custom parameters passed to the supplied templates (_See_ [_Template injection_](#template-injection))|

<br>

### Menu

|Option|Type/Default|Description|
|---|---|---|
|`orderForRoot`|`int\|null`<br>Default: -1|The order applied to the root page|
|`liActiveClass`|`string\|null`<br>Default: ''|CSS class for the active li tag|
|`liMainClass`|`string\|null`<br>Default: ''|CSS class for the main level li tags|
|`liMainActiveClass`|`string\|null`<br>Default: ''|CSS class for the active main level li tag|
|`liActiveTrailClass`|`string\|null`<br>Default: ''|CSS class for all li tags in the active trail|
|`liWithSubpagesClass`|`string\|null`<br>Default: ''|CSS class for all li tags with visible subpages|
|`pageActiveClass`|`string\|bool\|null`<br>Default: null|CSS class for the active page tag|
|`pageMainClass`|`string\|bool\|null`<br>Default: null|CSS class for the main level page tags|
|`pageMainActiveClass`|`string\|bool\|null`<br>Default: null|CSS class for the active main level page tag|
|`pageActiveTrailClass`|`string\|bool\|null`<br>Default: null|CSS class for all page tags in the active trail|

<br>

### Breadcrumbs


|Option|Type/Default|Description|
|---|---|---|
|`leafClass`|`string\|null`<br>Default: ''|CSS class for the leaf tag|
|`disableLeaf`|`bool\|null`<br>Default: false|Whether to disable rendering of leaf tag|
|`addLinkToLeaf`|`bool\|null`<br>Default: false|Whether to add a link to the leaf tag|
|`separator`|`string\|null`<br>Default: ''|The html used to separate the pages|

___
<br>

## Working with routes

Custom pages for symfony routes can be added to the navigation as well. They will automatically be added to the correct position by comparing the given path to the containers pages. If no page is found the route will be added at the top level.<br>Although not shown here the breadcrumb navigation is fully compatible with the route feature.

Let's assume we have the following route:

```yaml
app_product_detail:
  path: '/products/{productId}'
  controller: 'App\Controller\ProductController::detailAction'
```
<br>

The following code will generate a menu with a custom route added that links to a specific product detail page. This route will be inserted underneath the pimcore document with the path `/products`.

```twig
{# Note that a route is basically just a Page and can be configured like one #}

{%- set menu = menu().setRoutes([
  route()
    .setId('product-1')
    .setName('app_product_detail')
    .setParams({
        productId: 1,
    })
    .setVisible(true)
    .setOrder(0)
    .setLabel('Product 1 label')
    .setTitle('Product 1 title')
    .setClass('product-link-class')
    .setTarget('_blank')
    .setAccessKey('a')
    .setRelation('alternate')
    .setTabindex('1'),
]) -%}
```
<br>

```php
<?php

namespace App\Service;

use App\Navigation\Model\Menu;
use App\Navigation\Model\Route;

class MyService
{
    public function myFunction(): void
    {
        $menu = (new Menu())->setRoutes([
            (new Route())
                ->setId('product-1')
                ->setName('app_product_detail')
                ->setParams([
                    'productId' => 1,
                ])
                ->setVisible(true)
                ->setOrder(0)
                ->setLabel('Product 1 label')
                ->setTitle('Product 1 title')
                ->setClass('product-link-class')
                ->setTarget('_blank')
                ->setAccessKey('a')
                ->setRelation('alternate')
                ->setTabindex('1'),
        ]);
    }
}
```
<br>

An `addRoute` method also is available.<br>
The following example shows how it is possible to render a navigation only containing static links:

```twig
{%- set social_menu = menu().setOnlyRenderRoutes(true) -%}

{%- for social, path in {
  facebook: 'https://www.example.com/',
  twitter: 'https://www.example.com/',
} -%}
  {%- do social_menu.addRoute(
    route()
      .setPath(path)
      .setLabel(('social.label.' ~ social)|trans)
      .setTarget('_blank'),
  ) -%}
{%- endfor -%}

{{- render_navigation(build_navigation(social_menu), social_menu) -}}
```
<br>

An empty route can also be added. In that case the current route will be used as a link and added at the corresponding position. A label should at least be provided or the link will not be visible. This can be useful for adding a custom leaf to a breadcrumb navigation for example.

```twig
{%- set menu = menu().setRoutes([
  route().setLabel('Current route label'),
]) -%}

{%- set breadcrumbs = breadcrumbs().addRoute(
  route().setLabel('Current route label'),
) -%}
```

___
<br>

## Customizing classes and attributes

All of the following examples work for every one of these methods (or properties if array configuration is used): `setUlClass`, `setUlAttr`, `setLiClass`, `setLiAttr`, `setPageClass`, `setPageAttr`.

```twig
{%- set menu = menu()
  .setLiClass('class1 class2')
  .setLiAttr({
    id: 'main-nav',
  }) -%}

{# In the following case the two classes will be merged #}
{# Output: 'class1 class2' #}

{%- set menu = menu()
  .setPageClass('class1')
  .setPageAttr({
    class: 'class2',
  }) -%}
```
<br>

Classes and attributes can be applied for specific depths too. When no entry for the current depth is found the default entry will be used. If none is provided nothing will added to the element.

```twig
{%- set menu = menu()
  .setUlClass({
    default: 'any-other-ul',
    0: 'main-ul',
    1: 'sub-ul',
    2: 'subsub-ul',
  })
  .setUlAttr({
    default: {
      'aria-role': 'menu', 
    },
    0: {
      'aria-role': 'menubar',
    },
  }) -%}
```

___
<br>

## Template injection

Sometimes it is neccessary to add custom HTML content to the navigation. This can be done by using one of the insertion template methods:

```twig
{%- set menu = menu()
  .setTemplateAfterOpeningNav('after-opening-nav.html.twig')
  .setTemplateBeforeClosingNav('before-closing-nav.html.twig')

  .setTemplateAfterOpeningUl('after-opening-ul.html.twig')
  .setTemplateBeforeClosingUl('before-closing-ul.html.twig')

  .setTemplateAfterOpeningLi('after-opening-li.html.twig')
  .setTemplateBeforeClosingLi('before-closing-li.html.twig')

  .setTemplateAfterOpeningPage('after-opening-page.html.twig')
  .setTemplateBeforeClosingPage('before-closing-page.html.twig') -%}
```
<br>

The following parameters will be passed to the rendered view:

* `page`: the current page being rendered
* `container`: the navigation container
* `navigation`:  the navigation instance
* `depth`: the current depth

<br>

Custom parameters to pass to the view can also be added to the navigation:

```twig
{%- set menu = menu()
  .setTemplateBeforeClosingLi('before-closing-li.html.twig')
  .setTemplateParams({
    chevron_class: 'class1',
  }) -%}
```

```twig
{# /templates/before-closing-li.html.twig #}

{%- if page and page.hasVisiblePages -%}
  <button type="button" class="{{ chevron_class }}" title="{{ page.getLabel }}">
    {{- depth == 0 ? '⌄' : '❯' -}}
  </button>
{%- endif -%}
```
