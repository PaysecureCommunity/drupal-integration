<?php
namespace Drupal\hello_world\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/** 
 * /** is a docblock comment for documenting code and provide metadata for modules so that tools like ide can understand
 * Plugin implementation of the 'hello_world_field' field type.
 * 
 * @FieldType(
 * id = "hello_world_field",
 * label = @Translation("Hello World Field"),
 * description = @Translation("A custom field type for hello world module"),
 * category = @Translation("Hello World"),
 * default_widget = "hello_world_field_widget",
 * default_formatter = "hello_world_field_formatter"
 * )
*/
class HelloWorldFieldItem extends FieldItemBase {

    /**
     * function schema() is used to define the database schema for the field type.
     * {@inheritdoc}
     */

    public static function schema(FieldStorageDefinitionInterface $field_definition) {
        return [
            'columns' => [
                'value' => [
                    'type' => 'text', 
                    'size' => 'big',
                ]
            ]
                ];
    }
    
    /**
     * function propertyDefinitions() is used to define the properties of the field type.
     * {@inheritdoc}
     */
    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
        $properties['value'] = DataDefinition::create('string')
        ->setLabel(t('Hello World Value'));

        return $properties;
    }

    /**
     * function isEmpty() is used to check if the field value is empty.
     * this function is called by Drupal to determine if the field has a value or not.
     * {@inheritdoc}
     */
    public function isEmpty() {
        $value = $this->get('value')->getValue();
        // check if the value is empty
        return $value === NULL || $value === '';
    }
}