<?php @session_start();
class dbase{
    public $servername = "localhost";
    public $username = "bogotas_drup1";
    public $password = "E&mR9T4,&BSc";
    public $dbname = "bogotas_bookings";
    public $connection = "";
    function __construct() {
        $this->connection = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $conn->connect_error);
          }
    }
    function customQuery($str){
        $resultado = $this->connection->query($str);
        return $resultado;
        
    }
    function insert($table,$columns,$values){
        for($i = 0;$i<count($columns);$i++)
        {
            //$columns[$i] = "'".$columns[$i]."'";
            $values[$i] = "'".$values[$i]."'";
        }
        $finalArray = $this->audit(array($columns,$values));
        $cstr = implode(", ", $finalArray[0]);
        $vstr = implode(", ", $finalArray[1]);


         $sql = "INSERT INTO ".$table." (".$cstr.") VALUES (".$vstr.")";
         //echo $sql;
        //echo "<br>";


        if ($this->connection->query($sql) === TRUE) {
            $result = "success";
          } else {
            $result = "Error: " . $sql . "<br>" . $this->connection->error;
          }
          //print_r($this->connection);
          //$this->connection->close();
          //echo "id insertado ".$this->connection->insert_id;
          return $this->connection->insert_id;
    }

    function audit($data){
        $aut = $_SESSION['loggeduser']['nam_use'];
        
        if(isset($_SESSION['author']) && $_SESSION['author']!=""){ $aut = $_SESSION['author']; }
        array_push($data[0],"fec_crea");
        array_push($data[0],"fec_modif");
        array_push($data[0],"usu_acce");
        array_push($data[0],"reg_eli");
        array_push($data[1],"'".date("Y-m-d H:i:s")."'");
        array_push($data[1],"'".date("Y-m-d H:i:s")."'");
        array_push($data[1],"'0'");
        array_push($data[1],"'0'");

        return $data;
    }
}


?>