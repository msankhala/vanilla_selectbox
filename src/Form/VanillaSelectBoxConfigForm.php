<?php

namespace Drupal\vanilla_selectbox\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ThemeHandler;

/**
 * Implements a VanillaSelectBoxConfig form.
 */
class VanillaSelectBoxConfigForm extends ConfigFormBase {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandler
   *   Theme handler.
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ThemeHandler $themeHandler, MessengerInterface $messenger) {
    parent::__construct($config_factory);
    $this->themeHandler = $themeHandler;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('theme_handler'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vanilla_selectbox_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['vanilla_selectbox.settings'];
  }

  /**
   * Vanilla SelectBox configuration form.
   *
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $vanilla_selectbox_path = _vanilla_selectbox_lib_get_vanilla_selectbox_path();
    if (!$vanilla_selectbox_path) {
      $url = Url::fromUri(VANILLA_SELECTBOX_WEBSITE_URL);
      $link = Link::fromTextAndUrl($this->t('Vanilla SelectBox JavaScript file'), $url)->toString();

      $this->messenger->addError($this->t('The library could not be detected. You need to download the @vanilla_selectbox and extract the entire contents of the archive into the %path directory on your server.',
        ['@vanilla_selectbox' => $link, '%path' => 'libraries']
      ));
      return $form;
    }
    $form = parent::buildForm($form, $form_state);

    // Vanilla SelectBox settings:
    $vanilla_selectbox_conf = $this->configFactory->get('vanilla_selectbox.settings');

    $form['jquery_selector'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Apply Vanilla SelectBox to the following elements'),
      '#description' => $this->t('A comma-separated list of css selectors to apply Vanilla SelectBox to, such as <code>select#edit-operation, select#edit-type</code> or <code>.vanilla-selectbox-select</code>. Defaults to <code>select</code> to apply Vanilla SelectBox to all <code>&lt;select&gt;</code> elements.'),
      '#default_value' => $vanilla_selectbox_conf->get('jquery_selector'),
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Vanilla SelectBox general options'),
      '#open' => TRUE,
    ];

    $form['options']['disable_search'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable search box'),
      '#default_value' => $vanilla_selectbox_conf->get('disable_search'),
      '#description' => $this->t('Enable or disable the search box in the results list to filter out possible options.'),
    ];

    $form['options']['disable_select_all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable select all box'),
      '#default_value' => $vanilla_selectbox_conf->get('disable_select_all'),
      '#description' => $this->t('Enable or disable the select all box in the results list to select all options.'),
    ];

    $form['options']['stay_open'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Stay open'),
      '#default_value' => $vanilla_selectbox_conf->get('stay_open'),
      '#description' => $this->t("Defaut is false : that's a drop-down. Set it to true and that's a list."),
    ];

    $form['options']['maximum_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Maxium width of widget'),
      '#field_suffix' => 'px',
      '#size' => 6,
      '#default_value' => $vanilla_selectbox_conf->get('maximum_width'),
      '#description' => $this->t('The maxium width of the Vanilla SelectBox widget. Leave blank to have vanilla_selectbox determine this.'),
    ];

    $form['options']['minimum_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Minimum width of widget'),
      '#field_suffix' => 'px',
      '#size' => 6,
      '#default_value' => $vanilla_selectbox_conf->get('minimum_width'),
      '#description' => $this->t('The minium width of the Vanilla SelectBox widget. Leave blank to have vanilla_selectbox determine this.'),
    ];

    $form['options']['maximum_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Maxium height of widget'),
      '#field_suffix' => 'px',
      '#size' => 6,
      '#default_value' => $vanilla_selectbox_conf->get('maximum_height'),
      '#description' => $this->t('The maxium height of the Vanilla SelectBox widget. Leave blank to have vanilla_selectbox determine this.'),
    ];

    $form['options']['maximum_option_width'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum option width'),
      '#field_suffix' => 'px',
      '#size' => 6,
      '#default_value' => $vanilla_selectbox_conf->get('maximum_option_width'),
      '#description' => $this->t('Set a maximum width for each option for narrow menus.'),
    ];

    $form['options']['maximum_select'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum allowed select'),
      '#size' => 4,
      '#default_value' => $vanilla_selectbox_conf->get('maximum_select'),
      '#description' => $this->t('Set a maximum in the number of selectable options. CheckAll/uncheckAll is then disabled.'),
    ];


    $form['options']['item_separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Items separator'),
      '#size' => 1,
      '#default_value' => $vanilla_selectbox_conf->get('item_separator') ?? ',',
      '#description' => $this->t('To change the default "," item separator showing in the button.'),
    ];

    $form['options']['placeholder_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder text'),
      '#required' => TRUE,
      '#default_value' => $vanilla_selectbox_conf->get('placeholder_text') ?? 'Choose some options',
    ];

    $form['options']['vanilla_selectbox_include'] = [
      '#type' => 'radios',
      '#title' => $this->t('Use vanilla selectbox for admin pages and/or front end pages'),
      '#options' => [
        VANILLA_SELECTBOX_INCLUDE_EVERYWHERE => $this->t('Include Vanilla SelectBox on every page'),
        VANILLA_SELECTBOX_INCLUDE_ADMIN => $this->t('Include Vanilla SelectBox only on admin pages'),
        VANILLA_SELECTBOX_INCLUDE_NO_ADMIN => $this->t('Include Vanilla SelectBox only on front end pages'),
      ],
      '#default_value' => $vanilla_selectbox_conf->get('vanilla_selectbox_include'),
    ];

    $form['theme_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Vanilla SelectBox per theme options'),
      '#open' => TRUE,
    ];

    $default_disabled_themes = $vanilla_selectbox_conf->get('disabled_themes');
    $default_disabled_themes = is_array($default_disabled_themes) ? $default_disabled_themes : [];
    $form['theme_options']['disabled_themes'] = [
      '#type' => 'checkboxes',
      '#title' => t('Disable the default Vanilla SelectBox theme for the following themes'),
      '#options' => $this->enabledThemesOptions(),
      '#default_value' => $default_disabled_themes,
      '#description' => $this->t('Enable or disable the default Vanilla SelectBox CSS file. Select a theme if it contains custom styles for Vanilla SelectBox replacements.'),
    ];


    return $form;
  }

  /**
   * Vanilla SelectBox configuration form submit handler.
   *
   * Validates submission by checking for duplicate entries, invalid
   * characters, and that there is an abbreviation and phrase pair.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('vanilla_selectbox.settings');

    $config
      ->set('jquery_selector', $form_state->getValue('jquery_selector'))
      ->set('disable_search', $form_state->getValue('disable_search'))
      ->set('disable_select_all', $form_state->getValue('disable_select_all'))
      ->set('stay_open', $form_state->getValue('stay_open'))
      ->set('maximum_width', $form_state->getValue('maximum_width'))
      ->set('minimum_width', $form_state->getValue('minimum_width'))
      ->set('maximum_height', $form_state->getValue('maximum_height'))
      ->set('maximum_option_width', $form_state->getValue('maximum_option_width'))
      ->set('maximum_select', $form_state->getValue('maximum_select'))
      ->set('item_separator', $form_state->getValue('item_separator'))
      ->set('placeholder_text', $form_state->getValue('placeholder_text'))
      ->set('vanilla_selectbox_include', $form_state->getValue('vanilla_selectbox_include'))
      ->set('disabled_themes', $form_state->getValue('disabled_themes'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Helper function to get options for enabled themes.
   */
  private function enabledThemesOptions() {
    $options = [];

    // Get a list of available themes.
    $themes = $this->themeHandler->listInfo();

    foreach ($themes as $theme_name => $theme) {
      // Only create options for enabled themes.
      if ($theme->status) {
        if (!(isset($theme->info['hidden']) && $theme->info['hidden'])) {
          $options[$theme_name] = $theme->info['name'];
        }
      }
    }

    return $options;
  }

}
