<?php
    namespace Drupal\hello_world\Plugin\Block;

    use Drupal\Core\Block\BlockBase;
    use Drupal\Core\Form\FormStateInterface;

     /**
      * php doc(documentation block)- used to document code in a way that tools like ide can understand
      * provides a 'Hello World' block
      * 
    * @Block(
    * id="hello_world_block",
    * admin_label=@Translation("Hello World Block"),
    * )
    */
    class ExampleBlock extends BlockBase {

        /**
         * defaultConfiguration() sets default block message
         * 
         * {@inheritdoc}
         */
        public function defaultConfiguration() {
            // defaultConfiguration() method used to load initial values
            $default_config = \Drupal::config('hello_world.settings'); // drupal stores file under namespace: hello_world.settings
            return [
                'hello_message' => $default_config->get('hello.name'), // used to fetch config from module's hello_world.settings.yml
                // get('hello.name') returns 'Hello world'
            ];
        }

        /**
         * inheritdoc part of PHPDoc documentation standard used across PHP projects
         * build() method- display the block content or message
         * 
         * {@inheritdoc} 
         */
        public function build(){
        return[
            '#markup' => $this->configuration['hello_message'],
        ];
    }

    /**
     * Adds a config form to the block settings in admin.
     * 
     * {@inheritdoc} 
     */
    public function blockForm($form, FormStateInterface $form_state) {
        $form = parent::blockForm($form, $form_state);
        $form['hello_message']=[
            '#type' => 'textfield',
            '#title' => $this->t('Message'),
            '#default_value' => $this->configuration['hello_message'],
        ];  
        
        return $form;
    }

    /**
     * blockSubmit() method saves the form submission
     * 
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        parent::blockSubmit($form, $form_state);
        $this->configuration['hello_message'] = $form_state->getValue('hello_message');
    }
}
