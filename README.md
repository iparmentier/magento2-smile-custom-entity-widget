# Artbambou SmileCustomEntityWidget Module for Magento 2

[![Latest Stable Version](https://img.shields.io/github/v/release/iparmentier/magento2-smile-custom-entity-widget)](https://github.com/iparmentier/magento2-smile-custom-entity-widget/releases)
[![Magento 2](https://img.shields.io/badge/Magento-2.4.x-brightgreen.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.1|8.2|8.3-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/github/license/iparmentier/magento2-smile-custom-entity-widget)](https://github.com/iparmentier/magento2-smile-custom-entity-widget/blob/main/LICENSE.txt)

[SPONSOR: Amadeco](https://www.amadeco.fr)

⚠️ **Known Issue:** Currently, this module does not sort or filter attributes based on the selected attribute set. All custom entity attributes are displayed in the conditions section regardless of whether they belong to the chosen attribute set. This limitation may impact the user experience when working with multiple attribute sets that have different attribute structures.

## Overview

The Artbambou SmileCustomEntityWidget module extends Magento 2 by adding widget capabilities to Smile Custom Entities, empowering merchants to display filtered custom entity collections with advanced presentation options. This module bridges the gap between content management and presentation, allowing for dynamic display of custom entities on any content page.

<img width="1093" alt="Image" src="https://github.com/user-attachments/assets/da94ec88-780c-49b4-bc7a-1c7e24fb8f1e" />

## Features

- **Custom Entity Widget**: Create widgets that display custom entities from specified attribute sets
- **Advanced Filtering**: Filter entities using sophisticated condition combinations
  - **Custom Attribute to Filter** : "Entity Has Image", "Entity ID" (without chooser grid for now)
- **Customizable Sorting**: Sort entities by any attribute with configurable direction
- **Pagination Controls**: Optional paging functionality with customizable items per page

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

### Customizing Widget Options Through Theme Templates

To customize or extend the SmileCustomEntityWidget's options, you can override the widget configuration within your theme by creating a widget.xml file in your theme's configuration directory.

#### Instructions:

1. Create the following directory structure in your theme folder if it doesn't already exist:
   ```
   app/design/frontend/YourVendor/YourTheme/Artbambou_SmileCustomEntityWidget/etc/
   ```

2. Create a `widget.xml` file within this directory with the following structure:

   ```xml
   <?xml version="1.0" encoding="UTF-8"?>
   <widgets xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Widget:etc/widget.xsd">
       <widget id="artbambou_smile_custom_entity_set_widget"
               class="Artbambou\SmileCustomEntityWidget\Block\Set\Widget\CustomEntityWidget">
           <parameters>
               <!-- Add or modify parameters here -->
               <parameter name="template" xsi:type="select" required="true" visible="true">
                   <label translate="true">Template</label>
                   <options>
                       <option name="grid" value="Artbambou_SmileCustomEntityWidget::widget/grid.phtml">
                           <label translate="true">Grid Template</label>
                       </option>
                       <option name="custom_list" value="YourVendor_YourTheme::smile_entity/custom_list.phtml">
                           <label translate="true">Custom List Template</label>
                       </option>
                       <!-- Add more template options as needed -->
                   </options>
               </parameter>
           </parameters>
       </widget>
   </widgets>
   ```

3. After adding this file, clear the Magento cache:
   ```bash
   bin/magento cache:clean
   ```

This approach allows you to extend the widget configuration without modifying the core module code. You can add new template options, modify existing parameters, or introduce entirely new parameters to customize the widget's behavior to meet your specific design requirements.

Note that any custom templates referenced in your widget.xml must exist within your theme structure for them to work properly.

## Requirements

- Smile CustomEntity module (https://github.com/Smile-SA/magento2-module-custom-entity)
- Smile ScopedEav module (https://github.com/Smile-SA/magento2-module-scoped-eav)

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
