<?php if ( ! defined('FCPATH')) exit('No direct script access allowed');

if(!function_exists("hash_value")){
    
    function hash_value($value)
        {
            $salt = 'asalt';
            return crypt(hash('sha256', $value),$salt);
        }
}
use CodeIgniter\CLI\CLI;
if(!function_exists('getQuery')){
    
    function getQuery($field, $value, $exact = "true"){
        $paths = new \Config\Paths();
        // set path to writable directory
        $path = $paths->writableDirectory;
        // get contents of users.json file.
        $file = file_get_contents($path."/json/users.json");
        // decode json string to array/object
        $decode = json_decode($file);
        if(is_array($decode)){
            for($i=0;$i<sizeof($decode);$i++){
                if($exact == "true"){
                    // If field is email chack against hash_value for a match.
                    if($field == "email"){
                        if($decode[$i]->{$field} == $this->hash_value($value)){
                        
                            CLI::write( $decode[$i]->first_name." ".$decode[$i]->last_name, 'green');
                        }else{
                            CLI::write( 'No match found', 'yellow');
                        }
                    }else if($decode[$i]->{$field} == $value){
                        
                        CLI::write( $decode[$i]->first_name." ".$decode[$i]->last_name, 'green');
                    }else{
                        CLI::write( 'No match found', 'yellow');
                    }
                }else if($exact == "false"){
                    if(preg_match('/('.$value.')/', $decode[$i]->{$field})){
                        
                        CLI::write( $decode[$i]->first_name." ".$decode[$i]->last_name, 'green');
                    }else{
                        CLI::write( 'No match found', 'yellow');
                    }
                }
                
            }
        }else{
            CLI::write( 'Unable to read the file', 'red');
        }
    }
}