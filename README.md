# Vanilla SelectBox

This module provides the dropdown select list with vanilla selecbox library.
Vanilla SelectBox uses the [vanillaSelectBox](https://github.com/PhilippeMarcMeyer/vanillaSelectBox) javascript library to make your `<select>` elements more user-friendly. This module is developed by taking the inspiration from [chosen](https://drupal.org/project/chosen) module.

## Usage

By default, the module will automatically apply itself to any select elements that are visible, which is a reasonable default for which having vanillaSelectBox will be useful. To change or disable this automatic enhancement, you can change the selectors (or remove all selectors) from the Apply vanillaSelectBox to the following elements field on the vanillaSelectBox administration page <code>/admin/config/user-interface/vanilla-selectbox</code>.

## FAPI #vanilla_selectbox property

For developers, you can force the vanillaSelectBoxto be applied or never applied to your select FAPI element by adding <code>$element['#vanilla_selectbox'] = TRUE;</code> or <code>$element['#vanilla_selectbox'] = FALSE;</code> respectively.

## Field UI

You can force enable/disable vanillaSelectBox for certain field widgets: Select list (for both list and date fields), and Select (or other) list. If you have a field using one of these widgets, in the field settings, you will find an Apply vanillaSelectBox to the select fields in this widget? option with three values: Apply, Do not apply, or No preference (which will fall back to using the automatic application).

## Update with Composer for Drupal 8-9

`composer require drupal/vanilla_selectbox:[version]` **[NOT READY YET, COMING SOON]**

for example:
`composer require drupal/vanilla_selectbox:3.0.2`

## Manual Installation

1. Download the vanillaSelectBox jQuery plugin.
1. [Drupal 8-9] Extract the plugin under libraries/vanillaSelectBox.
1. Download and enable the module.
1. Configure at Administer > Configuration > User interface > Vanilla SelectBox (requires administer site configuration permission)
1. Installation via Drush
1. A Drush command is provided in the latest versions for easy installation of the vanillaSelectBox plugin.

```bash
drush vanilla-selectbox-plugin
```

The command will download the plugin and unpack it in "libraries".
It is possible to add another path as an option to the command, but not
recommended unless you know what you are doing.

## Similer modules

1. [Chosen](https://drupal.org/project/chosen)
