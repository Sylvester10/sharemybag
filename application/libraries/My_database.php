<?php
  defined('BASEPATH') OR exit('No direct script access allowed');
 

class My_database {

/**
 * public variable declaration
 * 
 */
 protected $servername, $username, $password, $dbname, $conn_status;
 protected $where_conditions=NULL,$select=NULL,$table =NULL,$cols = NULL;

 /**
 * private variable declaration
 * 
 */
 private $conn, $sql, $tables = array();




/**
 * __construct
 * - connects to the database
 * @param servername, @param username, @param password , @param dbName
 * @return - -----
 * 
 * 
 */
  function __construct($data){
            // Try Database connection
            try
            {
              $servername = $data['servername'];
              $username = $data['username'];
              $password = $data['password'];
              $dbname = $data['dbname'];
              //connect to database
              $conn = mysqli_connect($servername, $username, $password, $dbname);
                if (!$conn)
                {// on Failure
                  $this->conn_status = false;
                  throw new Exception('Unable to connect to Database');
                 
                }else{// on success
                  $this->conn_status = true;
                  $this->conn = $conn;
                  $this->load_tables();
                  $this->servername = $servername;
                  $this->username = $username;
                  $this->password = $password;
                  $this->dbname = $dbname;

                }
            }
            catch(Exception $e)
            {
                echo $e->getMessage();
            } 
  }



/**
 * run
 * - runs sql statement stored in the sql property of the object
 * @param ---------
 * @return - -----
 * 
 * 
 */
public function run(){
    try {
            if(!is_null($this->sql)){
              $sql = $this->sql;
              $conn = $this->conn;
              $result = mysqli_query($conn, $sql);
              if($result === false){throw new Exception("Error Check SQL query and try again");}
              
              $this->sql = NULL;
              return $result;
            }else{
              throw new Exception("No SQL query to work with!<br> Use sql() to load query");
            }
        }
    catch( Exception $e ) {
          $message = $e->getMessage();

          die( $message );
      }


}



/**
 * sql
 * - accepts sql statments to be run
 * @param ---------
 * @return - -----
 * 
 * 
 */
public function sql($sql){
       $this->sql = $sql;
}



/**
 * load_tables
 * - returnes all the tables in the database
 * @param ---------
 * @return - array(list of tables in the database)
 * 
 * 
 */
protected function load_tables(){

      $tables = array();
      $this->sql("show tables");
      $result = $this->run();

      /// push tables into table array
      if(!is_bool($result)){
        while($row = mysqli_fetch_assoc($result)) {
            foreach($row as $base=>$table){
             array_push($this->tables,$table);
            }
          }
      }

}



/**
 * tables
 * - returnes all the tables in the database
 * @param ---------
 * @return - array(list of tables in the database)
 * 
 * 
 */
public function tables(){
  return $this->tables;
}



/**
 * get_fields
 * - get fields in a table
 * @param tableName - string
 * @return array(list of columns/fields in the table providesd)
 * 
 * 
 */
public function get_fields($table){
  try {
        $fields = array();
        $tables = $this->tables;
          if (!in_array($table,$tables)){throw new Exception("Invalid Table Supplied!");}

          $this->sql("SELECT * FROM `$table`");
          $result = $this->run();
          while($row = mysqli_fetch_field($result)) {
            array_push($fields,$row->name);
          }

          return $fields;

      }

  catch( Exception $e ) {
          $message = $e->getMessage();

          die( $message );
      }
}



/**
 * split_conditions
 * - splits conditions into and array
 * @param tableName - string
 * @return array(list of columns/fields in the table providesd)
 * 
 * 
 */
public function split_conditions($conditions = ""){
  try {

      if($conditions == ""){
        throw new Exception("<b>Invalid Argument! </b>Provide Coditions to be split (Use <b>,</b> as seperator)");
      }

      $conditions_array = explode(",",$conditions);

      foreach($conditions_array as $condition){
        
        $parts = explode("=",$condition);
        echo"<br>";
        echo $parts[0];
        echo"<br>";
        echo $parts[1];

      }

  }

  catch( Exception $e ) {
      $message = $e->getMessage();

      die( $message );
  }

}



/**
 * is_field
 * - checks to see if a field exist in the databse
 * @param field - string
 * @return true|false(boolen)
 * 
 * 
 */
public function is_field($val=""){

        
        $tables = $this->tables; //load tables
        $counter = 0; //create a counter
        $val = trim($val); //remove white spaces from the counter

        foreach($tables as $table){// loop through table

          if($this->field_in_table($val,$table)){
            $counter += 1;
          }

        }

        if($counter>0){
          return true;
        }else{
          return false;
        }

}




/**
 * is_table
 * - checks to see if a table exist in the databse
 * @param field - string
 * @return true|false(boolen)
 * 
 * 
 */
public function is_table($table){
  $tables = $this->tables; //load tables
  if(in_array($table,$tables)){// check if table exist
    return true;
  }else{
    return false;
  }
}



/**
 * field_in_table
 * - checks to see if a field exist in a table
 * @param field - string @param table - string
 * @return true|false(boolen)
 * 
 * 
 */
public function field_in_table($field,$table){
  try{
        $counter = 0; // counter to check occurence of field in table
        $tables = $this->tables; //load tables

        if(!$this->is_table($table)){// throw error if the table doesnt exist in the database
          throw New exception("Invalid Table supplied");
        }

        $cols = $this->get_fields($table);
        if(in_array($field,$cols)){
          $counter += 1;
        }

        if($counter>0){
          return true;
        }else{
          return false;
        }


      }
  catch( Exception $e ) {
      $message = $e->getMessage();

      die( $message );
      }

}



/**
 * insert_str
 * - inserts data into the database
 * @param table - string @param data - string
 * @return true|false(boolen)
 * 
 * 
 */
public function insert_str($table,$values){
  try {

          // create array to hold values and fields
          $top = array();
          $bottom = array();

          // spliting the second parameter
          $conditions_array = explode(",",$values);

          $len = count($conditions_array);

          for($i=0; $i<$len; $i++){

            $parts = explode("=",$conditions_array[$i]);

            //checks if the request field exist in the table
            if(!$this->field_in_table(trim($parts[0]),$table)){
              throw new exception("<b>Invalid Field Provided!</b> '".trim($parts[0])."'<i> is not a field in </i>'".$table."'."); 
            }

            array_push($top,"`".trim($parts[0])."`");
            array_push($bottom,"'".$this->escape($parts[1])."'");
            
          }

         $columms = "(".implode(", ",$top).")";
         $val = "(".implode(", ",$bottom).")";
         $table = "`".$table."`";

         $sql = "INSERT INTO ".$table." ".$columms." VALUES ".$val.";";
         $this->sql($sql);
         if($this->run()){return true;}else{return false;}

    }

    catch( Exception $e ) {
    $message = $e->getMessage();

    die( $message );
    }

}


/**
 * insert_arr
 * - inserts data into the database
 * @param table - string @param data - string
 * @return true|false(boolen)
 * 
 * 
 */
public function insert_arr($table,$arr){

  try {

          // create array to hold values and fields
          $top = array();
          $bottom = array();

          foreach($arr  as $col=>$val){
            // check if col exist as a field in the table provided  
            if(!$this->field_in_table($col,$table)){
              throw new exception("<b>Invalid Field Provided!</b> '".trim($col)."'<i> is not a field in </i>'".$table."'."); 
            }
                  array_push($top,"`".trim($col)."`");
                  array_push($bottom,"'".$this->escape($val)."'");
            }


        $columms = "(".implode(", ",$top).")";
        $val = "(".implode(", ",$bottom).")";
        $table = "`".$table."`";

        $sql = "INSERT INTO ".$table." ".$columms." VALUES ".$val.";";
        $this->sql($sql);
        if($this->run()){return true;}else{return false;}

      }
  catch( Exception $e ) {
        $message = $e->getMessage();

        die( $message );
      }

}




/**
 * query_check
 * - checks if an sql statement is okay
 * @param sql - string 
 * @return true|false(boolen)
 * 
 * 
 */
public function query_check($sql){

  if($this->is_write($sql) || $this->is_read($sql)){
    return true;
  }else{
    return false;
  }
}



/**
 * query
 * - inserts data into the database calls insert_str if data is string and calls
 * insert_arr if data is array
 * @param sql - string @param data -array
 * @return Object
 * 
 * 
 */

public function query(){

      
    try{
          /// loading required variables
          $args = func_get_args(); //getting arguments from the functions
          $argsNum = count($args); // count the arguments provided
          // sql fixed variables
          
          // if no parameter was provided
          if($argsNum == 0){
            throw new Exception("Wrong Paramter:<br/> Please provide a parameter. (0) Provided");
          }

          //if one paramter was provided
          if($argsNum == 1){
            $sql = $args[0];
          }
          
          // More Than two Parameter provided
          if($argsNum > 2){
            throw new Exception("Too Many Parameters Provided");
            
          }

          // if two parameters was provided
          if($argsNum == 2)
          {

            $vals = $args[1];
            $sql = $args[0];

            // checks if param 2 is an array
            if(!is_array($vals)){
              throw new Exception("Wrong Paramter:<br/> Parameter (2) is supposed to be an array");
            }

            $numVals = count($vals);

            $count_question_mark = substr_count($sql,"?");
            $count_colon = substr_count($sql,":")/2;


            // if both : and ? are passed
            if($count_question_mark > 0 && $count_colon > 0){
              throw new Exception("Please cross check both parameters provided and try again", 1);
              
            }
            



            // when a non associative array is provided
            if($count_question_mark > 0 && $count_colon == 0 && !is_assoc($vals)){

              for ($i=0; $i <($count_question_mark) ; $i++) { 
                
                // check if a value provided is an array
                if(is_array($vals[$i])){
                  $vals[$i] = "(".implode(",",$vals[$i]).")";
                }else{
                  $vals[$i] = "'".$vals[$i]."'";
                }

                $start = stripos($sql,"?");
                $sql = substr_replace($sql,$vals[$i],$start,1);
                
              }

            }

            // when an associative array is provided
            if($count_colon > 0 && $count_question_mark == 0 && is_assoc($vals)){

              foreach($vals as $field => $values){
               // check if a value provided is an array
                if(is_array($values)){
                  $values = "(".implode(",",$values).")";
                }else{
                  $values = "'".$values."'";
                }


                $needle = ":".$field.":";
                $replace = strlen($needle);
                $start = stripos($sql,$needle);

                $sql = substr_replace($sql,$values,$start,$replace);
                           
              }


            }


        }


          // validate the sql
          if(isset($sql) && !$this->query_check($sql)){
            throw new Exception("Wrong Paramter:<br/> The Parameter(1) provided doesn't contain valid SQL Command", 1);                    
          }

          
          // running the query
          $this->sql($sql);
          $result = $this->run();

          $query_sql =$sql;

          $this->clear_sql_values();// clear all the values that created this result

          // collecting constructor Variables
          $servername = $this->servername;
          $username = $this->username;
          $password = $this->password;
          $dbname = $this->dbname;

          /// creating an anonymous class 
          return new class($servername, $username, $password, $dbname,$args,$result,$query_sql) extends My_database{
            //Protected Variables
            protected $args,$result,$query_sql;
        
                    /**
                    * __construct()
                    * - collects data and runs a code on class initiation
                    * insert_arr if data is array
                    * @param parameters for the class 
                    * @return true|false(boolen)
                    *   
                    */
                    public function __construct($servername, $username, $password, $dbname,$args,$result,$query_sql) {
                        $this->args = $args;
                        $this->result = $result;
                        $this->query_sql = $query_sql;
                    }
                    



                    /**
                    * result()
                    * - return raw sql results
                    * insert_arr if data is array
                    * @param - 
                    * @return object
                    *   
                    */
                    public function result(){
                      return $this->result;
                    }


                   /**
                    * fetch_all()
                    * - Fetch all rows and return the result-set as an associative array:
                    * 
                    * @param - 1 = MYSQLI_ASSOC
                    * @param - 2 = MYSQLI_NUM 
                    * @param - 3 = MYSQLI_BOTH
                    * @default 1
                    * @return array
                    *   
                    */
                    public function fetch_all($result_type = 1){

                        try{
                            $check = [
                              1=>true,
                              2=>true,
                              3=>true
                            ];
                          if(!isset($check[$result_type])){
                            throw new Exception("Parameter must either be <br> 1 for MYSQLI_ASSOC <br> 2 for MYSQLI_NUM <br> 3 for MYSQLI_BOTH", 1);
                            
                          }




                          return mysqli_fetch_all($this->result(),$result_type);

                        }catch( Exception $e ) {
                            $message = $e->getMessage();
                          
                            die( $message );
                        }

                      

                    }

                    
                   /**
                    * fetch_array()
                    * - Fetch a result row as a numeric array and as an associative array:
                    * insert_arr if data is array
                    * @param - 1 = MYSQLI_ASSOC
                    * @param - 2 = MYSQLI_NUM 
                    * @param - 3 = MYSQLI_BOTH
                    * @default 3
                    * @return array
                    *   
                    */
                    public function fetch_array($result_type = 3){

                      try{
                          $check = [
                            1=>true,
                            2=>true,
                            3=>true
                          ];
                        if(!isset($check[$result_type])){
                          throw new Exception("Parameter must either be <br> 1 for MYSQLI_ASSOC <br> 2 for MYSQLI_NUM <br> 3 for MYSQLI_BOTH", 1);
                          
                        }




                        return mysqli_fetch_array($this->result(),$result_type);

                      }catch( Exception $e ) {
                          $message = $e->getMessage();
                        
                          die( $message );
                      }

                    

                  }


                   /**
                    * fetch_assoc()
                    * -Fetch a result row as an associative array:
                    * @param - 
                    * @return array
                    *   
                    */
                    public function fetch_assoc(){

                      try{
                          




                        return mysqli_fetch_assoc($this->result());

                      }catch( Exception $e ) {
                          $message = $e->getMessage();
                        
                          die( $message );
                      }

                    

                  }


                    /**
                    * fetch_field()
                    * - Return the next field (column) in the result-set, then print each field's name, table, and max length:
                    * 
                    * @param - 
                    * @return object
                    *   
                    */
                    public function fetch_field(){
                      return mysqli_fetch_field($this->result());
                    }
                    
                    /**
                    * fetch_objects()
                    * - Return the current row of a result set, then print each field's value:
                    * 
                    * @param - 
                    * @return object
                    *   
                    */
                    public function fetch_object(){
                      return mysqli_fetch_object($this->result());
                    }



                    /**
                    * getResults()
                    * - This method returns the query result as an array of objects, or an empty array on failure. Typically you’ll use this in a foreach loop, like this
                    * 
                    * @param - 
                    * @return object
                    *   
                    */
                    public function get_results(string $type ="object"){
                      try{
                        $type = trim($type);
                        $type = strtoupper($type);
                      // if object was requested
                      if($type == "OBJECT"){
                        $arr = [];

                          while($row = mysqli_fetch_object($this->result())){
                            array_push($arr,$row);
                          }
                        
                          return $arr;
                      }


                      //if array was provided
                      if($type == "ARRAY"){

                        return mysqli_fetch_all($this->result(),1);
                      }else{
                        return false;
                      }



                      }catch( Exception $e ) {
                          $message = $e->getMessage();
                        
                          die( $message );
                        }

                      
                    }


                    /**
                    * num_rows()
                    * - Return the number of rows generated
                    * 
                    * @param - 
                    * @return int
                    *   
                    */
                    public function get_num_rows(){
                      $result = $this->result();
                      return $result->num_rows;
                    }



                    /**
                    * field_count()
                    * - Return the number of field generated
                    * 
                    * @param - 
                    * @return int
                    *   
                    */
                    public function get_field_count(){
                      $result = $this->result();
                      return $result->field_count;
                    }



                    /**
                    * get_row()
                    * - Return the number of field generated
                    * 
                    * @param - 
                    * @return int
                    *   
                    */
                    public function get_row(int $num=1,$type = "object"){
                      try{

                        // if number less than or equal to zero was provided
                        if($num <=0){
                          throw new Exception("Please provide a number greater 0", 1);
                          
                        }

                        //get the number of rows
                        $num_row = $this->get_num_rows();
                        
                        if($num > $num_row){
                         throw new Exception("The row requested for does't exist", 1);  
                        }

                        $num = $num-1;

                        
                        if(strtoupper(trim($type)) == "OBJECT"){
                          $result = $this->get_results();
                          return $result[$num];
                        }

                        if(strtoupper(trim($type)) == "ARRAY"){
                          $result = $this->get_results("array");
                          return $result[$num];
                        }else{
                          return false;
                        }
                        

                       


                      }catch( Exception $e ) {
                        $message = $e->getMessage();
                      
                        die( $message );
                      }
                    }



                    /**
                     * get_sql()
                     * - gets the most recent query statement that just ran
                     * 
                     */
                    public function get_sql(){
                      return $this->query_sql;
                    }



  
          };






    }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }
}



