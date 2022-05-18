<?php

namespace Drupal\vanilla_selectbox;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * VanillaSelectBoxFormRender.
 */
class VanillaSelectBoxFormRender implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRenderSelect',
      'preRenderDateCombo',
      'preRenderSelectOther',
    ];
  }

  /**
   * Render API callback: Apply Chosen to a select element.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The element.
   */
  public static function preRenderSelect(array $element) {
    // Exclude vanilla_selectbox from theme other than admin.
    $theme = \Drupal::theme()->getActiveTheme()->getName();
    $admin_theme = \Drupal::config('system.theme')->get('admin');
    $is_admin_path = \Drupal::service('router.admin_context')->isAdminRoute();
    $is_admin = $is_admin_path || $theme == $admin_theme;

    $vanilla_selectbox_include = \Drupal::config('vanilla_selectbox.settings')->get('vanilla_selectbox_include');
    if ($vanilla_selectbox_include != VANILLA_SELECTBOX_INCLUDE_EVERYWHERE && $is_admin == $vanilla_selectbox_include) {
      return $element;
    }

    // If the #vanilla_selectbox FAPI property is set, then add the appropriate class.
    if (isset($element['#vanilla_selectbox'])) {
      if (!empty($element['#vanilla_selectbox'])) {
        // Element has opted-in for Chosen, ensure the library gets added.
        $element['#attributes']['class'][] = 'vanilla-selectbox-enable';
      }
      else {
        $element['#attributes']['class'][] = 'vanilla-selectbox-disable';
        // Element has opted-out of Chosen. Do not add the library now.
        return $element;
      }
    }
    elseif (isset($element['#attributes']['class']) && is_array($element['#attributes']['class'])) {
      if (array_intersect($element['#attributes']['class'], ['vanilla-selectbox-disable'])) {
        // Element has opted-out of Chosen. Do not add the library now.
        return $element;
      }
      elseif (array_intersect($element['#attributes']['class'], ['vanilla-selectbox-enable'])) {
        // Element has opted-in for Chosen, ensure the library gets added.
      }
    }
    else {
      // Neither the #vanilla_selectbox property was set, nor any vanilla_selectbox classes found.
      // This element still might match the site-wide critera, so add the library.
    }

    if (isset($element['#field_name']) && !empty($element['#multiple'])) {
      // Remove '_none' from multi-select options.
      unset($element['#options']['_none']);

      if (isset($element['#entity_type']) && isset($element['#bundle']) && isset($element['#field_name'])) {
        // Set data-cardinality for fields that aren't unlimited.
        $field = FieldConfig::loadByName($element['#entity_type'], $element['#bundle'], $element['#field_name'])->getFieldStorageDefinition();
        $cardinality = $field->getCardinality();
        if ($cardinality != FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && $cardinality > 1) {
          $element['#attributes']['data-cardinality'] = $cardinality;
        }
      }
    }

    // Attach the library.
    vanilla_selectbox_attach_library($element);

    // Right to Left Support.
    $language_direction = \Drupal::languageManager()->getCurrentLanguage()->getDirection();
    if (LanguageInterface::DIRECTION_RTL == $language_direction) {
      $element['#attributes']['class'][] = 'vanilla-selectbox-rtl';
    }

    return $element;
  }

  /**
   * Render API callback: Apply Chosen to a date_combo element.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The element.
   */
  public static function preRenderDateCombo(array $element) {
    // Because the date_combo field contains many different select elements, we
    // need to recurse down and apply the FAPI property to each one.
    if (isset($element['#vanilla_selectbox'])) {
      vanilla_selectbox_element_apply_property_recursive($element, $element['#vanilla_selectbox']);
    }
    return $element;
  }

  /**
   * Render API callback: Apply Chosen to a select_or_other element.
   *
   * @param array $element
   *   The element.
   *
   * @return array
   *   The element.
   */
  public static function preRenderSelectOther(array $element) {
    if ($element['#select_type'] == 'select' && isset($element['#vanilla_selectbox'])) {
      $element['select']['#vanilla_selectbox'] = $element['#vanilla_selectbox'];
    }
    return $element;
  }


}
