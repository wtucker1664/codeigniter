<?php namespace App\Controllers;

use CodeIgniter\Controller;


class Users extends Controller {

        

        

        public function fetchusers()
        {
            // Call helper functions
            helper('filesystem');
            helper('users');
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
                        $enData[$i]->email = hash_value($enData[$i]->email); 
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
                    $enData->email = hash_value($enData->email); 
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

        public function query($field = "", $value = "", $exact = ""){
            helper('users');
            if($exact != ""){
                getQuery($field,$value,$exact);
            }else{
                getQuery($field,$value); 
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
                        // As array is already sorted we can get set the last record
                        $report['full_names'] .= " Record ".$numRecords." ".$decode[$i]->first_name." ".$decode[$i]->last_name;
                        $report['average_age'] += $decode[$i]->age;
                        $report['word_count'] += str_word_count($decode[$i]->about); 
                        // Check if colour exists and increment or create a new colour
                        if(array_key_exists($decode[$i]->favorite_colour,$favColor)){
                            $favColor[$decode[$i]->favorite_colour]++;
                        }else{
                            $favColor[$decode[$i]->favorite_colour] = 1;
                        }

                    }else if($i == 0){
                        // As array is already sorted we can get set the first record
                        $report['full_names'] = "Record 1 ".$decode[$i]->first_name." ".$decode[$i]->last_name;
                        $report['average_age'] = $decode[$i]->age;
                        $report['word_count'] = str_word_count($decode[$i]->about); 
                        // As this is the first record set the first colour to 1;
                        $favColor[$decode[$i]->favorite_colour] = 1;
                    }else{
                        $report['average_age'] += $decode[$i]->age;
                        $report['word_count'] += str_word_count($decode[$i]->about); 
                        // Check if colour exists and increment or create a new colour
                        if(array_key_exists($decode[$i]->favorite_colour,$favColor)){
                            $favColor[$decode[$i]->favorite_colour]++;
                        }else{
                            $favColor[$decode[$i]->favorite_colour]= 1;
                        }
                        
                    }
                    
                }
            }
            // Get the average age as 2 decimal number. 
            $report['average_age'] = number_format(($report['average_age']/$numRecords),2,'.',',');
            // Sort favorite colour so the top most colour is the first key.
            arsort($favColor);
            // Get the colours as keys 
            $keys = array_keys($favColor);
            // as array is now sorted use the first key as the favoriate colour
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