/**
 * is_write
 * - checks if a query is write
 * insert_arr if data is array
 * @param sql - 
 * @return Boolen
 * 
 * 
 */
public function is_write($query){
  try{

        if ($query == "") {
         throw new Exception("Provide a valid SQL statement to for analysis", 1);
        }


         // an array holding sql database modifiying key words
        $keys = ["INSERT","UPDATE","DELETE","CREATE","ALTER","DROP"];
        $query = trim($query," ");

        $count = 0;


        // Checking INSERT
        $insert = substr_count(strtoupper($query), "INSERT INTO",0,strlen("INSERT INTO"));
        if($insert == 1 ){
          $values = substr_count(strtoupper($query),"VALUES",strlen("INSERT INTO"),stripos($query,")")+strlen("VALUES")+3);
        }

              if ($insert == 1 && $values == 1) {
                    $count +=1;
              }


        //Checking UPDATE
        $update = substr_count(strtoupper($query), "UPDATE",0,strlen("UPDATE"));
        $set = substr_count(strtoupper($query), "SET");
        $where = substr_count(strtoupper($query), "WHERE");

              if($update == 1 && $set >=1 && $where >=0){
                    $count +=1;
              }

        //checking for DELETE
          $delete =  substr_count(strtoupper($query), "DELETE FROM",0,strlen("DELETE FROM"));

          if($delete == 1){
            $where = substr_count(strtoupper($query), "WHERE",strlen("DELETE FROM"));
          }
         
              if($delete == 1 && $where ==1){
                    $count +=1;
              }

        //Checking CREATE TABLE
          $create = substr_count(strtoupper($query), "CREATE TABLE",0,strlen("CREATE TABLE"));

          if ($create) {
            $count +=1;
          }


          //Checking DROP TABLE
          $drop = substr_count(strtoupper($query), "DROP TABLE",0,strlen("DROP TABLE"));

          if ($drop) {
            $count +=1;
          }

          //Checking ALTER TABLE
          $alter = substr_count(strtoupper($query), "ALTER TABLE",0,strlen("ALTER TABLE"));
          $add = substr_count(strtoupper($query), "ADD",strlen("ALTER TABLE"));
          $drop_col = substr_count(strtoupper($query), "DROP COLUMN",strlen("ALTER TABLE"));
          $alter_col = substr_count(strtoupper($query), "ALTER COLUMN",strlen("ALTER TABLE"));
          $check = $add + $drop_col + $alter_col;

          if ($alter == 1 && $check >=1) {
            $count +=1;
          }


          // displaying boolen based on outcome
          if($count){
            return true;
          }else{
            return false;
          }



    }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }
}


/**
 * is_read
 * - checks if a query is read
 * insert_arr if data is array
 * @param sql - 
 * @return Boolen
 * 
 * 
 */
public function is_read($query){
  try{

        if ($query == "") {
         throw new Exception("Provide a valid SQL statement to for analysis", 1);
        }


         // an array holding sql database modifiying key words
        $keys = ["SELECT"."SELECT DISTINCT","SELECT TOP"];
        $query = trim($query," ");

        $count = 0;

        //Checking for SELECT
        $select = substr_count(strtoupper($query), "SELECT",0,strlen("SELECT"));
        // checking for WHERE
        $where = substr_count(strtoupper($query),"WHERE",(stripos(strtoupper($query), "FROM")+strlen("FROM")));
        // Checking for FROM
        if($where){
          $from =  substr_count(strtoupper($query), "FROM",strlen("SELECT"),(stripos(strtoupper($query), "FROM")));
        }else{
          $from =  substr_count(strtoupper($query), "FROM",strlen("SELECT"));
        }
      

        if($select){
          $count +=1;
        }
        if($where){
          $count +=1;
        }
        if($from){
          $count +=1;
        }

        //return query
        if($select && $count >=2){
          return true;
        }else{
          return false;
        }




  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }
}



