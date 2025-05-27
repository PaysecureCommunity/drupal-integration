<?php
namespace Drupal\hello_world\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * plugin implementation of the 'hello_world_field_widget' widget.
 * 
 * @FieldWidget(
 * id = "hello_world_field_widget",
 * label = @Translation("Hello World Field Widget"),
 * @Translation used to mark strings for translation, allows module to support multiple languages without code changes
 * field_types = {
 *  "hello_world_field"
 * } 
 * )
 * 
 */
class HelloWorldFieldWidget extends WidgetBase {

    /**
     * {@inheritdoc}
     */
    public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
        $element['value'] = [
            '#type' => 'textfield' ,
            '#title' => t('HelloWorldValue'),
            '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : '',
        ];

        return $element;
    }
} 
