<?php
$config=array(
    /** database configuration (uses Pear DB) **/
    /* db_type - can be any of the following:
       mysql  -> MySQL
       pgsql  -> PostgreSQL
       ibase  -> InterBase
       msql   -> Mini SQL
       mssql  -> Microsoft SQL Server
       oci8   -> Oracle 7/8/8i
       odbc   -> ODBC (Open Database Connectivity)
       sybase -> SyBase
       ifx    -> Informix
       fbsql  -> FrontBase
    */
    "db_type" =>          "mysql",
    /* db_host - database hostname */
    "db_host" =>          "localhost",
    /* db_name - name of the database */
    "db_name" =>          "svg",
    /* db_user - username with SELECT, INSERT, UPDATE, DELETE privileges */
    "db_user" =>          "svg",
    /* db_password - password for the above user */
    "db_password" =>      "svg",
	  /* lib_dir - includes directory (added to include path with ini_set) */
	  "lib_dir"=>           "d:\\Emma Kay\\soa\\generator\\includes",
    /* svg_store_dir */
    "svg_store_dir"=>     "d:\\Emma Kay\\soa\\generator\\SVG\\",
    /* imports_store_dir */
    "imports_store_dir"=> "d:\\Emma Kay\\soa\\generator\\imports\\",
	  /* error log - enable/disable logs of system errors */
	  "error_log"=>         "",
	  /* sql_log - filename for log of all database calls */
	  "sql_log"=>           "",
	  /* sql_error_log - filename for log of all database errors */
	  "sql_error_log"=>     "",
    "default_width"=>     350,
    "default_lines"=>     1
);
// set the include path to 
ini_set("include_path",ini_get("include_path") . ";" . $config["lib_dir"]);
?>