/**
 * insert
 * - inserts data into the database calls insert_str if data is string and calls
 * insert_arr if data is array
 * @param table - string @param data - string or array
 * @return true|false(boolen)
 * 
 * 
 */
public function insert($table,$values){
  try{
      // check if table exist
      if(!$this->is_table(trim($table))){
        throw new exception("<b>Invalid Table Provided!</b> '".$table."' is not a valid table in the database");
      }

      if(is_array($values)){
        return $this->insert_arr($table,$values);
      }

      if(is_string($values)){
        return $this->insert_str($table,$values);
      }


    }
  catch( Exception $e ) {
      $message = $e->getMessage();

      die( $message );
    }
}




/**
 * select
 * - Selects data from the database
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return Boolen
 * @example select('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
 try
 {
    // checking for limit
    if($limit !=""){

      //if array is provided
      if(is_array($limit)){
        $limit = implode(",",$limit);
      }

      $limit = explode(",",$limit);
      $limit_count = count($limit);

      if($limit_count == 1){
        $offset = 0;
        $range = $limit[0];
      }elseif($limit_count == 2){
        $offset = $limit[0];
        $range = $limit[1];
      }

      if($limit_count>2){
        throw new Exception("please provide at most two values for limit in the 5th argument", 1);
      }

      $limit =" LIMIT ".$offset.", ".$range;

    }


    // checking for table
    if($table == ""){
      $table = $this->get_table();

        // check if a table is still empty
        if($table == NULL){
          throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select() to set the table", 1);
        }

      }else{
        // check if a table exists in the database first
        if(!$this->is_table($table)){
          throw new Exception("Invalid table name passed - (".$table.")", 1);
        }
    }
  

      // checking order by
      if($order_by != ""){
        if(!is_string($order_by) and is_array($order_by)){

          $order_string ="";
          $order_num = count($order_by);
          $order_counter = 1;

          foreach($order_by as $order){

            $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";

            if(substr_count($order,"-")){
                $details = explode("-",$order);
                $col = $details[0];
                $filter = strtoupper($details[1]);

                // check to make sure filter is okay
                if($filter != "DESC" and $filter != "ASC"){
                  throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                  
                }
            }else{
              $col = $order;
              $filter = "ASC";
            }

            // check the column provided
            if(!$this->field_in_table($col,$table)){
              throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
            }

            $order_string .=$col." ".$filter.$comma;

            $order_counter++;
          }

          $order_string =" ORDER BY ".$order_string;


            }else{
              throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
            }
            
        }else{
          $order_string ="";
      }
    
    // checking for columns
    if($cols == ""){// if no column is provided to the select methos

        $cols = $this->get_cols();// assign the column from the general column holder

        //check if cols is still empty
        if($cols == NULL){
          throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select() to set the column", 1);
        }

      }else{
         // checking each field across table
         if(is_array($cols)){
                foreach($cols as $col){
                  if(!$this->field_in_table($col,$table)){
                      throw new Exception($col." is not a field in ".$table, 1);
                  }
                }
          }
          $this->set_cols($cols);

          $cols = $this->get_cols();
    }
    

    //checking for WHERE CONDITION

      // if array is provided
      if(is_array($where)){
        $this->where($where);
        $where = $this->where_condition();
      }

    // if empty
    if($where ==""){
      $where = $this->where_condition();
      // if($where == NULL){
      //   throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select() to set the conditions", 1);
      // }
    }
   
    // checking if a table is provided
    if($table == NULL){
      throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
      
    }

    //checking for cols 
    if($cols == NULL){
      $cols = "*";
    }

    //checking for where
    if($where == NULL){
      $where ="";
      }else{
        $where = " WHERE ".$where;
    }
    $table = "`".$table."`";
    $sql = "SELECT ".$cols." FROM ".$table.$where.$order_string.$limit;

    return $this->query($sql);
    //return $sql;




 }catch( Exception $e ) {
  $message = $e->getMessage();

  die( $message );
 }


}


/**
 * select_distinct
 * - selects distinct data from the database.
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return object
 * @example select_distinct('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select_distinct($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
  try
  {
     // checking for limit
     if($limit !=""){
 
       //if array is provided
       if(is_array($limit)){
         $limit = implode(",",$limit);
       }
 
       $limit = explode(",",$limit);
       $limit_count = count($limit);
 
       if($limit_count == 1){
         $offset = 0;
         $range = $limit[0];
       }elseif($limit_count == 2){
         $offset = $limit[0];
         $range = $limit[1];
       }
 
       if($limit_count>2){
         throw new Exception("please provide at most two values for limit in the 5th argument", 1);
       }
 
       $limit =" LIMIT ".$offset.", ".$range;
 
     }
 
 
     // checking for table
     if($table == ""){
       $table = $this->get_table();
 
         // check if a table is still empty
         if($table == NULL){
           throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select_distinct() to set the table", 1);
         }
 
       }else{
         // check if a table exists in the database first
         if(!$this->is_table($table)){
           throw new Exception("Invalid table name passed - (".$table.")", 1);
         }
     }
   
 
       // checking order by
       if($order_by != ""){
         if(!is_string($order_by) and is_array($order_by)){
 
           $order_string ="";
           $order_num = count($order_by);
           $order_counter = 1;
 
           foreach($order_by as $order){
 
             $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";
 
             if(substr_count($order,"-")){
                 $details = explode("-",$order);
                 $col = $details[0];
                 $filter = strtoupper($details[1]);
 
                 // check to make sure filter is okay
                 if($filter != "DESC" and $filter != "ASC"){
                   throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                   
                 }
             }else{
               $col = $order;
               $filter = "ASC";
             }
 
             // check the column provided
             if(!$this->field_in_table($col,$table)){
               throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
             }
 
             $order_string .=$col." ".$filter.$comma;
 
             $order_counter++;
           }
 
           $order_string =" ORDER BY ".$order_string;
 
 
             }else{
               throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
             }
             
         }else{
           $order_string ="";
       }
     
     // checking for columns
     if($cols == ""){// if no column is provided to the select methos
 
         $cols = $this->get_cols();// assign the column from the general column holder
 
         //check if cols is still empty
         if($cols == NULL){
           throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select_distinct() to set the column", 1);
         }
 
       }else{

        // checking each field across table
        if(is_array($cols)){
          foreach($cols as $col){
            if(!$this->field_in_table($col,$table)){
                throw new Exception($col." is not a field in ".$table, 1);
                
            }
          }
        }
       
           $this->set_cols($cols);
 
           $cols = $this->get_cols();
     }
     
 
     //checking for WHERE CONDITION
 
       // if array is provided
       if(is_array($where)){
         $this->where($where);
         $where = $this->where_condition();
       }
 
     // if empty
     if($where ==""){
       $where = $this->where_condition();
      //  if($where == NULL){
      //    throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select_distinct() to set the conditions", 1);
      //  }
     }
    
     // checking if a table is provided
     if($table == NULL){
       throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
       
     }
 
     //checking for cols 
     if($cols == NULL){
       $cols = "*";
     }
 
     //checking for where
     if($where == NULL){
       $where ="";
       }else{
         $where = " WHERE ".$where;
     }
 
     $sql = "SELECT DISTINCT ".$cols." FROM ".$table.$where.$order_string.$limit;
 
     return $this->query($sql);
 
 
 
 
  }catch( Exception $e ) {
   $message = $e->getMessage();
 
   die( $message );
  }
 
 
}




/**
 * select_count_distinct
 * - selects and counts distinct data from the database.
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return int
 * @example select_count_distinct('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select_count_distinct($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
  try
  {
     // checking for limit
     if($limit !=""){
 
       //if array is provided
       if(is_array($limit)){
         $limit = implode(",",$limit);
       }
 
       $limit = explode(",",$limit);
       $limit_count = count($limit);
 
       if($limit_count == 1){
         $offset = 0;
         $range = $limit[0];
       }elseif($limit_count == 2){
         $offset = $limit[0];
         $range = $limit[1];
       }
 
       if($limit_count>2){
         throw new Exception("please provide at most two values for limit in the 5th argument", 1);
       }
 
       $limit =" LIMIT ".$offset.", ".$range;
 
     }
 
 
     // checking for table
     if($table == ""){
       $table = $this->get_table();
 
         // check if a table is still empty
         if($table == NULL){
           throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select_count_distinct() to set the table", 1);
         }
 
       }else{
         // check if a table exists in the database first
         if(!$this->is_table($table)){
           throw new Exception("Invalid table name passed - (".$table.")", 1);
         }
     }
   
 
       // checking order by
       if($order_by != ""){
         if(!is_string($order_by) and is_array($order_by)){
 
           $order_string ="";
           $order_num = count($order_by);
           $order_counter = 1;
 
           foreach($order_by as $order){
 
             $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";
 
             if(substr_count($order,"-")){
                 $details = explode("-",$order);
                 $col = $details[0];
                 $filter = strtoupper($details[1]);
 
                 // check to make sure filter is okay
                 if($filter != "DESC" and $filter != "ASC"){
                   throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                   
                 }
             }else{
               $col = $order;
               $filter = "ASC";
             }
 
             // check the column provided
             if(!$this->field_in_table($col,$table)){
               throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
             }
 
             $order_string .=$col." ".$filter.$comma;
 
             $order_counter++;
           }
 
           $order_string =" ORDER BY ".$order_string;
 
 
             }else{
               throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
             }
             
         }else{
           $order_string ="";
       }
     
     // checking for columns
     if($cols == ""){// if no column is provided to the select methos
 
         $cols = $this->get_cols();// assign the column from the general column holder
 
         //check if cols is still empty
         if($cols == NULL){
           throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select_count_distinct() to set the column", 1);
         }
 
       }else{

        // checking each field across table
        foreach($cols as $col){
          if(!$this->field_in_table($col,$table)){
              throw new Exception($col." is not a field in ".$table, 1);
              
          }
        }
           $this->set_cols($cols);
 
           $cols = $this->get_cols();
     }
     
 
     //checking for WHERE CONDITION
 
       // if array is provided
       if(is_array($where)){
         $this->where($where);
         $where = $this->where_condition();
       }
 
     // if empty
     if($where ==""){
       $where = $this->where_condition();
      //  if($where == NULL){
      //    throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select_count_distinct() to set the conditions", 1);
      //  }
     }
    
     // checking if a table is provided
     if($table == NULL){
       throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
       
     }
 
     //checking for cols 
     if($cols == NULL){
       $cols = "*";
     }
 
     //checking for where
     if($where == NULL){
       $where ="";
       }else{
         $where = " WHERE ".$where;
     }
 
     $sql = "SELECT count(DISTINCT ".$cols.") as count FROM ".$table.$where.$order_string.$limit;
 
     $result = $this->query($sql)->get_results();

     return $result = $result[0]->count;
 
 
 
 
  }catch( Exception $e ) {
   $message = $e->getMessage();
 
   die( $message );
  }
 
 
}




/**
 * select_sum
 * - selects and sum data from the database.
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return int
 * @example select_sum('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select_sum($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
  try
  {
     // checking for limit
     if($limit !=""){
 
       //if array is provided
       if(is_array($limit)){
         $limit = implode(",",$limit);
       }
 
       $limit = explode(",",$limit);
       $limit_count = count($limit);
 
       if($limit_count == 1){
         $offset = 0;
         $range = $limit[0];
       }elseif($limit_count == 2){
         $offset = $limit[0];
         $range = $limit[1];
       }
 
       if($limit_count>2){
         throw new Exception("please provide at most two values for limit in the 5th argument", 1);
       }
 
       $limit =" LIMIT ".$offset.", ".$range;
 
     }
 
 
     // checking for table
     if($table == ""){
       $table = $this->get_table();
 
         // check if a table is still empty
         if($table == NULL){
           throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select_sum() to set the table", 1);
         }
 
       }else{
         // check if a table exists in the database first
         if(!$this->is_table($table)){
           throw new Exception("Invalid table name passed - (".$table.")", 1);
         }
     }
   
 
       // checking order by
       if($order_by != ""){
         if(!is_string($order_by) and is_array($order_by)){
 
           $order_string ="";
           $order_num = count($order_by);
           $order_counter = 1;
 
           foreach($order_by as $order){
 
             $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";
 
             if(substr_count($order,"-")){
                 $details = explode("-",$order);
                 $col = $details[0];
                 $filter = strtoupper($details[1]);
 
                 // check to make sure filter is okay
                 if($filter != "DESC" and $filter != "ASC"){
                   throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                   
                 }
             }else{
               $col = $order;
               $filter = "ASC";
             }
 
             // check the column provided
             if(!$this->field_in_table($col,$table)){
               throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
             }
 
             $order_string .=$col." ".$filter.$comma;
 
             $order_counter++;
           }
 
           $order_string =" ORDER BY ".$order_string;
 
 
             }else{
               throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
             }
             
         }else{
           $order_string ="";
       }
     
     // checking for columns
     if($cols == ""){// if no column is provided to the select methos
 
         $cols = $this->get_cols();// assign the column from the general column holder
 
         //check if cols is still empty
         if($cols == NULL){
           throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select_sum() to set the column", 1);
         }
 
       }else{

        if(is_array($cols) and count($cols)>1){
          throw new Exception("select_sum() -only 1 column is required ".count($cols)." was provided", 1);
          
        }
        // checking each field across table
        if(is_array($cols)){
          foreach($cols as $col){
            if(!$this->field_in_table($col,$table)){
                throw new Exception($col." is not a field in ".$table, 1);
                
            }
          }
        }
           $this->set_cols($cols);
 
           $cols = $this->get_cols();
     }
     
 
     //checking for WHERE CONDITION
 
       // if array is provided
       if(is_array($where)){
         $this->where($where);
         $where = $this->where_condition();
       }
 
     // if empty
     if($where ==""){
       $where = $this->where_condition();
      //  if($where == NULL){
      //    throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select_sum() to set the conditions", 1);
      //  }
     }
    
     // checking if a table is provided
     if($table == NULL){
       throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
       
     }
 
     //checking for cols 
     if($cols == NULL){
       $cols = "*";
     }
 
     //checking for where
     if($where == NULL){
       $where ="";
       }else{
         $where = " WHERE ".$where;
     }
 
     $sql = "SELECT sum(".$cols.") as sum FROM ".$table.$where.$order_string.$limit;
 
     $result = $this->query($sql)->get_results();

     return $result = $result[0]->sum;
 
 
 
 
  }catch( Exception $e ) {
   $message = $e->getMessage();
 
   die( $message );
  }
 
 
}






/**
 * select_count
 * - selects and counts data from the database.
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return int
 * @example select_count('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select_count($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
  try
  {
     // checking for limit
     if($limit !=""){
 
       //if array is provided
       if(is_array($limit)){
         $limit = implode(",",$limit);
       }
 
       $limit = explode(",",$limit);
       $limit_count = count($limit);
 
       if($limit_count == 1){
         $offset = 0;
         $range = $limit[0];
       }elseif($limit_count == 2){
         $offset = $limit[0];
         $range = $limit[1];
       }
 
       if($limit_count>2){
         throw new Exception("please provide at most two values for limit in the 5th argument", 1);
       }
 
       $limit =" LIMIT ".$offset.", ".$range;
 
     }
 
 
     // checking for table
     if($table == ""){
       $table = $this->get_table();
 
         // check if a table is still empty
         if($table == NULL){
           throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select_count() to set the table", 1);
         }
 
       }else{
         // check if a table exists in the database first
         if(!$this->is_table($table)){
           throw new Exception("Invalid table name passed - (".$table.")", 1);
         }
     }
   
 
       // checking order by
       if($order_by != ""){
         if(!is_string($order_by) and is_array($order_by)){
 
           $order_string ="";
           $order_num = count($order_by);
           $order_counter = 1;
 
           foreach($order_by as $order){
 
             $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";
 
             if(substr_count($order,"-")){
                 $details = explode("-",$order);
                 $col = $details[0];
                 $filter = strtoupper($details[1]);
 
                 // check to make sure filter is okay
                 if($filter != "DESC" and $filter != "ASC"){
                   throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                   
                 }
             }else{
               $col = $order;
               $filter = "ASC";
             }
 
             // check the column provided
             if(!$this->field_in_table($col,$table)){
               throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
             }
 
             $order_string .=$col." ".$filter.$comma;
 
             $order_counter++;
           }
 
           $order_string =" ORDER BY ".$order_string;
 
 
             }else{
               throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
             }
             
         }else{
           $order_string ="";
       }
     
     // checking for columns
     if($cols == ""){// if no column is provided to the select methos
 
         $cols = $this->get_cols();// assign the column from the general column holder
 
         //check if cols is still empty
         if($cols == NULL){
           throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select_count() to set the column", 1);
         }
 
       }else{

        if(is_array($cols) and count($cols)>1){
          throw new Exception("select_sum() -only 1 column is required ".count($cols)." was provided", 1);
          
        }
        // checking each field across table
        if(is_array($cols)){
          foreach($cols as $col){
            if(!$this->field_in_table($col,$table)){
                throw new Exception($col." is not a field in ".$table, 1);
                
            }
          }
        }
           $this->set_cols($cols);
 
           $cols = $this->get_cols();
     }
     
 
     //checking for WHERE CONDITION
 
       // if array is provided
       if(is_array($where)){
         $this->where($where);
         $where = $this->where_condition();
       }
 
     // if empty
     if($where ==""){
       $where = $this->where_condition();
      //  if($where == NULL){
      //    throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select_count() to set the conditions", 1);
      //  }
     }
    
     // checking if a table is provided
     if($table == NULL){
       throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
       
     }
 
     //checking for cols 
     if($cols == NULL){
       $cols = "*";
     }
 
     //checking for where
     if($where == NULL){
       $where ="";
       }else{
         $where = " WHERE ".$where;
     }
 
     $sql = "SELECT count(".$cols.") as count FROM ".$table.$where.$order_string.$limit;
 
     $result = $this->query($sql)->get_results();

     return $result = $result[0]->count;
 
 
 
 
  }catch( Exception $e ) {
   $message = $e->getMessage();
 
   die( $message );
  }
 
 
}



/**
 * select_avg
 * - selects and gets average data from the a field database.
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return int
 * @example select_count('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select_avg($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
  try
  {
     // checking for limit
     if($limit !=""){
 
       //if array is provided
       if(is_array($limit)){
         $limit = implode(",",$limit);
       }
 
       $limit = explode(",",$limit);
       $limit_count = count($limit);
 
       if($limit_count == 1){
         $offset = 0;
         $range = $limit[0];
       }elseif($limit_count == 2){
         $offset = $limit[0];
         $range = $limit[1];
       }
 
       if($limit_count>2){
         throw new Exception("please provide at most two values for limit in the 5th argument", 1);
       }
 
       $limit =" LIMIT ".$offset.", ".$range;
 
     }
 
 
     // checking for table
     if($table == ""){
       $table = $this->get_table();
 
         // check if a table is still empty
         if($table == NULL){
           throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select_avg() to set the table", 1);
         }
 
       }else{
         // check if a table exists in the database first
         if(!$this->is_table($table)){
           throw new Exception("Invalid table name passed - (".$table.")", 1);
         }
     }
   
 
       // checking order by
       if($order_by != ""){
         if(!is_string($order_by) and is_array($order_by)){
 
           $order_string ="";
           $order_num = count($order_by);
           $order_counter = 1;
 
           foreach($order_by as $order){
 
             $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";
 
             if(substr_count($order,"-")){
                 $details = explode("-",$order);
                 $col = $details[0];
                 $filter = strtoupper($details[1]);
 
                 // check to make sure filter is okay
                 if($filter != "DESC" and $filter != "ASC"){
                   throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                   
                 }
             }else{
               $col = $order;
               $filter = "ASC";
             }
 
             // check the column provided
             if(!$this->field_in_table($col,$table)){
               throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
             }
 
             $order_string .=$col." ".$filter.$comma;
 
             $order_counter++;
           }
 
           $order_string =" ORDER BY ".$order_string;
 
 
             }else{
               throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
             }
             
         }else{
           $order_string ="";
       }
     
     // checking for columns
     if($cols == ""){// if no column is provided to the select methos
 
         $cols = $this->get_cols();// assign the column from the general column holder
 
         //check if cols is still empty
         if($cols == NULL){
           throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select_avg() to set the column", 1);
         }
 
       }else{

        if(is_array($cols) and count($cols)>1){
          throw new Exception("select_sum() -only 1 column is required ".count($cols)." was provided", 1);
          
        }
        // checking each field across table
        if(is_array($cols)){
          foreach($cols as $col){
            if(!$this->field_in_table($col,$table)){
                throw new Exception($col." is not a field in ".$table, 1);
                
            }
          }
        }
           $this->set_cols($cols);
 
           $cols = $this->get_cols();
     }
     
 
     //checking for WHERE CONDITION
 
       // if array is provided
       if(is_array($where)){
         $this->where($where);
         $where = $this->where_condition();
       }
 
     // if empty
     if($where ==""){
       $where = $this->where_condition();
      //  if($where == NULL){
      //    throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select_avg() to set the conditions", 1);
      //  }
     }
    
     // checking if a table is provided
     if($table == NULL){
       throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
       
     }
 
     //checking for cols 
     if($cols == NULL){
       $cols = "*";
     }
 
     //checking for where
     if($where == NULL){
       $where ="";
       }else{
         $where = " WHERE ".$where;
     }
 
     $sql = "SELECT avg(".$cols.") as avg FROM ".$table.$where.$order_string.$limit;
 
     $result = $this->query($sql)->get_results();

     return $result = $result[0]->avg;
 
 
 
 
  }catch( Exception $e ) {
   $message = $e->getMessage();
 
   die( $message );
  }
 
 
}





/**
 * select_min
 * - selects and gets minimum value from the a field database.
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return int
 * @example select_min('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select_min($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
  try
  {
     // checking for limit
     if($limit !=""){
 
       //if array is provided
       if(is_array($limit)){
         $limit = implode(",",$limit);
       }
 
       $limit = explode(",",$limit);
       $limit_count = count($limit);
 
       if($limit_count == 1){
         $offset = 0;
         $range = $limit[0];
       }elseif($limit_count == 2){
         $offset = $limit[0];
         $range = $limit[1];
       }
 
       if($limit_count>2){
         throw new Exception("please provide at most two values for limit in the 5th argument", 1);
       }
 
       $limit =" LIMIT ".$offset.", ".$range;
 
     }
 
 
     // checking for table
     if($table == ""){
       $table = $this->get_table();
 
         // check if a table is still empty
         if($table == NULL){
           throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select_min() to set the table", 1);
         }
 
       }else{
         // check if a table exists in the database first
         if(!$this->is_table($table)){
           throw new Exception("Invalid table name passed - (".$table.")", 1);
         }
     }
   
 
       // checking order by
       if($order_by != ""){
         if(!is_string($order_by) and is_array($order_by)){
 
           $order_string ="";
           $order_num = count($order_by);
           $order_counter = 1;
 
           foreach($order_by as $order){
 
             $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";
 
             if(substr_count($order,"-")){
                 $details = explode("-",$order);
                 $col = $details[0];
                 $filter = strtoupper($details[1]);
 
                 // check to make sure filter is okay
                 if($filter != "DESC" and $filter != "ASC"){
                   throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                   
                 }
             }else{
               $col = $order;
               $filter = "ASC";
             }
 
             // check the column provided
             if(!$this->field_in_table($col,$table)){
               throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
             }
 
             $order_string .=$col." ".$filter.$comma;
 
             $order_counter++;
           }
 
           $order_string =" ORDER BY ".$order_string;
 
 
             }else{
               throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
             }
             
         }else{
           $order_string ="";
       }
     
     // checking for columns
     if($cols == ""){// if no column is provided to the select methos
 
         $cols = $this->get_cols();// assign the column from the general column holder
 
         //check if cols is still empty
         if($cols == NULL){
           throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select_min() to set the column", 1);
         }
 
       }else{

        if(is_array($cols) and count($cols)>1){
          throw new Exception("select_sum() -only 1 column is required ".count($cols)." was provided", 1);
          
        }
        // checking each field across table
        if(is_array($cols)){
          foreach($cols as $col){
            if(!$this->field_in_table($col,$table)){
                throw new Exception($col." is not a field in ".$table, 1);
                
            }
          }
        }
           $this->set_cols($cols);
 
           $cols = $this->get_cols();
     }
     
 
     //checking for WHERE CONDITION
 
       // if array is provided
       if(is_array($where)){
         $this->where($where);
         $where = $this->where_condition();
       }
 
     // if empty
     if($where ==""){
       $where = $this->where_condition();
      //  if($where == NULL){
      //    throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select_min() to set the conditions", 1);
      //  }
     }
    
     // checking if a table is provided
     if($table == NULL){
       throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
       
     }
 
     //checking for cols 
     if($cols == NULL){
       $cols = "*";
     }
 
     //checking for where
     if($where == NULL){
       $where ="";
       }else{
         $where = " WHERE ".$where;
     }
 
     $sql = "SELECT min(".$cols.") as min FROM ".$table.$where.$order_string.$limit;
 
     $result = $this->query($sql)->get_results();

     return $result = $result[0]->min;
 
 
 
 
  }catch( Exception $e ) {
   $message = $e->getMessage();
 
   die( $message );
  }
 
 
}




/**
 * select_max
 * - selects and gets maximum value from the a field database.
 * @param table - string
 * @param Cols- array or string
 * @param where - array or string
 * @param order_by- array @example ['caption','id-desc']
 * @param limit - array or string (one value provid the RANGE) (two values provid OFFSET , RANGE)
 * @return int
 * @example select_max('events',['caption','featured_image','id','event_date'],['id>'=>4],['caption','id-desc'],40)
 * 
 */
