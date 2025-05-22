<?php
    namespace Drupal\hello_world\Plugin\Block;
    use Drupal\Core\Block\BlockBase;
    use Drupal\core\Form\FormStateInterface;
     /**
      * php doc(documentation block)- used to document code in a way that tools like ide can understand
      * provides a 'Hello World' block
    * @Block(
    * id="hello_world_block",
    * admin_label=@Translation("Hello World Block"),
    * )
    */
    class ExampleBlock extends BlockBase {
        /**
         * defaultConfiguration() sets default block message
         * {@inheritdoc}
         */
        public function defaultConfiguration() {
            return [
                'hello_message' => $this->t('Hello World!'),
            ];
        }

        /**
         * inheritdoc part of PHPDoc documentation standard used across PHP projects
         * build() method- display the block content or message
         * {@inheritdoc} 
         */
    public function build(){
        return[
            '#markup' => $this->configuration('hello_message'),
        ];
    }

    /**
     * {@inheritdoc} 
     * blockForm() method allows us to create configuration form for custom block, creating configuration inteface in drupal admin UI
     */
    public function blockForm($form, FormStateInterface $form_state) {
        $form['hello_message']=[
            '#type' => 'textfield',
            '#title' => $this->t('Message'),
            '#default_value' => $this->configuration('hello_message'),
        ];
        
        return $form;
    }

    /**
     * blockSubmit() method saves the form submission
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        $this->configuration['hello_message']=$form_state->getValue('hello_message');
    }       
}
