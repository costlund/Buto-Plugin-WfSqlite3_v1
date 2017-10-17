<?php
/**
SQLite3.
#code-php#
wfPlugin::includeonce('wf/sqlite3_v1');
$sqlite = new PluginWfSqlite3_v1();
$sqlite->filename = '/pat/to/db/database.db';
$sqlite->open();
$sqlite->exec("insert into customer (id , name) values (1, 'John')");
$rs = $sqlite->query("select * from customer;");
echo '<pre>';
print_r(wfHelp::getYmlDump($rs));
echo '</pre>';
#code#
 */
class PluginWfSqlite3_v1{
  public $filename = null;
  public $db = null;
  /**
   * Open db.
   */
  public function open(){
    if(!$this->filename){
      throw new Exception("PluginWfSqlite3_v1 says filname is not set.");
    }
    $this->db = new SQLite3(wfSettings::replaceDir($this->filename));
  }
  /**
   * Execute db.
   * @param string $value
   */
  public function exec($value){
    if(!$this->db){
      throw new Exception("PluginWfSqlite3_v1 says db is not set.");
    }
    $this->db->exec($value);
  }
  /**
   * Query db.
   * @param string $value
   * @return array
   */
  public function query($value, $one = false){
    if(!$this->db){
      throw new Exception("PluginWfSqlite3_v1 says db is not set.");
    }
    $result = $this->db->query($value);
    $row = array(); 
    if(!$one){
      $i = 0;
      while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
         $row[$i] = $res;
         $i++; 
      }
      return $row;
    }else{
      $row = array();
      while($res = $result->fetchArray(SQLITE3_ASSOC)){ 
         $row = $res;
         break;
      }
      return $row;
    }
  }
  /**
   * Test plugin by using a widget.
  #code-yml#
  data:
    filename: '[app_dir]/_data/test_sqlite3_v1.db'
    tasks:
      -
        type: exec
        value: "DROP TABLE IF EXISTS customer;"
      -
        type: exec
        value: "CREATE TABLE IF NOT EXISTS customer (id int(11) NOT NULL,name varchar(255),created_at timestamp NULL default CURRENT_TIMESTAMP)"
      -
        type: exec
        value: "delete from customer"
      -
        type: exec
        value: "insert into customer (id , name) values (1, 'John')"
      -
        type: exec
        value: "insert into customer (id , name) values (2, 'Melinda')"
      -
        type: query
        value: "select * from customer where 1=1;"
  #code#
   * @param PluginWfArray $data
   */
  public static function widget_test($data){
    /**
     * Handle data.
     */
    wfPlugin::includeonce('wf/array');
    $data = new PluginWfArray($data);
    /**
     * Open db.
     */
    $sqlite = new PluginWfSqlite3_v1();
    $sqlite->filename = $data->get('data/filename');
    $sqlite->open();
    foreach ($data->get('data/tasks') as $key => $value){
      $item = new PluginWfArray($value);
      if($item->get('type') == 'exec'){
        /**
         * Execute.
         */
        $sqlite->exec($item->get('value'));
      }else if($item->get('type') == 'query'){
        /**
         * Query.
         */
        $rs = $sqlite->query($item->get('value'));
        echo '<pre>';
        print_r(wfHelp::getYmlDump($rs));
        echo '</pre>';
      }
    }
  }
}