public function select_max($table = "", $cols="" ,$where="",$order_by = "",$limit =""){
  try
  {
     // checking for limit
     if($limit !=""){
 
       //if array is provided
       if(is_array($limit)){
         $limit = implode(",",$limit);
       }
 
       $limit = explode(",",$limit);
       $limit_count = count($limit);
 
       if($limit_count == 1){
         $offset = 0;
         $range = $limit[0];
       }elseif($limit_count == 2){
         $offset = $limit[0];
         $range = $limit[1];
       }
 
       if($limit_count>2){
         throw new Exception("please provide at most two values for limit in the 5th argument", 1);
       }
 
       $limit =" LIMIT ".$offset.", ".$range;
 
     }
 
 
     // checking for table
     if($table == ""){
       $table = $this->get_table();
 
         // check if a table is still empty
         if($table == NULL){
           throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in select_max() to set the table", 1);
         }
 
       }else{
         // check if a table exists in the database first
         if(!$this->is_table($table)){
           throw new Exception("Invalid table name passed - (".$table.")", 1);
         }
     }
   
 
       // checking order by
       if($order_by != ""){
         if(!is_string($order_by) and is_array($order_by)){
 
           $order_string ="";
           $order_num = count($order_by);
           $order_counter = 1;
 
           foreach($order_by as $order){
 
             $comma = ($order_num == 1 or $order_num == $order_counter)?"":", ";
 
             if(substr_count($order,"-")){
                 $details = explode("-",$order);
                 $col = $details[0];
                 $filter = strtoupper($details[1]);
 
                 // check to make sure filter is okay
                 if($filter != "DESC" and $filter != "ASC"){
                   throw new Exception("Please use ASC or DESC Instead of ".$filter, 1);
                   
                 }
             }else{
               $col = $order;
               $filter = "ASC";
             }
 
             // check the column provided
             if(!$this->field_in_table($col,$table)){
               throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
             }
 
             $order_string .=$col." ".$filter.$comma;
 
             $order_counter++;
           }
 
           $order_string =" ORDER BY ".$order_string;
 
 
             }else{
               throw new Exception("Not Array provided: The 4th argument must be an array the first value in the array must be ASC or DESC , while the other values can be the columns to be orderd");
             }
             
         }else{
           $order_string ="";
       }
     
     // checking for columns
     if($cols == ""){// if no column is provided to the select methos
 
         $cols = $this->get_cols();// assign the column from the general column holder
 
         //check if cols is still empty
         if($cols == NULL){
           throw new Exception("Parameter 2 has to be a COLUMN/FIELD in the database and must not be empty<br> use set_set() to set the column OR<br> use the second paramter in select_max() to set the column", 1);
         }
 
       }else{

        if(is_array($cols) and count($cols)>1){
          throw new Exception("select_sum() -only 1 column is required ".count($cols)." was provided", 1);
          
        }
        // checking each field across table
        if(is_array($cols)){
          foreach($cols as $col){
            if(!$this->field_in_table($col,$table)){
                throw new Exception($col." is not a field in ".$table, 1);
                
            }
          }
        }
           $this->set_cols($cols);
 
           $cols = $this->get_cols();
     }
     
 
     //checking for WHERE CONDITION
 
       // if array is provided
       if(is_array($where)){
         $this->where($where);
         $where = $this->where_condition();
       }
 
     // if empty
     if($where ==""){
       $where = $this->where_condition();
      //  if($where == NULL){
      //    throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in select_max() to set the conditions", 1);
      //  }
     }
    
     // checking if a table is provided
     if($table == NULL){
       throw new Exception("Please Provide a table. use the method(' set_table() ')", 1);
       
     }
 
     //checking for cols 
     if($cols == NULL){
       $cols = "*";
     }
 
     //checking for where
     if($where == NULL){
       $where ="";
       }else{
         $where = " WHERE ".$where;
     }
 
     $sql = "SELECT max(".$cols.") as max FROM ".$table.$where.$order_string.$limit;
 
     $result = $this->query($sql)->get_results();

     return $result = $result[0]->max;
 
 
 
 
  }catch( Exception $e ) {
   $message = $e->getMessage();
 
   die( $message );
  }
 
 
}







