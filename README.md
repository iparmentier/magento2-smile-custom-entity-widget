# Artbambou SmileCustomEntityWidget Module for Magento 2

[![Magento 2](https://img.shields.io/badge/Magento-2.4.x-brightgreen.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.1|8.2|8.3-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/github/license/iparmentier/magento2-smile-custom-entity-widget)](https://github.com/iparmentier/magento2-smile-custom-entity-widget/blob/main/LICENSE.txt)

[SPONSOR: Amadeco](https://www.amadeco.fr)

## Overview

The Artbambou SmileCustomEntityWidget module extends Magento 2 by adding widget capabilities to Smile Custom Entities, empowering merchants to display filtered custom entity collections with advanced presentation options. This module bridges the gap between content management and presentation, allowing for dynamic display of custom entities on any content page.

## Features

- **Custom Entity Widget**: Create widgets that display custom entities from specified attribute sets
- **Advanced Filtering**: Filter entities using sophisticated condition combinations
- **Customizable Sorting**: Sort entities by any attribute with configurable direction
- **Pagination Controls**: Optional paging functionality with customizable items per page
- **Responsive Design**: Configure image dimensions to ensure optimal display across devices
- **Call-to-Action Support**: Optional footer button with customizable text
- **Flexible Templates**: Choose from different display templates to match your design needs

## Installation

### Composer Installation

Execute the following commands in your Magento root directory:
```bash
composer require artbambou/module-smile-custom-entity-widget
bin/magento module:enable Artbambou_SmileCustomEntityWidget
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

### Manual Installation

1. Create directory `app/code/Artbambou/SmileCustomEntityWidget` in your Magento installation
2. Clone or download this repository into that directory
3. Enable the module and update the database:
```bash
bin/magento module:enable Artbambou_SmileCustomEntityWidget
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy
```

## Usage

After installation, a new widget type "Smile Custom Entity Widget" will be available in the Magento Admin Panel:

1. Navigate to **Content > Elements > Widgets**
2. Click **Add Widget**
3. Select **Smile Custom Entity Widget** as the widget type
4. Configure the widget with the following options:
   - **Heading Title**: Optional title for the widget section
   - **Attribute Set**: Select the custom entity attribute set to display
   - **Conditions**: Define filtering rules to determine which entities to display
   - **Display Page Control**: Enable/disable pagination
   - **Number of Items**: Configure how many items to display
   - **Sorting Options**: Choose which attribute to sort by and the direction
   - **Image Dimensions**: Set the width and height for entity images
   - (Not implemented in template) **Footer Button**: Optionally display a call-to-action button
   - (Not implemented in template) **Template**: Select the display template

The widget can be placed on any CMS page, block, or within layout XML, providing flexible integration options.

## Requirements

- Smile CustomEntity module
- Smile ScopedEav module

## Compatibility

- Magento 2.4.x
- PHP 8.3

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues or feature requests, please create an issue on our GitHub repository.

## License

This module is licensed under the Open Software License ("OSL") v3.0. See the [LICENSE.txt](LICENSE.txt) file for details.

## Credits

Developed by [Ilan Parmentier](https://github.com/iparmentier) for [Amadeco](https://www.amadeco.fr).
