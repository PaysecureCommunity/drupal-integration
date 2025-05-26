<?php

namespace Drupal\hello_world\Form;

// uses namespaces to organize and automate the loading of classes
use Drupal\core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defining admin settings form for Hello World module. 
 */
class HelloWorldSettingsForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'hello_world_settings_form';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames(){
        return [
            'hello_world.settings'
        ];
    }

    /**
     * {@inheritdoc}
     * buildForm() method constructs and returns the form array
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('hello_world.settings');

        $form['hello_name']=[
            '#type' => 'textfield',
            '#title' => $this->t('Default Hello Message'), 
            '#default_value' => $config->get('hello.name'), // get() method fetches value of hello.name from hello_world.settings.yml
            '#description' => $this->t('Enter a default message to be used by Hello World Block.'),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     * submitForm() executed when user submits the form processing and saving the form data
     */
    public function submitForm(array &$form, FormStateInterface $form_state) { 
        // drupal uses &$form to pass the form array by reference
        parent::submitForm($form, $form_state);

        $this->config('hello_world.settings')
        ->set('hello.name', $form_state->getValue('hello_name')) // set() method sets value of hello.name in hello_world.settings.yml
        ->save();
    }
}