/**
 * update
 * - update a certain row or rows in a table
 * @param table - 
 * @param Cols-
 * @param where -
 * @return Boolen true - process was successful
 * @return Boolen false - process wasn't successful
 * 
 * 
 */
public function update($table ="",$data = "",$where=""){
  try{
          //checking for WHERE CONDITION
        // if array is provided
        if(is_array($where)){
          $this->where($where);
          $where = $this->where_condition();
        }

        // if empty is provided
        if($where ==""){
          $where = $this->where_condition();
          if($where == NULL){
            throw new Exception("Parameter 3 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in update() to set the conditions", 1);
          }
        }

            // checking for table
          if($table == ""){
            $table = $this->get_table();

              // check if a table is still empty
              if($table == NULL){
                throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in update() to set the table", 1);
              }

            }else{
              // check if a table exists in the database first
              if(!$this->is_table($table)){
                throw new Exception("Invalid table name passed - (".$table.")", 1);
              }
          }
  
          // CHECKING THE DATA
          if(!is_array($data)){
            throw new Exception("Please Argument 2 Needs to be an Associative Array - Data to be UPDATED", 1);
          }

          $num_data = count($data);
          $data_counter = 1;
          $comma = ", ";
          $data_string = "";

          foreach($data as $col=>$val){
              $comma = ",";
              // check if col exist as a field in the table provided  
              if(!$this->field_in_table($col,$table)){
                throw new exception("<b>Invalid Field Provided!</b> '".trim($col)."'<i> is not a field in </i>'".$table."'."); 
              }
              $val = $this->escape($val);
              // if no error was found
              $val = (is_numeric($val))? $val : "'".$val."'";

              if($num_data == 1 or $num_data == $data_counter){
                $comma="";
              }

              $data_string .=$col." = ".$val.$comma;
              
              $data_counter++;
          }

          $sql = "UPDATE `".$table."` SET ".$data_string." WHERE ".$where.";";
          $this->sql($sql);
          if($this->run()){return true;}else{return false;}

  }catch( Exception $e ) {
      $message = $e->getMessage();

      die( $message );
    }

}




