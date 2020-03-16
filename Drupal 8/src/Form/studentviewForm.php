<?php

namespace Drupal\student_upload\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class studentviewForm extends FormBase {

   public function getFormId(){
     return 'studentviewForm';
    }
   

    public function buildForm(array $form, FormStateInterface $form_state) {
    // to Show extensions
        $form['studentid']=array(
         '#type'=>"textfield",
         '#title'=>t("Student ID"),
         '#size'=>30,
         '#maxlength'=>20,
         '#required'=>true
         );
         $form["somediv"] = array(
            '#type'=>'markup',
            '#prefix'=>'<div><b>Extensions : </b>'.$ext,
            '#suffix'=>'</div>'
         );
        $form["file1"] = array(
            '#type' => 'file',
            '#title' => t('Choose a file'),
            '#size' => 22,
          );
         // The save Button
       $form['save'] = array(
            '#type' => 'submit', 
            '#value' => t('Save or Modify'),
            '#ajax' => array(
                'callback' => 'student_upload_save_student',
                )
        );
        // The all summision button
        $form['submit']=array(
            '#type'=>'submit',
            '#value'=>t('All Summitions'),
            '#ajax'=>array(
                'callback'=>'student_upload_all_sum')
        
        );
        
       // Div to Display the Submitions
         $form['display_assignment']=array(
            '#type'=>'markup',
            '#prefix'=>'<div id="display_files">',
            '#suffix'=>'</div>'
         );
       return $form;
    }       


    // Function to Save The Data to the database
public function student_upload_save_student(array &$form, FormStateInterface $form_state){
  $message = "Data Can't Be Saved"; 
  $data = array(); // The row field variable to store values 
  $data['student_id'] = $formState['values']['studentid'];
  $data['filename'] = '';
  $data['show_filename'] = '';
  $data['feedback'] = 'No Feedback';
  $data['rating'] = 1;   

  $ext = variable_get('teacher_extension');
  $val = array(
      'file_validate_extensions' => array($ext),
  );
  $file = file_save_upload('file1',$val,"public://");
    
     if ($file) {
      if ($file = file_move($file, 'public://', FILE_EXISTS_RENAME)) {
          $data['filename'] = $file->uri;
          $data['show_filename'] = $file->filename;
      }
      else {
        form_set_error('file', t('Failed to write the uploaded file the site\'s file folder.'));
      }
    }
    else {
      form_set_error('file', t('No file was uploaded.'));
    }
  /// Check Wether the Student ID  is not empty
  $is_empty = false;
  foreach($data as $d)
      if(is_string($d) && empty($d))
          $is_empty = true;
  // if table exist and roll no and name is not empty then add the data to the database
  if(!$is_empty && db_table_exists('student_upload_data')){
      $query = db_insert('student_upload_data');
      $query->fields($data)->execute();
      $message = '<b>Data Saved</b>'; // Change the message to data is saved
  }
  $commands[] = ajax_command_invoke(NULL,"diplaySave",array($message));
  return array('#type'=>'ajax','#commands'=>$commands);
}




// The ajax callback function to Display the Data 
public function student_upload_all_sum(array &$form, FormStateInterface $form_state){


  $out = ''; // variable to store the output html
  // Check wether table exist
  if(db_table_exists('student_upload_data')){
      $query = db_query('SELECT * FROM student_upload_data WHERE student_id = (:val)',array(':val'=>$formState['values']['studentid']));
      $rows = $query->fetchall(); 
      if(count($rows) < 1) $out = '<b>  Not Found :( </b>';
      else{
          $out .= '<table><tr><th>File Name</th><th>Feedback</th><th>Rating</th></tr>';
          foreach($rows as $row){
              $out .= '<tr>';
              $out .= '<td><a href="sites/default/files/'.str_replace("public://","",$row->filename).'">'.$row->show_filename.'</a></td>';
              $out .= '<td>'.$row->feedback.'</td>';
              $out .= '<td>'.$row->rating.'/5</td>';
              $out .= '</tr>';
          }
          $out .= '</table>';           
      }
  }else $out = '<b>Data Not Found :( </b>';
  $commands[] = ajax_command_invoke(NULL,"diplaySave",array($out));
  return array('#type'=>'ajax','#commands'=>$commands);    
}
}
