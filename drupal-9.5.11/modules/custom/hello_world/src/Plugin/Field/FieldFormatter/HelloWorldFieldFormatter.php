<?php
// we use namespace to organize code into logical groups
namespace Drupal\hello_world\Plugin\Field\FieldFormatter;

// we use (use) keyword to import classes, interfaces from other namespaces to make code more readable 
// FormatterBase class located in C:\xampp\htdocs\drupal-integration\drupal-9.5.11\core\lib\Drupal\Core\Field\FormatterBase.php
// FieldItemListInterface located in c:\xampp\htdocs\drupal-integration\drupal-9.5.11\core\lib\Drupal\Core\Field\FieldItemListInterface.php
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
/**
 * 
*/ 
class HelloWorldFieldFormatter extends FormatterBase {

    /**
     * {@inheritdoc}
     */
    public function viewElements(FieldItemListInterface $items, $Langcode){
        $elements = [];
        // loop through each item in the field
        foreach ($items as $delta => $item) {
            // create a render array for each item
            $elements[$delta] = [
                '#markup' => $item->value,  // use $item->value to get the value of the field item
            ]; 
        }
        return $elements; // return the render array
    }
}
