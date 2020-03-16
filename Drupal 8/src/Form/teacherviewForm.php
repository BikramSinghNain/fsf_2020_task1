<?php

namespace Drupal\student_upload\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class teacherviewForm extends FormBase {

	public function getFormId(){
      return 'teacherviewForm';
    }
    

    function student_upload_teacher($form,&$formState){    
      $form['task_taken'] = array(
              '#type' => 'checkboxes',
              '#options' => drupal_map_assoc(array(t('Java'),t("Text"),t("Pdf"),t("C"),t("Ruby"),t("Php"),t("JavaScript"),t("Jpg"), t('C++'),t('Python'))),
              '#title' => t('Select File Format'),
          );
  
     // The Submit Button
     $form['submit'] = array(
      '#type' => 'submit', 
      '#value' => t('Save'),
      '#ajax' => array(
          'callback' => 'student_upload_save_teacher',
          )
  );
       // The Display All Button
       $form['displayallsubmition'] = array(
          '#type'=>'submit',
          '#value'=>t('Display ALL Submitions'),
          '#ajax' => array(
              'callback' =>'student_upload_all',
              'wrapper' => 'display_files'
              )
      );    
  
      // A Div to Display the Results
      $form['save_div']=array(
          '#type'=>'markup',
          '#prefix'=>'<div id="display_files">',    
          '#suffix'=>'</div>'
      ); 
        return $form; 
  }
  public function  student_upload_save_feed(array &$form, FormStateInterface $form_state){
      $query = db_query('SELECT * FROM student_upload_data');
      $rows = $query->fetchall(); 
      $cnt = count($rows);
      for($i=0;$i<$cnt;$i++){
          $feed = $formState['values']['stu_feedbak'.$i];
          $rat = $formState['values']['rating'.$i] + 1;
          if(strcmp($feed,$rows[$i]->feedback) != 0 || $rows[$i]->rating != $rat){
              try{
                  db_query("UPDATE student_upload_data SET feedback = :feed,rating = :rat WHERE filename = :sid",array(
                      ":feed"=>$feed,
                      ":rat"=>$rat,
                      ':sid'=> $rows[$i]->filename
                  ))->execute();
              }catch(Exception $e){
              }
          }
      }
      return $form;
  }
  function student_upload_feed(array &$form, FormStateInterface $form_state){
      $form['save_btn'] = array(
          '#type'=>'submit',
          '#value'=>t('Update'),
          '#ajax' => array(
              'callback' =>'student_upload_save_feed',
              'wrapper' => 'display_files'
              )
      );
      if(db_table_exists('student_upload_data')){
          $query = db_query('SELECT * FROM student_upload_data');
          $rows = $query->fetchall(); 
          // if(count($rows) < 1) $out = '<b>  Not Found :( </b>';
          // else{
              $form['table_open']=array(
                  '#type'=>'markup',
                  '#prefix'=>'<table><tr><th>Student ID</th><th>File Name</th><th>Feedback</th><th>Rating</th></tr>',    
              );
              for($i = 0;$i < count($rows);$i++){
                  $row = $rows[$i];
                  $form['stu_open'.$i]=array(
                      '#type'=>'markup',
                      '#prefix'=>'<tr><td>'.$row->student_id.'</td><td><a href="sites/default/files/'.str_replace("public://","",$row->filename).'" target="_blank">'.$row->show_filename.'</a></td>',    
                  );        
                  $form['stu_feedbak'.$i]=array(
                      '#type'=>'textfield',
                      '#prefix'=>'<td>',    
                      '#suffix'=>'</td>',
                      '#default_value'=> $row->feedback 
                  );        
                  $form['rating'.$i] = array(
                      '#type' => 'select',
                      '#prefix'=>'<td>',    
                      '#suffix'=>'</td>',    
                      '#options' => array(
                         0 => t('1'),
                        1 => t('2'),
                        2 => t('3'),
                        3 => t('4'),
                        4 => t('5'),
                      ),
                      '#default_value' => ($row->rating - 1),
                  );
                  $form['stu_close'.$i]=array(
                      '#type'=>'markup',
                      '#suffix'=>'</tr>'   
                  );         
              }
              $form['table_close']=array(
                  '#type'=>'markup',
                  '#suffix'=>'</div>'
              ); 
      }
      return $form;
  }
  public function student_upload_all(array &$form, FormStateInterface $form_state){
      return drupal_get_form("student_upload_feed");
  }
  // Function to Save The Data to the database
  public function student_upload_save_teacher(array &$form, FormStateInterface $form_state){
      $message = "Data  Saved"; // message variable to be Printed
      $data = array(); // The row field variable to store values
      $temp = ''; 
      $mapping = array(
          'Pdf' => 'pdf',
          "Text" => 'txt',
          "Java" => 'java',
          "C" => "c",
          "Ruby" => "rb",
          'Php' => "php",
          "JavaScript" => "js",
          "Jpg" => "jpg jpeg",
          "C++" => "cpp",
          "Python" => "py"
      );
      foreach($formState['values']['task_taken'] as $key => $value){
          if(strcmp($key,$value) == 0) 
          $temp .= $mapping[$key]  . ' ';  
      }
      variable_set('teacher_extension_state',$formState['values']['task_taken']);
      variable_set('teacher_extension',$temp);  
    
      $commands[] = ajax_command_invoke(NULL,"diplaySave",array('<strong>'.$message.'</strong>'));
      return array('#type'=>'ajax','#commands'=>$commands);
  }


}  
