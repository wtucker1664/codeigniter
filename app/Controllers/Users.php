<?php namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\CLI\CLI;

class Users extends Controller {

        private $salt = 'asalt';

        private function hash_value($value)
        {
            return crypt(hash('sha256', $value),$this->salt);
        }

        public function fetchusers()
        {
            // Call helper functions
            helper('filesystem');
            /*
                Get API data
            */
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', 'https://tst-epos.feeditback.com/test/', [
                'auth' => ['dev_test_user', 'V8(Zp7K9Ab94uRgmmx2gyuT.']
            ]);
            // check if request is 200 success
            if($res->getStatusCode() == 200){
                    
                $data =  $res->getBody();
                $paths = new \Config\Paths();

                $path = $paths->writableDirectory;

                // Decode json object
                $enData = json_decode($data);

                // Itterate over object and apply security.
                if(is_array($enData)){
                    for($i=0;$i<sizeof($enData);$i++){
                        $enData[$i]->email = $this->hash_value($enData[$i]->email); 
                        unset($enData[$i]->latitude);
                        unset($enData[$i]->longitude);
                        // Astrisk address.
                        $address = explode(' ',$enData[$i]->address);
                        for($n=0;$n<sizeof($address);$n++){
                            if(!is_numeric($address[$n])){
                                if(strlen($address[$n]) > 2){
                                    $len = strlen($address[$n]);
                                    $address[$n] = substr($address[$n], 0, 2).str_repeat('*', $len - 2);
                                }
                                
                            }
                        }

                        $enData[$i]->address = implode(',',$address);
                            
                    }
                }else{
                    $enData->email = $this->hash_value($enData->email); 
                    unset($enData->latitude);
                    unset($enData->longitude);
                    // Astrisk address.
                    $address = explode(' ',$enData->address);
                    for($n=0;$n<sizeof($address);$n++){
                        if(!is_numeric($address[$n])){
                            if(strlen($address[$n]) > 2){
                                $len = strlen($address[$n]);
                                $address[$n] = substr($address[$n], 0, 2).str_repeat('*', $len - 2);
                            }
                            
                        }
                    }

                    $enData->address = implode(',',$address);
                }
                

                
                // Write users.json file 
                if ( ! write_file($path."/json/users.json", json_encode($enData)))
                {
                       
                        CLI::write( 'Unable to write the file', 'red');
                }
                else
                {
                        
                        CLI::write( 'File written!', 'green');
                }
            }else{

            }
        }

        private function getQuery($field, $value, $exact = "true"){
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
                        if($field == "email"){
                            if($decode[$i]->{$field} == $this->hash_value($value)){
                            
                                CLI::write( $decode[$i]->first_name." ".$decode[$i]->last_name, 'green');
                            }else{
                                CLI::write( 'No match found', 'red');
                            }
                        }else if($decode[$i]->{$field} == $value){
                            
                            CLI::write( $decode[$i]->first_name." ".$decode[$i]->last_name, 'green');
                        }else{
                            CLI::write( 'No match found', 'red');
                        }
                    }else if($exact == "false"){
                        echo "searching";
                        if(preg_match('/('.$value.')/', $decode[$i]->{$field})){
                            
                            CLI::write( $decode[$i]->first_name." ".$decode[$i]->last_name, 'green');
                        }else{
                            CLI::write( 'No match found', 'red');
                        }
                    }
                    
                }
            }else{
                CLI::write( 'Unable to read the file', 'red');
            }
        }

        public function query($field = "", $value = "", $exact = ""){
            if($exact != ""){
                $this->getQuery($field,$value,$exact);
            }else{
                $this->getQuery($field,$value); 
            }
        }

        public function report(){
            // Call helper functions
            helper('filesystem');
            $paths = new \Config\Paths();
            // set path to writable directory
            $path = $paths->writableDirectory;
            // get contents of users.json file.
            $file = file_get_contents($path."/json/users.json");
            // decode json string to array/object
            $decode = json_decode($file);
            // get number of records
            $numRecords = sizeof($decode);
            // Reports array
            $report = [];
            usort($decode, function($a, $b) {
                return strcmp($a->created, $b->created);
            });

            $favColor = [];
            // Get report data
            if(is_array($decode)){
                for($i=0;$i<$numRecords;$i++){
                    if(($i+1) == $numRecords){
                        $report['full_names'] .= " Record ".$numRecords." ".$decode[$i]->first_name." ".$decode[$i]->last_name;
                        $report['average_age'] += $decode[$i]->age;
                        $report['word_count'] += str_word_count($decode[$i]->about); 
                        if(array_key_exists($decode[$i]->favorite_colour,$favColor)){
                            $favColor[$decode[$i]->favorite_colour]++;
                        }else{
                            $favColor[$decode[$i]->favorite_colour] = 1;
                        }

                    }else if($i == 0){
                        $report['full_names'] = "Record 1 ".$decode[$i]->first_name." ".$decode[$i]->last_name;
                        $report['average_age'] = $decode[$i]->age;
                        $report['word_count'] = str_word_count($decode[$i]->about); 
                        $favColor[$decode[$i]->favorite_colour] = 1;
                    }else{
                        $report['average_age'] += $decode[$i]->age;
                        $report['word_count'] += str_word_count($decode[$i]->about); 
                        if(array_key_exists($decode[$i]->favorite_colour,$favColor)){
                            $favColor[$decode[$i]->favorite_colour]++;
                        }else{
                            $favColor[$decode[$i]->favorite_colour]= 1;
                        }
                        
                    }
                    
                }
            }
            $report['average_age'] = number_format(($report['average_age']/$numRecords),2,'.',',');

            

            
            arsort($favColor);
            $keys = array_keys($favColor);
            $report['favorite_colour'] = $keys[0];

            // Write users.json file 
            if ( ! write_file($path."/json/users-report.json", json_encode($report)))
            {
                   
                    CLI::write( 'Unable to write the file', 'red');
            }
            else
            {
                    
                    CLI::write( 'File written!', 'green');
            }

        }
}