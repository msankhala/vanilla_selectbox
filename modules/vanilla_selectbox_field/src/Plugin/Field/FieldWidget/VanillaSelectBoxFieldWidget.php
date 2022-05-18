<?php

namespace Drupal\vanilla_selectbox_field\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;

/**
 * Plugin implementation of the 'vanilla_selectbox_select' widget.
 *
 * @FieldWidget(
 *   id = "vanilla_selectbox_select",
 *   label = @Translation("Vanilla SelectBox"),
 *   field_types = {
 *     "list_integer",
 *     "list_float",
 *     "list_string",
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class VanillaSelectBoxFieldWidget extends OptionsSelectWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element += [
      '#vanilla_selectbox' => 1,
    ];

    return $element;
  }

}
