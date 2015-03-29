#!/usr/bin/env php
<?php

// 
// Copyright 2015 ProofID
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
// Author: Paul Heaney
//

require ('config.inc.php');

$dir = '/data/pingfederate-7.3.0/pingfederate/log';

$files = scandir($dir);

$db = new PDO($db_connection_string, $db_user, $db_password);
$stmt = $db->prepare("INSERT INTO {$db_audittable} (dtime,event,username,ip,app,host,protocol,role,partnerid,status,adapterid,description,responsetime) VALUES (:dtime,:event,:username,:ip,:app,:host,:protocol,:role,:partnerid,:status,:adapterid,:description,:responsetime)");

foreach ($files AS $logfile)
{
    if (strpos($logfile, 'audit.log') === 0)
    {
        // Audit log file
        echo $logfile."\n";
        $fh = fopen($dir . '/' . $logfile, 'r');
        if ($fh)
        {
            while (($line = fgets($fh)) != false)
            {
                $components = explode("|", $line);
                
                if (sizeof($components) < 13)
                {
                    while (true)
                    {
                        $next = fgets($fh);
                        if (preg_match("/^\d{4}-\d{2}-\d{2}/", $next))
                        {
                            $line = $next;
                        }
                        else
                        {
                            $line = $line . $next;
                        }
                        
                        $components = explode("|", $line);
                        if (sizeof($components) == 13) break;
                        if (sizeof($components) > 13)
                        {
                            echo "Error on joining lines, got \n";
                            echo "\t{$line}\n";
                            echo "But its too long, size is ".sizeof($components)."\n";
                        }
                    }
                    
                }
                
                if (sizeof($components) > 13)
                {
                    echo "Unable to process the following line as it doesn't have a valid structure\n";
                    echo "\t{$line}\n";
                }
                else
                {
                    if (!$stmt->execute(array(
                        ':dtime' => trim(explode(',', trim($components[0]))[0]),
                        ':event' => trim($components[1]),
                        ':username' => trim($components[2]),
                        ':ip' => trim($components[3]),
                        ':app' => trim($components[4]),
                        ':host' => trim($components[7]),
                        ':protocol' => trim($components[6]),
                        ':role' => trim($components[8]),
                        ':partnerid' => trim($components[5]),
                        ':status' => trim($components[9]),
                        ':adapterid' => trim($components[10]),
                        ':description' => trim($components[11]),
                        ':responsetime' => trim($components[12])
                    )))
                    {
                        echo "PDO error";
                        print_r($stmt->errorInfo());
                    }
                }
                // exit;
            }
            
            fclose($fh);
            rename ($dir . '/' . $logfile, $dir . '/' . $logfile .".imported");
        }
        else
        {
            echo "Failed to open file {$logfile}\n";
        }
    }
}
