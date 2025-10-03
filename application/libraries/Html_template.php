<?php
  defined('BASEPATH') OR exit('No direct script access allowed');

class Html_template {

private $replace_list = array(), $filepath=null,$header=null,$footer=null;





/**
 * load
 * - the name of the template you trying to load. NOTE: should be in the mail_template folder
 * @param filename - name of the file with extention
 * @return - -----
 * 
 * 
 */
public function load($file){

  try{

    $filepath = mail_template($file);

    if(!file_exists($filepath)){
      throw new Exception("The file $file doesn't exist.", 1);
    }

    $extention = pathinfo($filepath)['extension'];

    if($extention !="html" and $extention != "php"){
        throw new Exception("invalid extention($extention) - only .php and .html is accepeted", 1);
    }

    $this->filepath = $filepath;

    return true;

    



  }catch(Exception $e){
    
        echo $e->getMessage();
    }   
}



public function load_header($file){

  try{

    $filepath = mail_template($file);

    if(!file_exists($filepath)){
      throw new Exception("The file $file doesn't exist.", 1);
    }

    $extention = pathinfo($filepath)['extension'];

    if($extention !="html" and $extention != "php"){
        throw new Exception("invalid extention($extention) - only .php and .html is accepted", 1);
    }

    $this->header = $filepath;

    return true;

    



  }catch(Exception $e){
    
        echo $e->getMessage();
    }   
}



public function load_footer($file){

  try{

    $filepath = mail_template($file);

    if(!file_exists($filepath)){
      throw new Exception("The file $file doesn't exist.", 1);
    }

    $extention = pathinfo($filepath)['extension'];

    if($extention !="html" and $extention != "php"){
        throw new Exception("invalid extention($extention) - only .php and .html is accepeted", 1);
    }

    $this->footer = $filepath;

    return true;

    



  }catch(Exception $e){
    
        echo $e->getMessage();
    }   
}



public function add_variable($variable,$new_variable){
  try{
    
    $data['variable'] = $variable;
    $data['new_variable'] = $new_variable;

    array_push($this->replace_list,$data);

    return true;


  }catch(Exception $e){
    
        echo $e->getMessage();
    }   
}



public function add_css($css_name,$css_path){
  try{

    $filepath = builders($css_path);
    
    if(!file_exists($filepath)){
      throw new Exception("The file $css_path doesn't exist.", 1);
    }

    $data['variable'] = $css_name;
    $data['new_variable'] = read_file($filepath);


    array_push($this->replace_list,$data);

    return true;


  }catch(Exception $e){
    
        echo $e->getMessage();
    }   
}



public function generate(){
  try{

    if(is_null($this->filepath)){
      throw new Exception("please specify the file to work with use the 'template->load()' method to load the file", 1);
    }
    if(is_null($this->header)){
      throw new Exception("please specify the header file to work with use the 'template->load_header()' method to load the file", 1);
    }
    if(is_null($this->footer)){
      throw new Exception("please specify the footer file to work with use the 'template->load_footer()' method to load the file", 1);
    }

    $header = read_file($this->header);

    $file = read_file($this->filepath);

    $footer = read_file($this->footer);

    $template = $header.$file.$footer;

    if(!empty($this->replace_list)){
      foreach($this->replace_list as $data){
        $template = str_replace("{{".$data['variable']."}}",$data['new_variable'] ,$template);
      }
    }

    $this->filepath = null;
    $this->header = null;
    $this->footer = null;
    unset($this->replace_list);
    $this->replace_list = array();

    return $template;

  }catch(Exception $e){
    
        echo $e->getMessage();
    }  
}



public function replace_list(){
  return $this->replace_list;
}



public function single_load($file, $datas = array()) {

  try{

    $filepath = mail_template($file);

    if(!file_exists($filepath)){
      throw new Exception("The file $file doesn't exist.", 1);
    }

    $extention = pathinfo($filepath)['extension'];

    if($extention !="html" and $extention != "php"){
        throw new Exception("invalid extention($extention) - only .php and .html is accepeted", 1);
    }

    
    $template = read_file($filepath);

    if(!empty($datas)){
      foreach($datas as $variable => $value){
        $template = str_replace("{{".$variable."}}",$value ,$template);
      }
    }

    return $template;


  }catch(Exception $e){
    
        echo $e->getMessage();
    }   

}

































}




?>