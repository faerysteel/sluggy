<?php

namespace Drupal\sluggy\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'slugify_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "slugify_formatter",
 *   label = @Translation("Slugify formatter"),
 *   field_types = {
 *     "string",
 *     "string_long"
 *   }
 * )
 */
class slugifyFormatter extends FormatterBase
  implements ContainerFactoryPluginInterface {

  protected $SluggySlugify;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      // Add any services you want to inject here
      $container->get('sluggy.slugify')
    );
  }

  /**
   * Construct a formatter object
   *
   * @param Drupal\sluggy\SluggySlugify $sluggySluggify
   *   The entity manager service
   */

  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, \Drupal\sluggy\SluggySlugify $SluggySlugify) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->SluggySlugify = $SluggySlugify;
  }


  /**
   * Defines the default settings for this plugin.
   *
   * @return array
   * A list of default settings, keyed by the setting name.
   */
  public static function defaultSettings() {
    return [
      'separator' => '-',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['separator'] = [
      '#title' => t('Separator'),
      '#type' => 'textfield',
      '#size' => 5,
      '#default_value' => $this->getSetting('separator'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Slugify text using "%sep" as a separator',
      array('%sep' => $this->getSetting('separator')));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = ['#markup' => $this->viewValue($item)];
    }

    return $elements;
  }

  /**
   * Sluggify text.
   *
   * @param $text
   *   Text to slugify.
   * @return string
   *   The sluggified string.
   */
  protected function sluggify($text) {
    return $this->SluggySlugify->slugify($text, $this->getSetting('separator'));
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return nl2br(Html::escape($this->sluggify($item->value)));
  }
}