/**
 * delete
 * - delete records from the databse
 * @param table - 
 * @param where -
 * @return Boolen true - process was successful
 * @return Boolen false - process wasn't successful
 * 
 * 
 */
public function delete($table ="",$where=""){
  try{

        // checking for table
        if($table == ""){
          $table = $this->get_table();

            // check if a table is still empty
            if($table == NULL){
              throw new Exception("Parameter 1 has to be a table in the database and must not be empty<br> use set_table() to set the table OR<br> use the first paramter in delete() to set the table", 1);
            }

          }else{
            // check if a table exists in the database first
            if(!$this->is_table($table)){
              throw new Exception("Invalid table name passed - (".$table.")", 1);
            }
        }

        // if array is provided for the where
        if(is_array($where)){
          $this->where($where);
          $where = $this->where_condition();
        }
  
      // if empty for the where
      if($where ==""){
        $where = $this->where_condition();
        if($where == NULL){
          throw new Exception("Parameter 2 has to be a WHERE CONDITION and must not be empty<br> use where() to set the conditins OR<br> use the third paramter in delete() to set the conditions", 1);
        }
      }

      //checking for where 
      if($where == NULL){
        $where ="";
        }else{
          $where = " WHERE ".$where;
      }

      $sql = "DELETE FROM ".$table." ".$where;

      $this->sql($sql);
      if($this->run()){return true;}else{return false;}


  }catch( Exception $e ) {
      $message = $e->getMessage();

      die( $message );
    }
}




/**
 * where
 * - creates a where condition with AND
 *
 * @param array - associative array
 * @return Boolen
 * 
 * 
 */
public function where(){
  try{

    $string = "";
    $args = func_get_args();//getting arguments
    $numArgs = count($args);//counting argunments
    $status1_assoc=false;$status2=false;$status1_string=false;
    $comp_array = [">","<",">=","<="];

    //if no argument was provided
    if($numArgs == 0){
      throw new Exception("Arguments reqired", 1);
    }

    //if empty args provided
    foreach($args as $arg){
      if(empty($arg)){
        throw new Exception("Empty Arguments cannot be provided", 1);
        
      }
    }



    if($numArgs > 2){
      throw new Exception("Too Many Arguments Passed to the \"Where\" Function:<br>- <i>Atmost 2 arguments are expected<i/>", 1);
      
    }



    //if one argument is provided
    if($numArgs == 1){
      if(is_assoc($args[0])){
        $status1_assoc = true;
      }
      if(is_string($args[0])){
        $status1_string = true;
      }
    }



    //if two arguments are provided
    if($numArgs == 2){
      if(is_string($args[0])){
        if(is_assoc($args[1]) || is_object($args[1])){
          throw new Exception("Argument two canaot be an Associative Array or an Object", 1);
        }
        $status2 = true;

      }else{
        throw new Exception("Argument 1 must be a string, if 2 arguments are passed");
        
      }
    }



    ///Main Functionality for 1 arguments assoc
    if($status1_assoc){
      $conditions = $args[0];
      $num_cond = count($conditions);
      $count = 0;
      
      //looping through conditions

      foreach($conditions as $field=>$value){
        $divider = " AND ";
        $count ++;
        $com_for_con = false;
        $field = trim($field);

        //checking for <
        if(substr_count($field,"<") && !substr_count($field,"<=")){
          $com_for_con = "<";
        }

        //checking for >
        if(substr_count($field,">") && !substr_count($field,">=")){
          $com_for_con = ">";
        }

        //checking for >=
        if(substr_count($field,">=")){
          $com_for_con = ">=";
        }

        // checking for <>
       if(substr_count($field,"<>")){
         $com_for_con = "<>";
       }

        //checking for <=
        if(substr_count($field,"<=")){
          $com_for_con = "<=";
        }
        //checking for !=
        if(substr_count($field,"!=")){
          $com_for_con = "!=";
        }  
        // if not equal to false
        if(!$com_for_con === false){
          $r_field = substr($field,0,stripos($field,$com_for_con));
          $r_field = trim($r_field);

          if(!$this->is_field($r_field)){
            throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
            
          }
          $new_field = $r_field;
        }


        // if  equal to false
        if($com_for_con === false){
          $com_for_con = "=";
          $r_field = trim($field);

          if(!$this->is_field($r_field)){
            throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
            
          }
          $new_field = $r_field;
        }
        //when to show combinator
        if($num_cond == 1 or $count == $num_cond){
          $divider ="";
        }

        //checking if value is numeric
        if(is_numeric($value)){
          $value = $value;
        }

        //checking if value is bool
        if(is_bool($value)){
          $value = $value;
        }

        $value =$this->escape($value);

        //checking if value is string
        if(is_string($value)){
          $value = "'".$value."'";
        }



        $string .=$new_field." ".$com_for_con." ".$value.$divider;


      }
   


    }


    //Main functionality for 1 Argument Assoc
    if($status1_string){
      $string = $args[0];
    }

    
    //Main functionality for 2 Argument strings
    if($status2){
      $com_for_con = false;
      $field = trim($args[0]);
      $value = trim($args[1]);


      //checking for <
      if(substr_count($field,"<") && !substr_count($field,"<=")){
        $com_for_con = "<";
      }

      //checking for >
      if(substr_count($field,">") && !substr_count($field,">=")){
        $com_for_con = ">";
      }

      //checking for >=
      if(substr_count($field,">=")){
        $com_for_con = ">=";
      }

      // checking for <>
      if(substr_count($field,"<>")){
        $com_for_con = "<>";
      }
      
      //checking for <=
      if(substr_count($field,"<=")){
        $com_for_con = "<=";
      }
      //checking for !=
      if(substr_count($field,"!=")){
        $com_for_con = "!=";
      }
      
      // if not equal to false
      if(!$com_for_con === false){
        $r_field = substr($field,0,stripos($field,$com_for_con));
        $r_field = trim($r_field);

        if(!$this->is_field($r_field)){
          throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
          
        }
        $new_field = $r_field;
      }


      // if  equal to false
      if($com_for_con === false){
        $com_for_con = "=";
        $r_field = trim($field);

        if(!$this->is_field($r_field)){
          throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
          
        }
        $new_field = $r_field;
      }
     

      //checking if value is numeric
      if(is_numeric($value)){
        $value = $value;
      }

      //checking if value is bool
      if(is_bool($value)){
        $value = $value;
      }

      $value =$this->escape($value);

      //checking if value is string
      if(is_string($value) && !is_numeric($value)){
        $value = "'".$value."'";
      }
      
      $string .=$new_field." ".$com_for_con." ".$value;








    }


    $divider = " AND ";


            $where_conditions = $this->where_conditions;

            if($where_conditions == NULL){
              $where_conditions = $string;
            }else{
              $where_conditions .= $divider.$string;
            }



            $this->where_conditions = $where_conditions;


    

            return $string;



  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }

}



/**
 * or_where
 * - creates a where condition with OR
 *
 *
 * @param array - 
 * @return Boolen
 * 
 * 
 */
