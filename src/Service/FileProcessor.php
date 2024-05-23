namespace Drupal\dll_self_service_custom_module\Service;

use Drupal\file\Entity\File;

class FileProcessor {
  public function processFile(File $file) {
    $file_path = $file->getFileUri();
    $real_path = \Drupal::service('file_system')->realpath($file_path);

    $xslt_path = drupal_get_path('module', 'dll_self_service_custom_module') . '/transform.xslt';
    $latex_path = 'temporary://output.tex';
    $pdf_path = 'temporary://output.pdf';

    // Apply XSLT transformation
    $xslt_command = "xsltproc $xslt_path $real_path > $latex_path";
    exec($xslt_command, $output, $return_var);
    if ($return_var != 0) {
      \Drupal::messenger()->addError(t('XSLT transformation failed.'));
      return;
    }

    // Compile LaTeX to PDF
    $latex_command = "xelatex -output-directory " . dirname($pdf_path) . " $latex_path";
    exec($latex_command, $output, $return_var);
    if ($return_var != 0) {
      \Drupal::messenger()->addError(t('LaTeX compilation failed.'));
      return;
    }

    // Provide the PDF for download
    $pdf_url = file_create_url($pdf_path);
    \Drupal::messenger()->addMessage(t('Download your PDF: <a href="@url">PDF</a>', ['@url' => $pdf_url]));
  }
}
