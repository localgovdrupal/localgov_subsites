<?php

namespace Drupal\localgov_campaigns\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget;

/**
 * Class PageBuilderWidget.
 *
 * @package Drupal\localgov_campaigns\Plugin\Field\FieldWidget
 *
 * @FieldWidget(
 *   id = "page_builder_entity_reference_paragraphs",
 *   label = @Translation("Page builder"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class PageBuilderWidget extends InlineParagraphsWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $state = isset($element['top']['links']['collapse_button']) ? 'edit' : 'closed';
    $element['top']['paragraph_type_title']['#attributes']['class'][] = $state;

    if (isset($element['top']['links']['collapse_button'])) {
      $element['top']['links']['collapse_button']['#value'] = $this->t('Minimise');
    }

    if (isset($element['top']['links']['remove_button'])) {
      $element['top']['links']['remove_button']['#value'] = $this->t('Delete');
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);
    return $elements;
  }

}
