namespace Drupal\dll_self_service_custom_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FileUploadForm extends FormBase {
  public function getFormId() {
    return 'dll_self_service_custom_module_file_upload_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['xml_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Upload XML File'),
      '#description' => $this->t('Upload your XML file for processing.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['xml'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $validators = ['file_validate_extensions' => ['xml']];
    if ($file = file_save_upload('xml_file', $validators, FALSE, 0, FILE_EXISTS_REPLACE)) {
      // Move file to a permanent location.
      $file->setPermanent();
      $file->save();

      // Process the file.
      \Drupal::service('dll_self_service_custom_module.file_processor')->processFile($file);
      \Drupal::messenger()->addMessage($this->t('File uploaded and processed successfully.'));
    }
  }
}