public function or_where(){
  try{

    $string = "";
    $args = func_get_args();//getting arguments
    $numArgs = count($args);//counting argunments
    $status1_assoc=false;$status2=false;$status1_string=false;
    $comp_array = [">","<",">=","<="];

    //if no argument was provided
    if($numArgs == 0){
      throw new Exception("Arguments reqired", 1);
    }

    //if empty args provided
    foreach($args as $arg){
      if(empty($arg)){
        throw new Exception("Empty Arguments cannot be provided", 1);
        
      }
    }



    if($numArgs > 2){
      throw new Exception("Too Many Arguments Passed to the \"Where\" Function:<br>- <i>Atmost 2 arguments are expected<i/>", 1);
      
    }



    //if one argument is provided
    if($numArgs == 1){
      if(is_assoc($args[0])){
        $status1_assoc = true;
      }
      if(is_string($args[0])){
        $status1_string = true;
      }
    }



    //if two arguments are provided
    if($numArgs == 2){
      if(is_string($args[0])){
        if(is_assoc($args[1]) || is_object($args[1])){
          throw new Exception("Argument two canaot be an Associative Array or an Object", 1);
        }
        $status2 = true;

      }else{
        throw new Exception("Argument 1 must be a string, if 2 arguments are passed");
        
      }
    }



    ///Main Functionality for 1 arguments assoc
    if($status1_assoc){
      $conditions = $args[0];
      $num_cond = count($conditions);
      $count = 0;
      
      //looping through conditions

      foreach($conditions as $field=>$value){
        $divider = " OR ";
        $count ++;
        $com_for_con = false;
        $field = trim($field);

        //checking for <
        if(substr_count($field,"<") && !substr_count($field,"<=")){
          $com_for_con = "<";
        }

        //checking for >
        if(substr_count($field,">") && !substr_count($field,">=")){
          $com_for_con = ">";
        }

        //checking for >=
        if(substr_count($field,">=")){
          $com_for_con = ">=";
        }

        // checking for <>
        if(substr_count($field,"<>")){
          $com_for_con = "<>";
        }

        //checking for <=
        if(substr_count($field,"<=")){
          $com_for_con = "<=";
        }
        //checking for !=
        if(substr_count($field,"!=")){
          $com_for_con = "!=";
        }  
        // if not equal to false
        if(!$com_for_con === false){
          $r_field = substr($field,0,stripos($field,$com_for_con));
          $r_field = trim($r_field);

          if(!$this->is_field($r_field)){
            throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
            
          }
          $new_field = $r_field;
        }


        // if  equal to false
        if($com_for_con === false){
          $com_for_con = "=";
          $r_field = trim($field);

          if(!$this->is_field($r_field)){
            throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
            
          }
          $new_field = $r_field;
        }
        //when to show combinator
        if($num_cond == 1 or $count == $num_cond){
          $divider ="";
        }

        //checking if value is numeric
        if(is_numeric($value)){
          $value = $value;
        }

        //checking if value is bool
        if(is_bool($value)){
          $value = $value;
        }

        $value =$this->escape($value);

        //checking if value is string
        if(is_string($value)){
          $value = "'".$value."'";
        }



        $string .=$new_field." ".$com_for_con." ".$value.$divider;


      }
   


    }


    //Main functionality for 1 Argument Assoc
    if($status1_string){
      $string = $args[0];
    }

    
    //Main functionality for 2 Argument strings
    if($status2){
      $com_for_con = false;
      $field = trim($args[0]);
      $value = trim($args[1]);


      //checking for <
      if(substr_count($field,"<") && !substr_count($field,"<=")){
        $com_for_con = "<";
      }

      //checking for >
      if(substr_count($field,">") && !substr_count($field,">=")){
        $com_for_con = ">";
      }

      //checking for >=
      if(substr_count($field,">=")){
        $com_for_con = ">=";
      }

      // checking for <>
      if(substr_count($field,"<>")){
        $com_for_con = "<>";
      }

      //checking for <=
      if(substr_count($field,"<=")){
        $com_for_con = "<=";
      }
      //checking for !=
      if(substr_count($field,"!=")){
        $com_for_con = "!=";
      }
      
      // if not equal to false
      if(!$com_for_con === false){
        $r_field = substr($field,0,stripos($field,$com_for_con));
        $r_field = trim($r_field);

        if(!$this->is_field($r_field)){
          throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
          
        }
        $new_field = $r_field;
      }


      // if  equal to false
      if($com_for_con === false){
        $com_for_con = "=";
        $r_field = trim($field);

        if(!$this->is_field($r_field)){
          throw new Exception("\"".$r_field."\" - is Not a field on any table in the database", 1);
          
        }
        $new_field = $r_field;
      }
     

      //checking if value is numeric
      if(is_numeric($value)){
        $value = $value;
      }

      //checking if value is bool
      if(is_bool($value)){
        $value = $value;
      }

      $value =$this->escape($value);
      //checking if value is string
      if(is_string($value) && !is_numeric($value)){
        $value = "'".$value."'";
      }
      
      $string .=$new_field." ".$com_for_con." ".$value;








    }


    $divider = " OR ";

            $where_conditions = $this->where_conditions;

            if($where_conditions == NULL){
              $where_conditions = $string;
            }else{
              $where_conditions .= $divider.$string;
            }



            $this->where_conditions = $where_conditions;


    
            return $string;




  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }

}




/**
 * where_in
 * - creates a where in  condition with AND
 * @param 1 string - the field/column
 * @param 2 array: an array of values
 *
 * @param array - 
 * @return Boolen
 * 
 * 
 */
public function where_in($field,$values){

  try{
    $divider =" AND ";

    $field = trim($field);

    if(!is_string($field)){
      throw new Exception("Argument 1 must be a string (A valid field in a the database)", 1);
      
    }

    //check if field is a string
    if(!$this->is_field($field)){
      throw new Exception($field." - is not a field on any table in the database", 1);
    }


    //check if array is provided for second argument
    if(!is_array($values)){
      throw new Exception("Argument 2 must be an array", 1);
      
    }

    // generating values
    $values_count = count($values);//counting the values provided
    $counter =0;// creating a counter
    $values_grouped = "("; //starting the grouping 

    //looping through each value
    foreach($values as $value){
      $counter++;

      if($values_count == 1 || $counter == $values_count){
        $comma = "";
      }else{
        $comma =",";
      }
      $value =$this->escape($value);
      $values_grouped .="'".$value."'".$comma;

    }
    $values_grouped.=")";// ending the grouping

    $string = $field." IN ".$values_grouped;

    $divider = " AND ";

            $where_conditions = $this->where_conditions;

            if($where_conditions == NULL){
              $where_conditions = $string;
            }else{
              $where_conditions .= $divider.$string;
            }



            $this->where_conditions = $where_conditions;


            return $string;







  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}





/**
 * where_not_in
 * - creates a where not in  condition with AND
  * @param 1 string - the field/column
 * @param 2 array: an array of values
 *
 * @param array - 
 * @return Boolen
 * 
 * 
 */
public function where_not_in($field,$values){

  try{
    $divider =" AND ";

    $field = trim($field);

    if(!is_string($field)){
      throw new Exception("Argument 1 must be a string (A valid field in a the database)", 1);
      
    }

    //check if field is a string
    if(!$this->is_field($field)){
      throw new Exception($field." - is not a field on any table in the database", 1);
    }


    //check if array is provided for second argument
    if(!is_array($values)){
      throw new Exception("Argument 2 must be an array", 1);
      
    }

    // generating values
    $values_count = count($values);//counting the values provided
    $counter =0;// creating a counter
    $values_grouped = "("; //starting the grouping 

    //looping through each value
    foreach($values as $value){
      $counter++;

      if($values_count == 1 || $counter == $values_count){
        $comma = "";
      }else{
        $comma =",";
      }
      $value =$this->escape($value);

      $values_grouped .="'".$value."'".$comma;

    }
    $values_grouped.=")";// ending the grouping

    $string = $field." NOT IN ".$values_grouped;

    $divider = " AND ";

            $where_conditions = $this->where_conditions;

            if($where_conditions == NULL){
              $where_conditions = $string;
            }else{
              $where_conditions .= $divider.$string;
            }



            $this->where_conditions = $where_conditions;


            return $string;
  







  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}




/**
 * or_where_in
 * - creates a where in condition with OR
 * @param 1 string - the field/column
 * @param 2 array: an array of values
 *
 * @param array - 
 * @return Boolen
 * 
 * 
 */
public function or_where_in($field,$values){

  try{
    $divider =" OR ";

    $field = trim($field);

    if(!is_string($field)){
      throw new Exception("Argument 1 must be a string (A valid field in a the database)", 1);
      
    }

    //check if field is a string
    if(!$this->is_field($field)){
      throw new Exception($field." - is not a field on any table in the database", 1);
    }


    //check if array is provided for second argument
    if(!is_array($values)){
      throw new Exception("Argument 2 must be an array", 1);
      
    }

    // generating values
    $values_count = count($values);//counting the values provided
    $counter =0;// creating a counter
    $values_grouped = "("; //starting the grouping 

    //looping through each value
    foreach($values as $value){
      $counter++;

      if($values_count == 1 || $counter == $values_count){
        $comma = "";
      }else{
        $comma =",";
      }
      $value =$this->escape($value);
      $values_grouped .="'".$value."'".$comma;

    }
    $values_grouped.=")";// ending the grouping

    $string = $field." IN ".$values_grouped;

    $divider = " OR ";

            $where_conditions = $this->where_conditions;

            if($where_conditions == NULL){
              $where_conditions = $string;
            }else{
              $where_conditions .= $divider.$string;
            }



            $this->where_conditions = $where_conditions;


  


            return $string;




  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}





/**
 * or_where_not_in
 * - creates a where not in  condition with AND
 * * @param 1 string - the field/column
 * @param 2 array: an array of values
 * @param array - 
 * @return Boolen
 * 
 * 
 */
public function or_where_not_in($field,$values){

  try{
    $divider =" OR ";

    $field = trim($field);

    if(!is_string($field)){
      throw new Exception("Argument 1 must be a string (A valid field in a the database)", 1);
      
    }

    //check if field is a string
    if(!$this->is_field($field)){
      throw new Exception($field." - is not a field on any table in the database", 1);
    }


    //check if array is provided for second argument
    if(!is_array($values)){
      throw new Exception("Argument 2 must be an array", 1);
      
    }

    // generating values
    $values_count = count($values);//counting the values provided
    $counter =0;// creating a counter
    $values_grouped = "("; //starting the grouping 

    //looping through each value
    foreach($values as $value){
      $counter++;

      if($values_count == 1 || $counter == $values_count){
        $comma = "";
      }else{
        $comma =",";
      }
      $value =$this->escape($value);

      $values_grouped .="'".$value."'".$comma;

    }
    $values_grouped.=")";// ending the grouping

    $string = $field." NOT IN ".$values_grouped;

    $divider = " OR ";

            $where_conditions = $this->where_conditions;

            if($where_conditions == NULL){
              $where_conditions = $string;
            }else{
              $where_conditions .= $divider.$string;
            }



            $this->where_conditions = $where_conditions;


            return $string;







  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}








/**
 * where_condition
 * - Display Where Conditions generated
 * insert_arr if data is array
 * @param array - 
 * @return Boolen
 * 
 * 
 */
public function where_condition(){
  return $this->where_conditions;
}



/**
 * set_table
 * -Sets table to be worked with
 * 
 * @param string - 
 * @return true
 * 
 * 
 */
public function set_table($table){

  try{

    // if nothing is passed;
    if($table ==""){
      throw new Exception("Provide a name of any table in the Database", 1);
      
    }

    // check if a table exists in the database first
    if(!$this->is_table($table)){
      throw new Exception("Invalid table name passed - (".$table.")", 1);
     }

     $this->table = $table;
     return true;



  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }

}



/**
 * get_table
 * -get table that was set
 * 
 * @param  - 
 * @return string
 * 
 * 
 */
public function get_table(){
  $table = $this->table;
  return $table;
}

/**
 * set_cols
 * -sets the cols to be generated
 * 
 * @param string - 
 * @return string
 * 
 * 
 */
public function set_cols($cols="*"){
  try{
    // if array provided
    if(is_array($cols)){
      $cols = implode(",",$cols);
    }


    $cols = trim($cols);
    $generated_cols = "";
    $check = substr_count($cols,",",0);

    // if one parameter is passed
    if($check == 0){
      // check if its the universal selector
      if($cols == "*"){
        $generated_cols = "*";
      }else{
        $table = $this->get_table();

        // if theres no table set
        if(is_null($table)){

          if(!$this->is_field($cols)){
            throw new Exception("' ".$cols." ' is not a field in any table the database", 1);
          }

        }else{

          if(!$this->field_in_table($cols,$table)){
            throw new Exception("' ".$cols." ' is not a field in ' ".$table." '", 1); 
          }

        }
        $generated_cols = $cols;
      }

    }


    // if two or more cols are passed

    if($check > 0){
      $cols = explode(",",$cols);
      $cols_num = count($cols);
      $divider = ",";
      $counter = 0;

      foreach($cols as $col){
        $col = trim($col);
        $counter ++;

        $table = $this->get_table();

          // if theres no table set
          if(is_null($table)){

            if(!$this->is_field($col)){
              throw new Exception("' ".$col." ' is not a field in any table the database", 1);
            }

          }else{

            if(!$this->field_in_table($col,$table)){
              throw new Exception("' ".$col." ' is not a field in ' ".$table." '", 1); 
            }

          }
          if($counter == $cols_num){
            $divider = "";
          }
          $generated_cols .=$col.$divider;
      }

    }



    $this->cols = $generated_cols;
    return true;



  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}


/**
 * get_cols
 * -gets the cols specified
 * 
 * @param  - 
 * @return string
 * 
 * 
 */
public function get_cols(){
  return $this->cols;
}


/**
 * clear_sql_values()
 * - this clears all the values holders like table,cols, where 
 */

public function clear_sql_values(){
  $this->where_conditions=NULL;
  $this->table =NULL;
  $this->cols = NULL;
  return true;
}



/**
 * escape()
 * - escapes a string / prepares it for an sql query
 * @param string 
 * @return escaped string
 */
public function escape($value){

  $conn = $this->conn;

  $value = trim($value);
  
  $value = htmlentities($value);

  $value = mysqli_real_escape_string($conn,$value);
 
  return $value;

}


/**
 * 
 * like()
 * -Adds a LIKE clause to a query, separating multiple calls with AND.
 * @param
 *      $field (string) – Field name
 *      $match (string) – Text portion to match
 *      $side (string) – Which side of the expression to put the ‘%’ wildcard on
 *      $escape (bool) – Whether to escape values and identifiers
 */
public function like($field,$match="",$side="both"){

  try{
      $divider = " AND ";
      $main = " LIKE ";
      $side = strtoupper($side);
      $string ="";


      // if string is provided
      if(is_string($field) && $match !=""){

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';
          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string = $field.$main.$match; 
      }

      // if array provided
      if(is_assoc($field)){

        $fields = $field;
        $count = count($fields);
        $i = 1;
        foreach($fields as $field=>$match){
          
          //CHECKING FOR DIVIDER
          if($i == 1){
            $divider ="";
            }else{
              $divider = " AND ";
          }

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';

          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string .= $divider.$field.$main.$match; 

          $i++; // increse couter

        }


      }



      // performing main action
      $where_conditions = $this->where_conditions;

      if($where_conditions == NULL){
        $where_conditions = $string;
      }else{
        $where_conditions .= $divider.$string;
      }



      $this->where_conditions = $where_conditions;
      
      return $string;
     



  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}


/**
 * 
 * not_like()
 * -Adds NOT LIKE clause to a query, separating multiple calls with AND.
 * @param
 *      $field (string) – Field name
 *      $match (string) – Text portion to match
 *      $side (string) – Which side of the expression to put the ‘%’ wildcard on
 *      $escape (bool) – Whether to escape values and identifiers
 */
public function not_like($field,$match="",$side="both"){

  try{
      $divider = " AND ";
      $main = " NOT LIKE ";
      $side = strtoupper($side);
      $string ="";


      // if string is provided
      if(is_string($field) && $match !=""){

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';
          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string = $field.$main.$match; 
      }

      // if array provided
      if(is_assoc($field)){

        $fields = $field;
        $count = count($fields);
        $i = 1;
        foreach($fields as $field=>$match){
          
          //CHECKING FOR DIVIDER
          if($i == 1){
            $divider ="";
            }else{
              $divider = " AND ";
          }

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';

          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string .= $divider.$field.$main.$match; 

          $i++; // increse couter

        }


      }



      // performing main action
      $where_conditions = $this->where_conditions;

      if($where_conditions == NULL){
        $where_conditions = $string;
      }else{
        $where_conditions .= $divider.$string;
      }



      $this->where_conditions = $where_conditions;
      
      return $string;
     



  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}


/**
 * 
 * or_like()
 * -Adds a LIKE clause to a query, separating multiple calls with OR.
 * @param
 *      $field (string) – Field name
 *      $match (string) – Text portion to match
 *      $side (string) – Which side of the expression to put the ‘%’ wildcard on
 *      $escape (bool) – Whether to escape values and identifiers
 */
public function or_like($field,$match="",$side="both"){

  try{
      $divider = " OR ";
      $main = " LIKE ";
      $side = strtoupper($side);
      $string ="";


      // if string is provided
      if(is_string($field) && $match !=""){

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';
          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string = $field.$main.$match; 
      }

      // if array provided
      if(is_assoc($field)){

        $fields = $field;
        $count = count($fields);
        $i = 1;
        foreach($fields as $field=>$match){
          
          //CHECKING FOR DIVIDER
          if($i == 1){
            $divider ="";
            }else{
              $divider = " OR ";
          }

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';

          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string .= $divider.$field.$main.$match; 

          $i++; // increse couter

        }


      }



      // performing main action
      $where_conditions = $this->where_conditions;

      if($where_conditions == NULL){
        $where_conditions = $string;
      }else{
        $where_conditions .= $divider.$string;
      }



      $this->where_conditions = $where_conditions;
      
      return $string;
     



  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}


/**
 * 
 * or_not_like()
 * -Adds a NOT LIKE clause to a query, separating multiple calls with OR.
 * @param
 *      $field (string) – Field name
 *      $match (string) – Text portion to match
 *      $side (string) – Which side of the expression to put the ‘%’ wildcard on
 *      $escape (bool) – Whether to escape values and identifiers
 */
public function or_not_like($field,$match="",$side="both"){

  try{
      $divider = " OR ";
      $main = " NOT LIKE ";
      $side = strtoupper($side);
      $string ="";


      // if string is provided
      if(is_string($field) && $match !=""){

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';
          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string = $field.$main.$match; 
      }

      // if array provided
      if(is_assoc($field)){

        $fields = $field;
        $count = count($fields);
        $i = 1;
        foreach($fields as $field=>$match){
          
          //CHECKING FOR DIVIDER
          if($i == 1){
            $divider ="";
            }else{
              $divider = " OR ";
          }

          //Check field
          if(!$this->is_field($field)){
            throw new Exception("<b>Inavlid Field($field) Provided</b>", 1);
          }

          $field = '`'.$field.'`';

          switch ($side) {
            case 'BOTH':
              $match = "'%".$match."%' ESCAPE '!'";
              break;

            case 'BEFORE':
              $match = "'%".$match."' ESCAPE '!'";
              break;

            case 'AFTER':
                $match = "'".$match."%' ESCAPE '!'";
                break;

            case 'NONE':
              $match = "'".$match."' ESCAPE '!'";
              break;
              
            
            default:
                $match = "'%".$match."%'";
              break;
          }
          
          $string .= $divider.$field.$main.$match; 

          $i++; // increse couter

        }


      }



      // performing main action
      $where_conditions = $this->where_conditions;

      if($where_conditions == NULL){
        $where_conditions = $string;
      }else{
        $where_conditions .= $divider.$string;
      }



      $this->where_conditions = $where_conditions;
      
      return $string;
     



  }catch( Exception $e ) {
      $message = $e->getMessage();
    
      die( $message );
    }


}



/**
 * and()
 *  - groups each arguement in a bracket and adds AND inbetween each one of them
 */
public function and(){
  try{

    $string = "";
    $args = func_get_args();//getting arguments
    $string = "";
    $count = count($args);
    $i = 1;
    if($count == 0){
      throw new Exception("<b>Invalid Arguments</b>0 Arguments passed", 1);
      
    }
    
    foreach($args as $arg){
      // divider check
      if($i == 1){
        $divider = "";
      }else{
        $divider = " AND ";
      }
      $string.=$divider.'('.$arg.')';

      $i++;
    }

    return $string;

  }catch( Exception $e ) {
    $message = $e->getMessage();
  
    die( $message );
  }
}


/**
* or()
*  - groups each arguement in a bracket and adds OR inbetween each one of them
*/
public function or(){
  try{

    $string = "";
    $args = func_get_args();//getting arguments
    $string = "";
    $count = count($args);
    $i = 1;
    if($count == 0){
      throw new Exception("<b>Invalid Arguments</b>0 Arguments passed", 1);
      
    }
    
    foreach($args as $arg){
      // divider check
      if($i == 1){
        $divider = "";
      }else{
        $divider = " OR ";
      }
      $string.=$divider.'('.$arg.')';

      $i++;
    }

    return $string;

  }catch( Exception $e ) {
    $message = $e->getMessage();
  
    die( $message );
  }
}







}
// closing part of the database
//$db = new Database($servername, $username, $password, $dbname);
















